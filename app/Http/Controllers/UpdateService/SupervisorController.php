<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessUser;
use App\Models\ServicePayment;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupervisorController extends Controller
{
    public function admins(): JsonResponse
    {
        $admins = BusinessUser::whereIn('role', ['admin', 'supervisor'])
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'phone'       => $u->phone ?? '',
                'role'        => $u->role,
                'is_active'   => $u->is_active ?? true,
                'permissions' => $u->permissions ?? [],
            ]);

        return response()->json($admins);
    }

    public function updateAdminPermissions(Request $request, int $id): JsonResponse
    {
        $request->validate(['permissions' => 'required|array', 'permissions.*' => 'string']);

        $admin = BusinessUser::findOrFail($id);

        if ($admin->role === 'supervisor') {
            return response()->json(['message' => 'لا يمكن تعديل صلاحيات السوبرفايزر'], 422);
        }

        $allowed = ['approve_offices', 'view_revenue', 'view_reports', 'manage_catalog'];
        $perms   = array_values(array_filter($request->permissions, fn($p) => in_array($p, $allowed)));

        $admin->update(['permissions' => $perms]);

        return response()->json(['message' => 'تم تحديث الصلاحيات', 'permissions' => $perms]);
    }

    public function toggleAdmin(int $id): JsonResponse
    {
        $admin = BusinessUser::findOrFail($id);

        if ($admin->role === 'supervisor') {
            return response()->json(['message' => 'لا يمكن تعطيل السوبرفايزر'], 422);
        }

        $admin->update(['is_active' => !($admin->is_active ?? true)]);

        return response()->json(['message' => $admin->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب', 'is_active' => $admin->is_active]);
    }

    public function createAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:business.bs_users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:20',
        ]);

        $admin = BusinessUser::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'role'        => 'admin',
            'is_active'   => true,
            'permissions' => [],
        ]);

        return response()->json(['message' => 'تم إنشاء حساب المدير', 'admin' => [
            'id'    => $admin->id,
            'name'  => $admin->name,
            'email' => $admin->email,
            'role'  => $admin->role,
        ]], 201);
    }

    public function revenueReport(Request $request): JsonResponse
    {
        $months = min((int)($request->months ?? 6), 12);

        $monthly = collect(range($months - 1, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $revenue = (float) ServiceRequest::where('status', 'done')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('price');
            $count = ServiceRequest::where('status', 'done')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->count();
            return [
                'month'   => $date->format('Y-m'),
                'label'   => $date->locale('ar')->isoFormat('MMM YYYY'),
                'revenue' => $revenue,
                'count'   => $count,
            ];
        });

        $totalRevenue  = (float) ServiceRequest::where('status', 'done')->sum('price');
        $monthRevenue  = (float) ServiceRequest::where('status', 'done')
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->sum('price');
        $pendingRevenue = (float) ServiceRequest::whereIn('status', ['pending', 'processing', 'in_progress'])->sum('price');

        return response()->json([
            'monthly'         => $monthly,
            'total_revenue'   => $totalRevenue,
            'month_revenue'   => $monthRevenue,
            'pending_revenue' => $pendingRevenue,
        ]);
    }

    public function monthlyReport(Request $request): JsonResponse
    {
        $year  = (int)($request->year  ?? now()->year);
        $month = (int)($request->month ?? now()->month);

        $base = ServiceRequest::whereYear('created_at', $year)->whereMonth('created_at', $month);

        $total      = $base->count();
        $done       = (clone $base)->where('status', 'done')->count();
        $rejected   = (clone $base)->where('status', 'rejected')->count();
        $pending    = (clone $base)->where('status', 'pending')->count();
        $processing = (clone $base)->whereIn('status', ['processing', 'in_progress'])->count();
        $revenue    = (float)(clone $base)->where('status', 'done')->sum('price');

        $topServices = ServiceRequest::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('service_id, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('govService.entity')
            ->get()
            ->map(fn($r) => [
                'name_ar'    => $r->govService?->name_ar ?? '—',
                'name_en'    => $r->govService?->name_en ?? '—',
                'entity_ar'  => $r->govService?->entity?->name_ar ?? '',
                'count'      => (int) $r->count,
                'revenue'    => (float) $r->revenue,
            ]);

        $charges = ServicePayment::where('type', 'charge')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');

        return response()->json([
            'period'     => sprintf('%04d-%02d', $year, $month),
            'total'      => $total,
            'done'       => $done,
            'rejected'   => $rejected,
            'pending'    => $pending,
            'processing' => $processing,
            'revenue'    => $revenue,
            'charges'    => (float) $charges,
            'top_services' => $topServices,
        ]);
    }
}
<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class IconController extends Controller
{
    private string $iconsDir;

    public function __construct()
    {
        $this->iconsDir = public_path('icons');
    }

    public function page(): View
    {
        return view('update_service.admin_icons');
    }

    /** GET /amrtm/api/admin/icons?q=search — list icons */
    public function list(Request $request): JsonResponse
    {
        if (! File::isDirectory($this->iconsDir)) {
            return response()->json([]);
        }

        $files = File::files($this->iconsDir);
        $q     = strtolower(trim($request->query('q', '')));

        $icons = collect($files)
            ->filter(fn($f) => in_array(strtolower($f->getExtension()), ['svg', 'png', 'jpg', 'jpeg', 'webp']))
            ->filter(fn($f) => $q === '' || str_contains(strtolower($f->getFilenameWithoutExtension()), $q))
            ->sortBy(fn($f) => $f->getFilenameWithoutExtension())
            ->map(fn($f) => [
                'name' => $f->getFilenameWithoutExtension(),
                'file' => $f->getFilename(),
                'url'  => asset('icons/' . $f->getFilename()),
                'ext'  => strtolower($f->getExtension()),
            ])
            ->values();

        return response()->json($icons);
    }

    /** POST /amrtm/api/admin/icons — upload SVG files */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'icons'   => ['required', 'array', 'max:200'],
            'icons.*' => ['required', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:512'],
        ]);

        if (! File::isDirectory($this->iconsDir)) {
            File::makeDirectory($this->iconsDir, 0755, true);
        }

        $uploaded = 0;
        $skipped  = 0;

        foreach ($request->file('icons') as $file) {
            $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $file->getClientOriginalName());
            $dest = $this->iconsDir . '/' . $name;

            // Skip duplicates
            if (File::exists($dest)) {
                $skipped++;
                continue;
            }

            $file->move($this->iconsDir, $name);
            $uploaded++;
        }

        return response()->json([
            'message'  => "تم رفع {$uploaded} أيقونة" . ($skipped ? " — تم تخطي {$skipped} موجودة مسبقاً" : ''),
            'uploaded' => $uploaded,
            'skipped'  => $skipped,
        ], 201);
    }

    /** DELETE /amrtm/api/admin/icons — delete one icon by filename */
    public function delete(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'string', 'max:200']]);

        // Security: prevent path traversal
        $filename = basename($request->file);
        $path     = $this->iconsDir . '/' . $filename;

        if (! File::exists($path)) {
            return response()->json(['message' => 'الأيقونة غير موجودة'], 404);
        }

        File::delete($path);

        return response()->json(['message' => 'تم الحذف']);
    }
}
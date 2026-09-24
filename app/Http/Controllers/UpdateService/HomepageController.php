<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\HomepageSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class HomepageController extends Controller
{
    /** GET /amrtm/admin/homepage — management page */
    public function page(): View
    {
        return view('update_service.admin_homepage');
    }

    /** GET /amrtm/api/admin/homepage/settings */
    public function getSettings(): JsonResponse
    {
        $keys = [
            'site_title',
            'site_tagline',
            'site_subtitle',
            'contract_button_text',
            'contact_phone',
            'contact_whatsapp',
            'contact_address',
            'main_office_label',
            'video_file',
            'video_poster',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = HomepageSetting::get($key);
        }

        return response()->json([
            'settings' => $settings,
            'defaults' => [
                'video_file'   => 'videos/0829.mp4',
                'video_poster' => 'images/logo2.jpg',
            ],
        ]);
    }

    /** POST /amrtm/api/admin/homepage/settings — save texts + optional file uploads */
    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'site_title'           => ['nullable', 'string', 'max:255'],
            'site_tagline'         => ['nullable', 'string', 'max:255'],
            'site_subtitle'        => ['nullable', 'string', 'max:1000'],
            'contract_button_text' => ['nullable', 'string', 'max:255'],
            'contact_phone'        => ['nullable', 'string', 'max:50'],
            'contact_whatsapp'     => ['nullable', 'string', 'max:50'],
            'contact_address'      => ['nullable', 'string', 'max:500'],
            'main_office_label'    => ['nullable', 'string', 'max:255'],
            'video_file'           => ['nullable', 'file', 'mimes:mp4,mov,mkv,webm', 'max:102400'],
            'video_poster'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:102400'],
        ]);

        $textKeys = [
            'site_title',
            'site_tagline',
            'site_subtitle',
            'contract_button_text',
            'contact_phone',
            'contact_whatsapp',
            'contact_address',
            'main_office_label',
        ];

        foreach ($textKeys as $key) {
            HomepageSetting::set($key, $request->input($key) ?? '');
        }

        if ($request->hasFile('video_file')) {
            $path = $this->storeMedia($request->file('video_file'), 'homepage/videos', ['mp4', 'mov', 'mkv', 'webm']);
            if ($path) {
                HomepageSetting::set('video_file', $path);
            }
        }

        if ($request->hasFile('video_poster')) {
            $path = $this->storeMedia($request->file('video_poster'), 'homepage/posters', ['jpg', 'jpeg', 'png', 'webp']);
            if ($path) {
                HomepageSetting::set('video_poster', $path);
            }
        }

        return response()->json(['message' => 'تم حفظ إعدادات الواجهة بنجاح']);
    }

    /** GET /amrtm/api/admin/homepage/slides */
    public function listSlides(): JsonResponse
    {
        $slides = HomepageSlide::orderBy('sort_order')->orderBy('id')->get()->map(fn ($s) => [
            'id'         => $s->id,
            'title'      => $s->title,
            'image_path' => $s->image_path,
            'image_url'  => $s->image_url,
            'link_url'   => $s->link_url,
            'is_active'  => (bool) $s->is_active,
            'sort_order' => (int) $s->sort_order,
        ]);

        return response()->json(['slides' => $slides]);
    }

    /** POST /amrtm/api/admin/homepage/slides */
    public function storeSlide(Request $request): JsonResponse
    {
        $request->validate([
            'title'     => ['nullable', 'string', 'max:255'],
            'link_url'  => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'image'     => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:102400'],  // 100MB — الضغط النهائي يتم في المتصفح
        ]);

        $path = $this->storeMedia($request->file('image'), 'homepage/slides', ['jpg', 'jpeg', 'png', 'webp']);
        if (! $path) {
            return response()->json(['message' => 'فشل حفظ صورة السلايد'], 422);
        }

        $maxOrder = (int) HomepageSlide::max('sort_order');

        $slide = HomepageSlide::create([
            'title'      => $request->input('title'),
            'image_path' => $path,
            'link_url'   => $request->input('link_url'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['message' => 'تمت إضافة السلايد بنجاح', 'slide' => $slide], 201);
    }

    /** PUT /amrtm/api/admin/homepage/slides/{id} */
    public function updateSlide(Request $request, int $id): JsonResponse
    {
        $slide = HomepageSlide::find($id);
        if (! $slide) {
            return response()->json(['message' => 'السلايد غير موجود'], 404);
        }

        $request->validate([
            'title'     => ['nullable', 'string', 'max:255'],
            'link_url'  => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'image'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:102400'],  // 100MB — الضغط النهائي يتم في المتصفح
        ]);

        $data = [
            'title'     => $request->input('title', $slide->title),
            'link_url'  => $request->input('link_url', $slide->link_url),
            'is_active' => $request->has('is_active')
                ? $request->boolean('is_active')
                : $slide->is_active,
        ];

        if ($request->hasFile('image')) {
            $path = $this->storeMedia($request->file('image'), 'homepage/slides', ['jpg', 'jpeg', 'png', 'webp']);
            if ($path) {
                $data['image_path'] = $path;
            }
        }

        $slide->update($data);

        return response()->json(['message' => 'تم تحديث السلايد بنجاح']);
    }

    /** POST /amrtm/api/admin/homepage/slides/reorder */
    public function reorderSlides(Request $request): JsonResponse
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($request->input('order') as $index => $id) {
            HomepageSlide::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'تم تحديث الترتيب بنجاح']);
    }

    /** POST /amrtm/api/admin/homepage/slides/{id}/toggle */
    public function toggleSlide(int $id): JsonResponse
    {
        $slide = HomepageSlide::find($id);
        if (! $slide) {
            return response()->json(['message' => 'السلايد غير موجود'], 404);
        }

        $slide->update(['is_active' => ! $slide->is_active]);

        return response()->json(['message' => 'تم تغيير حالة السلايد']);
    }

    /** DELETE /amrtm/api/admin/homepage/slides/{id} */
    public function deleteSlide(int $id): JsonResponse
    {
        $slide = HomepageSlide::find($id);
        if (! $slide) {
            return response()->json(['message' => 'السلايد غير موجود'], 404);
        }

        if ($this->isStoredPath($slide->image_path)) {
            $full = storage_path('app/public/' . $slide->image_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $slide->delete();

        return response()->json(['message' => 'تم حذف السلايد']);
    }

    /** Move an uploaded file into storage and return the relative path. */
    private function storeMedia($file, string $dir, array $allowed): ?string
    {
        try {
            $name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs($dir, $name, 'public');

            return $path;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Homepage media store failed: ' . $e->getMessage());

            return null;
        }
    }

    private function isStoredPath(string $path): bool
    {
        return str_starts_with($path, 'homepage/');
    }
}

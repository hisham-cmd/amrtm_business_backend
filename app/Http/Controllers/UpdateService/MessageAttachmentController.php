<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\OfficeMessage;
use App\Models\ServiceRequest;
use App\Support\MessageAttachmentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * تقديم مرفقات المحادثات عبر نقاط نهاية مصادق عليها.
 *
 * الصلاحيات:
 *  - مستخدم (business): مالك الطلب أو أدمن/مشرف.
 *  - مكتب (office): المكتب المسند للطلب فقط.
 * المرفق لا يُصرف إلا إذا كان ضمن مرفقات الرسالة الواقعة على الطلب نفسه.
 */
class MessageAttachmentController extends Controller
{
    public function show(Request $request, int $requestId, int $messageId, string $file)
    {
        $message = OfficeMessage::where('request_id', $requestId)
            ->find($messageId);

        if ($message === null) {
            abort(404, 'الرسالة غير موجودة');
        }

        $sr = ServiceRequest::select('id', 'user_id', 'office_id')->find($requestId);

        if ($sr === null) {
            abort(404, 'الطلب غير موجود');
        }

        if (! $this->canAccess($sr)) {
            abort(403, 'غير مصرح لك بالاطلاع على هذا المرفق.');
        }

        $attachments = (array) $message->attachments;
        $match = null;

        foreach ($attachments as $attachment) {
            if (($attachment['path'] ?? null) === $file) {
                $match = $attachment;
                break;
            }
        }

        if ($match === null || ! MessageAttachmentStorage::exists($file)) {
            abort(404, 'الملف غير موجود');
        }

        return MessageAttachmentStorage::response($file, (string) ($match['name'] ?? $file));
    }

    private function canAccess(ServiceRequest $sr): bool
    {
        $officeAuth = Auth::guard('office')->user();
        $businessAuth = Auth::guard('business')->user();

        if ($officeAuth !== null) {
            return (int) $sr->office_id === (int) $officeAuth->office_id;
        }

        if ($businessAuth !== null) {
            if ($businessAuth->isAdmin()) {
                return true;
            }

            return (int) $sr->user_id === (int) $businessAuth->id;
        }

        return false;
    }
}
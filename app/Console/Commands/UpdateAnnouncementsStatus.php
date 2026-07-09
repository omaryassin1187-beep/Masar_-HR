<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\Announcements\NewAnnouncementNotification;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable; // لمنع التداخل على مستوى السيرفر
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class UpdateAnnouncementsStatus extends Command implements Isolatable
{
    protected $signature = 'announcements:update-status';
    protected $description = 'يحدّث حالة التعميمات بالتوقيت المناسب مع معالجة الإشعارات بأداء عالٍ وآمن';

    public function handle(): int
    {
        $now = now();
        $activatedCount = 0;

        // 1️⃣ جلب وتحديث التعميمات التي أصبحت نشطة بأمان (Pessimistic Locking)
        // نستخدم الترانزاكشن والقفل لضمان عدم التقاطها من أي Process أخرى بالتزامن
        $activatedAnnouncements = DB::transaction(function () use ($now) {
            $announcements = Announcement::where('status', Announcement::STATUS_SCHEDULED)
                ->where('starts_at', '<=', $now)
                ->lockForUpdate() // قفل الأسطر المحددة
                ->get();

            foreach ($announcements as $announcement) {
                $announcement->update(['status' => Announcement::STATUS_ACTIVE]);
            }

            return $announcements;
        });

        // 2️⃣ إرسال الإشعارات خارج الـ Transaction لعدم قفل قاعدة البيانات لفترة طويلة
        foreach ($activatedAnnouncements as $announcement) {
            try {
                $this->sendNotificationsToAudience($announcement);
                $activatedCount++;
            } catch (\Throwable $e) {
                // في حال فشل إرسال إشعار لتعميم، نسجل الخطأ وننتقل للتعميم التالي دون انهيار النظام
                Log::error("Failed to dispatch notifications for announcement #{$announcement->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // 3️⃣ إنهاء التعميمات المنتهية بـ Bulk Update
        $expiredCount = Announcement::where('status', Announcement::STATUS_ACTIVE)
            ->where('expires_at', '<', $now)
            ->update(['status' => Announcement::STATUS_EXPIRED]);

        if ($activatedCount > 0 || $expiredCount > 0) {
            Log::info("Announcements status update pipeline executed.", [
                'activated' => $activatedCount,
                'expired' => $expiredCount
            ]);
        }

        $this->info("تم تفعيل {$activatedCount} تعميم وإنهاء {$expiredCount} تعميم بنجاح.");

        return self::SUCCESS;
    }

    private function sendNotificationsToAudience(Announcement $announcement): void
    {
        $query = User::query();

        switch ($announcement->target_audience) {
            case Announcement::AUDIENCE_DEPARTMENT:
                $query->where('dep_id', $announcement->department_id);
                break;

            case Announcement::AUDIENCE_MANAGERS:
                // تنبيه: تأكد هندسياً من وجود الـ Scope داخل مودل User
                if (method_exists(User::class, 'scopeRole')) {
                    $query->role('manager');
                } else {
                    Log::warning("Scope 'role' not found on User model. Skipping manager filtering.");
                    return;
                }
                break;

            case Announcement::AUDIENCE_ALL:
            default:
                break;
        }
        // ✅ أضيفي هالـ Log هنا
        Log::info("📢 Debug: Users count before sending", [
            'announcement_id' => $announcement->id,
            'target_audience' => $announcement->target_audience,
            'users_count' => $query->count(),
        ]);

        // استخدام chunkById أداءه أفضل بكثير مع الجداول الكبيرة لأنه يعتمد على الاستعلام المباشر (> id)
        $query->chunkById(500, function ($users) use ($announcement) {
            Notification::send($users, new NewAnnouncementNotification($announcement));
        });

        Log::info("📢 Notification dispatch completed for announcement ID: {$announcement->id}");
    }
}

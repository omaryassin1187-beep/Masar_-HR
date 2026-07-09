<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\Announcements\ManagerAnnouncementCreatedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AnnouncementService
{

public function create(array $data, User $author): Announcement
{
    return DB::transaction(function () use ($data, $author) {
        $data['author_id'] = $author->id;
        $data['status'] = $this->computeStatus(
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['expires_at']),
        );

        $announcement = Announcement::create($data);

        // ✅ إرسال إشعار لـ HR إذا كان المنشئ Manager
        if ($author->hasRole('manager')) {
            $hrUsers = User::role('HR')->get();
            Notification::send($hrUsers, new ManagerAnnouncementCreatedNotification(
                $announcement,
                $author->full_name
            ));
        }

        return $announcement;
    });
}



    public function update(Announcement $announcement, array $data): Announcement
    {
        return DB::transaction(function () use ($announcement, $data) {
            $announcement->fill($data);

            $announcement->status = $this->computeStatus(
                $announcement->starts_at,
                $announcement->expires_at,
            );

            $announcement->save();

            return $announcement->fresh();
        });
    }


public function publish(Announcement $announcement): Announcement
{
    if ($announcement->status === Announcement::STATUS_ACTIVE) {
        throw new \Exception('This announcement is already published.', 422);
    }

    return DB::transaction(function () use ($announcement) {
        $announcement->starts_at = now();
        $announcement->status = Announcement::STATUS_ACTIVE;
        $announcement->save();

        return $announcement->fresh();
    });
}

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }


    public function computeStatus(Carbon $startsAt, Carbon $expiresAt): string
    {
        $now = now();

        if ($now->lt($startsAt)) {
            return Announcement::STATUS_SCHEDULED;
        }

        if ($now->gt($expiresAt)) {
            return Announcement::STATUS_EXPIRED;
        }

        return Announcement::STATUS_ACTIVE;
    }
}

<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if ($user->hasAnyRole(['admin', 'HR'])) {
            return true;
        }

        if ($announcement->author_id === $user->id) {
            return true;
        }

        return $announcement->status === Announcement::STATUS_ACTIVE
            && $this->isTargeted($user, $announcement);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'HR', 'manager']);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('HR')) {
            $author = $announcement->author;
            return $author && ($author->id === $user->id || $author->hasRole('manager'));
        }

        return $user->hasRole('manager') && $announcement->author_id === $user->id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('HR')) {
            $author = $announcement->author;
            return $author && ($author->id === $user->id || $author->hasRole('manager'));
        }

        return $user->hasRole('manager') && $announcement->author_id === $user->id;
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('HR')) {
            $author = $announcement->author;
            return $author && ($author->id === $user->id || $author->hasRole('manager'));
        }

        if ($user->hasRole('manager')) {
            return $announcement->author_id === $user->id;
        }

        return false;
    }

    private function isTargeted(User $user, Announcement $announcement): bool
    {
        return match ($announcement->target_audience) {
            Announcement::AUDIENCE_ALL => true,
            Announcement::AUDIENCE_DEPARTMENT => $announcement->department_id === $user->department_id,
            Announcement::AUDIENCE_MANAGERS => $user->hasRole('manager'),
            default => false,
        };
    }
}

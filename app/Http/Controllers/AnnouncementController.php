<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcements\StoreAnnouncementRequest;
use App\Http\Requests\Announcements\UpdateAnnouncementRequest;
use App\Http\Resources\Announcements\AnnouncementResource;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
        use AuthorizesRequests;

    public function __construct(
        private readonly AnnouncementService $announcementService,
    ) {}


public function index(Request $request): AnonymousResourceCollection
{
    $this->authorize('viewAny', Announcement::class);
    $user = $request->user();

    $query = Announcement::with(['author', 'department'])->latest();

    if (!$user->hasAnyRole(['admin', 'HR'])) {
        // حماية منطق الـ Scope باستخدام Advanced Where Clauses لحصر تأثير الـ OR
        $query->where(function ($subQuery) use ($user) {
            $subQuery->where(function ($q) use ($user) {
                $q->active()->forUser($user);
            })->orWhere('author_id', $user->id);
        });
    }

    $announcements = $query->paginate(15);

    return AnnouncementResource::collection($announcements);
}


    public function announcementsActive(Request $request): AnonymousResourceCollection
    {
        $announcements = Announcement::with(['author', 'department'])
            ->active()
            ->forUser($request->user())
            ->latest('starts_at')
            ->get();

        return AnnouncementResource::collection($announcements);
    }


    public function show(Announcement $announcement): AnnouncementResource
    {
        $this->authorize('view', $announcement);

        $announcement->load(['author', 'department']);

        return new AnnouncementResource($announcement);
    }


    public function store(StoreAnnouncementRequest $request): AnnouncementResource
    {
        $announcement = $this->announcementService->create(
            $request->validated(),
            $request->user(),
        );

        return new AnnouncementResource($announcement->load(['author', 'department']));
    }


    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): AnnouncementResource
    {
        $announcement = $this->announcementService->update(
            $announcement,
            $request->validated(),
        );

        return new AnnouncementResource($announcement->load(['author', 'department']));
    }


    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);

        $this->announcementService->delete($announcement);

        return response()->json(['message' => 'Announcement deleted successfully.']);
    }


  public function publish(Announcement $announcement): AnnouncementResource
{
    $this->authorize('publish', $announcement);

    $announcement = $this->announcementService->publish($announcement);

    return new AnnouncementResource($announcement->load(['author', 'department']));
}



public function getAnnouncementsStats(): JsonResponse
    {
        $total = Announcement::count();
        $active = Announcement::active()->count();
        $highPriority = Announcement::where('priority', Announcement::PRIORITY_HIGH)->count();
        $expired = Announcement::where('status', Announcement::STATUS_EXPIRED)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'active' => $active,
                'high_priority' => $highPriority,
                'expired' => $expired,
            ]
        ], 200);
    }
}

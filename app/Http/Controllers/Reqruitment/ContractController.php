<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Document;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    use AuthorizesRequests;

    public function show(): JsonResponse
    {
        $user     = auth()->user();
        $contract = $user->contract()->with('offer')->firstOrFail();

        $this->authorize('view', $contract);

        return response()->json(new ContractResource($contract));
    }
    public function download(): Response
    {
        $user     = auth()->user();
        $contract = $user->contract()->with(['offer', 'user'])->firstOrFail();

        $this->authorize('view', $contract);

        $pdf = Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
            'user'     => $user,
            'jobTitle' => $contract->offer->jobPosting->requisition->job_title,

        ])->setPaper('a4', 'portrait');

        return $pdf->download("contract-{$contract->id}.pdf");
    }
    public function documents(): JsonResponse
    {
        $user      = auth()->user();
        $documents = $user->documents()->get();

        return response()->json($documents);
    }
    public function downloadDocument(Document $document)
    {
        $user = auth()->user();
        if ($user->hasRole('employee') && $document->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }
        return response()->download(
            Storage::disk('private')->path($document->file_path),
            $document->file_name
        );
    }
    public function showForEmployee(User $user): JsonResponse
{
    $contract = $user->contract()->firstOrFail();

    $this->authorize('view', $contract);

    return response()->json(new ContractResource($contract));
}
public function downloadForEmployee(User $user): Response
{
    $contract = $user->contract()->firstOrFail();

    $this->authorize('view', $contract);

    $pdf = Pdf::loadView('contracts.pdf', [
        'contract' => $contract,
        'user'     => $user,
        'jobTitle' => $contract->offer->jobPosting->requisition->job_title,
    ])->setPaper('a4', 'portrait');

    return $pdf->download("contract-{$contract->id}.pdf");
}

public function documentsForEmployee(User $user): JsonResponse
{
    return response()->json($user->documents()->get());
}
}

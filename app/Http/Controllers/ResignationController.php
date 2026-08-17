<?php
namespace App\Http\Controllers;

use App\Exceptions\ResignationException;
use App\Http\Requests\Resignation\ClassifyResignationRequest;
use App\Http\Requests\Resignation\StoreResignationRequest;
use App\Http\Resources\Resignation\ResignationResource;
use App\Models\Document;
use App\Models\Resignation;
use App\Services\ResignationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class ResignationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ResignationService $service) {}

    public function store(StoreResignationRequest $request): ResignationResource
    {
        $resignation = $this->service->submit($request->user(), $request->validated());

        return new ResignationResource($resignation);
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $resignations = Resignation::where('user_id', $request->user()->id)->latest()->get();

        return ResignationResource::collection($resignations);
    }

    /**
     * عرض تفاصيل طلب استقالة واحد كاملة، متضمنة المستندات المرفقة.
     */
    public function show(int $resignation): ResignationResource
    {
        $resignationModel = Resignation::with([
            'employee',
            'classifiedBy',
            'settlement',
            'documents',
        ])->find($resignation);

        if (! $resignationModel) {
            throw ResignationException::notFound();
        }

        $this->authorize('view', $resignationModel);

        return new ResignationResource($resignationModel);
    }

    public function downloadDocument(int $resignation, int $document)
    {
        $resignationModel = Resignation::find($resignation);

        if (! $resignationModel) {
            throw ResignationException::notFound();
        }

        $this->authorize('view', $resignationModel);

        $documentModel = Document::where('id', $document)
            ->where('owner_type', Resignation::class)
            ->where('owner_id', $resignationModel->id)
            ->first();

        if (! $documentModel || ! Storage::disk('private')->exists($documentModel->file_path)) {
            throw ResignationException::documentNotFound();
        }

        return Storage::disk('private')->download(
            $documentModel->file_path,
            $documentModel->file_name
        );
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Resignation::class);

        return ResignationResource::collection(
            $this->service->listForHr($request->only('type'))
        );
    }

   public function classify(ClassifyResignationRequest $request, Resignation $resignation): ResignationResource
{
    $this->authorize('classify', $resignation);

    $updatedResignation = $this->service->classify(
        $request->user(),
        $resignation,
        $request->validated()
    );

    return new ResignationResource($updatedResignation);
}
}

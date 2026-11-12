<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CauLacBoRequest;
use App\Http\Resources\CauLacBoResource;
use App\Services\CauLacBoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CauLacBoApiController extends Controller
{
    public function __construct(private readonly CauLacBoService $cauLacBoService) {}

    public function index(): AnonymousResourceCollection
    {
        return CauLacBoResource::collection($this->cauLacBoService->getAll());
    }

    public function show(string $caulacbo): CauLacBoResource
    {
        return new CauLacBoResource($this->cauLacBoService->getById($caulacbo));
    }

    public function store(CauLacBoRequest $request): JsonResponse
    {
        return (new CauLacBoResource($this->cauLacBoService->create($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function update(CauLacBoRequest $request, string $caulacbo): CauLacBoResource
    {
        return new CauLacBoResource(
            $this->cauLacBoService->update($caulacbo, $request->validated()),
        );
    }

    public function destroy(string $caulacbo): JsonResponse
    {
        $this->cauLacBoService->delete($caulacbo);

        return response()->json(['message' => 'Đã xóa câu lạc bộ thành công.']);
    }
}

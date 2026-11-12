<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuKienRequest;
use App\Http\Resources\SuKienResource;
use App\Services\SuKienService;
use Illuminate\Http\JsonResponse;

class SuKienApiController extends Controller
{
    protected SuKienService $suKienService;

    public function __construct(SuKienService $suKienService)
    {
        $this->suKienService = $suKienService;
    }

    // GET /api/sukien
    public function index()
    {
        $suKiens = $this->suKienService->getAll();

        return SuKienResource::collection($suKiens);
    }

    // GET /api/sukien/{id}
    public function show(string $id)
    {
        $suKien = $this->suKienService->getById($id);

        return new SuKienResource($suKien);
    }

    // POST /api/sukien
    public function store(SuKienRequest $request): JsonResponse
    {
        $suKien = $this->suKienService->create($request->validated());

        return (new SuKienResource($suKien))
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/sukien/{id}
    public function update(SuKienRequest $request, string $id)
    {
        $suKien = $this->suKienService->update($id, $request->validated());

        return new SuKienResource($suKien);
    }

    // DELETE /api/sukien/{id}
    public function destroy(string $id): JsonResponse
    {
        $this->suKienService->delete($id);

        return response()->json([
            'message' => 'Đã xóa sự kiện thành công!',
        ], 200);
    }
}

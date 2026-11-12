<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkSuKienRequest;
use App\Http\Requests\SuKienRequest;
use App\Models\NguoiDung;
use App\Services\CauLacBoService;
use App\Services\SuKienService;
use Illuminate\Http\Request;

class SuKienController extends Controller
{
    protected SuKienService $suKienService;

    public function __construct(
        SuKienService $suKienService,
        protected CauLacBoService $cauLacBoService,
    ) {
        $this->suKienService = $suKienService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clubId = $request->integer('ma_clb') ?: null;
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($clubId !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $clubId)) {
            abort(403, 'Bạn không có quyền quản lý sự kiện của câu lạc bộ này.');
        }

        return view('sukien.index', [
            ...$this->suKienService->getIndexData(
                $request->query('trang_thai'),
                $request->query('q'),
                $request->boolean('marked'),
                $clubId,
            ),
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData(
                $clubId,
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clubId = $request->integer('ma_clb') ?: null;
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($clubId !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $clubId)) {
            abort(403, 'Bạn không có quyền tạo sự kiện cho câu lạc bộ này.');
        }

        return view('sukien.create');
    }

    public function show(string $id)
    {
        return view('sukien.show', [
            'suKien' => $this->suKienService->getById($id),
        ]);
    }

    public function attendance(Request $request, string $id)
    {
        $event = $this->suKienService->getById($id);
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($event->MaCLB !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $event->MaCLB)) {
            abort(403, 'Bạn không có quyền quản lý điểm danh sự kiện của câu lạc bộ này.');
        }

        return view('sukien.attendance', [
            'suKien' => $event,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SuKienRequest $request)
    {
        $clubId = $request->integer('MaCLB') ?: null;
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($clubId !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $clubId)) {
            abort(403, 'Bạn không có quyền tạo sự kiện cho câu lạc bộ này.');
        }

        $event = $this->suKienService->create($request->validated());

        return redirect()->route('sukien.index', $event->MaCLB ? ['ma_clb' => $event->MaCLB] : [])
            ->with('success', 'Đã thêm sự kiện thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $event = $this->suKienService->getById($id);
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($event->MaCLB !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $event->MaCLB)) {
            abort(403, 'Bạn không có quyền chỉnh sửa sự kiện của câu lạc bộ này.');
        }

        return view('sukien.edit', $this->suKienService->getEditData($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SuKienRequest $request, string $id)
    {
        $event = $this->suKienService->getById($id);
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($event->MaCLB !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $event->MaCLB)) {
            abort(403, 'Bạn không có quyền cập nhật sự kiện của câu lạc bộ này.');
        }

        $newClubId = $request->integer('MaCLB') ?: null;
        if ($newClubId !== null && $newClubId !== $event->MaCLB && $user !== null && ! $this->cauLacBoService->canManageClub($user, $newClubId)) {
            abort(403, 'Bạn không có quyền chuyển sự kiện sang câu lạc bộ này.');
        }

        $updatedEvent = $this->suKienService->update($id, $request->validated());

        return redirect()->route('sukien.index', $updatedEvent->MaCLB ? ['ma_clb' => $updatedEvent->MaCLB] : [])
            ->with('success', 'Đã cập nhật sự kiện thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $event = $this->suKienService->getById($id);
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($event->MaCLB !== null && $user !== null && ! $this->cauLacBoService->canManageClub($user, $event->MaCLB)) {
            abort(403, 'Bạn không có quyền xóa sự kiện của câu lạc bộ này.');
        }

        $clubId = $this->suKienService->deleteAndGetClubId($id);

        return redirect()->route('sukien.index', $clubId ? ['ma_clb' => $clubId] : [])
            ->with('success', 'Đã xóa sự kiện thành công!');
    }

    public function bulk(BulkSuKienRequest $request)
    {
        $validated = $request->validated();
        $message = $this->suKienService->performBulkAction(
            $validated['event_ids'],
            $validated['action'],
        );

        return redirect()->route('sukien.index', $request->only('trang_thai', 'marked', 'q', 'page', 'ma_clb'))
            ->with('success', $message);
    }
}

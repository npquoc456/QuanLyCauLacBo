<?php

namespace App\Http\Controllers;

use App\Enums\ThanhVienTrangThai;
use App\Http\Requests\CauLacBoRequest;
use App\Models\NguoiDung;
use App\Services\CauLacBoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CauLacBoController extends Controller
{
    public function __construct(private readonly CauLacBoService $cauLacBoService) {}

    public function index(Request $request): View
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;

        if ($request->query('tab') === 'joined') {
            return view('caulacbo.index', [
                'tab' => 'joined',
                ...$this->cauLacBoService->getJoinedClubsData($request->query('q'), $user),
            ]);
        }

        return view('caulacbo.index', $this->cauLacBoService->getManagementData(
            $request->query('q'),
            $request->query('filter'),
            $request->query('category'),
            $user,
        ) + [
            'tab' => 'managed',
            'joinedClubCount' => $this->cauLacBoService->getJoinedClubCount($user),
        ]);
    }

    public function members(Request $request, string $caulacbo): View
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user !== null && ! $this->cauLacBoService->canManageClub($user, (int) $caulacbo)) {
            abort(403, 'Bạn không có quyền quản lý câu lạc bộ này.');
        }

        return view('caulacbo.members', [
            ...$this->cauLacBoService->getClubMembersData(
                $caulacbo,
                $request->query('q'),
                $request->query('role'),
                $request->query('status'),
            ),
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
            'canManage' => $user === null || $this->cauLacBoService->canManageClub($user, (int) $caulacbo),
            'isOwner' => $user === null || $this->cauLacBoService->isClubOwner($user, (int) $caulacbo),
        ]);
    }

    public function store(CauLacBoRequest $request): RedirectResponse
    {
        $club = $this->cauLacBoService->create($request->validated());

        return redirect()
            ->route('caulacbo.index')
            ->withFragment('club-'.$club->MaCLB)
            ->with('success', 'Đã tạo câu lạc bộ.');
    }

    public function update(CauLacBoRequest $request, string $caulacbo): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user !== null && ! $this->cauLacBoService->canManageClub($user, (int) $caulacbo)) {
            abort(403, 'Bạn không có quyền quản lý câu lạc bộ này.');
        }

        $this->cauLacBoService->update($caulacbo, $request->validated());

        return redirect()->route('caulacbo.index')->with('success', 'Đã cập nhật câu lạc bộ.');
    }

    public function destroy(Request $request, string $caulacbo): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user !== null && ! $this->cauLacBoService->canManageClub($user, (int) $caulacbo)) {
            abort(403, 'Bạn không có quyền quản lý câu lạc bộ này.');
        }

        $this->cauLacBoService->delete($caulacbo);

        return redirect()->route('caulacbo.index')->with('success', 'Đã xóa câu lạc bộ.');
    }

    public function updateMember(Request $request, string $caulacbo, string $thanhVien): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user !== null && ! $this->cauLacBoService->canManageClub($user, (int) $caulacbo)) {
            abort(403, 'Bạn không có quyền quản lý câu lạc bộ này.');
        }

        $validated = $request->validate([
            'ChucVu' => ['required', 'string', 'max:100'],
            'TrangThai' => ['sometimes', 'nullable', 'string', Rule::enum(ThanhVienTrangThai::class)],
        ]);

        $status = $validated['TrangThai'] ?? null;
        $this->cauLacBoService->updateMemberRole(
            (int) $caulacbo,
            (int) $thanhVien,
            $validated['ChucVu'],
            $status,
        );

        $message = match ($status) {
            ThanhVienTrangThai::DANG_HOAT_DONG->value => 'Đã phê duyệt chính thức thành viên vào câu lạc bộ.',
            ThanhVienTrangThai::DA_ROI_CLB->value => 'Đã từ chối tiếp nhận ứng viên vào câu lạc bộ.',
            default => 'Đã cập nhật vai trò và quyền hạn của thành viên.',
        };

        return redirect()
            ->route('caulacbo.members', $caulacbo)
            ->with('success', $message);
    }

    public function destroyMember(Request $request, string $caulacbo, string $thanhVien): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user === null) {
            abort(401);
        }

        $this->cauLacBoService->destroyMember((int) $caulacbo, (int) $thanhVien, $user);

        return redirect()
            ->route('caulacbo.members', $caulacbo)
            ->with('success', 'Đã xóa thành viên khỏi câu lạc bộ thành công.');
    }

    public function requestLeave(Request $request, string $caulacbo, string $thanhVien): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user === null) {
            abort(401);
        }

        $this->cauLacBoService->requestLeaveClub((int) $caulacbo, (int) $thanhVien, $user);

        return redirect()
            ->route('caulacbo.members', $caulacbo)
            ->with('success', 'Đã gửi yêu cầu rời câu lạc bộ. Vui lòng chờ Chủ quản phê duyệt.');
    }

    public function approveLeave(Request $request, string $caulacbo, string $thanhVien): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user === null) {
            abort(401);
        }

        $this->cauLacBoService->approveLeaveClub((int) $caulacbo, (int) $thanhVien, $user);

        return redirect()
            ->route('caulacbo.members', $caulacbo)
            ->with('success', 'Đã phê duyệt cho thành viên rời câu lạc bộ.');
    }

    public function rejectLeave(Request $request, string $caulacbo, string $thanhVien): RedirectResponse
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;
        if ($user === null) {
            abort(401);
        }

        $membership = $this->cauLacBoService->rejectLeaveClub((int) $caulacbo, (int) $thanhVien, $user);

        $msg = $membership->MaNguoiDung === $user->MaNguoiDung
            ? 'Đã hủy yêu cầu rời câu lạc bộ.'
            : 'Đã từ chối yêu cầu rời và giữ lại thành viên trong CLB.';

        return redirect()
            ->route('caulacbo.members', $caulacbo)
            ->with('success', $msg);
    }
}

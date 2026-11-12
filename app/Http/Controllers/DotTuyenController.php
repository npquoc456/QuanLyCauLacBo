<?php

namespace App\Http\Controllers;

use App\Enums\DonUngTrangThai;
use App\Http\Requests\ApplyDotTuyenRequest;
use App\Http\Requests\DotTuyenThanhVienRequest;
use App\Http\Requests\ReviewDonUngTuyenRequest;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Services\CauLacBoService;
use App\Services\DotTuyenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DotTuyenController extends Controller
{
    public function __construct(
        private readonly DotTuyenService $dotTuyenService,
        private readonly CauLacBoService $cauLacBoService,
    ) {}

    public function index(Request $request, string $caulacbo): View
    {
        $user = $this->authenticatedUser($request);

        return view('dot-tuyen.index', [
            ...$this->dotTuyenService->getManagementData(
                (int) $caulacbo,
                $user,
                $request->query('q'),
                $request->query('status'),
            ),
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
        ]);
    }

    public function create(Request $request, string $caulacbo): View
    {
        $user = $this->authenticatedUser($request);

        return view('dot-tuyen.create', [
            ...$this->dotTuyenService->getCreateData((int) $caulacbo, $user),
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
        ]);
    }

    public function edit(Request $request, string $caulacbo, string $dotTuyen): View
    {
        $user = $this->authenticatedUser($request);

        return view('dot-tuyen.create', [
            ...$this->dotTuyenService->getEditData((int) $caulacbo, (int) $dotTuyen, $user),
            'editing' => true,
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
        ]);
    }

    public function store(DotTuyenThanhVienRequest $request, string $caulacbo): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $this->dotTuyenService->create(
            (int) $caulacbo,
            $user,
            $request->validated(),
            $request->file('poster'),
            $request->validated('action') === 'draft',
        );

        return redirect()
            ->route('caulacbo.recruitments.index', $caulacbo)
            ->with('success', $request->validated('action') === 'draft'
                ? 'Đã lưu bản nháp. Bản nháp chưa hiển thị trên trang chủ.'
                : 'Đã tạo đợt tuyển. Tin tuyển thành viên đã được đăng trên trang chủ.');
    }

    public function update(DotTuyenThanhVienRequest $request, string $caulacbo, string $dotTuyen): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $this->dotTuyenService->update(
                (int) $caulacbo,
                (int) $dotTuyen,
                $user,
                $request->validated(),
                $request->file('poster'),
                $request->validated('action') === 'draft',
        );

        return redirect()
                ->route('caulacbo.recruitments.index', $caulacbo)
                ->with('success', $request->validated('action') === 'draft'
                    ? 'Đã lưu thay đổi dưới dạng bản nháp.'
                    : 'Đã cập nhật đợt tuyển.');
    }

    public function destroy(Request $request, string $caulacbo, string $dotTuyen): RedirectResponse
    {
        $this->dotTuyenService->delete(
                (int) $caulacbo,
                (int) $dotTuyen,
                $this->authenticatedUser($request),
        );

        return redirect()
                ->route('caulacbo.recruitments.index', $caulacbo)
                ->with('success', 'Đã xóa đợt tuyển.');
    }

    public function show(Request $request, string $dotTuyen): View
    {
        $user = $request->user() instanceof NguoiDung ? $request->user() : null;

        return view('dot-tuyen.show', [
            'campaign' => $this->dotTuyenService->getPublicCampaign((int) $dotTuyen, $user),
        ]);
    }

    public function apply(ApplyDotTuyenRequest $request, string $dotTuyen): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $campaign = DotTuyenThanhVien::query()->findOrFail($dotTuyen);
        $this->dotTuyenService->apply(
            $campaign,
            $user,
            (int) $request->validated('MaViTri'),
            $request->validated('NoiDung'),
            $request->file('resume'),
        );

        return redirect()
            ->route('dot-tuyen.show', $dotTuyen)
            ->with('success', 'Ứng tuyển thành công. Câu lạc bộ sẽ liên hệ với bạn khi hồ sơ được xem.');
    }

    public function applications(Request $request, string $caulacbo, string $dotTuyen): View
    {
        $user = $this->authenticatedUser($request);

        return view('dot-tuyen.applications', [
            ...$this->dotTuyenService->getApplicationsData(
                (int) $caulacbo,
                (int) $dotTuyen,
                $user,
                $request->query('status'),
            ),
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
        ]);
    }

    public function application(Request $request, string $caulacbo, string $donUngTuyen): View
    {
        $user = $this->authenticatedUser($request);
        $application = $this->dotTuyenService->getApplicationForManager(
            (int) $caulacbo,
            (int) $donUngTuyen,
            $user,
        );

        return view('dot-tuyen.application', [
            'club' => $this->dotTuyenService->managedClub((int) $caulacbo, $user),
            'application' => $application,
            'managementNavigation' => $this->cauLacBoService->getManagementNavigationData((int) $caulacbo),
        ]);
    }

    public function review(ReviewDonUngTuyenRequest $request, string $caulacbo, string $donUngTuyen): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $this->dotTuyenService->updateApplicationStatus(
            (int) $caulacbo,
            (int) $donUngTuyen,
            $user,
            DonUngTrangThai::from($request->validated('TrangThai')),
            $request->validated('GhiChu'),
        );

        return redirect()
            ->route('caulacbo.recruitments.application', [$caulacbo, $donUngTuyen])
            ->with('success', 'Đã cập nhật trạng thái hồ sơ ứng tuyển.');
    }

    public function resume(Request $request, string $caulacbo, string $donUngTuyen): StreamedResponse
    {
        $application = $this->dotTuyenService->getResumeForManager(
            (int) $caulacbo,
            (int) $donUngTuyen,
            $this->authenticatedUser($request),
        );
        abort_if(! $application->URLHoSo || ! Storage::disk('local')->exists($application->URLHoSo), 404);

        $extension = pathinfo($application->URLHoSo, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($application->URLHoSo, $extension ? 'ho-so.'.$extension : 'ho-so');
    }

    private function authenticatedUser(Request $request): NguoiDung
    {
        $user = $request->user();
        abort_unless($user instanceof NguoiDung, 403);

        return $user;
    }
}

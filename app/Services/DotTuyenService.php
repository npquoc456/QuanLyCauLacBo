<?php

namespace App\Services;

use App\Enums\DonUngTrangThai;
use App\Enums\DotTuyenTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Models\Ban;
use App\Models\CauLacBo;
use App\Models\DonUngTuyen;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Notifications\RecruitmentApplicationReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DotTuyenService
{
    /**
     * @return array<string, mixed>
     */
    public function getManagementData(int $clubId, NguoiDung $user, ?string $search, ?string $status): array
    {
        $club = $this->managedClub($clubId, $user);
        $search = trim($search ?? '');
        $status = in_array($status, ['all', 'open', 'upcoming', 'closed', 'draft', 'DANG_TUYEN', 'CHUA_TUYEN', 'starred'], true) ? $status : 'all';
        $query = DotTuyenThanhVien::query()
            ->where('MaCLB', $clubId)
            ->with(['viTriTuyens'])
            ->withCount('viTriTuyens')
            ->when($search !== '', fn (Builder $builder) => $builder->where(
                fn (Builder $searchQuery) => $searchQuery->where('TenDotTuyen', 'like', "%{$search}%")
                    ->orWhere('MoTa', 'like', "%{$search}%"),
            ));

        $now = now();
        match ($status) {
            'open', 'DANG_TUYEN' => $query->where('LaBanNhap', false)->where(function ($q) use ($now) {
                $q->where('TrangThai', DotTuyenTrangThai::DANG_TUYEN->value)
                    ->orWhere(function ($sub) use ($now) {
                        $sub->where('NgayBatDau', '<=', $now)->where('NgayKetThuc', '>', $now);
                    });
            }),
            'upcoming', 'CHUA_TUYEN' => $query->where('LaBanNhap', false)->where(function ($q) use ($now) {
                $q->where('TrangThai', DotTuyenTrangThai::CHUA_TUYEN->value)
                    ->orWhere('NgayBatDau', '>', $now);
            }),
            'closed' => $query->where('LaBanNhap', false)->where('NgayKetThuc', '<=', $now),
            'draft' => $query->where('LaBanNhap', true),
            'starred' => $query->whereRaw('0 = 1'),
            default => null,
        };

        $campaigns = $query->orderByDesc('created_at')->paginate(8)->withQueryString();
        $campaignIds = $campaigns->getCollection()->pluck('MaDotTuyen');
        $applicationCounts = DB::table('don_ung_tuyens')
            ->join('vi_tri_tuyens', 'don_ung_tuyens.MaViTri', '=', 'vi_tri_tuyens.MaViTri')
            ->whereIn('vi_tri_tuyens.MaDotTuyen', $campaignIds)
            ->where(function ($query): void {
                $query->whereNull('don_ung_tuyens.TrangThai')
                    ->orWhere('don_ung_tuyens.TrangThai', '!=', DonUngTrangThai::DA_HUY->value);
            })
            ->groupBy('vi_tri_tuyens.MaDotTuyen')
            ->selectRaw('vi_tri_tuyens.MaDotTuyen, COUNT(*) AS application_count')
            ->pluck('application_count', 'MaDotTuyen');

        foreach ($campaigns as $campaign) {
            $campaign->setAttribute(
                'target_count',
                $campaign->viTriTuyens->sum('SoLuong'),
            );
            $campaign->setAttribute(
                'application_count',
                (int) ($applicationCounts[$campaign->MaDotTuyen] ?? 0),
            );
        }

        $applications = DonUngTuyen::query()
            ->whereHas('viTri.dotTuyen', fn (Builder $builder) => $builder->where('MaCLB', $clubId));
        $notifications = $user->notifications()
            ->where('type', RecruitmentApplicationReceived::class)
            ->latest()
            ->take(100)
            ->get()
            ->filter(fn ($notification): bool => (int) ($notification->data['MaCLB'] ?? 0) === $clubId)
            ->whereNull('read_at')
            ->values();
        $newApplicationCounts = $notifications
            ->whereNull('read_at')
            ->groupBy(fn ($notification): int => (int) ($notification->data['MaDotTuyen'] ?? 0))
            ->map->count();

        foreach ($campaigns as $campaign) {
            $campaign->setAttribute(
                'new_application_count',
                (int) ($newApplicationCounts[$campaign->MaDotTuyen] ?? 0),
            );
        }

        return [
            'club' => $club,
            'campaigns' => $campaigns,
            'search' => $search,
            'statusFilter' => $status,
            'campaignCount' => DotTuyenThanhVien::query()->where('MaCLB', $clubId)->count(),
            'draftCampaignCount' => DotTuyenThanhVien::query()
                ->where('MaCLB', $clubId)
                ->where('LaBanNhap', true)
                ->count(),
            'openCampaignCount' => DotTuyenThanhVien::query()
                ->where('MaCLB', $clubId)
                ->where('LaBanNhap', false)
                ->where(function ($q) use ($now) {
                    $q->where('TrangThai', DotTuyenTrangThai::DANG_TUYEN->value)
                        ->orWhere(function ($sub) use ($now) {
                            $sub->where('NgayBatDau', '<=', $now)->where('NgayKetThuc', '>', $now);
                        });
                })
                ->count(),
            'upcomingCampaignCount' => DotTuyenThanhVien::query()
                ->where('MaCLB', $clubId)
                ->where('LaBanNhap', false)
                ->where(function ($q) use ($now) {
                    $q->where('TrangThai', DotTuyenTrangThai::CHUA_TUYEN->value)
                        ->orWhere('NgayBatDau', '>', $now);
                })
                ->count(),
            'closedCampaignCount' => DotTuyenThanhVien::query()
                ->where('MaCLB', $clubId)
                ->where('LaBanNhap', false)
                ->where('NgayKetThuc', '<=', $now)
                ->count(),
            'nearestDeadline' => DotTuyenThanhVien::query()
                ->where('MaCLB', $clubId)
                ->where('LaBanNhap', false)
                ->where('NgayKetThuc', '>=', $now)
                ->orderBy('NgayKetThuc')
                ->value('NgayKetThuc'),
            'pendingApplicationCount' => (clone $applications)
                ->whereIn('TrangThai', [DonUngTrangThai::CHO_XY_LY->value, DonUngTrangThai::DANG_XET_DUYET->value])
                ->count(),
            'applicationCount' => (clone $applications)->count(),
            'newNotifications' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->take(5),
        ];
    }

    /**
     * @return array{club: CauLacBo, departments: Collection<int, Ban>}
     */
    public function getCreateData(int $clubId, NguoiDung $user): array
    {
        $club = $this->managedClub($clubId, $user);

        return [
            'club' => $club,
            'departments' => Ban::query()->where('MaCLB', $clubId)->orderBy('TenBan')->get(),
        ];
    }

    /**
     * @return array{club: CauLacBo, campaign: DotTuyenThanhVien, departments: \Illuminate\Database\Eloquent\Collection<int, Ban>}
     */
    public function getEditData(int $clubId, int $campaignId, NguoiDung $user): array
    {
        $club = $this->managedClub($clubId, $user);
        $campaign = $club->dotTuyenThanhViens()
            ->with('viTriTuyens')
            ->findOrFail($campaignId);

        return [
            'club' => $club,
            'campaign' => $campaign,
            'departments' => Ban::query()->where('MaCLB', $clubId)->orderBy('TenBan')->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(
        int $clubId,
        NguoiDung $user,
        array $data,
        ?UploadedFile $poster,
        bool $draft = false,
    ): DotTuyenThanhVien {
        $club = $this->managedClub($clubId, $user);
        $positions = $data['positions'];
        $this->validatePositions($clubId, $positions);
        $posterPath = $poster?->store('recruitment-posters', 'public');

        try {
            return DB::transaction(function () use ($club, $data, $positions, $posterPath, $draft): DotTuyenThanhVien {
                $campaign = $club->dotTuyenThanhViens()->create([
                    'TenDotTuyen' => $data['TenDotTuyen'],
                    'MoTa' => $data['MoTa'] ?? null,
                    'URLPoster' => $posterPath ? Storage::disk('public')->url($posterPath) : null,
                    'DoiTuong' => $data['DoiTuong'] ?? null,
                    'DiaDiemPhongVan' => $data['DiaDiemPhongVan'],
                    'NgayBatDau' => $data['NgayBatDau'],
                    'NgayKetThuc' => $data['NgayKetThuc'],
                    'NgayPhongVan' => $data['NgayPhongVan'],
                    'NgayCongBoKetQua' => $data['NgayCongBoKetQua'],
                    'LaBanNhap' => $draft,
                    'TrangThai' => ! empty($data['TrangThai'])
                        ? DotTuyenTrangThai::from($data['TrangThai'])
                        : (now()->betweenIncluded($data['NgayBatDau'], $data['NgayKetThuc'])
                            ? DotTuyenTrangThai::DANG_TUYEN
                            : DotTuyenTrangThai::CHUA_TUYEN),
                ]);

                foreach ($positions as $position) {
                    $campaign->viTriTuyens()->create([
                        'TenViTri' => $position['TenViTri'],
                        'Mota' => $position['Mota'] ?? '',
                        'SoLuong' => $position['SoLuong'],
                        'MaBan' => $position['MaBan'] ?? null,
                    ]);
                }

                return $campaign;
            });
        } catch (\Throwable $exception) {
            if ($posterPath) {
                Storage::disk('public')->delete($posterPath);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(
        int $clubId,
        int $campaignId,
        NguoiDung $user,
        array $data,
        ?UploadedFile $poster,
        bool $draft,
    ): DotTuyenThanhVien {
        $club = $this->managedClub($clubId, $user);
        $campaign = $club->dotTuyenThanhViens()->with('viTriTuyens')->findOrFail($campaignId);
        $positions = $data['positions'];
        $existingPositions = $campaign->viTriTuyens->keyBy('MaViTri');
        $this->validatePositions($clubId, $positions);

        foreach ($positions as $position) {
            if (! empty($position['MaViTri']) && ! $existingPositions->has((int) $position['MaViTri'])) {
                throw ValidationException::withMessages([
                    'positions' => 'Vị trí chỉnh sửa không thuộc đợt tuyển này.',
                ]);
            }
        }

        $retainedPositionIds = collect($positions)
            ->pluck('MaViTri')
            ->filter()
            ->map(static fn ($id): int => (int) $id);
        $removedPositions = $existingPositions->except($retainedPositionIds->all());

        foreach ($removedPositions as $position) {
            if ($position->donUngTuyens()->exists()) {
                throw ValidationException::withMessages([
                    'positions' => 'Không thể xóa vị trí đã có hồ sơ ứng tuyển.',
                ]);
            }
        }

        $posterPath = $poster?->store('recruitment-posters', 'public');
        $previousPosterPath = $campaign->URLPoster;

        try {
            $updatedCampaign = DB::transaction(function () use (
                $campaign,
                $data,
                $positions,
                $removedPositions,
                $posterPath,
                $draft,
            ): DotTuyenThanhVien {
                $campaign->forceFill([
                    'TenDotTuyen' => $data['TenDotTuyen'],
                    'MoTa' => $data['MoTa'] ?? null,
                    'URLPoster' => $posterPath ? Storage::disk('public')->url($posterPath) : $campaign->URLPoster,
                    'DoiTuong' => $data['DoiTuong'] ?? null,
                    'DiaDiemPhongVan' => $data['DiaDiemPhongVan'],
                    'NgayBatDau' => $data['NgayBatDau'],
                    'NgayKetThuc' => $data['NgayKetThuc'],
                    'NgayPhongVan' => $data['NgayPhongVan'],
                    'NgayCongBoKetQua' => $data['NgayCongBoKetQua'],
                    'LaBanNhap' => $draft,
                    'TrangThai' => ! empty($data['TrangThai'])
                        ? DotTuyenTrangThai::from($data['TrangThai'])
                        : $campaign->TrangThai,
                ])->save();

                foreach ($removedPositions as $position) {
                    $position->delete();
                }

                foreach ($positions as $positionData) {
                    $position = ! empty($positionData['MaViTri'])
                        ? $campaign->viTriTuyens()->whereKey($positionData['MaViTri'])->firstOrFail()
                        : $campaign->viTriTuyens()->make();
                    $position->forceFill([
                        'TenViTri' => $positionData['TenViTri'],
                        'Mota' => $positionData['Mota'] ?? '',
                        'SoLuong' => $positionData['SoLuong'],
                        'MaBan' => $positionData['MaBan'] ?? null,
                    ])->save();
                }

                return $campaign;
            });
        } catch (\Throwable $exception) {
            if ($posterPath) {
                Storage::disk('public')->delete($posterPath);
            }

            throw $exception;
        }

        if ($posterPath && $previousPosterPath) {
            $previousPath = ltrim(
                str_replace('/storage/', '', (string) parse_url($previousPosterPath, PHP_URL_PATH)),
                '/',
            );
            if (str_starts_with($previousPath, 'recruitment-posters/')) {
                Storage::disk('public')->delete($previousPath);
            }
        }

        return $updatedCampaign;
    }

    public function delete(int $clubId, int $campaignId, NguoiDung $user): void
    {
        $club = $this->managedClub($clubId, $user);
        $campaign = $club->dotTuyenThanhViens()->with('viTriTuyens')->findOrFail($campaignId);
        $hasApplications = DonUngTuyen::query()
            ->whereIn('MaViTri', $campaign->viTriTuyens->pluck('MaViTri'))
            ->exists();

        if ($hasApplications) {
            throw ValidationException::withMessages([
                'campaign' => 'Không thể xóa đợt tuyển đã có hồ sơ ứng tuyển.',
            ]);
        }

        $posterPath = ltrim(
            str_replace('/storage/', '', (string) parse_url((string) $campaign->URLPoster, PHP_URL_PATH)),
            '/',
        );
        DB::transaction(function () use ($campaign): void {
            $campaign->viTriTuyens()->delete();
            $campaign->delete();
        });

        if (str_starts_with($posterPath, 'recruitment-posters/')) {
            Storage::disk('public')->delete($posterPath);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $positions
     */
    private function validatePositions(int $clubId, array $positions): void
    {
        foreach ($positions as $position) {
            if (! empty($position['MaBan']) && ! Ban::query()
                ->where('MaCLB', $clubId)
                ->where('MaBan', $position['MaBan'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'positions' => 'Ban tuyển phải thuộc câu lạc bộ đang quản lý.',
                ]);
            }
        }
    }

    public function getPublicCampaign(int $campaignId, ?NguoiDung $user): DotTuyenThanhVien
    {
        $campaign = DotTuyenThanhVien::query()
            ->with(['cauLacBo', 'viTriTuyens.ban'])
            ->findOrFail($campaignId);
        $isOpen = ! $campaign->LaBanNhap && (
            $campaign->TrangThai === DotTuyenTrangThai::DANG_TUYEN
            || ($campaign->NgayBatDau?->lte(now()) && $campaign->NgayKetThuc?->gt(now()))
        );
        abort_unless($isOpen, 404);
        $campaign->setAttribute('target_count', $campaign->viTriTuyens->sum('SoLuong'));

        if ($user) {
            $positionIds = $campaign->viTriTuyens->pluck('MaViTri');
            $campaign->setAttribute(
                'applied_position_ids',
                DonUngTuyen::query()
                    ->where('MaNguoiUngTuyen', $user->MaNguoiDung)
                    ->whereIn('MaViTri', $positionIds)
                    ->where('TrangThai', '!=', DonUngTrangThai::DA_HUY->value)
                    ->pluck('MaViTri')
                    ->all(),
            );
        } else {
            $campaign->setAttribute('applied_position_ids', []);
        }

        return $campaign;
    }

    public function apply(
        DotTuyenThanhVien $campaign,
        NguoiDung $user,
        int $positionId,
        ?string $motivation,
        ?UploadedFile $resume,
    ): DonUngTuyen {
        $isOpen = ! $campaign->LaBanNhap && (
            $campaign->TrangThai === DotTuyenTrangThai::DANG_TUYEN
            || ($campaign->NgayBatDau?->lte(now()) && $campaign->NgayKetThuc?->gt(now()))
        );
        abort_unless($isOpen, 404);
        $position = $campaign->viTriTuyens()->where('MaViTri', $positionId)->firstOrFail();
        $existingApplication = DonUngTuyen::query()
            ->where('MaNguoiUngTuyen', $user->MaNguoiDung)
            ->where('MaViTri', $position->MaViTri)
            ->where('TrangThai', '!=', DonUngTrangThai::DA_HUY->value)
            ->exists();

        if ($existingApplication) {
            throw ValidationException::withMessages([
                'MaViTri' => 'Bạn đã gửi hồ sơ cho vị trí này rồi.',
            ]);
        }

        $resumePath = $resume?->store('recruitment-resumes', 'local');

        try {
            return DB::transaction(function () use ($campaign, $position, $user, $motivation, $resumePath): DonUngTuyen {
                $application = DonUngTuyen::query()->create([
                    'MaViTri' => $position->MaViTri,
                    'MaNguoiUngTuyen' => $user->MaNguoiDung,
                    'NoiDung' => $motivation,
                    'URLHoSo' => $resumePath,
                    'TrangThai' => DonUngTrangThai::CHO_XY_LY,
                    'NgayNopDon' => now(),
                    'NgayCapNhat' => now(),
                ]);

                $managerIds = ThanhVienTrangThai::DANG_HOAT_DONG->value;
                $managers = $campaign->cauLacBo->memberships()
                    ->with('nguoiDung')
                    ->where('TrangThai', $managerIds)
                    ->get()
                    ->filter(fn ($membership): bool => $membership->nguoiDung !== null
                        && $this->isManagementRole($membership->ChucVu))
                    ->pluck('nguoiDung')
                    ->unique('MaNguoiDung')
                    ->values();

                if ($managers->isEmpty()) {
                    $managers = NguoiDung::query()->where('VaiTro', NguoiDungVaiTro::ADMIN)->get();
                }

                if ($managers->isNotEmpty()) {
                    Notification::send($managers, new RecruitmentApplicationReceived($application));
                }

                return $application;
            });
        } catch (\Throwable $exception) {
            if ($resumePath) {
                Storage::disk('local')->delete($resumePath);
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getApplicationsData(int $clubId, int $campaignId, NguoiDung $user, ?string $status): array
    {
        $club = $this->managedClub($clubId, $user);
        $campaign = $club->dotTuyenThanhViens()
            ->with('viTriTuyens')
            ->findOrFail($campaignId);
        $validStatuses = array_map(static fn (DonUngTrangThai $value): string => $value->value, DonUngTrangThai::cases());
        $status = in_array($status, $validStatuses, true) ? $status : '';
        $query = DonUngTuyen::query()
            ->with(['nguoiUngTuyen', 'viTri'])
            ->whereHas('viTri', fn (Builder $builder) => $builder->where('MaDotTuyen', $campaignId));

        if ($status !== '') {
            $query->where('TrangThai', $status);
        }

        return [
            'club' => $club,
            'campaign' => $campaign,
            'applications' => $query->orderByDesc('NgayNopDon')->paginate(10)->withQueryString(),
            'pendingCount' => DonUngTuyen::query()
                ->whereHas('viTri', fn (Builder $builder) => $builder->where('MaDotTuyen', $campaignId))
                ->whereIn('TrangThai', [DonUngTrangThai::CHO_XY_LY->value, DonUngTrangThai::DANG_XET_DUYET->value])
                ->count(),
            'statusFilter' => $status,
        ];
    }

    public function getApplicationForManager(int $clubId, int $applicationId, NguoiDung $user): DonUngTuyen
    {
        $club = $this->managedClub($clubId, $user);
        $application = DonUngTuyen::query()
            ->with(['nguoiUngTuyen', 'nguoiXetDuyet', 'viTri.dotTuyen'])
            ->whereKey($applicationId)
            ->whereHas('viTri.dotTuyen', fn (Builder $builder) => $builder->where('MaCLB', $clubId))
            ->firstOrFail();

        $user->notifications()
            ->where('type', RecruitmentApplicationReceived::class)
            ->whereNull('read_at')
            ->latest()
            ->take(100)
            ->get()
            ->each(function ($notification) use ($applicationId): void {
                if ((int) ($notification->data['MaDon'] ?? 0) === $applicationId) {
                    $notification->markAsRead();
                }
            });

        return $application;
    }

    public function getResumeForManager(int $clubId, int $applicationId, NguoiDung $user): DonUngTuyen
    {
        $this->managedClub($clubId, $user);

        return DonUngTuyen::query()
            ->whereKey($applicationId)
            ->whereHas('viTri.dotTuyen', fn (Builder $builder) => $builder->where('MaCLB', $clubId))
            ->firstOrFail();
    }

    public function updateApplicationStatus(
        int $clubId,
        int $applicationId,
        NguoiDung $user,
        DonUngTrangThai $status,
        ?string $note,
    ): DonUngTuyen {
        $this->managedClub($clubId, $user);
        $application = DonUngTuyen::query()
            ->whereKey($applicationId)
            ->whereHas('viTri.dotTuyen', fn (Builder $builder) => $builder->where('MaCLB', $clubId))
            ->firstOrFail();
        $application->forceFill([
            'TrangThai' => $status,
            'MaNguoiXetDuyet' => $user->MaNguoiDung,
            'GhiChu' => $note,
            'NgayCapNhat' => now(),
        ])->save();

        return $application;
    }

    public function managedClub(int $clubId, NguoiDung $user): CauLacBo
    {
        $club = CauLacBo::query()->findOrFail($clubId);
        $isAdmin = $user->VaiTro === NguoiDungVaiTro::ADMIN;
        $hasManagementMembership = $club->memberships()
            ->where('MaNguoiDung', $user->MaNguoiDung)
            ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
            ->get(['ChucVu'])
            ->contains(fn ($membership): bool => $this->isManagementRole($membership->ChucVu));

        if (! $isAdmin && ! $hasManagementMembership) {
            throw new HttpException(403, 'Bạn không có quyền quản lý câu lạc bộ này.');
        }

        return $club;
    }

    private function isManagementRole(?string $role): bool
    {
        if ($role === null || trim($role) === '') {
            return false;
        }

        $normalizedRole = preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii($role))) ?? '';

        return Str::contains($normalizedRole, [
            'CHU NHIEM',
            'CHU TICH',
            'TRUONG CLB',
            'PHO CLB',
            'TRUONG BAN',
            'PHO BAN',
            'DONG SANG LAP',
            'QUAN LY',
            'MANAGER',
            'PRESIDENT',
            'ADMIN',
        ]);
    }
}

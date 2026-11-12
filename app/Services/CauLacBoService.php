<?php

namespace App\Services;

use App\Enums\CauLacBoTrangThai;
use App\Enums\DonUngTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Models\CauLacBo;
use App\Models\DonUngTuyen;
use App\Models\NguoiDung;
use App\Models\SuKien;
use App\Models\ThanhVienCLB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CauLacBoService
{
    /**
     * @return array{
     *     joinedClubs: Collection<int, ThanhVienCLB>,
     *     joinedClubCount: int,
     *     managedClubCount: int,
     *     officialCount: int,
     *     collaboratorCount: int,
     *     activeClubCount: int,
     *     otherClubCount: int,
     *     search: string
     * }
     */
    public function getJoinedClubsData(?string $search, ?NguoiDung $user): array
    {
        $memberships = $user === null
            ? collect()
            : ThanhVienCLB::query()
                ->with(['cauLacBo.suKiens'])
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->whereIn('TrangThai', [
                    ThanhVienTrangThai::DANG_HOAT_DONG->value,
                    ThanhVienTrangThai::TAM_NGHI->value,
                ])
                ->get()
                ->filter(fn (ThanhVienCLB $membership): bool => $membership->cauLacBo !== null)
                ->groupBy('MaCLB')
                ->map(fn (Collection $clubMemberships): ThanhVienCLB => $clubMemberships->first(
                    fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu),
                ) ?? $clubMemberships->first())
                ->values();

        $managedClubCount = $memberships
            ->filter(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu))
            ->count();
        $allJoinedClubs = $memberships
            ->reject(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu))
            ->values();
        $activeClubCount = $allJoinedClubs
            ->filter(fn (ThanhVienCLB $membership): bool => $membership->TrangThai === ThanhVienTrangThai::DANG_HOAT_DONG)
            ->count();

        foreach ($allJoinedClubs as $membership) {
            $club = $membership->cauLacBo;
            $clubEvents = $club->suKiens->sortByDesc('ThoiGianBatDau');
            $club->setAttribute('events_count', $clubEvents->count());
            $club->setAttribute('latest_event', $clubEvents->first());
        }

        $joinedClubCount = $allJoinedClubs->count();
        $officialCount = $allJoinedClubs->filter(fn (ThanhVienCLB $membership): bool => ! $this->isCollaboratorRole($membership->ChucVu))->count();
        $collaboratorCount = $allJoinedClubs->filter(fn (ThanhVienCLB $membership): bool => $this->isCollaboratorRole($membership->ChucVu))->count();
        $otherClubCount = $memberships->reject(
            fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu)
                || $membership->TrangThai === ThanhVienTrangThai::DANG_HOAT_DONG,
        )->count();
        $joinedClubs = $allJoinedClubs;
        $search = trim($search ?? '');

        $search = trim($search ?? '');

        if ($search !== '') {
            $normalizedSearch = Str::lower($search);
            $joinedClubs = $joinedClubs->filter(
                fn (ThanhVienCLB $membership): bool => str_contains(Str::lower($membership->cauLacBo->TenCLB), $normalizedSearch)
                    || str_contains((string) $membership->MaCLB, $search)
                    || str_contains(Str::lower($membership->ChucVu), $normalizedSearch),
            )->values();
        }

        return [
            'joinedClubs' => $joinedClubs,
            'joinedClubCount' => $joinedClubCount,
            'managedClubCount' => $managedClubCount,
            'officialCount' => $officialCount,
            'collaboratorCount' => $collaboratorCount,
            'activeClubCount' => $activeClubCount,
            'otherClubCount' => $otherClubCount,
            'search' => $search,
        ];
    }

    public function getJoinedClubCount(?NguoiDung $user): int
    {
        if ($user === null) {
            return 0;
        }

        return ThanhVienCLB::query()
            ->where('MaNguoiDung', $user->MaNguoiDung)
            ->whereHas('cauLacBo')
            ->whereIn('TrangThai', [
                ThanhVienTrangThai::DANG_HOAT_DONG->value,
                ThanhVienTrangThai::TAM_NGHI->value,
            ])
            ->get(['MaCLB', 'ChucVu'])
            ->groupBy('MaCLB')
            ->reject(fn (Collection $clubMemberships): bool => $clubMemberships->contains(
                fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu),
            ))
            ->count();
    }

    /**
     * @return array{
     *     club: CauLacBo,
     *     members: LengthAwarePaginator<int, ThanhVienCLB>,
     *     totalMembers: int,
     *     activeMembers: int,
     *     pausedMembers: int,
     *     pendingMembers: int,
     *     roles: array<string, int>,
     *     search: string,
     *     roleFilter: string,
     *     statusFilter: string
     * }
     */
    public function syncPendingMembersFromApprovedApplications(int $clubId): void
    {
        $approvedApplications = DonUngTuyen::query()
            ->with(['viTri'])
            ->where('TrangThai', DonUngTrangThai::DA_DUYET->value)
            ->whereHas('viTri.dotTuyen', fn ($builder) => $builder->where('MaCLB', $clubId))
            ->get();

        foreach ($approvedApplications as $app) {
            if (! $app->MaNguoiUngTuyen) {
                continue;
            }

            $membership = ThanhVienCLB::query()
                ->where('MaCLB', $clubId)
                ->where('MaNguoiDung', $app->MaNguoiUngTuyen)
                ->first();

            if (! $membership) {
                ThanhVienCLB::query()->create([
                    'MaCLB' => $clubId,
                    'MaNguoiDung' => $app->MaNguoiUngTuyen,
                    'ChucVu' => $app->viTri?->TenViTri ?: 'Thành viên',
                    'NgayThamGia' => $app->NgayCapNhat ?? $app->NgayNopDon ?? now(),
                    'TrangThai' => ThanhVienTrangThai::CHO_PHE_DUYET,
                ]);
            }
        }
    }

    public function getClubMembersData(string $clubId, ?string $search, ?string $role, ?string $status): array
    {
        $club = CauLacBo::query()->findOrFail($clubId);
        $this->syncPendingMembersFromApprovedApplications($club->MaCLB);

        $baseQuery = ThanhVienCLB::query()
            ->where('MaCLB', $club->MaCLB)
            ->whereNotNull('MaNguoiDung')
            ->whereHas('nguoiDung')
            ->where('TrangThai', '!=', ThanhVienTrangThai::DA_ROI_CLB->value);

        $totalMembers = (clone $baseQuery)->distinct()->count('MaNguoiDung');
        $activeMembers = (clone $baseQuery)
            ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
            ->distinct()
            ->count('MaNguoiDung');
        $pausedMembers = (clone $baseQuery)
            ->where('TrangThai', ThanhVienTrangThai::TAM_NGHI->value)
            ->distinct()
            ->count('MaNguoiDung');
        $pendingMembers = (clone $baseQuery)
            ->where('TrangThai', ThanhVienTrangThai::CHO_PHE_DUYET->value)
            ->distinct()
            ->count('MaNguoiDung');
        $roles = (clone $baseQuery)
            ->selectRaw('ChucVu, COUNT(DISTINCT MaNguoiDung) AS members_count')
            ->groupBy('ChucVu')
            ->orderBy('ChucVu')
            ->pluck('members_count', 'ChucVu')
            ->map(static fn ($count): int => (int) $count)
            ->all();

        $search = trim($search ?? '');
        $roleFilter = trim($role ?? '');
        $validStatuses = array_map(
            static fn (ThanhVienTrangThai $memberStatus): string => $memberStatus->value,
            ThanhVienTrangThai::cases(),
        );
        $statusFilter = in_array($status, $validStatuses, true) ? $status : '';

        $query = (clone $baseQuery)->with('nguoiDung');
        if ($search !== '') {
            $query->whereHas('nguoiDung', function ($userQuery) use ($search): void {
                $userQuery->where('HoTen', 'like', "%{$search}%")
                    ->orWhere('MSSV', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhere('TenTaiKhoan', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && array_key_exists($roleFilter, $roles)) {
            $query->where('ChucVu', $roleFilter);
        } else {
            $roleFilter = '';
        }

        if ($statusFilter !== '') {
            $query->where('TrangThai', $statusFilter);
        }

        $members = $query
            ->orderByRaw('CASE WHEN TrangThai = ? THEN 0 ELSE 1 END', [ThanhVienTrangThai::CHO_PHE_DUYET->value])
            ->orderBy('MaThanhVien')
            ->paginate(10)
            ->withQueryString();

        return [
            'club' => $club,
            'members' => $members,
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'pausedMembers' => $pausedMembers,
            'pendingMembers' => $pendingMembers,
            'roles' => $roles,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
        ];
    }

    /**
     * @return array{club: CauLacBo, memberCount: int, eventCount: int, recruitmentCount: int}|null
     */
    public function getManagementNavigationData(?int $clubId): ?array
    {
        if ($clubId === null) {
            return null;
        }

        $club = CauLacBo::query()->find($clubId);
        if ($club === null) {
            return null;
        }

        return [
            'club' => $club,
            'memberCount' => $club->memberships()
                ->whereNotNull('MaNguoiDung')
                ->whereHas('nguoiDung')
                ->where('TrangThai', '!=', ThanhVienTrangThai::DA_ROI_CLB->value)
                ->distinct()
                ->count('MaNguoiDung'),
            'eventCount' => $club->suKiens()->count(),
            'recruitmentCount' => $club->dotTuyenThanhViens()->count(),
        ];
    }

    public function isManagementRole(?string $role): bool
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

    public function canManageClub(?NguoiDung $user, CauLacBo|int|string $club): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->VaiTro === NguoiDungVaiTro::ADMIN) {
            return true;
        }

        $clubId = $club instanceof CauLacBo ? $club->MaCLB : (int) $club;

        return ThanhVienCLB::query()
            ->where('MaCLB', $clubId)
            ->where('MaNguoiDung', $user->MaNguoiDung)
            ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
            ->get(['ChucVu'])
            ->contains(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu));
    }

    public function isClubOwner(?NguoiDung $user, CauLacBo|int|string $club): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->VaiTro === NguoiDungVaiTro::ADMIN) {
            return true;
        }

        $clubId = $club instanceof CauLacBo ? $club->MaCLB : (int) $club;

        return ThanhVienCLB::query()
            ->where('MaCLB', $clubId)
            ->where('MaNguoiDung', $user->MaNguoiDung)
            ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
            ->get(['ChucVu'])
            ->contains(function (ThanhVienCLB $membership): bool {
                $normalized = preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii($membership->ChucVu ?? ''))) ?? '';

                return Str::contains($normalized, ['CHU NHIEM', 'CHU TICH', 'TRUONG CLB', 'PRESIDENT', 'DONG SANG LAP'])
                    && ! Str::contains($normalized, ['PHO CHU NHIEM', 'PHO CLB', 'PHO CHU TICH', 'PHO BAN']);
            });
    }

    public function updateMemberRole(int $clubId, int $memberId, string $role, ?string $status = null): ThanhVienCLB
    {
        $membership = ThanhVienCLB::query()
            ->where('MaCLB', $clubId)
            ->where('MaThanhVien', $memberId)
            ->firstOrFail();

        $membership->ChucVu = trim($role);

        if ($status !== null && ThanhVienTrangThai::tryFrom($status) !== null) {
            $membership->TrangThai = ThanhVienTrangThai::from($status);

            if ($membership->TrangThai === ThanhVienTrangThai::DA_ROI_CLB && $membership->MaNguoiDung) {
                DonUngTuyen::query()
                    ->where('MaNguoiUngTuyen', $membership->MaNguoiDung)
                    ->whereHas('viTri.dotTuyen', fn ($builder) => $builder->where('MaCLB', $clubId))
                    ->where('TrangThai', DonUngTrangThai::DA_DUYET->value)
                    ->update([
                        'TrangThai' => DonUngTrangThai::TU_CHOI->value,
                        'NgayCapNhat' => now(),
                    ]);
            }
        }

        $membership->save();

        return $membership;
    }

    private function isCollaboratorRole(?string $role): bool
    {
        if ($role === null) {
            return false;
        }

        return Str::contains(Str::lower(Str::ascii($role)), ['cong tac vien', 'ctv']);
    }

    /**
     * @return array{
     *     clubs: Collection<int, CauLacBo>,
     *     categories: array<int, int>,
     *     categoryCounts: array<int, int>,
     *     search: string,
     *     filter: string,
     *     stats: array{clubs: int, activeClubs: int, events: int}
     * }
     */
    public function getManagementData(?string $search, ?string $filter, ?string $category, ?NguoiDung $user = null): array
    {
        $query = CauLacBo::query()->orderByDesc('NgayTao')->orderByDesc('MaCLB');

        if ($user !== null && $user->VaiTro !== NguoiDungVaiTro::ADMIN) {
            $managedClubIds = ThanhVienCLB::query()
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
                ->get(['MaCLB', 'ChucVu'])
                ->filter(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu))
                ->pluck('MaCLB')
                ->unique()
                ->all();

            $query->whereIn('MaCLB', $managedClubIds);
        }

        $allClubs = $query->get();
        $clubIds = $allClubs->pluck('MaCLB');
        $eventsByClub = SuKien::query()
            ->whereIn('MaCLB', $clubIds)
            ->orderByDesc('ThoiGianBatDau')
            ->orderByDesc('MaSuKien')
            ->get()
            ->groupBy('MaCLB');

        foreach ($allClubs as $club) {
            $clubEvents = $eventsByClub->get($club->MaCLB, collect());
            $club->setAttribute('events_count', $clubEvents->count());
            $club->setAttribute('latest_event', $clubEvents->first());
            $club->setAttribute('is_active', $this->isActive($club->TrangThai));
        }

        $search = trim($search ?? '');
        $filter = $filter === 'active' ? 'active' : 'all';
        $clubs = $allClubs;

        if ($search !== '') {
            $normalizedSearch = Str::lower($search);
            $clubs = $clubs->filter(fn (CauLacBo $club): bool => str_contains(Str::lower($club->TenCLB), $normalizedSearch)
                || str_contains((string) $club->MaCLB, $search)
                || str_contains(Str::lower((string) $club->MaDanhMucCLB), $normalizedSearch));
        }

        if ($filter === 'active') {
            $clubs = $clubs->where('is_active', true);
        }

        if ($category !== null && ctype_digit($category)) {
            $clubs = $clubs->where('MaDanhMucCLB', (int) $category);
        }

        return [
            'clubs' => $clubs->values(),
            'categories' => $allClubs->pluck('MaDanhMucCLB')->filter()->unique()->sort()->values()->all(),
            'categoryCounts' => $allClubs->countBy('MaDanhMucCLB')->all(),
            'search' => $search,
            'filter' => $filter,
            'stats' => [
                'clubs' => $allClubs->count(),
                'activeClubs' => $allClubs->where('is_active', true)->count(),
                'events' => SuKien::query()->whereIn('MaCLB', $clubIds)->count(),
            ],
        ];
    }

    public function getAll(): Collection
    {
        return CauLacBo::query()->orderByDesc('NgayTao')->orderByDesc('MaCLB')->get();
    }

    public function getById(string $maCLB): CauLacBo
    {
        return CauLacBo::findOrFail($maCLB);
    }

    public function create(array $data): CauLacBo
    {
        $storedPaths = [];

        try {
            foreach ($this->imageDirectories() as $field => $directory) {
                if (($data[$field] ?? null) instanceof UploadedFile) {
                    $storedPath = $this->storeImage($data[$field], $directory);
                    $storedPaths[] = $storedPath;
                    $data[$field] = Storage::disk('public')->url($storedPath);
                }
            }

            return DB::transaction(function () use ($data): CauLacBo {
                $club = CauLacBo::create($data);
                $user = Auth::guard('web')->user();

                if ($user instanceof NguoiDung) {
                    ThanhVienCLB::query()->create([
                        'MaCLB' => $club->MaCLB,
                        'MaNguoiDung' => $user->MaNguoiDung,
                        'ChucVu' => 'CHU_NHIEM',
                        'NgayThamGia' => now(),
                        'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
                    ]);
                }

                return $club;
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedPaths);

            throw $exception;
        }
    }

    public function update(string $maCLB, array $data): CauLacBo
    {
        $cauLacBo = $this->getById($maCLB);
        $storedPaths = [];
        $oldImageUrls = [];

        try {
            foreach ($this->imageDirectories() as $field => $directory) {
                if (($data[$field] ?? null) instanceof UploadedFile) {
                    $oldImageUrls[] = $cauLacBo->{$field};
                    $storedPath = $this->storeImage($data[$field], $directory);
                    $storedPaths[] = $storedPath;
                    $data[$field] = Storage::disk('public')->url($storedPath);
                }
            }

            $cauLacBo->update($data);
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedPaths);

            throw $exception;
        }

        foreach ($oldImageUrls as $oldImageUrl) {
            $this->deleteManagedImage($oldImageUrl);
        }

        return $cauLacBo;
    }

    public function delete(string $maCLB): bool
    {
        return DB::transaction(function () use ($maCLB): bool {
            $club = $this->getById($maCLB);
            SuKien::query()->where('MaCLB', $club->MaCLB)->update(['MaCLB' => null]);

            return (bool) $club->delete();
        });
    }

    private function isActive(?CauLacBoTrangThai $status): bool
    {
        return in_array($status, [
            CauLacBoTrangThai::DANG_HOAT_DONG,
            CauLacBoTrangThai::HOAT_DONG,
        ], true);
    }

    /**
     * @return array<string, string>
     */
    private function imageDirectories(): array
    {
        return [
            'URLLogo' => 'clubs/logos',
            'URLAnhNen' => 'clubs/backgrounds',
        ];
    }

    private function storeImage(UploadedFile $image, string $directory): string
    {
        $path = $image->store($directory, 'public');

        if ($path === false) {
            throw new \RuntimeException('Không thể lưu ảnh câu lạc bộ vào bộ nhớ.');
        }

        return $path;
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteStoredImages(array $paths): void
    {
        foreach ($paths as $path) {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('Could not delete an image after a club save failed.', ['path' => $path]);
            }
        }
    }

    private function deleteManagedImage(?string $url): void
    {
        if ($url === null) {
            return;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH) ?? $url;
        $path = ltrim((string) preg_replace('#^/storage/#', '', $parsedPath), '/');

        if (! preg_match('#^clubs/(?:logos|backgrounds)/[A-Za-z0-9._-]+$#', $path)) {
            return;
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path) && ! $publicDisk->delete($path)) {
            Log::warning('Could not delete a replaced club image.', ['path' => $path]);
        }
    }
}

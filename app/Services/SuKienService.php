<?php

namespace App\Services;

use App\Enums\SuKienLoai;
use App\Enums\SuKienTrangThai;
use App\Models\SuKien;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SuKienService
{
    /**
     * Lấy toàn bộ danh sách sự kiện sắp xếp mới nhất.
     */
    public function getAll(): Collection
    {
        return SuKien::query()->latest('created_at')->latest('MaSuKien')->get();
    }

    /**
     * @return array{
     *     suKiens: LengthAwarePaginator,
     *     filter: ?string,
     *     starredOnly: bool,
     *     search: string,
     *     counts: array{all: int, starred: int, statuses: array<string, int>},
     *     statuses: array<int, string>
     * }
     */
    public function getIndexData(?string $statusFilter, ?string $search, bool $starredOnly = false, ?int $clubId = null): array
    {
        $search = trim($search ?? '');
        $scopeQuery = SuKien::query();

        if ($clubId !== null) {
            $scopeQuery->where('MaCLB', $clubId);
        }

        $statuses = array_map(
            static fn (SuKienTrangThai $status): string => $status->value,
            SuKienTrangThai::cases(),
        );
        $selectedStatus = $statusFilter === null ? null : SuKienTrangThai::tryFrom($statusFilter);
        $statusFilter = $selectedStatus?->value;
        $now = now();

        $query = (clone $scopeQuery)->latest('created_at')->latest('MaSuKien');

        if ($starredOnly) {
            $query->where('DaDanhDau', true);
        } elseif ($selectedStatus !== null) {
            $this->applyStatusFilter($query, $selectedStatus, $now);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('TenSuKien', 'like', "%{$search}%")
                    ->orWhere('DiaDiem', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $query->orWhere('MaSuKien', (int) $search);
                }
            });
        }

        $statusCounts = [];

        foreach (SuKienTrangThai::cases() as $status) {
            $statusQuery = clone $scopeQuery;
            $this->applyStatusFilter($statusQuery, $status, $now);
            $statusCounts[$status->value] = $statusQuery->count();
        }

        return [
            'suKiens' => $query->paginate(10)->withQueryString(),
            'filter' => $statusFilter,
            'starredOnly' => $starredOnly,
            'search' => $search,
            'counts' => [
                'all' => (clone $scopeQuery)->count(),
                'starred' => (clone $scopeQuery)->where('DaDanhDau', true)->count(),
                'statuses' => $statusCounts,
            ],
            'statuses' => $statuses,
        ];
    }

    private function applyStatusFilter(Builder $query, SuKienTrangThai $status, Carbon $now): void
    {
        match ($status) {
            SuKienTrangThai::CHUA_MO_DANG_KY => $query->where('ThoiGianMoDangKy', '>', $now),
            SuKienTrangThai::DANG_MO_DANG_KY => $query
                ->where('ThoiGianMoDangKy', '<=', $now)
                ->where('ThoiGianDongDangKy', '>', $now),
            SuKienTrangThai::DA_DONG_DANG_KY => $query->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $query) use ($now): void {
                    $query->where('ThoiGianDongDangKy', '<=', $now)
                        ->where('ThoiGianBatDau', '>', $now);
                })->orWhere('ThoiGianKetThuc', '<=', $now);
            }),
            SuKienTrangThai::SAP_DIEN_RA => $query
                ->where('ThoiGianBatDau', '<=', $now)
                ->where('ThoiGianKetThuc', '>', $now),
        };
    }

    /**
     * @return array{suKien: SuKien}
     */
    public function getEditData(string $maSuKien): array
    {
        return ['suKien' => $this->getById($maSuKien)];
    }

    /**
     * Tìm sự kiện theo mã, nếu không thấy tự bắn lỗi 404.
     */
    public function getById(string $maSuKien): SuKien
    {
        return SuKien::findOrFail($maSuKien);
    }

    public function create(array $data): SuKien
    {
        $loaiSuKien = $data['LoaiSuKien'] ?? SuKienLoai::NOI_BO->value;
        $data['LoaiSuKien'] = $loaiSuKien;

        if ($loaiSuKien === SuKienLoai::CONG_KHAI->value) {
            $data['PhanLoaiNoiBo'] = null;
        }

        $image = $data['URLAnhNen'] ?? null;
        $storedPath = $image instanceof UploadedFile ? $this->storeBackgroundImage($image) : null;

        if ($storedPath !== null) {
            $data['URLAnhNen'] = Storage::disk('public')->url($storedPath);
        }

        try {
            return SuKien::create($data);
        } catch (Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }
    }

    public function update(string $maSuKien, array $data): SuKien
    {
        $suKien = $this->getById($maSuKien);
        $loaiSuKien = $data['LoaiSuKien'] ?? $suKien->LoaiSuKien?->value;

        if ($loaiSuKien === SuKienLoai::CONG_KHAI->value) {
            $data['PhanLoaiNoiBo'] = null;
        }

        $image = $data['URLAnhNen'] ?? null;
        $oldImageUrl = $suKien->URLAnhNen;
        $storedPath = $image instanceof UploadedFile ? $this->storeBackgroundImage($image) : null;

        if ($storedPath !== null) {
            $data['URLAnhNen'] = Storage::disk('public')->url($storedPath);
        } elseif (! is_string($image)) {
            unset($data['URLAnhNen']);
        }

        try {
            $suKien->update($data);
        } catch (Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        if ($storedPath !== null) {
            $this->deleteStoredBackgroundImage($oldImageUrl);
        }

        return $suKien;
    }

    private function storeBackgroundImage(UploadedFile $image): string
    {
        $path = $image->store('events/backgrounds', 'public');

        if ($path === false) {
            throw new \RuntimeException('Không thể lưu ảnh nền sự kiện vào bộ nhớ.');
        }

        return $path;
    }

    private function deleteStoredBackgroundImage(?string $url): void
    {
        if ($url === null) {
            return;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH) ?? $url;
        $path = ltrim(preg_replace('#^/storage/#', '', $parsedPath), '/');

        if (! str_starts_with($path, 'events/backgrounds/')) {
            return;
        }

        $publicDisk = Storage::disk('public');
        if ($path !== '' && $publicDisk->exists($path) && ! $publicDisk->delete($path)) {
            Log::warning('Could not delete replaced event background image.', ['path' => $path]);
        }
    }

    public function delete(string $maSuKien): bool
    {
        return $this->getById($maSuKien)->delete();
    }

    public function deleteAndGetClubId(string $maSuKien): ?int
    {
        $suKien = $this->getById($maSuKien);
        $maCLB = $suKien->MaCLB;
        $suKien->delete();

        return $maCLB;
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function setMarked(array $ids, bool $marked): int
    {
        return SuKien::whereIn('MaSuKien', $ids)->update(['DaDanhDau' => $marked]);
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return SuKien::whereIn('MaSuKien', $ids)->delete();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function performBulkAction(array $ids, string $action): string
    {
        if ($action === 'delete') {
            $this->deleteMany($ids);

            return 'Đã xóa các sự kiện được chọn.';
        }

        $marked = $action === 'mark';
        $this->setMarked($ids, $marked);

        return $marked
            ? 'Đã đánh dấu các sự kiện được chọn.'
            : 'Đã bỏ đánh dấu các sự kiện được chọn.';
    }
}

<?php

namespace App\Services;

use App\Models\NguoiDung;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProfileService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(NguoiDung $user, array $data): void
    {
        $avatar = $data['URLAnhDaiDien'] ?? null;
        $newPassword = $data['new_password'] ?? null;
        unset($data['URLAnhDaiDien']);
        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);

        $storedPath = null;
        $oldAvatar = $user->URLAnhDaiDien;

        try {
            if ($avatar instanceof UploadedFile) {
                $storedPath = $avatar->store('avatars', 'public');
                if ($storedPath === false) {
                    throw new \RuntimeException('Không thể lưu ảnh đại diện vào bộ nhớ.');
                }

                $data['URLAnhDaiDien'] = Storage::disk('public')->url($storedPath);
            }

            if (is_string($newPassword) && $newPassword !== '') {
                $data['MatKhauHash'] = Hash::make($newPassword);
            }

            if (! $user->fill($data)->save()) {
                throw new \RuntimeException('Không thể cập nhật thông tin tài khoản.');
            }
        } catch (Throwable $exception) {
            if ($storedPath !== null && ! Storage::disk('public')->delete($storedPath)) {
                Log::warning('Could not delete an avatar after a profile update failed.', ['path' => $storedPath]);
            }

            throw $exception;
        }

        if ($storedPath !== null) {
            $this->deleteManagedAvatar($oldAvatar);
        }
    }

    private function deleteManagedAvatar(?string $url): void
    {
        if ($url === null) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || ! Str::startsWith($path, '/storage/avatars/')) {
            return;
        }

        $relativePath = Str::after($path, '/storage/');
        if (! Storage::disk('public')->delete($relativePath)) {
            Log::warning('Could not delete a replaced profile avatar.', ['path' => $relativePath]);
        }
    }
}

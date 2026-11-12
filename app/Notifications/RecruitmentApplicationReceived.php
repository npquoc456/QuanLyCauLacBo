<?php

namespace App\Notifications;

use App\Models\DonUngTuyen;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RecruitmentApplicationReceived extends Notification
{
    use Queueable;

    public function __construct(private readonly DonUngTuyen $application) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        $application = $this->application->loadMissing([
            'nguoiUngTuyen',
            'viTri.dotTuyen.cauLacBo',
        ]);

        return [
            'MaDon' => $application->MaDon,
            'MaCLB' => $application->viTri->dotTuyen->MaCLB,
            'MaDotTuyen' => $application->viTri->dotTuyen->MaDotTuyen,
            'TenCLB' => $application->viTri->dotTuyen->cauLacBo->TenCLB,
            'TenDotTuyen' => $application->viTri->dotTuyen->TenDotTuyen,
            'TenViTri' => $application->viTri->TenViTri,
            'MaNguoiUngTuyen' => $application->MaNguoiUngTuyen,
            'HoTen' => $application->nguoiUngTuyen->HoTen,
        ];
    }
}

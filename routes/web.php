<?php

use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\CauLacBoController;
use App\Http\Controllers\DotTuyenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuKienController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/dang-nhap', [AuthPageController::class, 'login'])->name('login');
Route::get('/dang-ky', [AuthPageController::class, 'register'])->name('register');

// Tuyến đường fallback phục vụ tệp storage khi symlink bị lỗi trên môi trường triển khai
Route::get('/storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/'.$path);
    if (! file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath, [
        'Cache-Control' => 'public, max-age=86400',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*')->name('storage.fallback');

Route::middleware('auth')->group(function () {
    Route::get('/cai-dat', [AuthPageController::class, 'settings'])->name('settings');
    Route::put('/cai-dat', [AuthPageController::class, 'updateSettings'])->name('settings.update');
    Route::post('/dang-xuat', [AuthPageController::class, 'logout'])->name('logout');

    Route::get('caulacbo/{caulacbo}/thanh-vien', [CauLacBoController::class, 'members'])
        ->whereNumber('caulacbo')
        ->name('caulacbo.members');

    Route::patch('caulacbo/{caulacbo}/thanh-vien/{thanhVien}', [CauLacBoController::class, 'updateMember'])
        ->whereNumber('caulacbo')
        ->whereNumber('thanhVien')
        ->name('caulacbo.members.update');

    Route::delete('caulacbo/{caulacbo}/thanh-vien/{thanhVien}', [CauLacBoController::class, 'destroyMember'])
        ->whereNumber('caulacbo')
        ->whereNumber('thanhVien')
        ->name('caulacbo.members.destroy');

    Route::post('caulacbo/{caulacbo}/thanh-vien/{thanhVien}/roi', [CauLacBoController::class, 'requestLeave'])
        ->whereNumber('caulacbo')
        ->whereNumber('thanhVien')
        ->name('caulacbo.members.requestLeave');

    Route::post('caulacbo/{caulacbo}/thanh-vien/{thanhVien}/duyet-roi', [CauLacBoController::class, 'approveLeave'])
        ->whereNumber('caulacbo')
        ->whereNumber('thanhVien')
        ->name('caulacbo.members.approveLeave');

    Route::post('caulacbo/{caulacbo}/thanh-vien/{thanhVien}/tu-choi-roi', [CauLacBoController::class, 'rejectLeave'])
        ->whereNumber('caulacbo')
        ->whereNumber('thanhVien')
        ->name('caulacbo.members.rejectLeave');

    Route::get('caulacbo/{caulacbo}/dot-tuyen', [DotTuyenController::class, 'index'])
        ->whereNumber('caulacbo')
        ->name('caulacbo.recruitments.index');
    Route::get('caulacbo/{caulacbo}/dot-tuyen/tao', [DotTuyenController::class, 'create'])
        ->whereNumber('caulacbo')
        ->name('caulacbo.recruitments.create');
    Route::post('caulacbo/{caulacbo}/dot-tuyen', [DotTuyenController::class, 'store'])
        ->whereNumber('caulacbo')
        ->name('caulacbo.recruitments.store');
    Route::get('caulacbo/{caulacbo}/dot-tuyen/{dotTuyen}/sua', [DotTuyenController::class, 'edit'])
        ->whereNumber('caulacbo')
        ->whereNumber('dotTuyen')
        ->name('caulacbo.recruitments.edit');
    Route::put('caulacbo/{caulacbo}/dot-tuyen/{dotTuyen}', [DotTuyenController::class, 'update'])
        ->whereNumber('caulacbo')
        ->whereNumber('dotTuyen')
        ->name('caulacbo.recruitments.update');
    Route::delete('caulacbo/{caulacbo}/dot-tuyen/{dotTuyen}', [DotTuyenController::class, 'destroy'])
        ->whereNumber('caulacbo')
        ->whereNumber('dotTuyen')
        ->name('caulacbo.recruitments.destroy');
    Route::get('caulacbo/{caulacbo}/dot-tuyen/{dotTuyen}/don-ung-tuyen', [DotTuyenController::class, 'applications'])
        ->whereNumber('caulacbo')
        ->whereNumber('dotTuyen')
        ->name('caulacbo.recruitments.applications');
    Route::get('caulacbo/{caulacbo}/don-ung-tuyen/{donUngTuyen}', [DotTuyenController::class, 'application'])
        ->whereNumber('caulacbo')
        ->whereNumber('donUngTuyen')
        ->name('caulacbo.recruitments.application');
    Route::patch('caulacbo/{caulacbo}/don-ung-tuyen/{donUngTuyen}', [DotTuyenController::class, 'review'])
        ->whereNumber('caulacbo')
        ->whereNumber('donUngTuyen')
        ->name('caulacbo.recruitments.review');
    Route::get('caulacbo/{caulacbo}/don-ung-tuyen/{donUngTuyen}/ho-so', [DotTuyenController::class, 'resume'])
        ->whereNumber('caulacbo')
        ->whereNumber('donUngTuyen')
        ->name('caulacbo.recruitments.resume');
    Route::post('dot-tuyen/{dotTuyen}/ung-tuyen', [DotTuyenController::class, 'apply'])
        ->whereNumber('dotTuyen')
        ->name('dot-tuyen.apply');

    Route::get('dot-tuyen/{dotTuyen}', [DotTuyenController::class, 'show'])
        ->whereNumber('dotTuyen')
        ->name('dot-tuyen.show');

    Route::resource('caulacbo', CauLacBoController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->whereNumber('caulacbo');

    Route::post('sukien/bulk', [SuKienController::class, 'bulk'])->name('sukien.bulk');
    Route::get('sukien/{id}/diem-danh', [SuKienController::class, 'attendance'])->whereNumber('id')->name('sukien.attendance');
    Route::get('sukien/{id}', [SuKienController::class, 'show'])->whereNumber('id')->name('sukien.show');
    Route::resource('sukien', SuKienController::class)->except('show');
});

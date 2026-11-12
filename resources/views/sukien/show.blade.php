@extends('layouts.main')

@section('title', $suKien->TenSuKien . ' | UniClub')

@section('content')
    <section class="event-create-page event-readonly-page">
        <a class="detail-back" href="{{ route('sukien.index', $suKien->MaCLB ? ['ma_clb' => $suKien->MaCLB] : []) }}">← Quay lại danh sách sự kiện</a>
        <h1>Chi tiết sự kiện</h1>
        <p class="event-create-subtitle">Thông tin đầy đủ của sự kiện #{{ $suKien->MaSuKien }}.</p>

        <div class="event-form-card">
            @include('sukien._form_fields', ['readonly' => true])
            <div class="event-form-actions">
                <a class="secondary-button" href="{{ route('sukien.index', $suKien->MaCLB ? ['ma_clb' => $suKien->MaCLB] : []) }}">Đóng</a>
                <a class="secondary-button" href="{{ route('sukien.attendance', $suKien->MaSuKien) }}">Điểm danh &amp; QR</a>
                <a class="primary-button" href="{{ route('sukien.edit', $suKien->MaSuKien) }}">Chỉnh sửa</a>
            </div>
        </div>
    </section>
@endsection

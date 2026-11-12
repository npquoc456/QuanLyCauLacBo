@extends('layouts.main')

@section('title', 'Chỉnh sửa ' . $suKien->TenSuKien . ' | UniClub')

@section('content')
    <section class="event-create-page">
        <a class="detail-back" href="{{ route('sukien.index', $suKien->MaCLB ? ['ma_clb' => $suKien->MaCLB] : []) }}">← Quay lại danh sách sự kiện</a>
        <h1>Chỉnh sửa sự kiện</h1>
        <p class="event-create-subtitle">Cập nhật thông tin cho sự kiện #{{ $suKien->MaSuKien }}.</p>

        @if($errors->any())
            <div class="event-form-errors" role="alert">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sukien.update', $suKien->MaSuKien) }}" method="POST" enctype="multipart/form-data" class="event-form-card">
            @csrf
            @method('PUT')
            @include('sukien._form_fields')
            <div class="event-form-actions">
                <a class="secondary-button" href="{{ route('sukien.index', $suKien->MaCLB ? ['ma_clb' => $suKien->MaCLB] : []) }}">Hủy</a>
                <button class="primary-button" type="submit">Lưu thay đổi</button>
            </div>
        </form>
    </section>
@endsection

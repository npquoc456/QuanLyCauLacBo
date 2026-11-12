@extends('layouts.main')

@section('title', 'Tạo sự kiện mới | UniClub')

@section('content')
    <section class="event-create-page">
        <a class="detail-back" href="{{ route('caulacbo.index') }}{{ request()->filled('ma_clb') ? '#club-'.request('ma_clb') : '' }}">← Quay lại quản lý câu lạc bộ</a>
        <h1>Tạo sự kiện mới</h1>
        <p class="event-create-subtitle">
            @if(request()->filled('ma_clb'))
                Điền thông tin để tạo sự kiện cho CLB #{{ request('ma_clb') }}.
            @else
                Chọn câu lạc bộ để tạo và quản lý sự kiện.
            @endif
        </p>

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

        <form action="{{ route('sukien.store') }}" method="POST" enctype="multipart/form-data" class="event-form-card">
            @csrf
            @include('sukien._form_fields')
            <div class="event-form-actions">
                <a class="secondary-button" href="{{ route('caulacbo.index') }}{{ request()->filled('ma_clb') ? '#club-'.request('ma_clb') : '' }}">Hủy</a>
                <button class="primary-button" type="submit">＋ Xác nhận tạo sự kiện</button>
            </div>
        </form>
    </section>
@endsection

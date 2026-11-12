@php($club = $club ?? null)
<div class="club-form-grid">
    <label class="event-field event-field-full">
        <span>Tên câu lạc bộ <b>*</b></span>
        <input class="event-input" type="text" name="TenCLB" value="{{ old('TenCLB', $club?->TenCLB) }}" maxlength="255" required>
    </label>
    <label class="event-field event-field-full">
        <span>Mô tả</span>
        <textarea class="event-input event-textarea" name="MoTa" rows="3" placeholder="Giới thiệu ngắn về câu lạc bộ...">{{ old('MoTa', $club?->MoTa) }}</textarea>
    </label>
    <label class="event-field">
        <span>Email liên hệ</span>
        <input class="event-input" type="email" name="Email" value="{{ old('Email', $club?->Email) }}" maxlength="255" placeholder="clb@example.com">
    </label>
    <label class="event-field">
        <span>Số điện thoại</span>
        <input class="event-input" type="tel" name="SDT" value="{{ old('SDT', $club?->SDT) }}" maxlength="30" placeholder="090 000 0000">
    </label>
    <label class="event-field">
        <span>Trạng thái</span>
        <select class="event-input" name="TrangThai">
            @foreach (\App\Enums\CauLacBoTrangThai::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('TrangThai', $club?->TrangThai?->value ?? 'DANG_HOAT_DONG') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
    </label>
    <label class="event-field">
        <span>Mã danh mục CLB</span>
        <input class="event-input" type="number" name="MaDanhMucCLB" value="{{ old('MaDanhMucCLB', $club?->MaDanhMucCLB) }}" min="1" placeholder="Nhập mã danh mục">
    </label>
    <div class="event-field event-field-full">
        <label for="club-logo-{{ $club?->MaCLB ?? 'new' }}"><span>Logo câu lạc bộ</span></label>
        <div class="club-image-upload">
            <input class="club-image-file-input event-background-input" id="club-logo-{{ $club?->MaCLB ?? 'new' }}" type="file" name="URLLogo" accept="image/*" aria-describedby="club-logo-help-{{ $club?->MaCLB ?? 'new' }} club-logo-name-{{ $club?->MaCLB ?? 'new' }}">
            <label class="club-image-upload-button" for="club-logo-{{ $club?->MaCLB ?? 'new' }}">↑ Chọn logo từ thiết bị</label>
            <span class="club-image-file-name" id="club-logo-name-{{ $club?->MaCLB ?? 'new' }}" data-upload-filename>Chưa chọn tệp</span>
        </div>
        <small class="event-background-help" id="club-logo-help-{{ $club?->MaCLB ?? 'new' }}">Chọn logo từ thiết bị (tối đa 5 MB). Để trống khi sửa nếu muốn giữ ảnh hiện tại.</small>
        <img class="event-background-preview club-image-preview club-logo-preview" data-background-preview src="{{ $club?->URLLogo }}" alt="Logo hiện tại của câu lạc bộ" @if(! $club?->URLLogo) hidden @endif>
    </div>
    <div class="event-field event-field-full">
        <label for="club-cover-{{ $club?->MaCLB ?? 'new' }}"><span>Ảnh nền câu lạc bộ</span></label>
        <div class="club-image-upload">
            <input class="club-image-file-input event-background-input" id="club-cover-{{ $club?->MaCLB ?? 'new' }}" type="file" name="URLAnhNen" accept="image/*" aria-describedby="club-cover-help-{{ $club?->MaCLB ?? 'new' }} club-cover-name-{{ $club?->MaCLB ?? 'new' }}">
            <label class="club-image-upload-button" for="club-cover-{{ $club?->MaCLB ?? 'new' }}">↑ Chọn ảnh nền từ thiết bị</label>
            <span class="club-image-file-name" id="club-cover-name-{{ $club?->MaCLB ?? 'new' }}" data-upload-filename>Chưa chọn tệp</span>
        </div>
        <small class="event-background-help" id="club-cover-help-{{ $club?->MaCLB ?? 'new' }}">Chọn ảnh nền từ thiết bị (tối đa 5 MB). Để trống khi sửa nếu muốn giữ ảnh hiện tại.</small>
        <img class="event-background-preview club-image-preview club-cover-preview" data-background-preview src="{{ $club?->URLAnhNen }}" alt="Ảnh nền hiện tại của câu lạc bộ" @if(! $club?->URLAnhNen) hidden @endif>
    </div>
</div>

@php
    $event = $suKien ?? null;
    $formReadonly = $readonly ?? false;
    $dateTimeValues = static function (string $field) use ($event): array {
        $value = old($field);
        $timeValue = old($field.'_time', '');

        if ($value !== null && $value !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return [$value, $timeValue];
            }

            try {
                $dateTime = \Carbon\Carbon::parse($value);

                return [$dateTime->format('Y-m-d'), $dateTime->format('H:i')];
            } catch (\Throwable) {
                return ['', ''];
            }
        }

        $dateTime = $event?->{$field};

        return [$dateTime?->format('Y-m-d') ?? '', $dateTime?->format('H:i') ?? ''];
    };
@endphp

<div class="event-form-grid">
    <label class="event-field event-field-full">
        <span>Tên sự kiện <b>*</b></span>
        <input class="event-input" type="text" name="TenSuKien" value="{{ old('TenSuKien', $event?->TenSuKien) }}" maxlength="255" required @readonly($formReadonly)>
    </label>

    <label class="event-field event-field-full">
        <span>Mô tả sự kiện</span>
        <textarea class="event-input event-textarea" name="MoTa" rows="3" placeholder="Mô tả nội dung và mục tiêu sự kiện..." @readonly($formReadonly)>{{ old('MoTa', $event?->MoTa) }}</textarea>
    </label>

    <div class="event-field event-field-full">
        <label for="event-background-input"><span>Ảnh nền sự kiện</span></label>
        <input class="event-input event-background-input" id="event-background-input" type="file" name="URLAnhNen" accept="image/*" aria-describedby="event-background-help" @disabled($formReadonly)>
        <small class="event-background-help" id="event-background-help">Chọn ảnh từ thiết bị (tối đa 5 MB). Để trống khi sửa nếu muốn giữ ảnh hiện tại.</small>
        <img
            class="event-background-preview"
            data-background-preview
            src="{{ $event?->URLAnhNen }}"
            alt="Ảnh nền hiện tại của sự kiện"
            @if(! $event?->URLAnhNen) hidden @endif
        >
    </div>

    <label class="event-field">
        <span>Mã câu lạc bộ</span>
        <input class="event-input" type="number" name="MaCLB" value="{{ old('MaCLB', $event?->MaCLB ?? request('ma_clb')) }}" min="1" placeholder="Nhập mã CLB" @readonly($formReadonly)>
    </label>

    <label class="event-field">
        <span>Mã danh mục sự kiện</span>
        <input class="event-input" type="number" name="MaDanhMucSuKien" value="{{ old('MaDanhMucSuKien', $event?->MaDanhMucSuKien) }}" min="1" placeholder="Nhập mã danh mục" @readonly($formReadonly)>
    </label>

    <fieldset class="event-field event-type-field event-field-full">
        <legend>Loại sự kiện</legend>
        <div class="event-type-inline-options">
            @foreach (\App\Enums\SuKienLoai::cases() as $loai)
                <label class="event-type-inline-option">
                    <input type="radio" name="LoaiSuKien" value="{{ $loai->value }}" @checked(old('LoaiSuKien', $event?->LoaiSuKien?->value ?? 'NOI_BO') === $loai->value) @disabled($formReadonly)>
                    {{ $loai->label() }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="event-field event-field-full" data-internal-field>
        <label for="event-internal-category"><span>Phân loại nội bộ</span></label>
        <select class="event-input" id="event-internal-category" name="PhanLoaiNoiBo" @disabled($formReadonly)>
            <option value="">Không chọn</option>
            @foreach (\App\Enums\SuKienPhanLoaiNoiBo::cases() as $phanLoai)
                <option value="{{ $phanLoai->value }}" @selected(old('PhanLoaiNoiBo', $event?->PhanLoaiNoiBo?->value) === $phanLoai->value)>
                    {{ $phanLoai->label() }}
                </option>
            @endforeach
        </select>
        <small class="event-background-help">Sự kiện công khai không có phân loại nội bộ.</small>
    </div>

    <label class="event-field">
        <span>Số lượng tối đa <b>*</b></span>
        <input class="event-input" type="number" name="SoLuongToiDa" value="{{ old('SoLuongToiDa', $event?->SoLuongToiDa ?? 50) }}" min="1" required @readonly($formReadonly)>
    </label>

    <div class="event-field">
        <span>Trạng thái</span>
        <output class="event-input">
            {{ $event?->TrangThai?->value
                ? str_replace('_', ' ', $event->TrangThai->value)
                : 'Sẽ tự động tính theo thời gian đã đặt' }}
        </output>
        <small class="event-background-help">Trạng thái tự động cập nhật theo các mốc thời gian.</small>
    </div>

    <label class="event-field event-field-full">
        <span>Địa điểm</span>
        <input class="event-input" type="text" name="DiaDiem" value="{{ old('DiaDiem', $event?->DiaDiem) }}" maxlength="255" placeholder="Nhập địa điểm tổ chức" @readonly($formReadonly)>
    </label>

    @foreach([
        'ThoiGianMoDangKy' => ['Thời gian mở đăng ký', true],
        'ThoiGianDongDangKy' => ['Thời gian đóng đăng ký', true],
        'ThoiGianBatDau' => ['Thời gian bắt đầu', true],
        'ThoiGianKetThuc' => ['Thời gian kết thúc', true],
    ] as $field => [$label, $required])
        @php([$dateValue, $timeValue] = $dateTimeValues($field))
        <fieldset class="event-field event-datetime-field">
            <legend>{{ $label }} @if($required)<b>*</b>@endif</legend>
            <div class="event-datetime-inputs">
                <label>
                    <span>Ngày</span>
                    <input class="event-input" type="date" name="{{ $field }}" value="{{ $dateValue }}" @required($required) @disabled($formReadonly)>
                </label>
                <label>
                    <span>Giờ (24h)</span>
                    <input class="event-input" type="time" name="{{ $field }}_time" value="{{ $timeValue }}" step="60" @required($required) @disabled($formReadonly)>
                </label>
            </div>
            <small class="event-background-help">Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.</small>
        </fieldset>
    @endforeach
</div>

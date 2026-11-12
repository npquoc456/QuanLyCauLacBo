@extends('layouts.main')

@section('title', isset($editing) && $editing ? 'Chỉnh sửa đợt tuyển | UniClub' : 'Tạo đợt tuyển mới | UniClub')

@section('content')
    @php
        $campaign = $campaign ?? null;
        $editing = $editing ?? false;
        $activeManagementTab = 'recruitments';
    @endphp

    <div class="sync-status"><span></span> Đã đồng bộ dữ liệu thời gian thực <i>·</i> Cập nhật {{ now()->format('H:i') }}</div>

    @if ($managementNavigation)
        @include('caulacbo.management-tabs')
    @endif

    <div class="uniclub-recruitment-page form-page-mode">
        <!-- Back Link -->
        <a class="uniclub-form-back-link" href="{{ route('caulacbo.recruitments.index', $club->MaCLB) }}">
            <span class="back-arrow">←</span> Quay lại danh sách đợt tuyển
        </a>

        <!-- Form Heading -->
        <div class="uniclub-form-header">
            <h1 class="form-title">{{ $editing ? 'Chỉnh sửa đợt tuyển' : 'Tạo đợt tuyển mới' }}</h1>
            <p class="form-subtitle">Nhập thông tin đầy đủ để khởi tạo đợt tuyển quân &amp; tiếp nhận hồ sơ thành viên mới.</p>
        </div>

        @if ($errors->any())
            <div class="uniclub-alert alert-error" role="alert">
                <span class="alert-icon">⚠</span>
                <div class="alert-text">
                    <strong>Vui lòng kiểm tra lại thông tin:</strong>
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form class="uniclub-form-container-card"
              method="POST"
              action="{{ $editing ? route('caulacbo.recruitments.update', [$club->MaCLB, $campaign->MaDotTuyen]) : route('caulacbo.recruitments.store', $club->MaCLB) }}"
              enctype="multipart/form-data"
              id="recruitment-form">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <!-- 1. Tên đợt tuyển -->
            <div class="form-group-block">
                <label class="form-field-label" for="field-ten-dot-tuyen">
                    Tên đợt tuyển <span class="required-star">*</span>
                </label>
                <input class="form-input-text"
                       type="text"
                       id="field-ten-dot-tuyen"
                       name="TenDotTuyen"
                       value="{{ old('TenDotTuyen', $campaign->TenDotTuyen ?? '') }}"
                       placeholder="Nhập tên đợt tuyển (Ví dụ: Tuyển Thành viên &amp; CTV Gen 6 - Kỳ Xuân 2026)"
                       maxlength="255"
                       required>
                @error('TenDotTuyen')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            <!-- 2. Mô tả đợt tuyển -->
            <div class="form-group-block">
                <label class="form-field-label" for="field-mo-ta">
                    Mô tả đợt tuyển
                </label>
                <textarea class="form-textarea-input"
                          id="field-mo-ta"
                          name="MoTa"
                          rows="3"
                          maxlength="500"
                          placeholder="Mô tả chi tiết nội dung, mục tiêu tuyển sinh, quyền lợi và tiêu chuẩn ứng viên...">{{ old('MoTa', $campaign->MoTa ?? '') }}</textarea>
                @error('MoTa')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            <!-- 3. Ảnh nền / Poster đợt tuyển -->
            <div class="form-group-block">
                <label class="form-field-label" for="field-poster">
                    Ảnh nền / Poster đợt tuyển
                </label>
                <div class="form-file-box">
                    <input type="file"
                           id="field-poster"
                           name="poster"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>
                <small class="form-helper-text">
                    @if (! empty($campaign?->URLPoster))
                        Đang có poster. Chọn ảnh mới nếu muốn thay thế (tối đa 5 MB). Định dạng JPG, PNG, WEBP.
                    @else
                        Chọn ảnh từ thiết bị (tối đa 5 MB). Định dạng JPG, PNG, WEBP. Để trống nếu muốn dùng ảnh mặc định CLB.
                    @endif
                </small>
                @error('poster')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            <!-- 4. Row 2 Columns: Mã CLB & Trạng thái -->
            <div class="form-row-two-cols">
                <div class="form-group-block">
                    <label class="form-field-label" for="field-ma-clb">
                        Mã câu lạc bộ <span class="required-star">*</span>
                    </label>
                    <input class="form-input-text is-readonly"
                           type="text"
                           id="field-ma-clb"
                           value="{{ $club->MaCLB }} - {{ $club->TenCLB }}"
                           readonly>
                </div>

                <div class="form-group-block">
                    <label class="form-field-label" for="field-trang-thai">
                        Trạng thái <span class="required-star">*</span>
                    </label>
                    <div class="select-dropdown-wrapper">
                        <select class="form-select-input" id="field-trang-thai" name="TrangThai">
                            <option value="DANG_TUYEN" @selected(old('TrangThai', $campaign->TrangThai?->value ?? 'DANG_TUYEN') === 'DANG_TUYEN')>
                                DANG_TUYEN (Đang mở nhận đơn)
                            </option>
                            <option value="CHUA_TUYEN" @selected(old('TrangThai', $campaign->TrangThai?->value ?? '') === 'CHUA_TUYEN')>
                                CHUA_TUYEN (Chưa mở nhận đơn)
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. Row 2 Columns: Chỉ tiêu & Đối tượng -->
            <div class="form-row-two-cols">
                <div class="form-group-block">
                    <label class="form-field-label" for="recruitment-target-total">
                        Chỉ tiêu số lượng tối đa <span class="required-star">*</span>
                    </label>
                    <input class="form-input-text"
                           type="number"
                           id="recruitment-target-total"
                           name="ChiTieuTong"
                           value="{{ old('ChiTieuTong', isset($campaign) ? $campaign->viTriTuyens->sum('SoLuong') : '') }}"
                           placeholder="Nhập chỉ tiêu tối đa"
                           min="1"
                           max="10000"
                           required>
                </div>

                <div class="form-group-block">
                    <label class="form-field-label" for="field-doi-tuong">
                        Đối tượng tuyển sinh
                    </label>
                    <input class="form-input-text"
                           type="text"
                           id="field-doi-tuong"
                           name="DoiTuong"
                           value="{{ old('DoiTuong', $campaign->DoiTuong ?? '') }}"
                           placeholder="Ví dụ: Sinh viên năm 1, năm 2, năm 3 toàn trường"
                           maxlength="255">
                </div>
            </div>

            <!-- 6. Địa điểm / Hình thức phỏng vấn -->
            <div class="form-group-block">
                <label class="form-field-label" for="field-dia-diem">
                    Địa điểm / Hình thức phỏng vấn <span class="required-star">*</span>
                </label>
                <input class="form-input-text"
                       type="text"
                       id="field-dia-diem"
                       name="DiaDiemPhongVan"
                       value="{{ old('DiaDiemPhongVan', $campaign->DiaDiemPhongVan ?? '') }}"
                       placeholder="Ví dụ: Văn phòng CLB Tòa A5 hoặc Phỏng vấn Online qua Google Meet"
                       maxlength="255"
                       required>
                @error('DiaDiemPhongVan')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            <!-- 7. Date Row 1: Mở nhận hồ sơ & Đóng nhận hồ sơ -->
            @php
                $startDate = $campaign?->NgayBatDau?->format('Y-m-d') ?? '';
                $startTime = $campaign?->NgayBatDau?->format('H:i') ?? '';
                $endDate = $campaign?->NgayKetThuc?->format('Y-m-d') ?? '';
                $endTime = $campaign?->NgayKetThuc?->format('H:i') ?? '';
                $interviewDate = $campaign?->NgayPhongVan?->format('Y-m-d') ?? '';
                $interviewTime = $campaign?->NgayPhongVan?->format('H:i') ?? '';
                $resultDate = $campaign?->NgayCongBoKetQua?->format('Y-m-d') ?? '';
                $resultTime = $campaign?->NgayCongBoKetQua?->format('H:i') ?? '';
            @endphp
            <div class="form-row-two-cols">
                <!-- Mở nhận hồ sơ -->
                <div class="form-group-block">
                    <label class="form-field-label">
                        Thời gian mở nhận hồ sơ <span class="required-star">*</span>
                    </label>
                    <div class="date-time-dual-inputs">
                        <div class="sub-input-box">
                            <span class="sub-box-label">Ngày</span>
                            <input class="form-input-date"
                                   type="date"
                                   name="NgayBatDau_date"
                                   value="{{ old('NgayBatDau_date', $startDate) }}"
                                   data-date-link="NgayBatDau"
                                   required>
                        </div>
                        <div class="sub-input-box">
                            <span class="sub-box-label">Giờ (24h)</span>
                            <input class="form-input-time"
                                   type="time"
                                   name="NgayBatDau_time"
                                   value="{{ old('NgayBatDau_time', $startTime) }}"
                                   data-time-link="NgayBatDau"
                                   required>
                        </div>
                    </div>
                    <input type="hidden"
                           id="hidden-NgayBatDau"
                           name="NgayBatDau"
                           value="{{ old('NgayBatDau', ($startDate && $startTime) ? $startDate . 'T' . $startTime : '') }}">
                    <small class="form-helper-text">Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.</small>
                    @error('NgayBatDau')
                        <span class="form-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Đóng nhận hồ sơ -->
                <div class="form-group-block">
                    <label class="form-field-label">
                        Thời gian đóng nhận hồ sơ <span class="required-star">*</span>
                    </label>
                    <div class="date-time-dual-inputs">
                        <div class="sub-input-box">
                            <span class="sub-box-label">Ngày</span>
                            <input class="form-input-date"
                                   type="date"
                                   name="NgayKetThuc_date"
                                   value="{{ old('NgayKetThuc_date', $endDate) }}"
                                   data-date-link="NgayKetThuc"
                                   required>
                        </div>
                        <div class="sub-input-box">
                            <span class="sub-box-label">Giờ (24h)</span>
                            <input class="form-input-time"
                                   type="time"
                                   name="NgayKetThuc_time"
                                   value="{{ old('NgayKetThuc_time', $endTime) }}"
                                   data-time-link="NgayKetThuc"
                                   required>
                        </div>
                    </div>
                    <input type="hidden"
                           id="hidden-NgayKetThuc"
                           name="NgayKetThuc"
                           value="{{ old('NgayKetThuc', ($endDate && $endTime) ? $endDate . 'T' . $endTime : '') }}">
                    <small class="form-helper-text">Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.</small>
                    @error('NgayKetThuc')
                        <span class="form-error-msg">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- 8. Date Row 2: Bắt đầu phỏng vấn & Công bố kết quả -->
            <div class="form-row-two-cols">
                <!-- Bắt đầu phỏng vấn -->
                <div class="form-group-block">
                    <label class="form-field-label">
                        Thời gian bắt đầu phỏng vấn <span class="required-star">*</span>
                    </label>
                    <div class="date-time-dual-inputs">
                        <div class="sub-input-box">
                            <span class="sub-box-label">Ngày</span>
                            <input class="form-input-date"
                                   type="date"
                                   name="NgayPhongVan_date"
                                   value="{{ old('NgayPhongVan_date', $interviewDate) }}"
                                   data-date-link="NgayPhongVan"
                                   required>
                        </div>
                        <div class="sub-input-box">
                            <span class="sub-box-label">Giờ (24h)</span>
                            <input class="form-input-time"
                                   type="time"
                                   name="NgayPhongVan_time"
                                   value="{{ old('NgayPhongVan_time', $interviewTime) }}"
                                   data-time-link="NgayPhongVan"
                                   required>
                        </div>
                    </div>
                    <input type="hidden"
                           id="hidden-NgayPhongVan"
                           name="NgayPhongVan"
                           value="{{ old('NgayPhongVan', ($interviewDate && $interviewTime) ? $interviewDate . 'T' . $interviewTime : '') }}">
                    <small class="form-helper-text">Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.</small>
                    @error('NgayPhongVan')
                        <span class="form-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Công bố kết quả -->
                <div class="form-group-block">
                    <label class="form-field-label">
                        Thời gian công bố kết quả <span class="required-star">*</span>
                    </label>
                    <div class="date-time-dual-inputs">
                        <div class="sub-input-box">
                            <span class="sub-box-label">Ngày</span>
                            <input class="form-input-date"
                                   type="date"
                                   name="NgayCongBoKetQua_date"
                                   value="{{ old('NgayCongBoKetQua_date', $resultDate) }}"
                                   data-date-link="NgayCongBoKetQua"
                                   required>
                        </div>
                        <div class="sub-input-box">
                            <span class="sub-box-label">Giờ (24h)</span>
                            <input class="form-input-time"
                                   type="time"
                                   name="NgayCongBoKetQua_time"
                                   value="{{ old('NgayCongBoKetQua_time', $resultTime) }}"
                                   data-time-link="NgayCongBoKetQua"
                                   required>
                        </div>
                    </div>
                    <input type="hidden"
                           id="hidden-NgayCongBoKetQua"
                           name="NgayCongBoKetQua"
                           value="{{ old('NgayCongBoKetQua', ($resultDate && $resultTime) ? $resultDate . 'T' . $resultTime : '') }}">
                    <small class="form-helper-text">Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.</small>
                    @error('NgayCongBoKetQua')
                        <span class="form-error-msg">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- 9. Chỉ tiêu vị trí ứng tuyển theo Ban -->
            <section class="positions-section-block">
                <div class="positions-header-bar">
                    <h3 class="positions-heading-title">
                        <span style="display: none;">Chỉ tiêu vị trí tuyển theo Ban</span>
                        <span>Chỉ tiêu vị trí ứng tuyển theo Ban</span>
                    </h3>
                    <button class="positions-add-btn" type="button" data-add-position>
                        + Thêm vị trí
                    </button>
                </div>

                <div class="positions-container-box" data-position-list>
                    @php
                        $defaultPositions = isset($campaign)
                            ? $campaign->viTriTuyens->map(fn ($pos) => [
                                'MaViTri' => $pos->MaViTri,
                                'TenViTri' => $pos->TenViTri,
                                'Mota' => $pos->Mota,
                                'SoLuong' => $pos->SoLuong,
                                'MaBan' => $pos->MaBan,
                            ])->all()
                            : [
                                ['TenViTri' => '', 'Mota' => '', 'SoLuong' => '', 'MaBan' => ''],
                            ];
                        $positionsList = old('positions', $defaultPositions);
                        $colorPalette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];
                    @endphp

                    @foreach ($positionsList as $idx => $position)
                        @php
                            $dotColor = $colorPalette[$idx % count($colorPalette)];
                            $hasTitle = ! empty($position['TenViTri']);
                        @endphp
                        <div class="position-row-card" data-position-row>
                            @if (! empty($position['MaViTri']))
                                <input type="hidden" name="positions[{{ $idx }}][MaViTri]" value="{{ $position['MaViTri'] }}">
                            @endif

                            <!-- Display strip matching image -->
                            <div class="position-preview-strip">
                                <div class="strip-left">
                                    <span class="strip-bullet-dot" style="background-color: {{ $dotColor }};"></span>
                                    <span class="strip-title-text" data-display-title>{{ $hasTitle ? $position['TenViTri'] : 'Nhập thông tin vị trí bên dưới' }}</span>
                                </div>
                                <div class="strip-right">
                                    <span class="strip-quota-text">Chỉ tiêu: <strong data-display-quota>{{ !empty($position['SoLuong']) ? $position['SoLuong'] : '0' }}</strong></span>
                                    <button class="strip-btn-edit" type="button" title="Chỉnh sửa thông tin vị trí" data-toggle-editor>✎</button>
                                    <button class="strip-btn-remove" type="button" aria-label="Xóa vị trí" data-remove-position>×</button>
                                </div>
                            </div>

                            <!-- Expandable Form Row for Editing Fields (visible by default when empty) -->
                            <div class="position-inline-inputs {{ $hasTitle ? '' : 'is-visible' }}" data-inline-editor>
                                <div class="input-col-name">
                                    <label>Tên vị trí <span class="required-star">*</span></label>
                                    <input class="form-input-sm"
                                           name="positions[{{ $idx }}][TenViTri]"
                                           value="{{ $position['TenViTri'] ?? '' }}"
                                           placeholder="Ví dụ: Ban Kỹ thuật &amp; R&amp;D AI"
                                           data-field-name
                                           required>
                                </div>
                                <div class="input-col-dept">
                                    <label>Ban thuộc CLB</label>
                                    <select class="form-select-sm" name="positions[{{ $idx }}][MaBan]">
                                        <option value="">Chưa phân ban</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->MaBan }}" @selected(($position['MaBan'] ?? '') == $department->MaBan)>
                                                {{ $department->TenBan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-col-quota">
                                    <label>Chỉ tiêu <span class="required-star">*</span></label>
                                    <input class="form-input-sm"
                                           type="number"
                                           name="positions[{{ $idx }}][SoLuong]"
                                           value="{{ $position['SoLuong'] ?? '' }}"
                                           min="1"
                                           max="10000"
                                           placeholder="Chỉ tiêu"
                                           data-position-count
                                           required>
                                </div>
                                <div class="input-col-desc">
                                    <label>Mô tả ngắn</label>
                                    <input class="form-input-sm"
                                           name="positions[{{ $idx }}][Mota]"
                                           value="{{ $position['Mota'] ?? '' }}"
                                           placeholder="Mô tả công việc">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- 10. Form Actions -->
            <div class="form-actions-toolbar">
                <a class="form-btn-white" href="{{ route('caulacbo.recruitments.index', $club->MaCLB) }}">
                    Đóng
                </a>
                <button class="form-btn-white btn-draft" type="submit" name="action" value="draft">
                    Lưu bản nháp
                </button>
                <button class="form-btn-navy" type="submit" name="action" value="publish">
                    {{ $editing ? 'Cập nhật đợt tuyển' : 'Tạo đợt tuyển' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Template for new Position -->
    <template id="recruitment-position-template">
        <div class="position-row-card" data-position-row>
            <div class="position-preview-strip">
                <div class="strip-left">
                    <span class="strip-bullet-dot" style="background-color: #3b82f6;"></span>
                    <span class="strip-title-text" data-display-title>Nhập thông tin vị trí bên dưới</span>
                </div>
                <div class="strip-right">
                    <span class="strip-quota-text">Chỉ tiêu: <strong data-display-quota>0</strong></span>
                    <button class="strip-btn-edit" type="button" title="Chỉnh sửa thông tin vị trí" data-toggle-editor>✎</button>
                    <button class="strip-btn-remove" type="button" aria-label="Xóa vị trí" data-remove-position>×</button>
                </div>
            </div>

            <div class="position-inline-inputs is-visible" data-inline-editor>
                <div class="input-col-name">
                    <label>Tên vị trí <span class="required-star">*</span></label>
                    <input class="form-input-sm"
                           data-name="TenViTri"
                           value=""
                           placeholder="Ví dụ: Ban Truyền thông &amp; Media"
                           data-field-name
                           required>
                </div>
                <div class="input-col-dept">
                    <label>Ban thuộc CLB</label>
                    <select class="form-select-sm" data-name="MaBan">
                        <option value="">Chưa phân ban</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->MaBan }}">{{ $department->TenBan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-col-quota">
                    <label>Chỉ tiêu <span class="required-star">*</span></label>
                    <input class="form-input-sm"
                           type="number"
                           data-name="SoLuong"
                           value=""
                           min="1"
                           max="10000"
                           placeholder="Chỉ tiêu"
                           data-position-count
                           required>
                </div>
                <div class="input-col-desc">
                    <label>Mô tả ngắn</label>
                    <input class="form-input-sm"
                           data-name="Mota"
                           value=""
                           placeholder="Mô tả công việc">
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        (() => {
            const list = document.querySelector('[data-position-list]');
            const template = document.querySelector('#recruitment-position-template');
            const totalInput = document.querySelector('#recruitment-target-total');

            // Synchronize date and time inputs to hidden combined inputs
            const syncDateTimes = () => {
                const fields = ['NgayBatDau', 'NgayKetThuc', 'NgayPhongVan', 'NgayCongBoKetQua'];
                fields.forEach((field) => {
                    const dateEl = document.querySelector(`[data-date-link="${field}"]`);
                    const timeEl = document.querySelector(`[data-time-link="${field}"]`);
                    const hiddenEl = document.querySelector(`#hidden-${field}`);
                    if (dateEl && timeEl && hiddenEl) {
                        const d = dateEl.value;
                        const t = timeEl.value || '00:00';
                        if (d) {
                            hiddenEl.value = `${d}T${t}`;
                        } else {
                            hiddenEl.value = '';
                        }
                    }
                });
            };

            document.querySelectorAll('[data-date-link], [data-time-link]').forEach((el) => {
                el.addEventListener('change', syncDateTimes);
                el.addEventListener('input', syncDateTimes);
            });
            syncDateTimes();

            // Position list management
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];

            const refreshPositions = () => {
                if (!list) return;
                let totalQuota = 0;
                let hasValidQuota = false;
                const rows = list.querySelectorAll('[data-position-row]');
                rows.forEach((row, index) => {
                    // Update input names
                    row.querySelectorAll('input, select').forEach((input) => {
                        const field = input.dataset.name || input.name.match(/\]\[([^\]]+)\]$/)?.[1];
                        if (field) {
                            input.name = `positions[${index}][${field}]`;
                        }
                    });

                    // Update dot color
                    const dot = row.querySelector('.strip-bullet-dot');
                    if (dot) dot.style.backgroundColor = colors[index % colors.length];

                    // Update preview values
                    const nameInput = row.querySelector('[data-field-name]');
                    const displayTitle = row.querySelector('[data-display-title]');
                    if (nameInput && displayTitle) {
                        displayTitle.textContent = nameInput.value.trim() || 'Nhập thông tin vị trí bên dưới';
                    }

                    const countInput = row.querySelector('[data-position-count]');
                    const displayQuota = row.querySelector('[data-display-quota]');
                    if (countInput && displayQuota) {
                        const val = Number.parseInt(countInput.value, 10);
                        if (!Number.isNaN(val) && val > 0) {
                            displayQuota.textContent = String(val);
                            totalQuota += val;
                            hasValidQuota = true;
                        } else {
                            displayQuota.textContent = '0';
                        }
                    }
                });

                if (totalInput && hasValidQuota) {
                    totalInput.value = String(totalQuota);
                }
            };

            document.querySelector('[data-add-position]')?.addEventListener('click', () => {
                if (!list || !template) return;
                const index = list.querySelectorAll('[data-position-row]').length;
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[data-name]').forEach((input) => {
                    input.name = `positions[${index}][${input.dataset.name}]`;
                });
                list.append(clone);
                refreshPositions();
            });

            list?.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('[data-remove-position]');
                if (removeBtn) {
                    const rows = list.querySelectorAll('[data-position-row]');
                    if (rows.length > 1) {
                        removeBtn.closest('[data-position-row]').remove();
                        refreshPositions();
                    } else {
                        alert('Đợt tuyển cần ít nhất 1 vị trí tuyển.');
                    }
                    return;
                }

                const toggleBtn = event.target.closest('[data-toggle-editor]');
                if (toggleBtn) {
                    const row = toggleBtn.closest('[data-position-row]');
                    const editor = row?.querySelector('[data-inline-editor]');
                    if (editor) {
                        editor.classList.toggle('is-visible');
                    }
                }
            });

            list?.addEventListener('input', (event) => {
                if (event.target.matches('[data-field-name], [data-position-count]')) {
                    refreshPositions();
                }
            });

            refreshPositions();
        })();
    </script>
@endpush

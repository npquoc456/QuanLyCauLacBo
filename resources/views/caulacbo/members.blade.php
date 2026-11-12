@extends('layouts.main')

@section('title', 'Thành viên CLB | UniClub')

@section('content')
    <div class="sync-status"><span></span> HỆ THỐNG VẬN HÀNH CLB SINH VIÊN</div>

    @php
        $activeManagementTab = 'members';

        $formatRole = function($role) {
            $normalized = strtoupper(trim((string)$role));
            return match ($normalized) {
                'CHU_NHIEM', 'CHU NHIEM' => 'Chủ nhiệm',
                'PHO_CHU_NHIEM', 'PHO CHU NHIEM' => 'Phó chủ nhiệm',
                'TRUONG_BAN', 'TRUONG BAN' => 'Trưởng ban',
                'PHO_BAN', 'PHO BAN' => 'Phó ban',
                'THANH_VIEN', 'THANH VIEN' => 'Thành viên',
                'CONG_TAC_VIEN', 'CONG TAC VIEN' => 'Cộng tác viên',
                'QUAN_LY', 'QUAN LY' => 'Quản lý',
                default => $role,
            };
        };

        $formatMemberStatus = function($status) {
            if ($status instanceof \App\Enums\ThanhVienTrangThai) {
                return $status->label();
            }
            $enum = \App\Enums\ThanhVienTrangThai::tryFrom((string) $status);
            if ($enum) {
                return $enum->label();
            }
            return match (strtoupper(trim((string)$status))) {
                'DANG_HOAT_DONG', 'DANG HOAT DONG' => 'Đang hoạt động',
                'TAM_NGHI', 'TAM NGHI' => 'Tạm nghỉ',
                'CHO_PHE_DUYET', 'CHO PHE DUYET' => 'Chờ phê duyệt',
                'CHO_DUYET_ROI', 'CHO DUYET ROI' => 'Chờ duyệt rời',
                'DA_ROI_CLB', 'DA ROI CLB' => 'Đã rời CLB',
                default => str_replace('_', ' ', (string) $status),
            };
        };
    @endphp
    @include('caulacbo.management-tabs')

    <div class="club-members-breadcrumb">
        <a href="{{ route('caulacbo.index') }}">UNICLUB</a>
        <span>/</span>
        <a href="{{ route('caulacbo.index') }}">QUẢN LÝ CÂU LẠC BỘ</a>
        <span>/</span>
        <strong>THÀNH VIÊN CLB</strong>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 16px;">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="page-intro club-members-intro">
        <div>
            <h1>Thành viên &amp; Điểm danh CLB</h1>
            <p>Danh sách thành viên thuộc {{ $club->TenCLB }}. Quản lý danh sách, cấp quyền điều hành và theo dõi trạng thái thành viên.</p>
        </div>
    </section>

    <section class="management-layout club-members-layout">
        <aside class="event-overview">
            <div class="overview-heading">
                <h2>Ban chuyên môn</h2>
                <span>{{ $totalMembers }}</span>
            </div>
            <nav class="overview-nav" aria-label="Lọc thành viên theo chức vụ">
                <a class="overview-link {{ $roleFilter === '' && $statusFilter === '' ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.members', $club->MaCLB) }}">
                    <span>▤</span><strong>Tất cả</strong><b>{{ $totalMembers }}</b>
                </a>
                @foreach ($roles as $role => $count)
                    <a class="overview-link {{ $roleFilter === $role ? 'is-active' : '' }}"
                       href="{{ route('caulacbo.members', ['caulacbo' => $club->MaCLB, 'role' => $role]) }}">
                        <span>♣</span><strong>{{ $formatRole($role) }}</strong><b>{{ $count }}</b>
                    </a>
                @endforeach
                <a class="overview-link {{ $statusFilter === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.members', ['caulacbo' => $club->MaCLB, 'status' => \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value]) }}">
                    <span>◷</span><strong>Chờ phê duyệt</strong><b>{{ $pendingMembers }}</b>
                </a>
                @if (($pendingLeaveMembers ?? 0) > 0)
                    <a class="overview-link {{ $statusFilter === \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value ? 'is-active' : '' }}"
                       href="{{ route('caulacbo.members', ['caulacbo' => $club->MaCLB, 'status' => \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value]) }}">
                        <span>⇥</span><strong>Chờ duyệt rời</strong><b>{{ $pendingLeaveMembers }}</b>
                    </a>
                @endif
            </nav>
        </aside>

        <section class="events-panel club-members-panel">
            <div class="overview-banner">
                <div class="overview-banner-top">
                    <div>
                        <strong>{{ $totalMembers }} <span>thành viên đang quản lý</span></strong>
                        <small>{{ $club->TenCLB }} · {{ $pendingMembers }} thành viên chờ phê duyệt</small>
                    </div>
                    <div class="overview-legend">
                        <span><i class="legend-internal"></i> Đang hoạt động: {{ $activeMembers }}</span>
                        <span><i class="legend-public"></i> Tạm nghỉ: {{ $pausedMembers }}</span>
                    </div>
                </div>
                <div class="overview-progress"><span style="width: {{ $totalMembers > 0 ? round($activeMembers / $totalMembers * 100) : 0 }}%"></span></div>
            </div>

            <div class="list-panel-header">
                <div class="list-title">
                    <h2>Danh sách thành viên</h2>
                    <span>{{ $members->total() }}</span>
                </div>
                <form class="search-form" method="GET" action="{{ route('caulacbo.members', $club->MaCLB) }}">
                    @if ($roleFilter !== '')
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                    @endif
                    @if ($statusFilter !== '')
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên thành viên, MSSV, email...">
                    <button type="submit" aria-label="Tìm kiếm">⌕</button>
                </form>
            </div>

            <div class="club-member-table">
                <div class="club-member-heading">
                    <span>Thành viên / MSSV</span>
                    <span>Ban chuyên môn / Vai trò</span>
                    <span>Điểm rèn luyện</span>
                    <span>Trạng thái</span>
                    <span>Thao tác</span>
                </div>
                @forelse ($members as $membership)
                    @php
                        $member = $membership->nguoiDung;
                        $memberStatus = $membership->TrangThai?->value ?? 'CHUA_CAP_NHAT';
                        $hasManagementRight = app(\App\Services\CauLacBoService::class)->isManagementRole($membership->ChucVu);
                        $isSelf = (int) $member->MaNguoiDung === (int) (auth()->id() ?? 0);
                    @endphp
                    <article class="club-member-row">
                        <div class="club-member-identity">
                            @if ($member->URLAnhDaiDien)
                                <img src="{{ $member->URLAnhDaiDien }}" alt="" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                                <span style="display: none;">{{ mb_strtoupper(mb_substr($member->HoTen, 0, 1)) }}</span>
                            @else
                                <span>{{ mb_strtoupper(mb_substr($member->HoTen, 0, 1)) }}</span>
                            @endif
                            <div>
                                <strong>{{ $member->HoTen }}</strong>
                                <small>{{ $member->Email }} · {{ $member->MSSV }}</small>
                            </div>
                        </div>
                        <div class="club-member-role">
                            <span class="meta-badge">CLB #{{ $club->MaCLB }}</span>
                            <strong>{{ $formatRole($membership->ChucVu) }}</strong>
                            @if ($hasManagementRight)
                                <span class="manager-role-badge">Quyền quản lý</span>
                            @endif
                            <small>{{ $membership->NgayThamGia?->format('d/m/Y') ?? 'Chưa cập nhật ngày tham gia' }}</small>
                        </div>
                        <div class="club-member-points">
                            <strong>—</strong>
                            <small>Chưa có dữ liệu</small>
                        </div>
                        <span class="checkin-badge {{ $memberStatus === \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value ? 'checkin-open' : ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value ? 'checkin-pending' : ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value ? 'checkin-leave' : '')) }}">
                            <i></i>{{ $formatMemberStatus($memberStatus) }}
                        </span>
                        <div class="club-member-actions">
                            @if ($canManage ?? true)
                                @if ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value)
                                    <form action="{{ route('caulacbo.members.update', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="ChucVu" value="{{ $membership->ChucVu ?: 'Thành viên' }}">
                                        <input type="hidden" name="TrangThai" value="{{ \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value }}">
                                        <button type="submit" class="btn-member-action btn-member-approve" title="Phê duyệt chính thức thành viên này">
                                            Phê duyệt
                                        </button>
                                    </form>
                                    <form action="{{ route('caulacbo.members.update', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối ứng viên này không?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="ChucVu" value="{{ $membership->ChucVu ?: 'Thành viên' }}">
                                        <input type="hidden" name="TrangThai" value="{{ \App\Enums\ThanhVienTrangThai::DA_ROI_CLB->value }}">
                                        <button type="submit" class="btn-member-action btn-member-reject" title="Từ chối ứng viên này">
                                            Từ chối
                                        </button>
                                    </form>
                                @elseif ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value)
                                    <form action="{{ route('caulacbo.members.approveLeave', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn đồng ý cho thành viên {{ $member->HoTen }} rời câu lạc bộ?');">
                                        @csrf
                                        <button type="submit" class="btn-member-action btn-member-approve" title="Phê duyệt cho thành viên rời CLB">
                                            Duyệt rời
                                        </button>
                                    </form>
                                    <form action="{{ route('caulacbo.members.rejectLeave', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn từ chối yêu cầu rời và giữ lại thành viên?');">
                                        @csrf
                                        <button type="submit" class="btn-member-action btn-member-detail" title="Từ chối yêu cầu rời, giữ lại thành viên">
                                            Giữ lại
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn-member-action" data-open-member-modal="member-modal-{{ $membership->MaThanhVien }}" title="Cấp quyền quản lý hoặc đổi vai trò">
                                        Cấp quyền
                                    </button>

                                    <dialog class="event-modal club-modal" id="member-modal-{{ $membership->MaThanhVien }}">
                                        <section class="event-modal-card">
                                            <header class="event-modal-header">
                                                <span class="event-modal-icon" aria-hidden="true">🛡️</span>
                                                <div>
                                                    <h2>Cấp quyền &amp; Phân vai trò</h2>
                                                    <p>{{ $member->HoTen }} · MSSV: {{ $member->MSSV }}</p>
                                                </div>
                                                <button type="button" class="event-modal-close" data-close-member-modal aria-label="Đóng">×</button>
                                            </header>
                                            <form action="{{ route('caulacbo.members.update', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" class="event-modal-form">
                                                @csrf
                                                @method('PATCH')
                                                <div class="event-modal-body" style="display: grid; gap: 16px; padding: 20px;">
                                                    <div>
                                                        <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">Chức vụ &amp; Quyền hạn quản lý <span style="color: #ef4444;">*</span></label>
                                                        <select name="ChucVu" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 13px;">
                                                            <optgroup label="⭐ Nhóm Có Quyền Quản Lý CLB (Xem, Tạo sự kiện, Tuyển thành viên)">
                                                                <option value="Phó chủ nhiệm" {{ $membership->ChucVu === 'Phó chủ nhiệm' ? 'selected' : '' }}>Phó chủ nhiệm (Được quản lý)</option>
                                                                <option value="Trưởng ban Kỹ thuật" {{ $membership->ChucVu === 'Trưởng ban Kỹ thuật' ? 'selected' : '' }}>Trưởng ban Kỹ thuật (Được quản lý)</option>
                                                                <option value="Trưởng ban Truyền thông" {{ $membership->ChucVu === 'Trưởng ban Truyền thông' ? 'selected' : '' }}>Trưởng ban Truyền thông (Được quản lý)</option>
                                                                <option value="Trưởng ban Sự kiện" {{ $membership->ChucVu === 'Trưởng ban Sự kiện' ? 'selected' : '' }}>Trưởng ban Sự kiện (Được quản lý)</option>
                                                                <option value="Quản lý" {{ $membership->ChucVu === 'Quản lý' ? 'selected' : '' }}>Quản lý CLB (Được quản lý)</option>
                                                                @if($isOwner ?? true)
                                                                    <option value="Chủ nhiệm" {{ in_array($membership->ChucVu, ['Chủ nhiệm', 'CHU_NHIEM']) ? 'selected' : '' }}>Chủ nhiệm (Chủ sở hữu CLB)</option>
                                                                @endif
                                                            </optgroup>
                                                            <optgroup label="👤 Nhóm Thành Viên (Chỉ tham gia, không có quyền quản lý)">
                                                                <option value="Thành viên" {{ in_array($membership->ChucVu, ['Thành viên', 'THANH_VIEN']) ? 'selected' : '' }}>Thành viên</option>
                                                                <option value="Cộng tác viên" {{ in_array($membership->ChucVu, ['Cộng tác viên', 'CTV']) ? 'selected' : '' }}>Cộng tác viên</option>
                                                            </optgroup>
                                                        </select>
                                                        <p style="margin: 6px 0 0; font-size: 11px; color: var(--text-faint); line-height: 1.4;">
                                                            Gán vai trò trong nhóm <strong>Có Quyền Quản Lý</strong> sẽ cho phép thành viên này quản lý câu lạc bộ, đăng bài đợt tuyển và tạo sự kiện.
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">Trạng thái hoạt động</label>
                                                        <select name="TrangThai" style="width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 13px;">
                                                            <option value="DANG_HOAT_DONG" {{ $memberStatus === \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value ? 'selected' : '' }}>Đang hoạt động (Kích hoạt quyền)</option>
                                                            <option value="CHO_PHE_DUYET" {{ $memberStatus === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value ? 'selected' : '' }}>Chờ phê duyệt</option>
                                                            <option value="TAM_NGHI" {{ $memberStatus === \App\Enums\ThanhVienTrangThai::TAM_NGHI->value ? 'selected' : '' }}>Tạm nghỉ (Tạm khóa quyền)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <footer class="event-modal-footer">
                                                    <button type="button" class="secondary-button" data-close-member-modal>Hủy bỏ</button>
                                                    <button type="submit" class="primary-button">Lưu thay đổi</button>
                                                </footer>
                                            </form>
                                        </section>
                                    </dialog>
                                @endif

                                <button type="button" class="btn-member-action btn-member-detail" data-open-member-modal="member-detail-modal-{{ $membership->MaThanhVien }}" title="Xem chi tiết thông tin thành viên">
                                    Chi tiết
                                </button>

                                @if ($isSelf)
                                    @if ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value)
                                        <form action="{{ route('caulacbo.members.rejectLeave', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;">
                                            @csrf
                                            <button type="submit" class="btn-member-action btn-member-detail" title="Hủy yêu cầu rời câu lạc bộ">
                                                Hủy yêu cầu
                                            </button>
                                        </form>
                                    @elseif ($isOwner ?? false)
                                        <button type="button" class="btn-member-action btn-member-leave" onclick="alert('Bạn đang là Chủ nhiệm của câu lạc bộ. Để rời câu lạc bộ, bạn cần chuyển giao quyền Chủ nhiệm cho thành viên khác trước.');" title="Chủ nhiệm không thể rời CLB khi chưa chuyển nhượng quyền">
                                            Rời CLB
                                        </button>
                                    @else
                                        <form action="{{ route('caulacbo.members.requestLeave', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn muốn gửi yêu cầu rời câu lạc bộ? Quyết định sẽ chờ Chủ quản phê duyệt.');">
                                            @csrf
                                            <button type="submit" class="btn-member-action btn-member-leave" title="Gửi yêu cầu rời câu lạc bộ">
                                                Rời CLB
                                            </button>
                                        </form>
                                    @endif
                                @elseif ($isOwner ?? false)
                                    <form action="{{ route('caulacbo.members.destroy', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên {{ $member->HoTen }} khỏi câu lạc bộ không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-member-action btn-member-delete" title="Xóa thành viên khỏi câu lạc bộ">
                                            Xóa
                                        </button>
                                    </form>
                                @endif

                                <dialog class="event-modal club-modal" id="member-detail-modal-{{ $membership->MaThanhVien }}">
                                    <section class="event-modal-card">
                                        <header class="event-modal-header">
                                            <span class="event-modal-icon" aria-hidden="true">👤</span>
                                            <div>
                                                <h2>Thông tin chi tiết thành viên</h2>
                                                <p>{{ $member->HoTen }} · MSSV: {{ $member->MSSV ?: 'Chưa cập nhật' }}</p>
                                            </div>
                                            <button type="button" class="event-modal-close" data-close-member-modal aria-label="Đóng">×</button>
                                        </header>
                                        <div class="event-modal-body" style="padding: 20px; display: grid; gap: 16px;">
                                            <div style="display: flex; align-items: center; gap: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--line);">
                                                @if ($member->URLAnhDaiDien)
                                                    <img src="{{ $member->URLAnhDaiDien }}" alt="" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='grid';">
                                                    <div style="display: none; width: 50px; height: 50px; border-radius: 50%; background: var(--blue-soft); color: var(--blue); place-items: center; font-weight: 800; font-size: 18px;">
                                                        {{ mb_strtoupper(mb_substr($member->HoTen, 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--blue-soft); color: var(--blue); display: grid; place-items: center; font-weight: 800; font-size: 18px;">
                                                        {{ mb_strtoupper(mb_substr($member->HoTen, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--text);">{{ $member->HoTen }}</h3>
                                                    <p style="margin: 2px 0 0; font-size: 12px; color: var(--text-soft);">{{ $formatRole($membership->ChucVu) }} · <span class="checkin-badge {{ $memberStatus === \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value ? 'checkin-open' : ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value ? 'checkin-pending' : ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value ? 'checkin-leave' : '')) }}"><i></i>{{ $formatMemberStatus($memberStatus) }}</span></p>
                                                </div>
                                            </div>

                                            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; font-size: 12px;">
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Mã số sinh viên</strong><span style="font-weight: 700;">{{ $member->MSSV ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Tên tài khoản</strong><span style="font-weight: 700;">{{ $member->TenTaiKhoan }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Email</strong><span style="font-weight: 700;">{{ $member->Email ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Số điện thoại</strong><span style="font-weight: 700;">{{ $member->SDT ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Ngày sinh</strong><span>{{ $member->NgaySinh?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Ngày tham gia CLB</strong><span>{{ $membership->NgayThamGia?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Khoa</strong><span>{{ $member->Khoa ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Ngành / Khóa</strong><span>{{ $member->Nganh ?: 'Chưa cập nhật' }} {{ $member->KhoaHoc ? '('.$member->KhoaHoc.')' : '' }}</span></div>
                                            </div>

                                            @php
                                                $userApp = \App\Models\DonUngTuyen::query()
                                                    ->where('MaNguoiUngTuyen', $member->MaNguoiDung)
                                                    ->whereHas('viTri.dotTuyen', fn ($q) => $q->where('MaCLB', $club->MaCLB))
                                                    ->with('viTri.dotTuyen')
                                                    ->latest('MaDon')
                                                    ->first();
                                            @endphp
                                            @if ($userApp)
                                                <div style="margin-top: 8px; padding-top: 12px; border-top: 1px dashed var(--line); font-size: 12px;">
                                                    <strong style="display: block; font-size: 12px; margin-bottom: 6px; color: var(--text);">Thông tin ứng tuyển gần nhất</strong>
                                                    <p style="margin: 0 0 4px; color: var(--text-soft);">Đợt tuyển: <strong>{{ $userApp->viTri?->dotTuyen?->TenDotTuyen }}</strong> — Vị trí: <strong>{{ $userApp->viTri?->TenViTri }}</strong></p>
                                                    @if ($userApp->NoiDung)
                                                        <p style="margin: 4px 0; padding: 8px 10px; background: var(--surface-soft); border-radius: 6px; color: var(--text); font-style: italic;">"{{ $userApp->NoiDung }}"</p>
                                                    @endif
                                                    @if ($userApp->URLHoSo)
                                                        <a href="{{ route('caulacbo.recruitments.resume', [$club->MaCLB, $userApp->MaDon]) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; color: var(--blue); font-weight: 700; text-decoration: none;">
                                                            ▣ Xem / Tải file CV đính kèm
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <footer class="event-modal-footer">
                                            <button type="button" class="secondary-button" data-close-member-modal>Đóng</button>
                                        </footer>
                                    </section>
                                </dialog>
                            @else
                                <button type="button" class="btn-member-action btn-member-detail" data-open-member-modal="member-detail-modal-{{ $membership->MaThanhVien }}" title="Xem chi tiết thông tin thành viên">
                                    Chi tiết
                                </button>
                                @if ($isSelf && $memberStatus !== \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->value)
                                    <form action="{{ route('caulacbo.members.requestLeave', ['caulacbo' => $club->MaCLB, 'thanhVien' => $membership->MaThanhVien]) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Bạn có chắc chắn muốn gửi yêu cầu rời câu lạc bộ? Quyết định sẽ chờ Chủ quản phê duyệt.');">
                                        @csrf
                                        <button type="submit" class="btn-member-action btn-member-leave" title="Gửi yêu cầu rời câu lạc bộ">
                                            Rời CLB
                                        </button>
                                    </form>
                                @endif
                                <dialog class="event-modal club-modal" id="member-detail-modal-{{ $membership->MaThanhVien }}">
                                    <section class="event-modal-card">
                                        <header class="event-modal-header">
                                            <span class="event-modal-icon" aria-hidden="true">👤</span>
                                            <div>
                                                <h2>Thông tin chi tiết thành viên</h2>
                                                <p>{{ $member->HoTen }} · MSSV: {{ $member->MSSV ?: 'Chưa cập nhật' }}</p>
                                            </div>
                                            <button type="button" class="event-modal-close" data-close-member-modal aria-label="Đóng">×</button>
                                        </header>
                                        <div class="event-modal-body" style="padding: 20px; display: grid; gap: 16px;">
                                            <div style="display: flex; align-items: center; gap: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--line);">
                                                <div>
                                                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--text);">{{ $member->HoTen }}</h3>
                                                    <p style="margin: 2px 0 0; font-size: 12px; color: var(--text-soft);">{{ $formatRole($membership->ChucVu) }} · {{ $formatMemberStatus($memberStatus) }}</p>
                                                </div>
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; font-size: 12px;">
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Mã số sinh viên</strong><span style="font-weight: 700;">{{ $member->MSSV ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Email</strong><span style="font-weight: 700;">{{ $member->Email ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Số điện thoại</strong><span style="font-weight: 700;">{{ $member->SDT ?: 'Chưa cập nhật' }}</span></div>
                                                <div><strong style="color: var(--text-faint); display: block; font-size: 11px;">Ngày tham gia CLB</strong><span>{{ $membership->NgayThamGia?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span></div>
                                            </div>
                                        </div>
                                        <footer class="event-modal-footer">
                                            <button type="button" class="secondary-button" data-close-member-modal>Đóng</button>
                                        </footer>
                                    </section>
                                </dialog>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <strong>{{ $search || $roleFilter || $statusFilter ? 'Không tìm thấy thành viên phù hợp' : 'Chưa có thành viên nào' }}</strong>
                        <span>{{ $search || $roleFilter || $statusFilter ? 'Hãy thử thay đổi nội dung tìm kiếm hoặc bộ lọc.' : 'Thành viên của câu lạc bộ sẽ hiển thị tại đây.' }}</span>
                    </div>
                @endforelse
            </div>
            <div class="panel-bottom club-members-pagination">
                <span>Hiển thị {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} trong {{ $totalMembers }} thành viên</span>
                {{ $members->links() }}
            </div>
        </section>
    </section>

    <script>
        document.querySelectorAll('[data-open-member-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                var modalId = button.getAttribute('data-open-member-modal');
                var modal = document.getElementById(modalId);
                if (modal && typeof modal.showModal === 'function') {
                    modal.showModal();
                }
            });
        });

        document.querySelectorAll('[data-close-member-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                var modal = button.closest('dialog');
                if (modal && typeof modal.close === 'function') {
                    modal.close();
                }
            });
        });
    </script>
@endsection

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
                        <span class="checkin-badge {{ $memberStatus === \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value ? 'checkin-open' : ($memberStatus === \App\Enums\ThanhVienTrangThai::CHO_PHE_DUYET->value ? 'checkin-pending' : '') }}">
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
                            @else
                                <span>—</span>
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

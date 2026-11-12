<nav class="club-manager-tabs" aria-label="Chức năng quản lý câu lạc bộ">
    <a class="club-manager-tab {{ $activeManagementTab === 'members' ? 'is-active' : '' }}"
       href="{{ route('caulacbo.members', $managementNavigation['club']->MaCLB) }}"
       @if ($activeManagementTab === 'members') aria-current="page" @endif>
        <span>Thành viên</span>
        <b>{{ $managementNavigation['memberCount'] }}</b>
    </a>
    <a class="club-manager-tab {{ $activeManagementTab === 'events' ? 'is-active' : '' }}"
       href="{{ route('sukien.index', ['ma_clb' => $managementNavigation['club']->MaCLB]) }}"
       @if ($activeManagementTab === 'events') aria-current="page" @endif>
        <span>Sự kiện</span>
        <b>{{ $managementNavigation['eventCount'] }}</b>
    </a>
    <a class="club-manager-tab {{ $activeManagementTab === 'recruitments' ? 'is-active' : '' }}"
       href="{{ route('caulacbo.recruitments.index', $managementNavigation['club']->MaCLB) }}"
       @if ($activeManagementTab === 'recruitments') aria-current="page" @endif>
        <span class="tab-active-dot">•</span>
        <span>Mở đợt tuyển</span>
        <b>{{ $managementNavigation['recruitmentCount'] }}</b>
    </a>
</nav>

<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Quản lý sự kiện CLB | UniClub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        (function() {
            var theme = localStorage.getItem('uniclub-theme');
            if (theme === 'dark' || theme === 'light') {
                document.documentElement.dataset.theme = theme;
            }
            var size = localStorage.getItem('uniclub-font-size');
            if (size === 'small' || size === 'medium' || size === 'large') {
                document.documentElement.dataset.fontSize = size;
            }
        })();
    </script>
</head>
<body>
    @include('layouts.header')

    <main class="page-content">
        @yield('content')
    </main>

    @hasSection('hideDefaultFooter')
    @else
        @include('layouts.footer')
    @endif

    <script src="{{ asset('js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/flatpickr.vn.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'FoodLog')</title>
  @yield('meta')
  <script>
    (function () {
      try {
        if (localStorage.getItem('foodlog_theme') === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      } catch (e) {}
    })();
  </script>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122h" />
</head>
<body>

  @include('partials.header', ['active' => $navActive ?? ''])

  @yield('content')

  @include('partials.footer')

  @yield('modals')

  @yield('scripts')
</body>
</html>

@php
  $currentLocale = app()->getLocale();
  $i18nDict = [];
  if ($currentLocale === 'en') {
      $enPath = lang_path('en.json');
      if (file_exists($enPath)) {
          $i18nDict = json_decode(file_get_contents($enPath), true) ?: [];
      }
  }
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
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
    window.LOCALE = @json($currentLocale);
    window.I18N = @json($i18nDict);
    window.t = function (key) {
      return (window.I18N && Object.prototype.hasOwnProperty.call(window.I18N, key)) ? window.I18N[key] : key;
    };
  </script>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122i" />
</head>
<body>

  @include('partials.header', ['active' => $navActive ?? ''])

  @yield('content')

  @include('partials.footer')

  @yield('modals')

  @yield('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="@yield('lang', 'ja')">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'FoodLog')</title>
  @yield('meta')
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122f" />
</head>
<body>

  @include('partials.nav', ['active' => $navActive ?? ''])

  @yield('content')

  @include('partials.footer')

  @yield('modals')

  @yield('scripts')
</body>
</html>

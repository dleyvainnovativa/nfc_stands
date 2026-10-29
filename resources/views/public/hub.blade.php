{{-- The inline "Link hub" was replaced by standalone Pages (/p/{code}).
     This view is no longer rendered; kept only so a stale reference can't 404. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ url('/t/' . ($stand->code ?? '')) }}">
</head>
<body></body>
</html>

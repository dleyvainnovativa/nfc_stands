{{-- Blocking inline script: resolve the theme BEFORE first paint so there is
     no flash of the wrong theme on navigation. localStorage is the source of
     truth; the cookie mirror lets the server render <html data-theme> to match. --}}
<script>
(function () {
  try {
    var ls = localStorage.getItem('theme');
    var m = document.cookie.match(/(?:^|;\s*)theme=(dark|light)/);
    var attr = document.documentElement.getAttribute('data-theme');
    var t = ls || (m && m[1]) || attr || 'light';
    if (t !== 'dark' && t !== 'light') t = 'light';
    document.documentElement.setAttribute('data-theme', t);
    document.cookie = 'theme=' + t + '; path=/; max-age=31536000; SameSite=Lax';
    if (ls !== t) localStorage.setItem('theme', t);
    // Tint the browser/OS toolbar (iOS) to match the theme, before paint.
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) { meta = document.createElement('meta'); meta.name = 'theme-color'; document.head.appendChild(meta); }
    meta.setAttribute('content', t === 'dark' ? '#0f1115' : '#f7f8fa');
  } catch (e) {}
})();
</script>

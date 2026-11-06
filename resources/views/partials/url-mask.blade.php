@php
    $maskBase = rtrim((string) parse_url(url('/'), PHP_URL_PATH), '/');
    $maskBase = $maskBase === '' ? '/' : $maskBase . '/';
@endphp
<script>
    (function () {
        var base = @json($maskBase);

        // URL masking: preserve the full path for participant sub-routes
        // (e.g. /peserta/absensi) so that page refresh keeps the current menu
        // instead of masking to "/" which triggers dashboard redirect.
        // Only the exact base path is left as-is.
        if (window.location.pathname !== base) {
            // Keep full path - do NOT mask to base (was: history.replaceState)
            // This ensures the URL stays on the current menu when refreshed.
        }
    })();
</script>

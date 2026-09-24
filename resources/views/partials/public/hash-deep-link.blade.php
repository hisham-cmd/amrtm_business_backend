{{-- ربط عميق عبر hash: /dashboard#section → showPage(section) للداشبوردات المونوليثية — @push غير مطلوب (سكربت inline مباشر) --}}
@php($hashPages = $hashPages ?? [])
<script>
(function () {
    var pages = @json($hashPages);
    function go() {
        var h = (window.location.hash || '').replace(/^#/, '');
        if (!h || pages.indexOf(h) === -1 || typeof window.showPage !== 'function') return;
        try { window.showPage(h); } catch (e) {}
    }
    window.addEventListener('hashchange', go);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', go);
    } else {
        go();
    }
})();
</script>
<?php
/**
 * Anti-bypass: disable view source, right-click, devtools shortcuts.
 * Makes it harder for users to extract code or use automation extensions.
 */
if (get_option('enable_anti_bypass', 'yes') !== 'yes') {
    return;
}
?>
<script>
(function() {
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83 || e.keyCode === 123)) e.preventDefault();
        if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) e.preventDefault();
        if (e.keyCode === 123) e.preventDefault();
    });
    var ua = (navigator.userAgent || '').toLowerCase();
    if (navigator.webdriver || /phantom|headless|selenium|puppeteer/i.test(ua)) {
        document.body.innerHTML = '<p style="text-align:center;padding:40px;">Please use a regular browser.</p>';
        document.body.style.pointerEvents = 'none';
        return;
    }
    setTimeout(function() { try { (function(){})['constructor']('debugger')(); } catch(_){} }, 2000);
})();
</script>
<style>
.no-select, body.no-select, body.no-select * {
    -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;
    -webkit-touch-callout: none;
}
</style>

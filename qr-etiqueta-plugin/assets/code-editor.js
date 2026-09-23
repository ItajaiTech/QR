(function () {
    'use strict';

    function init() {
        document.querySelectorAll('#codigos, #codigos-frontend').forEach(function (textarea) {
            if (textarea.parentElement.classList.contains('qr-code-editor')) return;
            var editor = document.createElement('div');
            editor.className = 'qr-code-editor';
            var gutter = document.createElement('div');
            gutter.className = 'qr-line-numbers';
            gutter.setAttribute('aria-hidden', 'true');
            textarea.parentNode.insertBefore(editor, textarea);
            editor.appendChild(gutter);
            editor.appendChild(textarea);
            textarea.wrap = 'off';
            textarea.spellcheck = false;

            function syncScroll() {
                gutter.scrollTop = textarea.scrollTop;
            }

            function refresh() {
                // Match the example lines while the empty field displays its placeholder.
                var text = textarea.value || textarea.placeholder || '';
                var count = text.split('\n').length;
                gutter.textContent = Array.from({ length: count }, function (_, i) { return i + 1; }).join('\n');
                gutter.style.height = textarea.clientHeight + 'px';
                syncScroll();
            }

            textarea.addEventListener('input', refresh);
            textarea.addEventListener('change', refresh);
            textarea.addEventListener('scroll', syncScroll);
            if (textarea.form) textarea.form.addEventListener('reset', function () { setTimeout(refresh, 0); });
            document.querySelectorAll('#novo-btn, #new-btn-frontend').forEach(function (button) {
                button.addEventListener('click', function () { setTimeout(refresh, 0); });
            });
            if (window.ResizeObserver) new ResizeObserver(refresh).observe(textarea);
            refresh();
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());

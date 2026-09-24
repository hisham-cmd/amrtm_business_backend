{{-- متطلبات كلمة المرور المعقدة — تتحول للأخضر عند تحقيق كل شرط أثناء الكتابة --}}
@props([
    'target' => '',
    'theme'  => 'light',
])

@php
    $pendingColor = $theme === 'dark' ? 'rgba(255,255,255,.4)' : '#94a3b8';
    $okColor      = $theme === 'dark' ? '#5FD4A8' : '#059669';
@endphp

<div class="pw-req-list mt-2.5 space-y-1.5" data-pw-list data-for="{{ e($target) }}" dir="rtl"
    style="display:none; --pw-pending:{{ $pendingColor }}; --pw-ok:{{ $okColor }}">
    <div class="pw-req-item" data-req="length">
        <i class="ti ti-circle text-[12px]"></i>
        <span>8 أحرف أو أكثر</span>
    </div>
    <div class="pw-req-item" data-req="lower">
        <i class="ti ti-circle text-[12px]"></i>
        <span>حرف إنجليزي صغير على الأقل (a-z)</span>
    </div>
    <div class="pw-req-item" data-req="upper">
        <i class="ti ti-circle text-[12px]"></i>
        <span>حرف إنجليزي كبير على الأقل (A-Z)</span>
    </div>
    <div class="pw-req-item" data-req="number">
        <i class="ti ti-circle text-[12px]"></i>
        <span>رقم واحد على الأقل (0-9)</span>
    </div>
    <div class="pw-req-item" data-req="special">
        <i class="ti ti-circle text-[12px]"></i>
        <span>رمزاً خاصاً واحداً على الأقل (!@#$%^&amp;*)</span>
    </div>
</div>

@push('styles')
    <style>
        .pw-req-list {
            max-width: 100%;
        }

        .pw-req-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.6;
            color: var(--pw-pending);
            transition: color .25s ease;
        }

        .pw-req-item i {
            flex: none;
            color: var(--pw-pending);
            transition: color .25s ease;
        }

        .pw-req-item.pw-ok,
        .pw-req-item.pw-ok i {
            color: var(--pw-ok);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-pw-list]').forEach(function (listEl) {
                if (listEl.dataset.pwReady) return;
                listEl.dataset.pwReady = '1';

                var input = document.getElementById(listEl.dataset.for);
                if (!input) return;

                var items = {};
                listEl.querySelectorAll('[data-req]').forEach(function (el) {
                    items[el.dataset.req] = el;
                });

                var rules = {
                    length: function (v) { return v.length >= 8; },
                    lower: function (v) { return /[a-z]/.test(v); },
                    upper: function (v) { return /[A-Z]/.test(v); },
                    number: function (v) { return /[0-9]/.test(v); },
                    special: function (v) { return /[^A-Za-z0-9]/.test(v); }
                };

                function evaluate() {
                    var value = input.value || '';
                    Object.keys(rules).forEach(function (key) {
                        var el = items[key];
                        if (!el) return;
                        var ok = rules[key](value);
                        el.classList.toggle('pw-ok', ok);
                        var icon = el.querySelector('i');
                        icon.classList.toggle('ti-circle-check', ok);
                        icon.classList.toggle('ti-circle', !ok);
                    });
                }

                function showList() {
                    listEl.style.display = 'block';
                }

                input.addEventListener('focus', showList);
                input.addEventListener('input', showList);
                input.addEventListener('input', evaluate);
                input.addEventListener('paste', function () {
                    setTimeout(evaluate, 0);
                });
                input.addEventListener('blur', function () {
                    if (!input.value) listEl.style.display = 'none';
                });
            });
        });
    </script>
@endpush
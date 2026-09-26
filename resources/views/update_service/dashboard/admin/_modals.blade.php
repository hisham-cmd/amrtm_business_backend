<!-- BALANCE ADJUSTMENT MODAL -->
    <div id="balance-modal" data-modal-target="balance-modal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 p-4">
        <div class="relative w-full max-w-md">
            <div class="relative rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex flex-col gap-3 rounded-t-2xl border-b border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white"><i
                                class="ti ti-wallet text-emerald-600"></i><span
                                id="bal-modal-ttl">تعديل الرصيد</span></h3>
                        <x-ui.button variant="ghost" size="sm" aria-label="إغلاق"
                            class="ms-auto h-8! w-8! shrink-0 rounded-lg! bg-transparent! p-0! text-sm! text-gray-400! hover:bg-gray-200! hover:text-gray-900! dark:hover:bg-gray-600! dark:hover:text-white!"
                            onclick="closeBalanceModal()"><i class="ti ti-x"></i></x-ui.button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-gray-700">
                        <span class="text-[13px] font-bold text-slate-700 dark:text-gray-200" id="bal-user-name">—</span>
                        <span class="text-[13px] text-slate-500 dark:text-gray-400">الرصيد الحالي: <strong id="bal-current"
                                class="text-emerald-600">0 ر.س</strong></span>
                    </div>
                </div>
                <div class="max-h-[85vh] space-y-4 overflow-y-auto p-4 md:p-5">
                    <div>
                        <x-ui.label>نوع العملية</x-ui.label>
                        <div class="flex gap-2">
                            <x-ui.radio-chip name="bal-type" value="charge" checked onchange="updateBalTypeUI()"
                                class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-emerald-900/10 px-3 py-2.5 transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-600/5"
                                id="bal-charge-lbl">
                                <span class="text-[13px] font-bold text-emerald-700">شحن رصيد</span>
                            </x-ui.radio-chip>
                            <x-ui.radio-chip name="bal-type" value="payment" onchange="updateBalTypeUI()"
                                class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-emerald-900/10 px-3 py-2.5 transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-600/5"
                                id="bal-deduct-lbl">
                                <span class="text-[13px] font-bold text-red-600">خصم رصيد</span>
                            </x-ui.radio-chip>
                        </div>
                    </div>
                    <div><x-ui.label>المبلغ (ر.س)</x-ui.label><x-ui.input type="number" id="bal-amount"
                            min="0.01" step="0.01" placeholder="0.00" dir="ltr" class="focus:ring-0!" /></div>
                    <div><x-ui.label>السبب (اختياري)</x-ui.label><x-ui.input type="text" id="bal-reason"
                            placeholder="سبب التعديل..." class="focus:ring-0!" /></div>
                </div>
                <div class="flex items-center justify-end gap-2 rounded-b-2xl border-t border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <x-ui.button class="border-emerald-900/10! bg-white! text-slate-700! hover:bg-emerald-600/5! hover:text-emerald-700!" onclick="closeBalanceModal()">إلغاء</x-ui.button>
                    <x-ui.button id="bal-submit-btn" onclick="doAdjustBalance()"><i
                            class="ti ti-check"></i>تأكيد</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- MANUAL CHARGE MODAL (شحن رصيد يدوي من لوحة الأدمن) -->
    <div id="manual-charge-modal" data-modal-target="manual-charge-modal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 p-4">
        <div class="relative w-full max-w-md">
            <div class="relative rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white"><i
                            class="ti ti-wallet text-emerald-600"></i><span>شحن رصيد يدوي</span></h3>
                    <x-ui.button variant="ghost" size="sm" aria-label="إغلاق"
                        class="ms-auto h-8! w-8! shrink-0 rounded-lg! bg-transparent! p-0! text-sm! text-gray-400! hover:bg-gray-200! hover:text-gray-900! dark:hover:bg-gray-600! dark:hover:text-white!"
                        onclick="closeManualCharge()"><i class="ti ti-x"></i></x-ui.button>
                </div>
                <div class="max-h-[85vh] space-y-4 overflow-y-auto p-4 md:p-5">
                    <div>
                        <x-ui.label>اختر العميل</x-ui.label>
                        <style>
                            #mc-results-inner button:hover { background: #F8FAFC; outline: 2px solid #059669; outline-offset: -2px; }
                        </style>
                        <div id="mc-combo" class="relative">
                            <div class="relative flex items-center">
                                <x-ui.input type="text" id="mc-search" autocomplete="off"
                                    placeholder="اكتب الاسم / البريد / الجوال للبحث..."
                                    class="h-10! w-full! rounded-xl border border-emerald-900/10! bg-white! pe-9! ps-3! text-[13px]! text-slate-900! outline-none! focus:border-emerald-600! focus:ring-0!"
                                    oninput="mcSearchInput()" onfocus="mcOpenDropdown()"
                                    onkeydown="if(event.key==='Escape'){mcCloseDropdown();}else if(event.key==='ArrowDown'){event.preventDefault();mcHighlight(1);}else if(event.key==='ArrowUp'){event.preventDefault();mcHighlight(-1);}else if(event.key==='Enter'){event.preventDefault();mcPickHighlighted();}" />
                                <span id="mc-caret"
                                    class="pointer-events-none absolute end-3 text-[13px] text-slate-500"><i
                                        class="ti ti-chevron-down"></i></span>
                                <span id="mc-clear-btn" onclick="clearMcClient()" title="إلغاء الاختيار"
                                    class="absolute end-2.5 hidden h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-slate-100 text-sm text-slate-700"><i
                                        class="ti ti-x"></i></span>
                            </div>
                            <div id="mc-results"
                                class="absolute end-0 start-0 top-12 z-[60] hidden overflow-hidden rounded-xl border border-emerald-900/10 bg-white shadow-xl shadow-emerald-900/5">
                                <div id="mc-results-inner" class="flex max-h-[220px] flex-col overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <x-ui.label>نوع العملية</x-ui.label>
                        <div class="flex gap-2">
                            <x-ui.radio-chip name="mc-type" value="charge" checked onchange="updateMcTypeUI()"
                                class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-emerald-900/10 px-3 py-2.5 transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-600/5">
                                <span class="text-[13px] font-bold text-emerald-700">شحن رصيد</span>
                            </x-ui.radio-chip>
                            <x-ui.radio-chip name="mc-type" value="payment" onchange="updateMcTypeUI()"
                                class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-emerald-900/10 px-3 py-2.5 transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-600/5">
                                <span class="text-[13px] font-bold text-red-600">خصم رصيد</span>
                            </x-ui.radio-chip>
                        </div>
                    </div>
                    <div><x-ui.label>المبلغ (ر.س)</x-ui.label><x-ui.input type="number" id="mc-amount"
                            min="0.01" step="0.01" placeholder="0.00" dir="ltr" class="focus:ring-0!" /></div>
                    <div><x-ui.label>السبب (اختياري)</x-ui.label><x-ui.input type="text" id="mc-reason"
                            placeholder="سبب الشحن / الخصم..." class="focus:ring-0!" /></div>
                </div>
                <div class="flex items-center justify-end gap-2 rounded-b-2xl border-t border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <x-ui.button class="border-emerald-900/10! bg-white! text-slate-700! hover:bg-emerald-600/5! hover:text-emerald-700!" onclick="closeManualCharge()">إلغاء</x-ui.button>
                    <x-ui.button id="mc-submit-btn" onclick="doManualCharge()"><i
                            class="ti ti-check"></i>تأكيد</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- CREATE ADMIN MODAL -->
    <div id="create-admin-modal" data-modal-target="create-admin-modal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 p-4">
        <div class="relative w-full max-w-md">
            <div class="relative rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white"><i
                            class="ti ti-user-plus text-emerald-600"></i>إضافة مدير جديد</h3>
                    <x-ui.button variant="ghost" size="sm" aria-label="إغلاق"
                        class="ms-auto h-8! w-8! shrink-0 rounded-lg! bg-transparent! p-0! text-sm! text-gray-400! hover:bg-gray-200! hover:text-gray-900! dark:hover:bg-gray-600! dark:hover:text-white!"
                        onclick="closeCreateAdminModal()"><i class="ti ti-x"></i></x-ui.button>
                </div>
                <div class="max-h-[85vh] space-y-4 overflow-y-auto p-4 md:p-5">
                    <div><x-ui.label>الاسم</x-ui.label><x-ui.input type="text" id="new-admin-name"
                            placeholder="الاسم الكامل" class="focus:ring-0!" /></div>
                    <div><x-ui.label>البريد الإلكتروني</x-ui.label><x-ui.input type="email" id="new-admin-email"
                            placeholder="admin@example.com" class="focus:ring-0!" /></div>
                    <div><x-ui.label>رقم الجوال</x-ui.label><x-ui.input type="tel" id="new-admin-phone"
                            placeholder="05xxxxxxxx" class="focus:ring-0!" /></div>
                    <div><x-ui.label>كلمة المرور</x-ui.label><x-ui.eye-field id="new-admin-pass"
                            placeholder="8 أحرف على الأقل" class="focus:ring-0!" /></div>
                </div>
                <div class="flex items-center justify-end gap-2 rounded-b-2xl border-t border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <x-ui.button class="border-emerald-900/10! bg-white! text-slate-700! hover:bg-emerald-600/5! hover:text-emerald-700!" onclick="closeCreateAdminModal()">إلغاء</x-ui.button>
                    <x-ui.button onclick="doCreateAdmin()"><i class="ti ti-check"></i>إنشاء الحساب</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTRACT MODAL (create/view) -->
    <div id="contract-view-modal" data-modal-target="contract-view-modal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 p-4">
        <div class="relative w-full max-w-3xl">
            <div class="relative rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between rounded-t-2xl border-b border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <h3 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white"><i
                            class="ti ti-file-description text-emerald-600"></i><span>عرض العقد</span></h3>
                    <x-ui.button variant="ghost" size="sm" aria-label="إغلاق"
                        class="ms-auto h-8! w-8! shrink-0 rounded-lg! bg-transparent! p-0! text-sm! text-gray-400! hover:bg-gray-200! hover:text-gray-900! dark:hover:bg-gray-600! dark:hover:text-white!"
                        onclick="closeContractView()"><i class="ti ti-x"></i></x-ui.button>
                </div>
                <div class="max-h-[85vh] space-y-1.5 overflow-y-auto p-4 md:p-5">

                    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-gradient-to-br from-emerald-600/5 to-emerald-600/10 p-4 ring-1 ring-emerald-900/10">
                        <div><div class="mb-0.5 text-[11px] text-slate-500">رقم العقد</div><div class="text-[17px] font-extrabold text-slate-900" id="cv-number">—</div></div>
                        <span class="ms-auto rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white" id="cv-type">—</span>
                        <span class="req-st" id="cv-status">—</span>
                    </div>

                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">الطرف الأول</div><div class="text-[13.5px] font-bold text-slate-900" id="cv-party1">—</div></div>
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">الطرف الثاني</div><div class="text-[13.5px] font-bold text-slate-900" id="cv-party2">—</div></div>
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">اسم الشركة / المكتب</div><div class="text-[13.5px] font-bold text-slate-900" id="cv-company">—</div></div>
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">سعر الخدمة</div><div class="text-[13.5px] font-bold text-emerald-700" id="cv-price">—</div></div>
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">تاريخ البداية</div><div class="text-[13.5px] font-bold text-slate-900" id="cv-start">—</div></div>
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-emerald-900/10"><div class="mb-1 text-[11px] text-slate-500">تاريخ النهاية</div><div class="text-[13.5px] font-bold text-slate-900" id="cv-end">—</div></div>
                    </div>

                    <div class="mb-3 rounded-xl bg-white p-4 ring-1 ring-emerald-900/10">
                        <div class="mb-2 flex items-center gap-1.5 font-extrabold text-slate-900"><i class="ti ti-mood-check text-emerald-600"></i> بنود العقد</div>
                        <div id="cv-clauses"></div>
                    </div>

                    <div class="mb-3 rounded-xl bg-white p-4 ring-1 ring-emerald-900/10">
                        <div class="mb-2 flex items-center gap-1.5 font-extrabold text-slate-900"><i class="ti ti-swipe text-emerald-600"></i> حالة التوقيع</div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2 rounded-lg bg-emerald-600/10 px-3 py-2 text-[13px] font-bold text-emerald-700"><i class="ti ti-check"></i><span id="cv-sign-first-txt">—</span></div>
                            <div class="flex items-center gap-2 rounded-lg bg-red-600/10 px-3 py-2 text-[13px] font-bold text-red-700" id="cv-sign-second"><i class="ti ti-clock"></i><span id="cv-sign-second-txt">—</span></div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 ring-1 ring-emerald-900/10">
                        <div class="mb-2 flex items-center gap-1.5 font-extrabold text-slate-900"><i class="ti ti-notes text-emerald-600"></i> وصف العقد</div>
                        <div class="text-[13px] leading-[1.9] text-slate-700" id="cv-desc">—</div>
                    </div>

                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 rounded-b-2xl border-t border-gray-200 p-4 dark:border-gray-600 md:p-5">
                    <x-ui.button class="border-emerald-900/10! bg-white! text-slate-700! hover:bg-emerald-600/5! hover:text-emerald-700!" onclick="closeContractView()">إغلاق</x-ui.button>
                    <a class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 hover:no-underline" id="cv-pdf-link" href="#" target="_blank"><i class="ti ti-download"></i>تحميل PDF</a>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTRACT VIEW MODAL (read-only) -->
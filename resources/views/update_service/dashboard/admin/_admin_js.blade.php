    <script>
        window.AMRTM_USER = {!! auth('business')->check()
            ? json_encode([
                'id' => auth('business')->id(),
                'name' => auth('business')->user()->name,
                'email' => auth('business')->user()->email,
                'phone' => auth('business')->user()->phone ?? '',
                'role' => auth('business')->user()->role,
                'balance' => 0,
            ])
            : 'null' !!};
        window.AMRTM_CSRF = '{{ csrf_token() }}';
        window.AMRTM_API_BASE = '{{ url('/api') }}';

        const XUI_INPUT = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20';
        const XUI_SELECT = 'w-full min-w-0 cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white ps-4 pe-9 py-2.5 text-sm text-gray-900 transition-all duration-200 focus:border-[#006C35] focus:outline-none focus:ring-4 focus:ring-[#006C35]/20';
        const XUI_TEXTAREA = XUI_INPUT + ' resize-y';
        const XUI_CHECKBOX = 'h-4 w-4 rounded border-slate-300 text-[#006C35] focus:ring-[#006C35]/30';
        const el = (tag, attrs = '', children = '') =>
            ['input', 'br', 'img'].includes(tag)
                ? '<' + tag + (attrs ? ' ' + attrs : '') + '>'
                : '<' + tag + (attrs ? ' ' + attrs : '') + '>' + children + '</' + tag + '>';
        const xuiLabel = (text, req = false) => el('label', 'class="mb-1.5 block text-sm font-semibold text-gray-700"', text + (req ? ' <span class="text-red-500">*</span>' : ''));
        const xuiInput = (attrs) => el('input', 'class="' + XUI_INPUT + '" ' + attrs);
        const xuiSelect = (inner, attrs) => el('div', 'class="relative w-full"', el('select', 'class="' + XUI_SELECT + '" ' + attrs, inner) + '<i class="ti ti-chevron-down" aria-hidden="true" style="position:absolute;inset-inline-end:10px;top:50%;transform:translateY(-50%);font-size:15px;color:var(--t3,#9CA3AF);pointer-events:none;"></i>');
        const xuiTextarea = (body, attrs) => el('textarea', 'class="' + XUI_TEXTAREA + '" ' + attrs, body);
        const xuiCheckbox = (attrs) => el('input', 'type="checkbox" class="' + XUI_CHECKBOX + '" ' + attrs);
        const xuiButton = (inner, attrs) => el('button', 'type="button" ' + attrs, inner);

        // ── Notifications SDK ──
        window.Notifications = {
            _base: window.AMRTM_API_BASE + '/notifications',
            _h: () => ({
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.AMRTM_CSRF,
                'Content-Type': 'application/json'
            }),
            getAll: async function(all) {
                const r = await fetch(this._base + (all ? '?all=1' : ''), {
                    headers: this._h(),
                    credentials: 'same-origin'
                });
                return r.json();
            },
            unreadCount: async function() {
                const r = await fetch(this._base + '/unread-count', {
                    headers: this._h(),
                    credentials: 'same-origin'
                });
                return r.json();
            },
            markRead: async function(id) {
                await fetch(this._base + '/' + id + '/read', {
                    method: 'POST',
                    headers: this._h(),
                    credentials: 'same-origin'
                });
            },
            markAllRead: async function() {
                await fetch(this._base + '/read-all', {
                    method: 'POST',
                    headers: this._h(),
                    credentials: 'same-origin'
                });
            },
        };
        window.AMRTM_ROUTES = {
            login: '{{ route('amrtm.login') }}',
            register: '{{ route('amrtm.register') }}',
            logout: '{{ route('amrtm.logout') }}',
            home: '{{ route('amrtm.index') }}',
            adminDashboard: '{{ route('amrtm.admin.dashboard') }}',
            mainSite: '{{ url('/') }}',
            officeCreate: '{{ route('amrtm.admin.offices.create-form') }}',
            officeEdit: '{{ route('amrtm.admin.offices.edit-form', ['id' => '__ID__']) }}',
        };
    </script>
    <script src="{{ asset('js/amrtm-web.js') }}"></script>
    <script>
        const T = {
            ar: {
                nm: 'آمر تم',
                da: 'لوحة التحكم',
                s1: 'الرئيسية',
                s2: 'الطلبات',
                s3: 'الإدارة',
                s4: 'أخرى',
                siOv: 'نظرة عامة',
                siReq: 'الطلبات',
                siPrice: 'التسعير',
                siFin: 'المالية',
                siSite: 'الموقع',
                siSet: 'الإعدادات',
                ovTtl: 'نظرة عامة على المنصة',
                ovSub: 'آخر تحديث: منذ لحظات',
                ovViewReq: 'عرض الطلبات',
                scTotal: 'إجمالي الطلبات',
                scPend: 'قيد الانتظار',
                scProc: 'جاري المعالجة',
                scDone: 'مكتملة',
                scRej: 'مرفوضة',
                scUsers: 'المستخدمين',
                ch1: 'الطلبات خلال 7 أيام',
                ch1s: 'عدد الطلبات اليومية',
                ch2: 'توزيع الطلبات',
                ch2s: 'حسب الحالة',
                dLbl: 'طلب',
                topTtl: 'أكثر الخدمات طلباً (آخر 30 يوم)',
                topSub: 'بناءً على عدد الطلبات المستلمة',
                reqTtl: 'قائمة الطلبات',
                rfAll: 'الكل',
                rfPend: 'قيد الانتظار',
                rfProc: 'جاري المعالجة',
                rfDone: 'مكتملة',
                rfRej: 'مرفوضة',
                saProc: 'جاري المعالجة',
                saInprog: 'قيد التنفيذ',
                saDone: 'تمت العملية',
                saRej: 'رفض',
                setTime: 'تحديد وقت الإنجاز',
                timePh: 'مثال: 5 أيام عمل',
                timeSave: 'حفظ',
                rejPlh: 'اكتب سبب الرفض...',
                rejSend: 'إرسال',
                logTtl: 'سجل النشاط',
                catTtl: 'إدارة الجهات والخدمات',
                ctrTtl: 'إدارة العقود',
                offTtl: 'إدارة المكاتب',
                permTtl: 'إدارة الصلاحيات',
                usrTtl: 'إدارة المستخدمين',
                anlTtl: 'التحليلات والتقارير',
                offFinTtl: 'مالية المكاتب',
                offSpecTtl: 'تخصصات المكاتب',
                offSrvAprTtl: 'اعتماد خدمات المكاتب',
                priceTtl: 'إدارة التسعير',
                priceSub: 'تحكم في أسعار الخدمات',
                finTtl: 'الحركة المالية',
                finSub: 'جميع المعاملات المالية',
                finExp: 'تصدير',
                finTotalL: 'إجمالي الإيرادات (ر.س)',
                finWeekL: 'هذا الأسبوع (ر.س)',
                finAvgL: 'متوسط قيمة الطلب',
                finPendL: 'معلقة (ر.س)',
                setTtl: 'الإعدادات',
                setProfileTtl: 'الملف الشخصي',
                setLblNm: 'الاسم',
                setLblEm: 'البريد الإلكتروني',
                setSaveL: 'حفظ التغييرات',
                sar: 'ر.س',
                noReqs: 'لا توجد طلبات',
                admin: 'مدير النظام',
                stPend: 'قيد الانتظار',
                stProc: 'جاري المعالجة',
                stInprog: 'قيد التنفيذ',
                stDone: 'تمت العملية',
                stRej: 'مرفوض',
                days: ['أح', 'إث', 'ثل', 'أر', 'خم', 'جم', 'سب'],
                updateSuc: 'تم التحديث بنجاح',
                priceSaved: 'تم حفظ السعر',
            },
            en: {
                nm: 'Amrtm',
                da: 'Dashboard',
                s1: 'Main',
                s2: 'Requests',
                s3: 'Management',
                s4: 'Other',
                siOv: 'Overview',
                siReq: 'Requests',
                siPrice: 'Pricing',
                siFin: 'Finance',
                siSite: 'Website',
                siSet: 'Settings',
                ovTtl: 'Platform Overview',
                ovSub: 'Last updated: moments ago',
                ovViewReq: 'View Requests',
                scTotal: 'Total Requests',
                scPend: 'Pending',
                scProc: 'Processing',
                scDone: 'Completed',
                scRej: 'Rejected',
                scUsers: 'Users',
                ch1: 'Requests over 7 days',
                ch1s: 'Daily request count',
                ch2: 'Request Distribution',
                ch2s: 'By status',
                dLbl: 'Requests',
                topTtl: 'Top Requested Services (Last 30 Days)',
                topSub: 'Based on number of requests received',
                reqTtl: 'Request List',
                rfAll: 'All',
                rfPend: 'Pending',
                rfProc: 'Processing',
                rfDone: 'Completed',
                rfRej: 'Rejected',
                saProc: 'Processing',
                saInprog: 'In Progress',
                saDone: 'Complete',
                saRej: 'Reject',
                setTime: 'Set Completion Time',
                timePh: 'e.g. 5 business days',
                timeSave: 'Save',
                rejPlh: 'Write rejection reason...',
                rejSend: 'Send',
                logTtl: 'Activity Log',
                catTtl: 'Entities & Services',
                ctrTtl: 'Contracts Management',
                offTtl: 'Offices Management',
                permTtl: 'Permissions Management',
                usrTtl: 'Users Management',
                anlTtl: 'Analytics & Reports',
                offFinTtl: 'Offices Finance',
                offSpecTtl: 'Office Specialties',
                offSrvAprTtl: 'Office Services Approval',
                priceTtl: 'Pricing Management',
                priceSub: 'Control service prices',
                finTtl: 'Financial Activity',
                finSub: 'All financial transactions',
                finExp: 'Export',
                finTotalL: 'Total Revenue (SAR)',
                finWeekL: 'This Week (SAR)',
                finAvgL: 'Avg. Request Value',
                finPendL: 'Pending (SAR)',
                setTtl: 'Settings',
                setProfileTtl: 'Profile',
                setLblNm: 'Name',
                setLblEm: 'Email',
                setSaveL: 'Save Changes',
                sar: 'SAR',
                noReqs: 'No requests found',
                admin: 'System Admin',
                stPend: 'Pending',
                stProc: 'Processing',
                stInprog: 'In Progress',
                stDone: 'Completed',
                stRej: 'Rejected',
                days: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                updateSuc: 'Updated successfully',
                priceSaved: 'Price saved',
            },
        };

        function pageTitle(l, p) {
            const titles = {
                overview: T[l].ovTtl,
                requests: T[l].reqTtl,
                pricing: T[l].priceTtl,
                finance: T[l].finTtl,
                settings: T[l].setTtl,
                catalog: T[l].catTtl,
                contracts: T[l].ctrTtl,
                offices: T[l].offTtl,
                permissions: T[l].permTtl,
                users: T[l].usrTtl,
                analytics: T[l].anlTtl,
                logs: T[l].logTtl,
                'off-finance': T[l].offFinTtl,
                'office-specialties': T[l].offSpecTtl,
                'services-approvals': T[l].offSrvAprTtl,
            };
            return titles[p] || '';
        }

        let lang = localStorage.getItem('amrtm_lang') || 'ar';
        let curPage = 'overview';
        let allReqs = [];
        let reqFilter = 'all';
        let reqPage = 1;
        let reqMeta = {};
        let statsData = {};
let allServices = [];
        let _reqSearchTimer = null;
        let _catInitDone = false;

        /* ══ INIT ══ */
async function init() {
            if (typeof Auth !== 'undefined' && !Auth.isLoggedIn()) {
                window.location.href = (AMRTM_ROUTES && AMRTM_ROUTES.login) || '/login';
                return;
            }
            applyLang(lang);
            curPage = (typeof window.AMRTM_PAGE !== 'undefined' && window.AMRTM_PAGE) || 'overview';
            const _ptEl = document.getElementById('dash-page-title');
            if (_ptEl) _ptEl.textContent = pageTitle(lang, curPage);
            const u = typeof Auth !== 'undefined' ? Auth.getUser() : window.AMRTM_USER;
            // Show supervisor-only items
            if (u && u.role === 'supervisor') {
                const w = document.getElementById('si-perms-wrap');
                if (w) w.style.display = '';
            }
            /* SSR embedded-data fast path — skip fetch on first paint */
            const ssrData = window.AMRTM_PAGE_DATA;
            window.AMRTM_PAGE_DATA = null;
            if (ssrData) {
                consumeSSRData(curPage, ssrData);
                return;
            }
            await loadPageData(curPage);
        }

        function consumeSSRData(page, data) {
            if (page === 'overview' && data.stats) {
                statsData = data.stats;
                allReqs   = (data.stats.recent_payments) || [];
                renderAll();

            } else if (page === 'requests' && data.requests) {
                const r = data.requests;
                allReqs = (r && r.data) || [];
                reqMeta = r ? {
                    current_page: r.current_page || 1,
                    last_page:    r.last_page    || 1,
                    total:        r.total        || 0,
                    per_page:     r.per_page     || 15,
                } : {};
                renderReqList();
                renderReqPagination();

            } else if (page === 'pricing' && data.services) {
                allServices = data.services || [];
                renderPricing();

            } else if (page === 'contracts' && data.settlements) {
                _contractsData = (data.settlements && data.settlements.data) || [];
                _contractsLoaded = true;
                renderContractsList();

            } else if (page === 'finance' && data.finance) {
                _finData = data.finance;
                const s = data.finance.summary || {};
                S('fin-total', riyalsOfl(s.total_revenue));
                S('fin-week', riyalsOfl(s.this_week));
                S('fin-avg', riyalsOfl(s.avg_order));
                S('fin-pend', riyalsOfl(s.pending));
                renderFinRows(data.finance.transactions || []);
                renderFinPager(data.finance.pagination || {});

            } else if (page === 'off-finance' && data.officeFinancial) {
                _oflData = data.officeFinancial;
                const s = data.officeFinancial.summary || {};
                S('ofl-total', s.total_requests ?? '—');
                S('ofl-gross', riyalsOfl(s.total_gross));
                S('ofl-comm', riyalsOfl(s.total_commission));
                S('ofl-net', riyalsOfl(s.total_net));
                S('ofl-completed', s.completed_requests ?? '—');
                /* office financial page renders inline in loadOfficeFinancial;
                   no separate render function exists — DOM updates only */
                const byOffice = data.officeFinancial.by_office || [];
                const oflByEl = document.getElementById('ofl-by-office');
                if (oflByEl) {
                    oflByEl.innerHTML = byOffice.length
                        ? byOffice.map(o => `<tr><td style="padding:.65rem .8rem;font-weight:700;">${esc(lang==='ar'?o.name_ar:(o.name_en||o.name_ar))}</td><td style="padding:.65rem .8rem;text-align:center;">${o.commission_rate??0}%</td><td style="padding:.65rem .8rem;text-align:center;">${o.req_count??0}</td><td style="padding:.65rem .8rem;">${riyalsOfl(o.gross)}</td><td style="padding:.65rem .8rem;color:var(--blue);font-weight:700;">${riyalsOfl(o.commission)}</td><td style="padding:.65rem .8rem;">${riyalsOfl(o.net)}</td><td style="padding:.65rem .8rem;text-align:center;">${o.completed??0}</td></tr>`).join('')
                        : '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--t3)">لا توجد بيانات</td></tr>';
                }
                const monthly = data.officeFinancial.monthly || [];
                const oflMEl = document.getElementById('ofl-monthly');
                if (oflMEl) {
                    oflMEl.innerHTML = monthly.length
                        ? monthly.map(m => `<tr><td style="padding:.65rem .8rem;font-weight:700;">${esc(m.month)}</td><td style="padding:.65rem .8rem;text-align:center;">${m.req_count??0}</td><td style="padding:.65rem .8rem;">${riyalsOfl(m.gross)}</td><td style="padding:.65rem .8rem;color:var(--blue);font-weight:700;">${riyalsOfl(m.commission)}</td></tr>`).join('')
                        : '<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--t3)">لا توجد بيانات</td></tr>';
                }

            } else if (page === 'catalog' && data.categories) {
                _catData = data.categories || [];
                _entData = data.entities   || [];
                _svcData = data.services   || [];
                _catInitDone  = true;
                renderCatList();
                populateCatSelects();
                renderEntList();
                populateEntSelects();
                renderSvcList();
                loadServiceSpecialtyOptions();

            } else if (page === 'users' && data.users) {
                const u = data.users;
                _usrData = (u && u.data) || [];
                _usrMeta = u ? {
                    current_page: u.current_page || 1,
                    last_page:    u.last_page    || 1,
                    total:        u.total        || 0,
                } : {};
                if (data.userStats) {
                    S('usr-sc-total', data.userStats.total || 0);
                    S('usr-sc-active', data.userStats.active || 0);
                    S('usr-sc-banned', data.userStats.banned || 0);
                    S('usr-sc-new', data.userStats.newThisMonth || 0);
                }
                renderUserList();
                renderUserPagination();

            } else if (page === 'analytics' && data.analytics) {
                _analyticsData = data.analytics;
                renderAnalytics();

            } else if (page === 'logs' && data.logs) {
                const l = data.logs;
                renderLogList((l && l.data) || [], l || {});
                renderLogPagination(l || {});

            } else if (page === 'offices' && data.offices) {
                const o = data.offices;
                _offData = (o && o.data) || [];
                _offMeta = o ? {
                    current_page: o.current_page || 1,
                    last_page:    o.last_page    || 1,
                    total:        o.total        || 0,
                } : {};
                if (data.officeStats) {
                    S('off-sc-total', data.officeStats.total || 0);
                    S('off-sc-verified', data.officeStats.verified || 0);
                    S('off-sc-pending', data.officeStats.pending || 0);
                    S('off-sc-inactive', data.officeStats.inactive || 0);
                }
                renderOffList();

            } else if (page === 'office-specialties' && data.specialties) {
                renderOfficeSpecialties(data.specialties || []);

            } else if (page === 'services-approvals' && data.pendingServices) {
                const ps = data.pendingServices || [];
                const pact = document.getElementById('off-apr-count');
                if (pact) pact.textContent = ps.length;
                const pWrap = document.getElementById('off-apr-count-wrap');
                if (pWrap) pWrap.style.display = ps.length ? '' : 'none';
                renderOfficeApprovals(ps);

            } else if (page === 'permissions' && data.admins) {
                _adminsData = (data.admins && data.admins.data) || [];
                renderAdminsList();
            }
            /* settings: no SSR data needed */
        }

        async function loadData() {
            try {
                const srch = document.getElementById('srch-inp')?.value?.trim() || '';
                const [stats, reqs, services] = await Promise.all([Dashboard.adminStats(), Admin.getRequests(reqFilter,
                    srch, reqPage), Services.getAll()]);
                statsData = stats;
                allReqs = (reqs && reqs.data) || [];
                reqMeta = reqs ? {
                    current_page: reqs.current_page || 1,
                    last_page: reqs.last_page || 1,
                    total: reqs.total || 0,
                    per_page: reqs.per_page || 15
                } : {};
                allServices = services || [];
                renderAll();
            } catch (e) {
                console.error('Dashboard load error:', e);
                if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'حدث خطأ في تحميل البيانات' :
                    'Failed to load data', 'error');
            }
        }

        function renderAll() {

            const t = T[lang];
            const r = statsData.requests || {};

            // Stats
            S('sc-total', r.total || 0);
            S('sc-pend', r.pending || 0);
            S('sc-proc', r.processing || 0);
            S('sc-done', r.done || 0);
            S('sc-rej', r.rejected || 0);
            S('sc-users', statsData.users || 0);

            { const _db = document.getElementById('dash-notif-badge'); if (_db) { _db.textContent = (r.pending || 0); _db.style.display = (r.pending > 0) ? '' : 'none'; } }

            S('req-sub', `${t.rfAll}: ${r.total || 0} ${lang === 'ar' ? 'طلب' : 'requests'}`);

            // Donut (للمشرف فقط)
            if (document.getElementById('d-total')) {

                const tot = r.total || 1;

                S('d-total', r.total || 0);

                const toDA = (v) => Math.round((v / tot) * 100);

                updateDonut(
                    'd-proc', toDA(r.processing || 0),
                    'd-pend', toDA(r.pending || 0),
                    'd-done', toDA(r.done || 0),
                    'd-rej', toDA(r.rejected || 0)
                );

                S('dl1', `${lang === 'ar' ? 'جاري' : 'Processing'} (${r.processing || 0})`);
                S('dl2', `${lang === 'ar' ? 'انتظار' : 'Pending'} (${r.pending || 0})`);
                S('dl3', `${lang === 'ar' ? 'مكتملة' : 'Completed'} (${r.done || 0})`);
                S('dl4', `${lang === 'ar' ? 'مرفوضة' : 'Rejected'} (${r.rejected || 0})`);
            }

            // Revenue
            const rev = statsData.revenue || {};

            S('fin-total', (rev.total || 0).toFixed(0));
            S('fin-week', (rev.week || 0).toFixed(0));
            S('fin-avg', (rev.avg || 0).toFixed(0));
            S('fin-pend', (rev.pending || 0).toFixed(0));

            // Render فقط إذا كانت الصفحة موجودة
            if (document.getElementById('bar-chart')) {
                renderBarChart();
            }

            if (document.getElementById('top-list')) {
                renderTopSvcs();
            }

            if (document.getElementById('req-list')) {
                renderReqList();
            }

            if (document.getElementById('price-grid')) {
                renderPricing();
            }

            if (document.getElementById('fin-table')) {
                renderFinTable();
            }
        }

        function updateDonut(id1, p1, id2, p2, id3, p3, id4, p4) {

            const setDA = (id, pct, offset) => {
                const el = document.getElementById(id);

                if (!el) return;

                el.setAttribute('stroke-dasharray', `${pct} ${100 - pct}`);
                el.setAttribute('stroke-dashoffset', offset);
            };

            setDA(id1, p1, 25);
            setDA(id2, p2, 25 - p1);
            setDA(id3, p3, 25 - p1 - p2);
            setDA(id4, p4, 25 - p1 - p2 - p3);
        }

        function renderBarChart() {

            const chart = document.getElementById('bar-chart');

            if (!chart) return;

            const t = T[lang];

            const data = statsData.chart_last7 || Array.from({
                length: 7
            }, (_, i) => ({
                count: Math.floor(Math.random() * 30 + 5),
                label: t.days[i]
            }));

            const max = Math.max(...data.map(d => d.count), 1);

            const colors = [
                '#059669',
                '#16a34a',
                '#047857',
                '#0277BD',
                '#059669',
                '#16a34a',
                '#047857'
            ];

            chart.innerHTML = data.map((d, i) => {

                const h = Math.round((d.count / max) * 90);

                return `
            <div class="relative flex flex-1 flex-col items-center">
                <div class="relative w-full rounded-t-[5px] transition-[height] duration-500" style="height:${h}px;background:${colors[i]};">
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 text-[9px] font-bold text-slate-700">${d.count}</span>
                    <span class="absolute -bottom-[18px] left-1/2 -translate-x-1/2 whitespace-nowrap text-[9px] text-slate-500">${d.label || t.days[i] || ''}</span>
                </div>
            </div>
        `;

            }).join('');
        }

        function renderTopSvcs() {

            const topList = document.getElementById('top-list');

            if (!topList) return;

            const t = T[lang];
            const tops = statsData.top_services || [];

            const max = Math.max(...tops.map(s => s.count), 1);

            topList.innerHTML = tops.map((s, i) => `
        <div class="flex items-center gap-3.5 border-b border-emerald-900/5 py-3 last:border-b-0">
            <div class="flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-md bg-emerald-600/10 text-[11px] font-extrabold text-emerald-600">${i + 1}</div>

            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background:${s.bg || 'rgba(5,150,105,.1)'}">
                ${renderIcon(s.icon, s.color || '#059669', 'ti-file-text')}
            </div>

            <div style="flex:1;min-width:0;">
                <div class="truncate text-[12.5px] font-bold text-slate-900">
                    ${lang === 'ar' ? (s.name_ar || s.name_en) : (s.name_en || s.name_ar)}
                </div>

                <div class="text-[10.5px] text-slate-500">
                    ${lang === 'ar' ? (s.entity_ar || '') : (s.entity_en || '')}
                </div>
            </div>

            <div class="h-1.5 w-20 overflow-hidden rounded-full bg-emerald-600/10">
                <div class="h-full rounded-full"
                     style="width:${Math.round((s.count / max) * 100)}%;background:${s.color || '#059669'}">
                </div>
            </div>

            <div class="min-w-[28px] text-center text-xs font-bold text-emerald-600">${s.count}</div>
        </div>
    `).join('');
        }

        /* ══ REQUESTS ══ */
        function filterReqs(f, btn) {
            reqFilter = f;
            reqPage = 1;
            document.querySelectorAll('.rf-btn').forEach(b => b.classList.remove('on'));
            if (btn) btn.classList.add('on');
            loadAdminRequests();
        }

        async function loadAdminRequests(page, status, search) {
            if (page !== undefined) reqPage = page;
            if (status !== undefined) reqFilter = status;
            const srch = search !== undefined ? search : (document.getElementById('srch-inp')?.value?.trim() || '');
            try {
                const res = await Admin.getRequests(reqFilter, srch, reqPage);
                if (!res) {
                    return;
                }
                allReqs = res.data || [];
                reqMeta = {
                    current_page: res.current_page || 1,
                    last_page: res.last_page || 1,
                    total: res.total || 0,
                    per_page: res.per_page || 15
                };
                renderReqList();
                renderReqPagination();
            } catch (e) {
                console.error('loadAdminRequests error:', e);
                if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'خطأ في تحميل الطلبات' :
                    'Failed to load requests', 'error');
            }
        }

        function renderReqPagination() {
            const container = document.getElementById('req-list-wrap') || document.getElementById('page-requests');
            const existing = document.getElementById('req-pag');
            if (existing) existing.remove();
            if (!reqMeta.last_page || reqMeta.last_page <= 1) return;
            const pag = document.createElement('div');
            pag.id = 'req-pag';
            pag.className = 'mt-4 flex flex-wrap items-center justify-center gap-2';
            const cur = reqMeta.current_page || 1;
            const last = reqMeta.last_page || 1;
            let html = '';
            if (cur > 1) html +=
                `<x-ui.button type="button" onclick="loadAdminRequests(${cur-1})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">${lang==='ar'?'السابق':'Prev'}</x-ui.button>`;
            for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
                const on = i === cur;
                const cls = on
                    ? 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-600 bg-emerald-600 text-white'
                    : 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent text-slate-700 hover:bg-emerald-600/5';
                html +=
                    `<x-ui.button type="button" onclick="loadAdminRequests(${i})" class="${cls} text-[13px] font-bold focus:ring-0!">${i}</x-ui.button>`;
            }
            if (cur < last) html +=
                `<x-ui.button type="button" onclick="loadAdminRequests(${cur+1})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">${lang==='ar'?'التالي':'Next'}</x-ui.button>`;
            pag.innerHTML = html;
            document.getElementById('req-list')?.insertAdjacentElement('afterend', pag);
        }

        /* أزرار الحالة المنطقية: تُعرض حسب رحلة الطلب (الخطوة التالية أولاً)
           وحسب صلاحيات الدور (الإسناد/الإنجاز للأدمن فقط) */
        function adminStatusActions(req, i) {
            const t = T[lang];
            const st = req.status || 'pending';
            const isOpen = ['pending', 'processing', 'in_progress'].includes(st);
            const isClosed = ['done', 'rejected'].includes(st);
            const role = (window.AMRTM_ADMIN_ROLE || 'admin').toLowerCase();

            const canFulfill = st === 'pending' && !req.fulfillment;
            const isPooled = req.origin === 'office' && st === 'pending' && req.fulfillment === 'open' && !req.office_id;
            const needFulfill = canFulfill || isPooled;
            const fulfillLbl = isPooled
                ? (lang === 'ar' ? 'بث / إنجاز' : 'Broadcast/Handle')
                : (lang === 'ar' ? 'إسناد / إنجاز' : 'Assign/Handle');

            const id = req.id || i;
            const btn = (cls, onClick, icon, label) =>
                `<x-ui.button type="button" class="sa ${cls} focus:ring-0!" onclick="${onClick}"><i class="ti ${icon}"></i>${label}</x-ui.button>`;

            /* طلب مغلق: شارة النتيجة فقط، لا أزرار حالة */
            if (isClosed) {
                const done = st === 'done';
                return `<span class="req-closed-flag ${st}"><i class="ti ${done ? 'ti-circle-check' : 'ti-x'}"></i>${done ? (lang === 'ar' ? 'اكتمل الطلب' : 'Completed') : (lang === 'ar' ? 'الطلب مرفوض' : 'Rejected')}</span>`;
            }
            if (!isOpen) return '';

            /* الخطوة المنطقية التالية في رحلة الطلب */
            let next = null;
            if (st === 'pending') next = ['processing', 'proc', 'ti-loader', t.saProc || (lang === 'ar' ? 'جاري المعالجة' : 'Process')];
            else if (st === 'processing') next = ['in_progress', 'inprog', 'ti-settings', t.saInprog || (lang === 'ar' ? 'قيد التنفيذ' : 'In progress')];
            else if (st === 'in_progress') next = ['done', 'done', 'ti-circle-check', t.saDone || (lang === 'ar' ? 'مكتمل' : 'Complete')];

            let html = '';
            if (next) html += btn(next[1] + ' sa-next', `updStatus(${i},'${id}','${next[0]}')`, next[2], next[3]);
            if (st === 'processing') html += btn('done', `updStatus(${i},'${id}','done')`, 'ti-circle-check', t.saDone || (lang === 'ar' ? 'مكتمل' : 'Complete'));
            html += '<span class="sa-sep"></span>';
            html += btn('rej', `togRejArea(${i})`, 'ti-x', t.saRej || (lang === 'ar' ? 'رفض' : 'Reject'));
            html += btn('note', `togNoteArea(${i})`, 'ti-message', lang === 'ar' ? 'ملاحظة' : 'Note');
            html += btn('info', `togInfoArea(${i})`, 'ti-info-circle', lang === 'ar' ? 'طلب معلومات' : 'Request Info');
            /* الإسناد/الإنجاز: للأدمن فقط وفي مرحلة البداية */
            if (needFulfill && role === 'admin') html += btn('assign', `togAssignArea(${i})`, 'ti-user-check', fulfillLbl);
            return html;
        }

        function renderReqList() {
            const t = T[lang];
            const filtered = reqFilter === 'all' ? allReqs : allReqs.filter(r => r.status === reqFilter);
            const reqListEl = document.getElementById('req-list');
            if (!reqListEl) return;
            if (!filtered.length) {
                reqListEl.innerHTML = `<div class="py-12 text-center text-sm text-slate-500">${t.noReqs}</div>`;
                return;
            }
            reqListEl.innerHTML = filtered.map((req, i) => {
                const {
                    label,
                    color,
                    bg
                } = stInfo(req.status);
                const gs = req.gov_service || {};
                const ent = req.entity || {};
                const svcNm = lang === 'ar' ? (gs.name_ar || gs.name_en || '—') : (gs.name_en || gs.name_ar || '—');
                const entNm = lang === 'ar' ? (ent.name_ar || '—') : (ent.name_en || '—');
                const c = ent.color || '#059669';
                const bg2 = ent.bg || 'rgba(5,150,105,.09)';
                const logs = req.logs || [];
                const canFulfill = req.status === 'pending' && !req.fulfillment;
                const isPooled = req.origin === 'office' && req.status === 'pending' && req.fulfillment === 'open' && !req.office_id;
                const needFulfill = canFulfill || isPooled;
                const fulfillLbl = isPooled ? (lang === 'ar' ? 'بث / إنجاز' : 'Broadcast/Handle') : (lang === 'ar' ? 'إسناد / إنجاز' : 'Assign/Handle');
                return `<div class="req-card" id="rc-${i}">
      <div class="req-hd" onclick="togReq(${i})">
        <div class="req-ico" style="background:${bg2};">${renderIcon(gs.icon,c,'ti-file-text')}</div>
        <div class="req-info">
          <div class="req-nm">${svcNm}</div>
          <div class="req-meta"><span>${req.client_name||'—'}</span><div class="dot"></div><span>${entNm}</span><div class="dot"></div><span>${req.ref_number||'—'}</span></div>
        </div>
        <div class="req-time">${fmtDate(req.created_at)}</div>
        <div class="req-st ${req.status}">${label}</div>
        <i class="ti ti-chevron-down req-chv"></i>
      </div>
      <div class="req-body">
        <!-- Details -->
        <div class="req-dg">
          <div class="rd"><div class="rd-l">${lang==='ar'?'الاسم':'Name'}</div><div class="rd-v">${req.client_name||'—'}</div></div>
          <div class="rd"><div class="rd-l">${lang==='ar'?'البريد':'Email'}</div><div class="rd-v">${req.client_email||'—'}</div></div>
          <div class="rd"><div class="rd-l">${lang==='ar'?'الجوال':'Phone'}</div><div class="rd-v">${req.client_phone||'—'}</div></div>
          <div class="rd"><div class="rd-l">${lang==='ar'?'الهوية':'ID'}</div><div class="rd-v">${req.client_id_number||'—'}</div></div>
          ${req.company_name?`<div class="rd"><div class="rd-l">${lang==='ar'?'الشركة':'Company'}</div><div class="rd-v">${req.company_name}</div></div>`:''}
          <div class="rd"><div class="rd-l">${lang==='ar'?'المبلغ':'Amount'}</div><div class="rd-v">${req.price||0} ${t.sar}</div></div>
        </div>
        <!-- Set estimated time -->
        <div class="time-row">
          ${xuiInput(`id="ti-${i}" value="${req.estimated_completion||''}" placeholder="${t.timePh}" class="time-inp focus:ring-0!"`)}
          <x-ui.button type="button" class="time-btn focus:ring-0!" onclick="setTime(${i},'${req.id||req.ref_number||i}')">${t.timeSave}</x-ui.button>
        </div>
        <!-- Status actions (منطقي حسب رحلة الطلب والصلاحيات) -->
        <div class="st-actions">
          ${adminStatusActions(req, i)}
        </div>
        <!-- Fulfillment decision area (internal / manual assign / pool broadcast) -->
        <div class="assign-area" id="aa-${i}" style="${needFulfill ? '' : 'display:none'}">
          <div class="assign-toggle">
            <x-ui.button type="button" class="at at-yes focus:ring-0!" onclick="takeInternal('${req.id||i}')"><i class="ti ti-building"></i>${lang==='ar'?'إنجاز من المنصة':'Handle Internally'}</x-ui.button>
            ${isPooled ? `
            <x-ui.button type="button" class="at at-off focus:ring-0!" onclick="togBroadcastPick(${i},'${req.id||i}')"><i class="ti ti-broadcast"></i>${lang==='ar'?'بث لشبكة المكاتب':'Broadcast to Offices'}</x-ui.button>` : `
            <x-ui.button type="button" class="at at-off focus:ring-0!" onclick="togOfficePick(${i},'${req.id||i}')"><i class="ti ti-building-bank"></i>${lang==='ar'?'إسناد لمكتب مساند':'Assign to Office'}</x-ui.button>`}
          </div>
          ${isPooled ? `
          <div class="assign-offices" id="bo-${i}" style="display:none">
            <p class="assign-hint">${lang==='ar'?'المكاتب المؤهلة لشبكة المكاتب — اختر للبث:':'Eligible pool offices — select to broadcast:'}</p>
            <div class="office-list" id="bl-${i}"></div>
          </div>` : `
          <div class="assign-offices" id="ao-${i}" style="display:none">
            <div class="assign-offices-head">
              <p class="assign-hint">${lang==='ar'?'اختر المكتب المساند:':'Select supporting office:'}</p>
              <x-ui.button type="button" class="of-all-btn focus:ring-0!" onclick="assignToAllOffices('${req.id||i}')"><i class="ti ti-broadcast"></i>${lang==='ar'?'إسناد / بث لكل المكاتب':'Assign to All'}</x-ui.button>
            </div>
            <!-- فلترة القوائم المنسدلة والبحث السريع -->
            <div class="of-filters-grid">
              <div class="of-select-wrap">
                ${xuiSelect(`<option value="">${lang==='ar'?'جميع المدن':'All Cities'}</option>`, `id="of-city-${i}" onchange="filterOfficeList(${i}, '${req.id||i}')"`)}
                <i class="ti ti-chevron-down"></i>
              </div>
              <div class="of-select-wrap">
                ${xuiSelect(`<option value="">${lang==='ar'?'جميع التخصصات / الأنشطة':'All Specialties'}</option>`, `id="of-type-${i}" onchange="filterOfficeList(${i}, '${req.id||i}')"`)}
                <i class="ti ti-chevron-down"></i>
              </div>
              <div class="of-search-wrap" style="margin-bottom:0">
                <i class="ti ti-search"></i>
                ${xuiInput(`id="of-search-${i}" class="of-search-inp" placeholder="${lang==='ar'?'بحث سريع بالاسم...':'Quick name search...'}" oninput="filterOfficeList(${i}, '${req.id||i}')"`)}
              </div>
            </div>
            <!-- قائمة منسدلة للإسناد السريع المباشر -->
            <div class="of-quick-assign-box">
              <div class="of-select-wrap" style="flex:1">
                ${xuiSelect(`<option value="">${lang==='ar'?'-- اختر مكتباً من القائمة للإسناد المباشر --':'-- Select office from dropdown to assign --'}</option>`, `id="of-select-${i}"`)}
                <i class="ti ti-chevron-down"></i>
              </div>
              <x-ui.button type="button" class="of-quick-assign-btn focus:ring-0!" onclick="doQuickAssign(${i}, '${req.id||i}')">
                <i class="ti ti-user-check"></i>${lang==='ar'?'إسناد للمكتب':'Assign'}
              </x-ui.button>
            </div>
            <!-- بطاقات المكاتب المصفاة -->
            <div class="office-list" id="ol-${i}"></div>
          </div>`}
        </div>
        <div class="assign-info" id="ai-${i}" style="display:${req.office ? 'flex' : 'none'}">
          <i class="ti ti-building"></i>
          <span>${req.office ? ((lang==='ar'?req.office.name_ar:req.office.name_en) + ' — ' + (req.office_status ? (assignStatusLabel(req.office_status)) : (lang==='ar'?'بانتظار قبول المكتب':'Awaiting office'))) : ''}</span>
        </div>
        <!-- Reject area -->
        <div class="rej-area" id="ra-${i}">
          ${xuiTextarea('', `id="rt-${i}" placeholder="${t.rejPlh}" rows="2" class="focus:ring-0!"`)}
          <x-ui.button type="button" class="rej-send focus:ring-0!" onclick="sendRej(${i},'${req.id||i}')"><i class="ti ti-send"></i>${t.rejSend}</x-ui.button>
        </div>
        <!-- Send Note area -->
        <div class="note-area" id="na-${i}">
          ${xuiTextarea('', `id="nt-${i}" placeholder="${lang==='ar'?'اكتب ملاحظة للمستخدم...':'Write a note to the user...'}" rows="2" class="focus:ring-0!"`)}
          <x-ui.button type="button" class="note-send focus:ring-0!" onclick="doSendNote(${i},'${req.id||i}')"><i class="ti ti-send"></i>${lang==='ar'?'إرسال الملاحظة':'Send Note'}</x-ui.button>
        </div>
        <!-- Request Info area -->
        <div class="info-area" id="ia-${i}">
          ${xuiTextarea('', `id="it-${i}" placeholder="${lang==='ar'?'اكتب ما تحتاجه من معلومات...':'Describe the information needed...'}" rows="2" class="focus:ring-0!"`)}
          <x-ui.button type="button" class="info-send focus:ring-0!" onclick="doRequestInfo(${i},'${req.id||i}')"><i class="ti ti-send"></i>${lang==='ar'?'إرسال طلب المعلومات':'Send Info Request'}</x-ui.button>
        </div>
        <!-- Log -->
        ${logs.length?`<div class="req-log-ttl"><i class="ti ti-history text-emerald-500"></i>${t.logTtl}</div>
                                                                                                        ${logs.map(l=>`<div class="log-row"><div class="log-dot" style="background:${stInfo(l.status).color}"></div><div class="log-txt">${l.note||stInfo(l.status).label}</div><div class="log-time">${fmtDate(l.created_at)}</div></div>`).join('')}`:''}
      </div>
    </div>`;
            }).join('');
        }

        function togReq(i) {
            document.getElementById('rc-' + i)?.classList.toggle('open');
        }

        function togRejArea(i) {
            document.getElementById('ra-' + i)?.classList.toggle('show');
        }

        async function updStatus(idx, reqId, status) {
            const t = T[lang];
            try {
                await Admin.updateStatus(reqId, status);
                if (typeof showToast !== 'undefined') showToast(t.updateSuc, 'success');
                await loadAdminRequests();
            } catch (e) {
                if (typeof showToast !== 'undefined') showToast('حدث خطأ', 'error');
            }
        }

        async function sendRej(idx, reqId) {
            const reason = document.getElementById('rt-' + idx)?.value?.trim();
            if (!reason) return;
            try {
                await Admin.updateStatus(reqId, 'rejected', reason);
                if (typeof showToast !== 'undefined') showToast(T[lang].updateSuc, 'success');
                await loadAdminRequests();
            } catch (e) {
                if (typeof showToast !== 'undefined') showToast('حدث خطأ', 'error');
            }
        }

        /* ══ FULFILLMENT (assign to office / handle internally) ══ */

        function assignStatusLabel(s) {
            const m = {
                pending: lang === 'ar' ? 'بانتظار قبول المكتب' : 'Awaiting office acceptance',
                accepted: lang === 'ar' ? 'قبلها المكتب' : 'Accepted by office',
                in_progress: lang === 'ar' ? 'قيد التنفيذ بالمكتب' : 'In progress at office',
                waiting_docs: lang === 'ar' ? 'ينتظر مستندات' : 'Waiting for docs',
                done: lang === 'ar' ? 'أنجزها المكتب' : 'Completed by office',
                rejected: lang === 'ar' ? 'رفضها المكتب' : 'Rejected by office',
            };
            return m[s] || s;
        }

        let assignableOfficesCache = null;

        async function getAssignableOffices() {
            if (!assignableOfficesCache) {
                assignableOfficesCache = await Admin.assignableOffices();
            }
            return assignableOfficesCache || [];
        }

        function togAssignArea(i) {
            document.getElementById('aa-' + i)?.classList.toggle('show');
        }

        function populateOfficeDropdownOptions(i, offices) {
            const citySel = document.getElementById('of-city-' + i);
            const typeSel = document.getElementById('of-type-' + i);
            if (citySel && citySel.options.length <= 1) {
                const cities = Array.from(new Set(offices.map(o => o.city).filter(Boolean))).sort();
                cities.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    citySel.appendChild(opt);
                });
            }
            if (typeSel && typeSel.options.length <= 1) {
                const types = Array.from(new Set(offices.map(o => o.type_ar || o.type).filter(Boolean))).sort();
                types.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    typeSel.appendChild(opt);
                });
            }
        }

        function updateQuickAssignDropdown(i, offices) {
            const selectEl = document.getElementById('of-select-' + i);
            if (!selectEl) return;
            const currentVal = selectEl.value;
            selectEl.innerHTML = `<option value="">${lang === 'ar' ? '-- اختر مكتباً للإسناد المباشر (' + offices.length + ' متاح) --' : '-- Select office to assign (' + offices.length + ' available) --'}</option>`;
            offices.forEach(o => {
                const name = lang === 'ar' ? o.name_ar : (o.name_en || o.name_ar);
                const city = o.city ? ` [${o.city}]` : '';
                const rate = o.commission_rate ? ` (${o.commission_rate}%)` : '';
                const type = o.type_ar ? ` - ${o.type_ar}` : '';
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = `${name}${city}${type}${rate}`;
                selectEl.appendChild(opt);
            });
            if (currentVal && offices.some(o => String(o.id) === String(currentVal))) {
                selectEl.value = currentVal;
            }
        }

        function renderOfficeListItems(listEl, offices, reqId) {
            if (!offices || !offices.length) {
                listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'لا توجد مكاتب مطابقة لخيارات الفلترة' : 'No matching offices found') + '</div>';
                return;
            }
            listEl.innerHTML = offices.map(o => `
                <div class="of-item">
                    <div class="of-info">
                        <i class="ti ti-building"></i>
                        <div>
                            <div class="font-semibold text-slate-800">${lang === 'ar' ? o.name_ar : (o.name_en || o.name_ar)}</div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                ${o.city ? `<span><i class="ti ti-map-pin text-[11px]"></i> ${o.city}</span>` : ''}
                                ${o.type_ar ? `<span><i class="ti ti-tag text-[11px]"></i> ${o.type_ar}</span>` : ''}
                                ${o.commission_rate ? `<span class="text-emerald-700 font-semibold">${o.commission_rate}%</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <x-ui.button type="button" class="of-pick focus:ring-0!" onclick="doAssign(${o.id}, '${reqId}')">${lang === 'ar' ? 'إسناد' : 'Assign'}</x-ui.button>
                </div>`).join('');
        }

        async function filterOfficeList(i, reqId) {
            const citySel = document.getElementById('of-city-' + i);
            const typeSel = document.getElementById('of-type-' + i);
            const searchInput = document.getElementById('of-search-' + i);
            const listEl = document.getElementById('ol-' + i);
            if (!listEl) return;

            const selectedCity = (citySel?.value || '').trim().toLowerCase();
            const selectedType = (typeSel?.value || '').trim().toLowerCase();
            const query = (searchInput?.value || '').trim().toLowerCase();

            const offices = await getAssignableOffices();
            const filtered = offices.filter(o => {
                const nameAr = (o.name_ar || '').toLowerCase();
                const nameEn = (o.name_en || '').toLowerCase();
                const city = (o.city || '').toLowerCase();
                const type = (o.type || '').toLowerCase();
                const typeAr = (o.type_ar || '').toLowerCase();

                const matchCity = !selectedCity || city === selectedCity;
                const matchType = !selectedType || type === selectedType || typeAr === selectedType;
                const matchSearch = !query || nameAr.includes(query) || nameEn.includes(query) || city.includes(query) || typeAr.includes(query);

                return matchCity && matchType && matchSearch;
            });

            updateQuickAssignDropdown(i, filtered);
            renderOfficeListItems(listEl, filtered, reqId);
        }

        async function doQuickAssign(i, reqId) {
            const selectEl = document.getElementById('of-select-' + i);
            const officeId = selectEl?.value;
            if (!officeId) {
                if (typeof showToast !== 'undefined') {
                    showToast(lang === 'ar' ? 'يرجى اختيار مكتب من القائمة المنسدلة أولاً' : 'Please select an office from the dropdown first', 'error');
                }
                return;
            }
            await doAssign(parseInt(officeId, 10), reqId);
        }

        async function assignToAllOffices(reqId) {
            const confirmMsg = lang === 'ar'
                ? 'تأكيد إسناد / بث هذا الطلب لجميع المكاتب المساندة المعتمدة؟ أول مكتب يحجز الطلب سيتولى التنفيذ.'
                : 'Confirm broadcasting this request to all supporting offices? The first office to claim it will handle it.';
            if (!confirm(confirmMsg)) return;
            try {
                const offices = await getAssignableOffices();
                if (!offices.length) {
                    if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'لا توجد مكاتب مساندة متاحة' : 'No supporting offices available', 'error');
                    return;
                }
                const officeIds = offices.map(o => o.id);
                const res = await Admin.broadcastRequest(reqId, officeIds);
                const msg = (res && res.message) || (lang === 'ar' ? 'تم بث الطلب لجميع المكاتب' : 'Broadcast to all offices');
                if (typeof showToast !== 'undefined') showToast(msg, 'success');
                await loadAdminRequests();
            } catch (e) {
                const msg = (e && e.data && e.data.message) || (lang === 'ar' ? 'حدث خطأ أثناء الإسناد' : 'Broadcast failed');
                if (typeof showToast !== 'undefined') showToast(msg, 'error');
            }
        }

        async function togOfficePick(i, reqId) {
            const box = document.getElementById('ao-' + i);
            if (!box) return;
            const showing = box.style.display === 'block';
            box.style.display = showing ? 'none' : 'block';
            if (!showing) {
                const id = 'ol-' + i;
                const listEl = document.getElementById(id);
                if (!listEl) return;
                const searchInput = document.getElementById('of-search-' + i);
                if (searchInput) searchInput.value = '';
                const citySel = document.getElementById('of-city-' + i);
                if (citySel) citySel.value = '';
                const typeSel = document.getElementById('of-type-' + i);
                if (typeSel) typeSel.value = '';

                listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'جاري التحميل...' : 'Loading...') + '</div>';
                try {
                    const offices = await getAssignableOffices();
                    if (!offices.length) {
                        listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'لا توجد مكاتب مساندة معتمدة متاحة' : 'No verified supporting offices available') + '</div>';
                        updateQuickAssignDropdown(i, []);
                        return;
                    }
                    populateOfficeDropdownOptions(i, offices);
                    updateQuickAssignDropdown(i, offices);
                    renderOfficeListItems(listEl, offices, reqId);
                } catch (e) {
                    listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'تعذر تحميل المكاتب' : 'Failed to load offices') + '</div>';
                }
            }
        }

        async function doAssign(officeId, reqId) {
            if (!officeId) return;
            try {
                await Admin.assignRequest(reqId, officeId);
                if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'تم إسناد الطلب للمكتب' : 'Assigned to office', 'success');
                await loadAdminRequests();
            } catch (e) {
                const msg = (e && e.data && e.data.message) || (lang === 'ar' ? 'حدث خطأ أثناء الإسناد' : 'Assign failed');
                if (typeof showToast !== 'undefined') showToast(msg, 'error');
            }
        }

        async function togBroadcastPick(i, reqId) {
            const box = document.getElementById('bo-' + i);
            if (!box) return;
            const showing = box.style.display === 'block';
            box.style.display = showing ? 'none' : 'block';
            if (!showing) {
                const listEl = document.getElementById('bl-' + i);
                if (!listEl) return;
                listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'جاري التحميل...' : 'Loading...') + '</div>';
                try {
                    const res = await Admin.eligibleOffices(reqId);
                    const offices = (res && res.offices) || [];
                    if (!offices.length) {
                        listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'لا توجد مكاتب مؤهلة لهذه الخدمة حالياً' : 'No eligible offices for this service yet') + '</div>';
                        return;
                    }
                    listEl.innerHTML = offices.map(o => `
                        <div class="of-item bc-item" onclick="togBc('bc-${i}-${o.id}')">
                            <x-ui.checkbox class="bc-chk" id="bc-${i}-${o.id}" value="${o.id}" name="bc-${i}[]" onclick="event.stopPropagation()" />
                            <div class="of-info" style="flex:1">
                                <i class="ti ti-building"></i>
                                <span>${lang === 'ar' ? o.name_ar : o.name_en}</span>
                                ${o.city ? `<small>${o.city}</small>` : ''}
                                <small class="text-emerald-700">${o.commission_rate ? o.commission_rate + '%' : ''}</small>
                            </div>
                        </div>`).join('') +
                        `<div class="bc-actions">
                            <x-ui.button type="button" class="of-pick focus:ring-0!" onclick="doBroadcast(${i},'${reqId}')">${lang === 'ar' ? 'بث للمكاتب المحددة' : 'Broadcast to Selected'}</x-ui.button>
                        </div>`;
                } catch (e) {
                    listEl.innerHTML = '<div class="of-empty">' + (lang === 'ar' ? 'تعذر تحميل المكاتب المؤهلة' : 'Failed to load eligible offices') + '</div>';
                }
            }
        }

        function togBc(id) {
            const ck = document.getElementById(id);
            if (ck) ck.checked = !ck.checked;
        }

        async function doBroadcast(idx, reqId) {
            const chks = Array.from(document.querySelectorAll('#bl-' + idx + ' .bc-chk:checked')).map(c => parseInt(c.value, 10));
            if (!chks.length) {
                if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'اختر مكتباً واحداً على الأقل' : 'Select at least one office', 'error');
                return;
            }
            if (!confirm(lang === 'ar' ? 'تأكيد بث الطلب لشبكة المكاتب المحددة؟ أول مكتب يحجز سيتولى التنفيذ.' : 'Broadcast this request to the selected offices? The first office to claim it will handle it.')) return;
            try {
                const res = await Admin.broadcastRequest(reqId, chks);
                const msg = (res && res.message) || (lang === 'ar' ? 'تم بث الطلب لشبكة المكاتب' : 'Request broadcast to offices');
                if (typeof showToast !== 'undefined') showToast(msg, 'success');
                await loadAdminRequests();
            } catch (e) {
                const msg = (e && e.data && e.data.message) || (lang === 'ar' ? 'فشل بث الطلب' : 'Broadcast failed');
                if (typeof showToast !== 'undefined') showToast(msg, 'error');
            }
        }

        async function takeInternal(reqId) {
            if (!confirm(lang === 'ar' ? 'تأكيد معالجة الطلب داخلياً من المنصة؟' : 'Confirm handling this request internally?')) return;
            try {
                await Admin.takeInternal(reqId);
                if (typeof showToast !== 'undefined') showToast(lang === 'ar' ? 'تم تحويل الطلب للمعالجة الداخلية' : 'Moved to internal handling', 'success');
                await loadAdminRequests();
            } catch (e) {
                const msg = (e && e.data && e.data.message) || (lang === 'ar' ? 'حدث خطأ' : 'An error occurred');
                if (typeof showToast !== 'undefined') showToast(msg, 'error');
            }
        }

        async function setTime(idx, reqId) {
            const time = document.getElementById('ti-' + idx)?.value?.trim();
            if (!time) return;
            try {
                await Admin.setTime(reqId, time);
                if (typeof showToast !== 'undefined') showToast(T[lang].updateSuc, 'success');
                await loadAdminRequests();
            } catch (e) {
                if (typeof showToast !== 'undefined') showToast('حدث خطأ', 'error');
            }
        }

        /* ══ PRICING ══ */
        function renderPricing() {
            const t = T[lang];
            const grid = document.getElementById('price-grid');
            if (!grid) return;
            const svcs = [];
            allServices.forEach(cat => (cat.entities || []).forEach(ent => (ent.services || []).forEach(s => svcs.push({
                ...s,
                entityNm_ar: ent.name_ar,
                entityNm_en: ent.name_en,
                color: ent.color || '#059669',
                bg: ent.bg || 'rgba(5,150,105,.09)'
            }))));
            if (!svcs.length) {
                grid.innerHTML = `<div style="color:var(--t3);">${t.noReqs}</div>`;
                return;
            }
            grid.innerHTML = svcs.map(s => {
                const nm = lang === 'ar' ? s.name_ar : s.name_en;
                const entNm = lang === 'ar' ? s.entityNm_ar : s.entityNm_en;
                return `<div class="rounded-2xl border border-emerald-900/10 bg-white p-5 shadow-sm shadow-emerald-900/5">
      <div class="mb-4 flex items-center gap-3 border-b border-emerald-900/5 pb-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:${s.bg}">${renderIcon(s.icon,s.color,'ti-file-text')}</div>
        <div class="min-w-0"><div class="truncate text-[13px] font-bold text-slate-900">${nm}</div><div class="text-[11px] text-slate-500">${entNm}</div></div>
      </div>
      <div class="flex items-center gap-3">
        ${xuiInput(`type="number" id="pi-${s.id}" value="${s.price||0}" min="0" class="flex-1! h-10 rounded-lg border border-emerald-900/10! bg-slate-50! px-3! text-[13.5px]! font-bold! text-slate-900! focus:border-emerald-600! focus:ring-0!"`)}
        <span class="shrink-0 text-xs font-medium text-slate-500">${t.sar}</span>
        <x-ui.button type="button" class="h-10 rounded-lg bg-emerald-600! px-3.5! text-[13px]! font-bold! text-white! transition hover:bg-emerald-700! focus:ring-0!" onclick="savePrice(${s.id},'pi-${s.id}')">${lang==='ar'?'حفظ':'Save'}</x-ui.button>
      </div>
    </div>`;
            }).join('');
        }

        async function savePrice(svcId, inputId) {
            const price = parseFloat(document.getElementById(inputId)?.value || 0);
            try {
                if (typeof Admin !== 'undefined') await Admin.updatePrice(svcId, price);
                if (typeof showToast !== 'undefined') showToast(T[lang].priceSaved, 'success');
            } catch (e) {
                if (typeof showToast !== 'undefined') showToast('حدث خطأ', 'error');
            }
        }

        /* ══ FINANCE TABLE ══ */
        function renderFinTable() {
            const t = T[lang];
            const el = document.getElementById('fin-table');
            if (!el) return;
            const pays = statsData.recent_payments || [];
            if (!pays.length) {
                el.innerHTML = `<div style="padding:2rem;text-align:center;color:var(--t3);">${t.noReqs}</div>`;
                return;
            }
            el.innerHTML =
                `<div class="fin-row"><div class="fin-ref">${lang==='ar'?'رقم الطلب':'Ref #'}</div><div class="fin-svc">${lang==='ar'?'الخدمة':'Service'}</div><div class="fin-client">${lang==='ar'?'العميل':'Client'}</div><div class="fin-amt">${lang==='ar'?'المبلغ':'Amount'}</div><div class="fin-status">${lang==='ar'?'النوع':'Type'}</div><div class="fin-date">${lang==='ar'?'التاريخ':'Date'}</div></div>` +
                pays.map(p => {
                    const isCharge = p.type === 'charge';
                    const sign = isCharge ? '+' : '-';
                    const amtCls = isCharge ? 'credit' : 'debit';
                    const desc = lang === 'ar' ? (p.description_ar || p.type) : (p.description_en || p.type);
                    return `<div class="fin-row">
      <div class="fin-ref">${p.transaction_ref||p.ref_number||'—'}</div>
      <div class="fin-svc">${desc}</div>
      <div class="fin-client">${p.user?.name||'—'}</div>
      <div class="fin-amt ${amtCls}">${sign}${parseFloat(p.amount||0).toFixed(2)} ${t.sar}</div>
      <div class="fin-status" style="font-size:12px;font-weight:600;color:${isCharge?'var(--green)':'var(--red)'};">${p.type}</div>
      <div class="fin-date">${fmtDate(p.created_at)}</div>
    </div>`;
                }).join('');
        }

        function showPage(p) {

            curPage = p;

            // إخفاء كل الصفحات
            document.querySelectorAll('.page').forEach(el => {
                el.classList.remove('on');
            });

            // إظهار الصفحة المطلوبة
            document.getElementById('page-' + p)?.classList.add('on');

S('dash-page-title', pageTitle(lang, p) || '');

            // تحميل البيانات حسب الصفحة
            loadPageData(p);
        }

        /* ══ LOAD DATA PER PAGE ══ */
        function loadPageData(p) {
            // المشرف العام على الصفحة الرئيسية يحمّل الإحصائيات الأساسية
            if (!p || p === 'overview') {
                loadData();
                return;
            }

            if (p === 'catalog') {
                initCatalog();
            }

            if (p === 'requests') {
                loadAdminRequests();
            }

            if (p === 'offices') {
                loadOfficeStats();
                loadOffices();
            }

            if (p === 'office-specialties') {
                loadOfficeSpecialties(
                    document.getElementById('specialty-filter-type')?.value || ''
                );
            }

            if (p === 'services-approvals') {
                loadOfficeApprovals();
            }

            if (p === 'permissions') {
                loadAdmins();
            }

            if (p === 'users') {
                loadUserStats();
                loadUsers();
            }

            if (p === 'analytics') {
                loadAnalytics();
            }

            if (p === 'logs') {
                loadLogs();
            }

            if (p === 'off-finance') {
                loadOfficeFinancial();
            }

            if (p === 'finance') {
                loadAdminFinance(_finPage);
            }

            if (p === 'contracts') {
                if (!_contractsLoaded) loadAdminContracts(); else renderContractsList();
            }
        }
        /* ══ LANG ══ */
        function setLang(l) {
            lang = l;
            localStorage.setItem('amrtm_lang', l);
            document.documentElement.setAttribute('lang', l);
            document.documentElement.setAttribute('dir', l === 'ar' ? 'rtl' : 'ltr');
            document.body.className = l;
            document.getElementById('la').classList.toggle('on', l === 'ar');
            document.getElementById('le').classList.toggle('on', l === 'en');
            applyLang(l);
            renderAll();
            S('dash-page-title', pageTitle(l, curPage) || '');
        }

        function applyLang(l) {
            const t = T[l];
            [
                ['ov-ttl', 'ovTtl'],
                ['ov-sub', 'ovSub'],
                ['ov-view-req', 'ovViewReq'],
                ['sc-total-l', 'scTotal'],
                ['sc-pend-l', 'scPend'],
                ['sc-proc-l', 'scProc'],
                ['sc-done-l', 'scDone'],
                ['sc-rej-l', 'scRej'],
                ['sc-users-l', 'scUsers'],
                ['ch1-ttl', 'ch1'],
                ['ch1-sub', 'ch1s'],
                ['ch2-ttl', 'ch2'],
                ['ch2-sub', 'ch2s'],
                ['d-lbl', 'dLbl'],
                ['top-ttl', 'topTtl'],
                ['top-sub', 'topSub'],
                ['req-ttl', 'reqTtl'],
                ['rf-all', 'rfAll'],
                ['rf-pend', 'rfPend'],
                ['rf-proc', 'rfProc'],
                ['rf-done', 'rfDone'],
                ['rf-rej', 'rfRej'],
                ['price-ttl', 'priceTtl'],
                ['price-sub', 'priceSub'],
                ['fin-ttl', 'finTtl'],
                ['fin-sub', 'finSub'],
                ['fin-exp', 'finExp'],
                ['fin-total-l', 'finTotalL'],
                ['fin-week-l', 'finWeekL'],
                ['fin-avg-l', 'finAvgL'],
                ['fin-pend-l', 'finPendL'],
                ['set-ttl', 'setTtl'],
                ['set-profile-ttl', 'setProfileTtl'],
                ['set-lbl-nm', 'setLblNm'],
                ['set-lbl-em', 'setLblEm'],
                ['set-save-l', 'setSaveL'],
                ['sb-role', 'admin'],
            ].forEach(([id, k]) => S(id, t[k]));
        }

        /* ══ HELPERS ══ */
        function S(id, v) {
            const el = document.getElementById(id);
            if (el) el.textContent = v;
        }

        function fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString(lang === 'ar' ? 'ar-SA' : 'en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function stInfo(s) {
            const m = {
                pending: {
                    label: T[lang].stPend,
                    color: 'var(--orange)',
                    bg: 'rgba(230,81,0,.1)'
                },
                processing: {
                    label: T[lang].stProc,
                    color: 'var(--blue)',
                    bg: 'rgba(2,119,189,.1)'
                },
                in_progress: {
                    label: T[lang].stInprog,
                    color: 'var(--yellow)',
                    bg: 'rgba(249,168,37,.1)'
                },
                done: {
                    label: T[lang].stDone,
                    color: 'var(--green)',
                    bg: 'rgba(4,120,87,.1)'
                },
                rejected: {
                    label: T[lang].stRej,
                    color: 'var(--red)',
                    bg: 'rgba(220,38,38,.1)'
                }
            };
            return m[s] || {
                label: s,
                color: '#999',
                bg: 'rgba(0,0,0,.05)'
            };
        }

        function doLogout() {
            if (typeof Auth !== 'undefined') Auth.logout();
            else {
                localStorage.removeItem('amrtm_token');
                localStorage.removeItem('amrtm_user');
                window.location.href = (AMRTM_ROUTES && AMRTM_ROUTES.login) || '/login';
            }
        }


document.getElementById('srch-inp')?.addEventListener('input', function() {
            clearTimeout(_reqSearchTimer);
            _reqSearchTimer = setTimeout(() => {
                reqPage = 1;
                loadAdminRequests();
            }, 400);
        });

        /* ══ ADMIN: NOTE & INFO ACTIONS ══ */
        function togNoteArea(i) {
            document.getElementById('na-' + i)?.classList.toggle('show');
            document.getElementById('ia-' + i)?.classList.remove('show');
            document.getElementById('ra-' + i)?.classList.remove('show');
        }

        function togInfoArea(i) {
            document.getElementById('ia-' + i)?.classList.toggle('show');
            document.getElementById('na-' + i)?.classList.remove('show');
            document.getElementById('ra-' + i)?.classList.remove('show');
        }

        async function doSendNote(idx, reqId) {
            const note = document.getElementById('nt-' + idx)?.value?.trim();
            if (!note) {
                showToast(lang === 'ar' ? 'الرجاء كتابة ملاحظة' : 'Please write a note', 'warning');
                return;
            }
            showLoader();
            try {
                if (typeof Admin !== 'undefined') await Admin.sendNote(reqId, note);
                showToast(lang === 'ar' ? 'تم إرسال الملاحظة' : 'Note sent', 'success');
                document.getElementById('na-' + idx)?.classList.remove('show');
                document.getElementById('nt-' + idx).value = '';
                await loadData();
            } catch (e) {
                showToast(lang === 'ar' ? 'فشل الإرسال' : 'Failed to send', 'error');
            } finally {
                hideLoader();
            }
        }

        async function doRequestInfo(idx, reqId) {
            const msg = document.getElementById('it-' + idx)?.value?.trim();
            if (!msg) {
                showToast(lang === 'ar' ? 'الرجاء كتابة رسالة' : 'Please write a message', 'warning');
                return;
            }
            showLoader();
            try {
                if (typeof Admin !== 'undefined') await Admin.requestInfo(reqId, msg);
                showToast(lang === 'ar' ? 'تم إرسال طلب المعلومات' : 'Info request sent', 'success');
                document.getElementById('ia-' + idx)?.classList.remove('show');
                document.getElementById('it-' + idx).value = '';
                await loadData();
            } catch (e) {
                showToast(lang === 'ar' ? 'فشل الإرسال' : 'Failed to send', 'error');
            } finally {
                hideLoader();
            }
        }

        /* ══ ADMIN NOTIFICATIONS → DashNotif (layout topbar) ══ */
        (window.__dashNotifProviders = window.__dashNotifProviders || []).push({
            getAll: (all) => window.Notifications.getAll(all),
            unreadCount: () => window.Notifications.unreadCount(),
            markRead: (id) => window.Notifications.markRead(id),
            markAllRead: () => window.Notifications.markAllRead(),
            allUrl: '/dashboard#requests'
        });

        /* ══ OPEN REQUEST FROM NOTIFICATION CLICK ══ */
        let _notifFocusBusy = false;
        window.addEventListener('amrtm:notif-open', async function (e) {
            const n = e.detail;
            if (!n || _notifFocusBusy) return;
            const reqId = n.request_id || (n.data && n.data.request_id);
            showPage('requests');
            if (!reqId) return;
            _notifFocusBusy = true;
            try {
                const srchEl = document.getElementById('srch-inp');
                if (srchEl) srchEl.value = '';
                const target = String(reqId);
                const maxPage = 50;
                let found = false;
                for (let page = 1; page <= maxPage && !found; page++) {
                    await loadAdminRequests(page, 'all', '');
                    const idx = (allReqs || []).findIndex(r => String(r.id) === target || String(r.ref_number) === target);
                    if (idx < 0) {
                        if (page >= (reqMeta.last_page || page)) break;
                        continue;
                    }
                    const card = document.getElementById('rc-' + idx);
                    if (card) {
                        if (!card.classList.contains('open')) togReq(idx);
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.classList.add('notif-focus');
                        window.setTimeout(() => card.classList.remove('notif-focus'), 2400);
                    }
                    found = true;
                }
                if (!found && typeof showToast !== 'undefined') {
                    showToast(lang === 'ar' ? 'لم يتم العثور على الطلب' : 'Request not found', 'info');
                }
            } finally {
                _notifFocusBusy = false;
            }
        });

        /* ══════════════════════════════════════════════════════════════
           CATALOG MANAGEMENT
        ══════════════════════════════════════════════════════════════ */

let _catData = [],
            _entData = [],
            _svcData = [];

        function catTab(name, btn) {
            document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('on'));
            document.querySelectorAll('.cat-tab-panel').forEach(p => p.classList.remove('on'));
            btn.classList.add('on');
            document.getElementById('cat-panel-' + name)?.classList.add('on');
        }

        async function initCatalog() {
            if (_catInitDone) return;
            _catInitDone = true;
            await loadCategories();
            await loadEntities();
            await loadServiceSpecialtyOptions();
            await loadSvcList();
        }

        /* ── Categories ── */
        async function loadCategories() {
            try {
                _catData = await Admin.catalog.getCategories();
                renderCatList();
                populateCatSelects();
            } catch (e) {
                showToast('خطأ في تحميل التصنيفات', 'error');
            }
        }

        function renderCatList() {
            const el = document.getElementById('cat-list-body');
            if (!_catData.length) {
                el.innerHTML = '<div class="cat-empty">لا توجد تصنيفات بعد.</div>';
                return;
            }
            el.innerHTML = _catData.map(c => `
    <div class="cat-row" style="grid-template-columns:36px 1fr 1fr 80px 60px 120px;" id="cat-row-${c.id}">
      <div class="ico-prev" style="background:${c.bg||'rgba(5,150,105,.1)'}">${renderIcon(c.icon,c.color||'#059669','ti-folder')}</div>
      <div><div class="cat-nm">${c.name_ar}</div><div class="cat-sub">${c.name_en}</div></div>
      <div class="cat-sub">${c.key||'—'}</div>
      <div><span class="badge-count">${c.entities_count||0}</span></div>
      <div><span class="cat-status-dot ${c.is_active?'active':'inactive'}"></span></div>
    <div class="cat-actions">

    <x-ui.button type="button"
        class="cat-act-btn edit focus:ring-0!"
        onclick="editCat(${c.id})">
        تعديل
    </x-ui.button>

    <x-ui.button type="button"
        class="cat-act-btn tog ${c.is_active?'':'off'} focus:ring-0!"
        onclick="toggleCat(${c.id},${c.is_active?1:0})">
        ${c.is_active?'نشط':'متوقف'}
    </x-ui.button>

    <x-ui.button type="button"
        class="cat-act-btn del focus:ring-0!"
        onclick="deleteCat(${c.id})">
        <i class="ti ti-trash"></i>
    </x-ui.button>

</div>
    </div>`).join('');
        }

        function populateCatSelects() {
            const opts = _catData.map(c => `<option value="${c.id}">${c.name_ar}</option>`).join('');
            ['ent-category-id', 'ent-filter-cat'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                const prev = el.value;
                const prefix = id === 'ent-filter-cat' ? '<option value="">الكل</option>' :
                    '<option value="">-- اختر تصنيفاً --</option>';
                el.innerHTML = prefix + opts;
                el.value = prev;
            });
        }



        let _editingCategoryId = null;

        function editCat(id) {
            const cat = _catData.find(c => c.id == id);
            if (!cat) return;

            _editingCategoryId = id;

            document.getElementById('cat-name-ar').value = cat.name_ar || '';
            document.getElementById('cat-name-en').value = cat.name_en || '';
            document.getElementById('cat-key').value = cat.key || '';
            document.getElementById('cat-icon').value = cat.icon || '';
            document.getElementById('cat-color').value = cat.color || '';
            document.getElementById('cat-bg').value = cat.bg || '';
            document.getElementById('cat-sort').value = cat.sort_order || '';

            const btn = document.querySelector('#cat-panel-categories .btn-pri');
            if (btn) {
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ التعديل';
            }

            document.getElementById('cat-name-ar').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }


        async function doCreateCategory() {

            const get = id => document.getElementById(id)?.value?.trim();

            const data = {
                key: get('cat-key'),
                name_ar: get('cat-name-ar'),
                name_en: get('cat-name-en'),
                icon: get('cat-icon'),
                color: get('cat-color'),
                bg: get('cat-bg'),
                sort_order: parseInt(get('cat-sort')) || undefined,
            };


            if (!data.key || !data.name_ar || !data.name_en || !data.icon || !data.color || !data.bg) {
                showToast('يرجى ملء جميع الحقول المطلوبة', 'warning');
                return;
            }

            try {

                showLoader();

                if (_editingCategoryId) {

                    await Admin.catalog.updateCategory(_editingCategoryId, data);

                    showToast('تم تعديل التصنيف بنجاح', 'success');

                    _editingCategoryId = null;

                    const btn = document.querySelector('#cat-panel-categories .btn-pri');

                    if (btn) {
                        btn.innerHTML =
                            '<i class="ti ti-plus"></i> حفظ التصنيف';
                    }

                } else {

                    await Admin.catalog.createCategory(data);

                    showToast('تم إضافة التصنيف بنجاح', 'success');

                }

                [
                    'cat-key',
                    'cat-name-ar',
                    'cat-name-en',
                    'cat-icon',
                    'cat-color',
                    'cat-bg',
                    'cat-sort'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });

                cpReset('cat-cp-row', 'cat-cp-label');

                await loadCategories();

            } catch (e) {

                showToast(e?.data?.message || 'حدث خطأ', 'error');

            } finally {

                hideLoader();

            }
        }

        async function toggleCat(id, wasActive) {
            try {
                await Admin.catalog.updateCategory(id, {
                    is_active: !wasActive
                });
                await loadCategories();
                showToast(wasActive ? 'تم إيقاف التصنيف' : 'تم تفعيل التصنيف', 'info');
            } catch (e) {
                showToast('خطأ', 'error');
            }
        }

        async function deleteCat(id) {
            if (!confirm('هل أنت متأكد من حذف هذا التصنيف؟')) return;
            try {
                showLoader();
                await Admin.catalog.deleteCategory(id);
                await loadCategories();
                showToast('تم الحذف', 'success');
            } catch (e) {
                showToast(e?.data?.message || 'لا يمكن الحذف', 'error');
            } finally {
                hideLoader();
            }
        }

        /* ── Entities ── */
        async function loadEntities(catId) {
            try {
                _entData = await Admin.catalog.getEntities(catId || '');
                renderEntList();
                populateEntSelects();
            } catch (e) {
                showToast('خطأ في تحميل الجهات', 'error');
            }
        }

        function renderEntList() {
            const el = document.getElementById('ent-list-body');
            if (!_entData.length) {
                el.innerHTML = '<div class="cat-empty">لا توجد جهات بعد.</div>';
                return;
            }

            el.innerHTML = _entData.map(e => `
    <div class="cat-row" style="grid-template-columns:36px 1fr 1fr 80px 60px 170px;">
      <div class="ico-prev" style="background:${e.bg || 'rgba(5,150,105,.1)'}">
        ${renderIcon(e.icon, e.color || '#059669', 'ti-building')}
      </div>

      <div>
        <div class="cat-nm">${e.name_ar}</div>
        <div class="cat-sub">
          ${e.name_en}${e.tag_ar ? ' · ' + e.tag_ar : ''}
        </div>
      </div>

      <div class="cat-sub">
        ${e.category?.name_ar || '—'}
      </div>

      <div>
        <span class="badge-count">${e.gov_services_count || 0}</span>
      </div>

      <div>
        <span class="cat-status-dot ${e.is_active ? 'active' : 'inactive'}"></span>
      </div>

      <div class="cat-actions">

        <x-ui.button type="button" class="cat-act-btn tog ${e.is_active ? '' : 'off'} focus:ring-0!"
                onclick="toggleEnt(${e.id},${e.is_active ? 1 : 0})">
          ${e.is_active ? 'نشط' : 'متوقف'}
        </x-ui.button>


    <x-ui.button type="button" class="cat-act-btn focus:ring-0!"
            onclick="editEntity(${e.id})">
        تعديل
    </x-ui.button>

        <x-ui.button type="button" class="cat-act-btn del focus:ring-0!"
                onclick="deleteEnt(${e.id})">
          <i class="ti ti-trash"></i>
        </x-ui.button>

      </div>

    </div>
  `).join('');
        }


        function editEntity(id) {

            const e = _entData.find(x => x.id == id);
            if (!e) return;

            _editingEntityId = id;

            document.getElementById('ent-category-id').value = e.category_id;
            document.getElementById('ent-name-ar').value = e.name_ar || '';
            document.getElementById('ent-name-en').value = e.name_en || '';

            document.getElementById('ent-icon').value = e.icon || '';
            document.getElementById('ent-color').value = e.color || '';
            document.getElementById('ent-bg').value = e.bg || '';

            document.getElementById('ent-tag-ar').value = e.tag_ar || '';
            document.getElementById('ent-tag-en').value = e.tag_en || '';

            document.getElementById('ent-sort').value = e.sort_order || '';




            // عرض صورة الجهة الحالية
            const preview = document.getElementById('ent-image-preview');
            const imageInput = document.getElementById('ent-image');

            imageInput.value = '';

            if (e.images) {
                preview.src = '/images/uploads/' + e.images;
                preview.style.display = 'block';
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }



            const btn = document.querySelector('#cat-panel-entities .cat-save-btn');
            if (btn) btn.innerHTML = 'حفظ التعديلات';

            document.getElementById('ent-name-ar').focus();

            showToast('يمكنك الآن تعديل البيانات ثم الضغط على حفظ التعديلات', 'info');

        }


        function populateEntSelects() {
            const opts = _entData.map(e => `<option value="${e.id}">${e.name_ar}</option>`).join('');
            ['svc-entity-id', 'svc-filter-ent'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                const prev = el.value;
                const prefix = id === 'svc-filter-ent' ? '<option value="">الكل</option>' :
                    '<option value="">-- اختر جهة --</option>';
                el.innerHTML = prefix + opts;
                el.value = prev;
            });
        }


        let _editingEntityId = null;

        async function doCreateEntity() {

            const get = id => document.getElementById(id)?.value?.trim();

            const formData = new FormData();

            formData.append('category_id', parseInt(get('ent-category-id')));
            formData.append('name_ar', get('ent-name-ar'));
            formData.append('name_en', get('ent-name-en'));
            formData.append('icon', get('ent-icon'));
            formData.append('color', get('ent-color'));
            formData.append('bg', get('ent-bg'));
            formData.append('tag_ar', get('ent-tag-ar') || '');
            formData.append('tag_en', get('ent-tag-en') || '');
            formData.append('sort_order', parseInt(get('ent-sort')) || '');

            const image = document.getElementById('ent-image').files[0];

            if (image) {
                formData.append('images', image);
            }

            if (
                !get('ent-category-id') ||
                !get('ent-name-ar') ||
                !get('ent-name-en') ||
                !get('ent-icon') ||
                !get('ent-color') ||
                !get('ent-bg')
            ) {
                showToast('يرجى ملء جميع الحقول المطلوبة', 'warning');
                return;
            }

            try {

                showLoader();

                if (_editingEntityId) {

                    await Admin.catalog.updateEntity(
                        _editingEntityId,
                        formData
                    );

                    showToast('تم تعديل الجهة بنجاح', 'success');

                } else {

                    await Admin.catalog.createEntity(
                        formData
                    );

                    showToast('تم إضافة الجهة بنجاح', 'success');

                }

                [
                    'ent-name-ar',
                    'ent-name-en',
                    'ent-icon',
                    'ent-color',
                    'ent-bg',
                    'ent-tag-ar',
                    'ent-tag-en',
                    'ent-sort'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });

                document.getElementById('ent-category-id').value = '';
                document.getElementById('ent-image').value = '';

                const preview = document.getElementById('ent-image-preview');

                if (preview) {
                    preview.src = '';
                    preview.style.display = 'none';
                }

                cpReset('ent-cp-row', 'ent-cp-label');

                _editingEntityId = null;

                const btn = document.querySelector('#cat-panel-entities .btn-pri');

                if (btn) {
                    btn.innerHTML = '<i class="ti ti-plus"></i> حفظ الجهة';
                }

                await loadEntities();

            } catch (e) {

                console.log(e.data);
                if (window.AmrtmNotify) AmrtmNotify.error(e?.data?.message || e?.response?.data?.message || 'حدث خطأ');

                showToast(
                    e?.data?.message ||
                    e?.response?.data?.message ||
                    'حدث خطأ',
                    'error'
                );

            } finally {

                hideLoader();

            }

        }

        async function toggleEnt(id, wasActive) {
            try {
                await Admin.catalog.updateEntity(id, {
                    is_active: !wasActive
                });
                await loadEntities(document.getElementById('ent-filter-cat')?.value || '');
                showToast(wasActive ? 'تم إيقاف الجهة' : 'تم تفعيل الجهة', 'info');
            } catch (e) {
                showToast('خطأ', 'error');
            }
        }

        async function deleteEnt(id) {
            if (!confirm('هل أنت متأكد من حذف هذه الجهة؟')) return;
            try {
                showLoader();
                await Admin.catalog.deleteEntity(id);
                await loadEntities(document.getElementById('ent-filter-cat')?.value || '');
                showToast('تم الحذف', 'success');
            } catch (e) {
                showToast(e?.data?.message || 'لا يمكن الحذف', 'error');
            } finally {
                hideLoader();
            }
        }

        /* ── Services ── */
        async function loadSvcList(entityId) {
            try {
                _svcData = await Admin.catalog.getServices(entityId || '');
                renderSvcList();
            } catch (e) {
                showToast('خطأ في تحميل الخدمات', 'error');
            }
        }


        /* ── Specialty linking (bs_specialty_services) ── */
        async function loadServiceSpecialtyOptions() {
            try {
                const res = await fetch('/office/specialties', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('bad response');
                _serviceSpecialtyData = await res.json();
                renderServiceSpecialtyChips();
            } catch (e) {
                const el = document.getElementById('svc-specialties-chips');
                if (el) el.innerHTML = '<div class="svc-fields-empty">تعذر تحميل التخصصات.</div>';
            }
        }

        function renderServiceSpecialtyChips() {
            const el = document.getElementById('svc-specialties-chips');
            if (!el) return;

            const filterType = document.getElementById('svc-spec-filter-type')?.value || '';
            const data = Array.isArray(_serviceSpecialtyData)
                ? _serviceSpecialtyData.filter(s => !filterType || s.office_type === filterType)
                : [];

            if (!data.length) {
                el.innerHTML = '<div class="svc-fields-empty">لا توجد تخصصات متاحة.</div>';
                return;
            }

            const groups = {};
            data.forEach(s => {
                (groups[s.office_type] = groups[s.office_type] || []).push(s);
            });

            const officeTypeLabels = {
                law: 'محاماة',
                services: 'تعقيب وخدمات',
                customs: 'جمارك',
                accounting: 'محاسبين',
                engineering: 'هندسة',
                freelance: 'أصحاب مهن'
            };

            el.innerHTML = Object.entries(groups).map(([type, items]) => `
                <div class="svc-spec-section">
                    <div class="svc-spec-section-title">${officeTypeLabels[type] || type}</div>
                    <div class="svc-spec-chips">
                        ${items.map(s => `
                            <div role="button" tabindex="0" class="svc-spec-chip ${serviceSpecialtyIds.includes(s.id) ? 'on' : ''}"
                                onclick="toggleServiceSpecialty(${s.id})"
                                onkeydown="if(event.key==='Enter'||event.key===' ') { event.preventDefault(); toggleServiceSpecialty(${s.id}); }">
                                <i class="ti ${serviceSpecialtyIds.includes(s.id) ? 'ti-check' : 'ti-plus'}"></i>
                                ${escapeHtml(s.name_ar || s.name_en || '')}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        }

        function toggleServiceSpecialty(id) {
            const idx = serviceSpecialtyIds.indexOf(id);
            if (idx >= 0) {
                serviceSpecialtyIds.splice(idx, 1);
            } else {
                serviceSpecialtyIds.push(id);
            }
            renderServiceSpecialtyChips();
        }

        function resetServiceSpecialties() {
            serviceSpecialtyIds = [];
            const filter = document.getElementById('svc-spec-filter-type');
            if (filter) filter.value = '';
            renderServiceSpecialtyChips();
        }


        function renderSvcList() {
            const el = document.getElementById('svc-list-body');

            if (!_svcData.length) {
                el.innerHTML = '<div class="cat-empty">لا توجد خدمات بعد.</div>';
                return;
            }

            el.innerHTML = _svcData.map(s => `
    <div class="cat-row"
         style="grid-template-columns:36px 1fr 1fr 80px 70px 60px 170px;">

      <div class="ico-prev"
           style="background:${s.entity?.bg || 'rgba(5,150,105,.1)'}">
        ${renderIcon(
          s.icon,
          s.entity?.color || '#059669',
          'ti-list'
        )}
      </div>

      <div>
        <div class="cat-nm">${s.name_ar}</div>
        <div class="cat-sub">${s.name_en}</div>
        ${(s.custom_fields || []).length ? `<div class="cat-sub" style="color:var(--pri);margin-top:3px;"><i class="ti ti-forms"></i> ${(s.custom_fields || []).length} حقل مخصص</div>` : ''}
        ${(s.specialties || []).length ? `<div class="cat-sub" style="color:var(--pri);margin-top:3px;"><i class="ti ti-award"></i> ${(s.specialties || []).map(x => x.name_ar).join('، ')}</div>` : ''}
      </div>

      <div class="cat-sub">
        ${s.entity?.name_ar || '—'}
      </div>

      <div class="cat-nm" style="color:var(--pri)">
        ${parseFloat(s.price || 0).toFixed(0)} ر.س
      </div>

      <div class="cat-sub">
        ${s.duration || '—'}
      </div>

      <div>
        <span class="cat-status-dot ${s.is_active ? 'active' : 'inactive'}"></span>
      </div>

    <div class="cat-actions">

    <x-ui.button type="button" class="cat-act-btn focus:ring-0!"
            onclick="editService(${s.id})">
        <i class="ti ti-edit"></i>
        تعديل
    </x-ui.button>

    <x-ui.button type="button" class="cat-act-btn tog ${s.is_active ? '' : 'off'} focus:ring-0!"
            onclick="toggleSvc(${s.id},${s.is_active ? 1 : 0})">
        ${s.is_active ? 'نشط' : 'متوقف'}
    </x-ui.button>

    <x-ui.button type="button" class="cat-act-btn del focus:ring-0!"
            onclick="deleteSvc(${s.id})">
        <i class="ti ti-trash"></i>
    </x-ui.button>

</div>

    </div>
  `).join('');
        }


        let editingServiceId = null;
        let serviceCustomFields = [];
        let serviceSpecialtyIds = [];
        let _serviceSpecialtyData = [];

        const serviceFieldTypes = {
            text: 'نص قصير',
            textarea: 'نص طويل',
            number: 'رقم',
            email: 'بريد إلكتروني',
            tel: 'رقم جوال',
            date: 'تاريخ',
            select: 'قائمة منسدلة',
            radio: 'اختيار واحد',
            checkbox: 'مربع اختيار',
            file: 'رفع ملف'
        };

        function addServiceCustomField(field = {}) {
            syncServiceCustomFieldsFromDom();
            serviceCustomFields.push({
                key: field.key || `field_${Date.now().toString().slice(-6)}`,
                type: field.type || 'text',
                label_ar: field.label_ar || '',
                label_en: field.label_en || '',
                placeholder_ar: field.placeholder_ar || '',
                placeholder_en: field.placeholder_en || '',
                help_ar: field.help_ar || '',
                help_en: field.help_en || '',
                required: !!field.required,
                min: field.min ?? '',
                max: field.max ?? '',
                options: Array.isArray(field.options) ? field.options : []
            });
            renderServiceCustomFields();
        }

        function removeServiceCustomField(index) {
            syncServiceCustomFieldsFromDom();
            serviceCustomFields.splice(index, 1);
            renderServiceCustomFields();
        }

        function syncServiceCustomFieldsFromDom() {
            const cards = [...document.querySelectorAll('#svc-custom-fields .svc-field-card')];
            if (!cards.length) return;
            serviceCustomFields = cards.map((card, index) => {
                const val = name => card.querySelector(`[data-cf="${name}"]`)?.value?.trim() || '';
                const current = serviceCustomFields[index] || {};
                return {
                    ...current,
                    key: val('key'),
                    label_ar: val('label_ar'),
                    label_en: val('label_en'),
                    type: val('type') || current.type || 'text',
                    placeholder_ar: val('placeholder_ar'),
                    placeholder_en: val('placeholder_en'),
                    help_ar: val('help_ar'),
                    help_en: val('help_en'),
                    required: !!card.querySelector('[data-cf="required"]')?.checked,
                    min: val('min'),
                    max: val('max'),
                    options: val('options') ? val('options').split('\n').map((line, optionIndex) => {
                        const [ar = '', en = '', rawValue = ''] = line.split('|').map(v => v.trim());
                        return ar ? { label_ar: ar, label_en: en || ar, value: rawValue || `option_${optionIndex + 1}` } : null;
                    }).filter(Boolean) : (current.options || [])
                };
            });
        }

        function setServiceFieldType(index, type) {
            syncServiceCustomFieldsFromDom();
            serviceCustomFields[index].type = type;
            renderServiceCustomFields();
        }

function serviceOptionsText(options) {
            return (options || []).map(o => `${o.label_ar || ''}|${o.label_en || ''}|${o.value || ''}`).join('\n');
        }

        function renderServiceCustomFields() {
            const el = document.getElementById('svc-custom-fields');
            if (!el) return;
            if (!serviceCustomFields.length) {
                el.innerHTML = '<div class="svc-fields-empty">لا توجد حقول مخصصة لهذه الخدمة.</div>';
                return;
            }
            el.innerHTML = serviceCustomFields.map((f, i) => {
                const hasOptions = ['select', 'radio'].includes(f.type);
                const hasLimits = ['text', 'textarea', 'tel', 'number'].includes(f.type);
                return `<div class="svc-field-card" data-field-index="${i}">
                    <div class="svc-field-card-head">
                        <div class="svc-field-card-title"><i class="ti ti-grip-vertical"></i> الحقل ${i + 1}</div>
                        ${xuiButton('<i class="ti ti-trash"></i>', `class="svc-field-remove" onclick="removeServiceCustomField(${i})" title="حذف الحقل"`)}
                    </div>
                    <div class="cat-form-grid" style="margin-bottom:0">
                        <div>${xuiLabel('المسمى (عربي)', true)}${xuiInput(`data-cf="label_ar" value="${escapeHtml(f.label_ar || '')}" placeholder="مثال: رقم الرخصة"`)}</div>
                        <div>${xuiLabel('المسمى (إنجليزي)')}${xuiInput(`data-cf="label_en" value="${escapeHtml(f.label_en || '')}" placeholder="License number"`)}</div>
                        <div>${xuiLabel('المفتاح البرمجي', true)}${xuiInput(`data-cf="key" value="${escapeHtml(f.key || '')}" dir="ltr" placeholder="license_number"`)}</div>
                        <div>${xuiLabel('نوع الحقل', true)}${xuiSelect(Object.entries(serviceFieldTypes).map(([value, label]) => `<option value="${value}" ${f.type === value ? 'selected' : ''}>${label}</option>`).join(''), `data-cf="type" onchange="setServiceFieldType(${i},this.value)"`)}</div>
                        <div>${xuiLabel('النص التوضيحي (عربي)')}${xuiInput(`data-cf="placeholder_ar" value="${escapeHtml(f.placeholder_ar || '')}" placeholder="يظهر داخل الحقل"`)}</div>
                        <div>${xuiLabel('النص التوضيحي (إنجليزي)')}${xuiInput(`data-cf="placeholder_en" value="${escapeHtml(f.placeholder_en || '')}" placeholder="Placeholder"`)}</div>
                        <div>${xuiLabel('مساعدة إضافية (عربي)')}${xuiInput(`data-cf="help_ar" value="${escapeHtml(f.help_ar || '')}" placeholder="تعليمات قصيرة للعميل"`)}</div>
                        <div>${xuiLabel('مساعدة إضافية (إنجليزي)')}${xuiInput(`data-cf="help_en" value="${escapeHtml(f.help_en || '')}" placeholder="Short instructions"`)}</div>
                        ${hasLimits ? `<div>${xuiLabel(f.type === 'number' ? 'أقل قيمة' : 'أقل عدد أحرف')}${xuiInput(`data-cf="min" type="number" value="${f.min ?? ''}" min="0"`)}</div>
                        <div>${xuiLabel(f.type === 'number' ? 'أعلى قيمة' : 'أقصى عدد أحرف')}${xuiInput(`data-cf="max" type="number" value="${f.max ?? ''}" min="0"`)}</div>` : ''}
                        <div>${xuiLabel('الإلزام')}<div class="svc-required-toggle">${xuiCheckbox(`${f.required ? 'checked' : ''} data-cf="required"`)}<span>حقل مطلوب</span></div></div>
                        ${f.type === 'file' ? `<div style="display:flex;align-items:center;color:var(--t3);font-size:11.5px;"><i class="ti ti-info-circle" style="margin-left:5px"></i> PDF أو JPG أو PNG، بحد أقصى 10MB</div>` : ''}
                        ${hasOptions ? `<div class="svc-field-options">${xuiLabel('الخيارات <span style="font-weight:400">(كل خيار في سطر: عربي | English | value)</span>', true)}${xuiTextarea(escapeHtml(serviceOptionsText(f.options)), `data-cf="options" dir="auto" placeholder="نعم|Yes|yes\nلا|No|no"`)}</div>` : ''}
                    </div>
                </div>`;
            }).join('');
        }

        function collectServiceCustomFields() {
            syncServiceCustomFieldsFromDom();
            const cards = [...document.querySelectorAll('#svc-custom-fields .svc-field-card')];
            const fields = cards.map((card, index) => {
                const val = name => card.querySelector(`[data-cf="${name}"]`)?.value?.trim() || '';
                const type = val('type') || 'text';
                const options = ['select', 'radio'].includes(type)
                    ? val('options').split('\n').map((line, optionIndex) => {
                        const [ar = '', en = '', rawValue = ''] = line.split('|').map(v => v.trim());
                        return ar ? { label_ar: ar, label_en: en || ar, value: rawValue || `option_${optionIndex + 1}` } : null;
                    }).filter(Boolean)
                    : [];
                return {
                    key: val('key'),
                    type,
                    label_ar: val('label_ar'),
                    label_en: val('label_en'),
                    placeholder_ar: val('placeholder_ar'),
                    placeholder_en: val('placeholder_en'),
                    help_ar: val('help_ar'),
                    help_en: val('help_en'),
                    required: !!card.querySelector('[data-cf="required"]')?.checked,
                    min: val('min') === '' ? null : Number(val('min')),
                    max: val('max') === '' ? null : Number(val('max')),
                    options,
                    sort_order: index
                };
            });
            if (fields.some(f => !f.label_ar || !/^[a-zA-Z][a-zA-Z0-9_]*$/.test(f.key))) {
                showToast('أدخل المسمى العربي ومفتاحاً برمجياً صحيحاً لكل حقل (أحرف إنجليزية وأرقام وشرطة سفلية).', 'warning');
                return null;
            }
            if (fields.some(f => ['select', 'radio'].includes(f.type) && !f.options.length)) {
                showToast('أضف خياراً واحداً على الأقل لحقول القائمة والاختيار.', 'warning');
                return null;
            }
            if (new Set(fields.map(f => f.key)).size !== fields.length) {
                showToast('المفتاح البرمجي يجب ألا يتكرر داخل الخدمة.', 'warning');
                return null;
            }
            return fields;
        }

        async function doCreateService() {

            const get = id => document.getElementById(id)?.value?.trim();

            const customFields = collectServiceCustomFields();
            if (customFields === null) return;

            const data = {
                entity_id: parseInt(get('svc-entity-id')),
                name_ar: get('svc-name-ar'),
                name_en: get('svc-name-en'),
                icon: get('svc-icon') || 'ti-file-text',
                price: parseFloat(get('svc-price')),
                duration_min: parseInt(get('svc-duration-min')),
                duration_max: parseInt(get('svc-duration-max')),
                duration_unit: get('svc-duration-unit') || 'day',
                description_ar: get('svc-desc-ar') || null,
                description_en: get('svc-desc-en') || null,
                sort_order: parseInt(get('svc-sort')) || undefined,
                custom_fields: customFields,
                specialty_ids: serviceSpecialtyIds,
            };

            const requiredChecks = [
                ['svc-entity-id', data.entity_id, 'الجهة'],
                ['svc-name-ar', data.name_ar, 'الاسم العربي'],
                ['svc-name-en', data.name_en, 'الاسم الإنجليزي'],
                ['svc-price', !Number.isNaN(data.price), 'السعر'],
                ['svc-duration-min', Number.isInteger(data.duration_min) && data.duration_min >= 1, 'مدة الإنجاز من'],
                ['svc-duration-max', Number.isInteger(data.duration_max) && data.duration_max >= data.duration_min, 'مدة الإنجاز إلى'],
            ];

            document.querySelectorAll('#cat-panel-services .cat-form-grid input, #cat-panel-services .cat-form-grid select').forEach(el => el.style.borderColor = '');

            const missing = requiredChecks.filter(([, valid]) => !valid);

            if (missing.length) {
                missing.forEach(([id]) => {
                    const el = document.getElementById(id);
                    if (el) el.style.borderColor = '#C62828';
                });
                showToast('تحقق من الحقول التالية: ' + missing.map(([, , label]) => label).join('، '), 'warning');
                document.getElementById(missing[0][0])?.focus();
                return;
            }

            try {

                showLoader();

                if (editingServiceId) {

                    await Admin.catalog.updateService(editingServiceId, data);

                    showToast('تم تعديل الخدمة بنجاح', 'success');

                } else {

                    await Admin.catalog.createService(data);

                    showToast('تم إضافة الخدمة بنجاح', 'success');

                }

                [
                    'svc-name-ar',
                    'svc-name-en',
                    'svc-icon',
                    'svc-price',
                    'svc-duration-min',
                    'svc-duration-max',
                    'svc-desc-ar',
                    'svc-desc-en',
                    'svc-sort'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                const unitEl = document.getElementById('svc-duration-unit');
                if (unitEl) unitEl.value = 'day';

                editingServiceId = null;
                serviceCustomFields = [];
                renderServiceCustomFields();
                resetServiceSpecialties();

                const btn = document.getElementById('svc-save-btn');
                if (btn) {
                    btn.innerHTML = '<i class="ti ti-plus"></i> إضافة الخدمة';
                }

                await loadSvcList(document.getElementById('svc-filter-ent')?.value || '');

            } catch (e) {

                showToast(e?.data?.message || 'حدث خطأ', 'error');

            } finally {

                hideLoader();

            }

        }


        function editService(id) {

            const s = _svcData.find(x => x.id == id);

            if (!s) return;

            editingServiceId = id;

            document.getElementById('svc-entity-id').value = s.entity_id;
            document.getElementById('svc-name-ar').value = s.name_ar || '';
            document.getElementById('svc-name-en').value = s.name_en || '';
            document.getElementById('svc-icon').value = s.icon || '';
            document.getElementById('svc-price').value = s.price || '';
            document.getElementById('svc-duration-min').value = s.duration_min || '';
            document.getElementById('svc-duration-max').value = s.duration_max || '';
            document.getElementById('svc-duration-unit').value = s.duration_unit || 'day';
            document.getElementById('svc-desc-ar').value = s.description_ar || '';
            document.getElementById('svc-desc-en').value = s.description_en || '';
            document.getElementById('svc-sort').value = s.sort_order || '';
            serviceCustomFields = JSON.parse(JSON.stringify(s.custom_fields || []));
            renderServiceCustomFields();
            serviceSpecialtyIds = (s.specialties || []).map(x => x.id);
            renderServiceSpecialtyChips();

            const btn = document.getElementById('svc-save-btn');

            if (btn) {
                btn.innerHTML = '<i class="ti ti-device-floppy"></i> حفظ التعديل';
            }

            document.getElementById('svc-name-ar').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

        }



        async function toggleSvc(id, wasActive) {
            try {
                await Admin.catalog.updateService(id, {
                    is_active: !wasActive
                });
                await loadSvcList(document.getElementById('svc-filter-ent')?.value || '');
                showToast(wasActive ? 'تم إيقاف الخدمة' : 'تم تفعيل الخدمة', 'info');
            } catch (e) {
                showToast('خطأ', 'error');
            }
        }

        async function deleteSvc(id) {
            if (!confirm('هل أنت متأكد من حذف هذه الخدمة؟')) return;
            try {
                showLoader();
                await Admin.catalog.deleteService(id);
                await loadSvcList(document.getElementById('svc-filter-ent')?.value || '');
                showToast('تم الحذف', 'success');
            } catch (e) {
                showToast(e?.data?.message || 'لا يمكن الحذف — قد تكون للخدمة طلبات مرتبطة بها', 'error');
            } finally {
                hideLoader();
            }
        }

        /* ── Icon renderer: handles both Tabler class names and custom 'img:file' values ── */
        function renderIcon(val, color, fallback) {
            if (val && val.startsWith('img:')) {
                const file = val.slice(4);
                return '<img src="/icons/' + encodeURIComponent(file) +
                    '" style="max-width:80%;max-height:80%;object-fit:contain;" onerror="this.style.opacity=\'.2\'">';
            }
            return '<i class="ti ' + (val || fallback || 'ti-folder') + '" style="color:' + (color || '#059669') + '"></i>';
        }

        /* ══════════════════════════════════════════════════════════════
           OFFICES PAGE
        ══════════════════════════════════════════════════════════════ */

        let _offStatus = 'all',
            _offType = 'all',
            _offPage = 1,
            _offData = [],
            _offMeta = {};

        function filterOffices(s, btn) {
            _offStatus = s;
            _offPage = 1;
            document.querySelectorAll('#page-offices .req-filters .rf-btn').forEach(b => b.classList.remove('on'));
            if (btn) btn.classList.add('on');
            loadOffices();
        }

        function filterOfficeType(t, btn) {
            _offType = t;
            _offPage = 1;
            document.querySelectorAll('.off-type-tabs .rf-btn').forEach(b => b.classList.remove('on'));
            if (btn) btn.classList.add('on');
            loadOffices();
        }

        async function loadOfficeStats() {
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/offices/stats`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const d = await res.json();
                S('off-sc-total', d.total || 0);
                S('off-sc-verified', d.verified || 0);
                S('off-sc-pending', d.pending || 0);
                S('off-sc-inactive', d.inactive || 0);
            } catch (_) {}
        }

        async function loadOffices() {
            const el = document.getElementById('off-list');
            if (!el) return;
            el.innerHTML = '<div style="text-align:center;padding:3rem;color:var(--t3);">جارٍ التحميل...</div>';
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const params = new URLSearchParams({
                    page: _offPage
                });
                if (_offStatus !== 'all') params.set('status', _offStatus);
                if (_offType !== 'all') params.set('type', _offType);
                const res = await fetch(`${base}/admin/offices?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    el.innerHTML =
                        '<div style="text-align:center;padding:3rem;color:var(--red);">فشل في تحميل البيانات</div>';
                    return;
                }
                const d = await res.json();
                _offData = d.data || [];
                _offMeta = {
                    current_page: d.current_page || 1,
                    last_page: d.last_page || 1,
                    total: d.total || 0
                };
                renderOffList();
            } catch (e) {
                el.innerHTML = '<div style="text-align:center;padding:3rem;color:var(--red);">حدث خطأ</div>';
            }
        }

        function renderOffList() {
            const el = document.getElementById('off-list');
            if (!el) return;
            if (!_offData.length) {
                el.innerHTML = '<div style="text-align:center;padding:3rem;color:var(--t3);">لا توجد مكاتب</div>';
                return;
            }

            const typeLabels = {
                law: 'محاماة',
                services: 'تعقيب وخدمات',
                customs: 'جمارك',
                accounting: 'محاسبة',
                engineering: 'هندسه',
                freelance: 'اصحاب المهن'
            };
            const typeIcons = {
                law: 'ti-scale',
                services: 'ti-briefcase',
                customs: 'ti-container',
                accounting: 'ti-scale',
                engineering: 'ti-briefcase',
                freelance: 'ti-container'
            };
            const typeColors = {
                law: 'rgba(5,150,105,.1)',
                services: 'rgba(2,119,189,.1)',
                customs: 'rgba(0,105,92,.1)',
                accounting: 'rgba(5,150,105,.1)',
                engineering: 'rgba(5,150,105,.1)',
                freelance: 'rgba(5,150,105,.1)'
            };
            const typeClr = {
                law: '#059669',
                services: '#0277BD',
                customs: '#00695C',
                accounting: '#059669',
                engineering: '#059669',
                freelance: '#059669'
            };

            el.innerHTML = _offData.map((o, i) => {
                const isVerified = o.is_verified;
                const isActive = o.is_active;
                const verBadge = !isActive ?
                    '<span class="off-verify-badge inactive">موقوف</span>' :
                    isVerified ?
                    '<span class="off-verify-badge verified"><i class="ti ti-circle-check"></i> معتمد</span>' :
                    '<span class="off-verify-badge pending"><i class="ti ti-clock"></i> ينتظر الاعتماد</span>';

                return `<div class="off-office-card" id="ofc-${i}">
      <div class="off-card-ico" style="background:${typeColors[o.type]||'rgba(5,150,105,.1)'}">
        <i class="ti ${typeIcons[o.type]||'ti-building'}" style="color:${typeClr[o.type]||'#059669'}"></i>
      </div>
      <div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:4px;">
          <span style="font-size:14px;font-weight:800;color:var(--t1);">${o.name_ar||o.name_en||'—'}</span>
          <span class="off-type-badge ${o.type||''}">${typeLabels[o.type]||o.type}</span>
          ${verBadge}
        </div>
        <div class="off-card-meta">
          ${o.email ? `<span style="font-size:11.5px;color:var(--t3);"><i class="ti ti-mail" style="font-size:11px;"></i> ${o.email}</span>` : ''}
          ${o.phone ? `<span style="font-size:11.5px;color:var(--t3);margin-right:.5rem;"><i class="ti ti-phone" style="font-size:11px;"></i> ${o.phone}</span>` : ''}
          ${o.city  ? `<span style="font-size:11.5px;color:var(--t3);margin-right:.5rem;"><i class="ti ti-map-pin" style="font-size:11px;"></i> ${o.city}</span>` : ''}
          ${o.cr_number ? `<span style="font-size:11.5px;color:var(--t3);margin-right:.5rem;">س.ت: ${o.cr_number}</span>` : ''}
        </div>
      </div>
      <div class="off-card-actions">

  <x-ui.button type="button"
    class="cat-act-btn focus:ring-0!"
    style="background:rgba(2,119,189,.08);color:#0277BD;border-color:rgba(2,119,189,.2);"
    onclick="showOfficeDetails(${o.id})"
  >
    <i class="ti ti-eye"></i>
    عرض البيانات
  </x-ui.button>

  <x-ui.button type="button"
    class="cat-act-btn focus:ring-0!"
    style="background:rgba(139,92,246,.08);color:#8B5CF6;border-color:rgba(139,92,246,.2);"
    onclick="window.location.href = window.AMRTM_ROUTES.officeEdit.replace('__ID__', ${o.id})"
  >
    <i class="ti ti-pencil"></i>
    تعديل
  </x-ui.button>

  ${!isVerified ? `<x-ui.button type="button"
                                                                                                    class="cat-act-btn focus:ring-0!"
                                                                                                    style="background:rgba(4,120,87,.08);color:var(--green);border-color:rgba(4,120,87,.2);"
                                                                                                    onclick="doVerifyOffice(${o.id})"
                                                                                                  >
                                                                                                    <i class="ti ti-circle-check"></i>
                                                                                                    اعتماد
                                                                                                  </x-ui.button>` : ''}

  <x-ui.button type="button"
    class="cat-act-btn tog${!isActive ? ' off' : ''} focus:ring-0!"
    onclick="doToggleOffice(${o.id})"
  >
    ${isActive ? 'إيقاف' : 'تفعيل'}
  </x-ui.button>

  <x-ui.button type="button"
    class="cat-act-btn del focus:ring-0!"
    onclick="doDeleteOffice(${o.id})"
  >
    <i class="ti ti-trash"></i>
  </x-ui.button>

</div>

    </div>`;
            }).join('');
        }


        async function showOfficeDetails(id) {
            try {
                showLoader();

                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');

                const res = await fetch(
                    `${base}/admin/offices/${id}/details`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                        },
                        credentials: 'same-origin'
                    }
                );

                const data = await res.json();

                if (!res.ok) {
                    showToast(
                        data.message || 'تعذر تحميل بيانات المكتب',
                        'error'
                    );
                    return;
                }

                renderOfficeDetailsModal(data);

            } catch (error) {
                console.error('showOfficeDetails:', error);
                showToast(
                    'حدث خطأ أثناء تحميل بيانات المكتب',
                    'error'
                );
            } finally {
                hideLoader();
            }
        }

        function renderOfficeDetailsModal(d) {

            const o = d.office || {};
            const p = d.profile || {};
            const specialties = d.specialties || [];
            const documents = d.documents || [];
            const services = d.services || [];

            document.getElementById('office-details-modal')?.remove();

            const status = !o.is_active ?
                'موقوف' :
                o.is_verified ?
                'معتمد' :
                'ينتظر الاعتماد';

            const specialtiesHtml = specialties.length ?
                specialties.map(s => `
            <span class="office-detail-tag">
                ${s.name_ar || s.name_en || '—'}
            </span>
        `).join('') :
                '<span class="office-detail-empty">لا توجد تخصصات</span>';

            const documentsHtml = documents.length ?
                documents.map(doc => `
            <div class="office-detail-document">

                <div>
                    <strong>
                        ${doc.file_name || doc.document_type || 'مستند'}
                    </strong>

                    <small>
                        ${doc.document_type || ''}
                    </small>
                </div>

                <div style="display:flex;align-items:center;gap:.5rem;">

                    ${
                        doc.is_verified
                            ? '<span class="off-verify-badge verified">موثق</span>'
                            : '<span class="off-verify-badge pending">غير موثق</span>'
                    }

                    ${
                        doc.file
                            ? `
                                                                                                                                <a
                                                                                                                                    href="${doc.file}"
                                                                                                                                    target="_blank"
                                                                                                                                    class="cat-act-btn"
                                                                                                                                >
                                                                                                                                    <i class="ti ti-eye"></i>
                                                                                                                                    عرض
                                                                                                                                </a>
                                                                                                                            `
                            : ''
                    }

                </div>

            </div>
        `).join('') :
                '<div class="office-detail-empty">لا توجد مستندات</div>';

            const servicesHtml = services.length ?
                services.map(s => `
            <div class="office-detail-service">

                <div>
                    <strong>
                        ${s.name_ar || s.name_en || '—'}
                    </strong>

                    ${
                        s.description_ar
                            ? `<small>${s.description_ar}</small>`
                            : ''
                    }
                </div>

                <div style="text-align:left;">
                    <strong>
                        ${s.price ?? 0} ر.س
                    </strong>

                    ${
                        s.duration
                            ? `<small>${s.duration}</small>`
                            : ''
                    }
                </div>

            </div>
        `).join('') :
                '<div class="office-detail-empty">لا توجد خدمات</div>';

            const modal = document.createElement('div');

            modal.id = 'office-details-modal';
            modal.className = 'office-details-modal hidden fixed inset-0 z-[99999] items-center justify-center overflow-y-auto overflow-x-hidden p-6 bg-[rgba(15,23,42,.45)] backdrop-blur-sm';

            modal.innerHTML = `
        <div class="office-details-dialog">

            <div class="office-details-header">

                <div>
                    <div class="office-details-title">
                        بيانات المكتب
                    </div>

                    <div class="office-details-sub">
                        ${o.name_ar || o.name_en || '—'}
                    </div>
                </div>

                <x-ui.button type="button"
                    class="office-details-close focus:ring-0!"
                    onclick="closeOfficeDetailsModal()"
                >
                    <i class="ti ti-x"></i>
                </x-ui.button>

            </div>


            <div class="office-details-body">

                <!-- البيانات الأساسية -->
                <div class="office-detail-section">

                    <div class="office-detail-section-title">
                        <i class="ti ti-building"></i>
                        البيانات الأساسية
                    </div>

                    <div class="office-detail-grid">

                        <div>
                            <x-ui.label class="font-normal!">اسم المكتب</x-ui.label>
                            <span>${o.name_ar || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الاسم بالإنجليزية</x-ui.label>
                            <span>${o.name_en || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">نوع المكتب</x-ui.label>
                            <span>${o.type_label_ar || o.type || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الحالة</x-ui.label>
                            <span>${status}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الهاتف</x-ui.label>
                            <span>${o.phone || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">البريد الإلكتروني</x-ui.label>
                            <span>${o.email || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">المدينة</x-ui.label>
                            <span>${o.city || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">السجل التجاري</x-ui.label>
                            <span>${o.cr_number || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">عمولة المنصة</x-ui.label>
                            <span>${o.commission_rate ?? 0}%</span>
                        </div>

                    </div>
                </div>


                <!-- بيانات الملف -->
                <div class="office-detail-section">

                    <div class="office-detail-section-title">
                        <i class="ti ti-id"></i>
                        بيانات الملف والترخيص
                    </div>

                    <div class="office-detail-grid">

                        <div>
                            <x-ui.label class="font-normal!">رقم الترخيص</x-ui.label>
                            <span>${p.license_number || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">رقم السجل التجاري</x-ui.label>
                            <span>${p.cr_number || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الجوال</x-ui.label>
                            <span>${p.mobile || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الدولة</x-ui.label>
                            <span>${p.country || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">المنطقة</x-ui.label>
                            <span>${p.governorate || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">المدينة</x-ui.label>
                            <span>${p.city || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الحي</x-ui.label>
                            <span>${p.district || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">الشارع</x-ui.label>
                            <span>${p.street || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">رقم المبنى</x-ui.label>
                            <span>${p.building_number || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">رقم المكتب</x-ui.label>
                            <span>${p.office_number || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">كود المكتب</x-ui.label>
                            <span>${p.office_code || '—'}</span>
                        </div>

                        <div>
                            <x-ui.label class="font-normal!">حالة الملف</x-ui.label>
                            <span>
                                ${p.profile_completed ? 'مكتمل' : 'غير مكتمل'}
                            </span>
                        </div>

                    </div>
                </div>


                <!-- التخصصات -->
                <div class="office-detail-section">

                    <div class="office-detail-section-title">
                        <i class="ti ti-category"></i>
                        التخصصات
                    </div>

                    <div class="office-detail-tags">
                        ${specialtiesHtml}
                    </div>

                </div>


                <!-- المستندات -->
                <div class="office-detail-section">

                    <div class="office-detail-section-title">
                        <i class="ti ti-file"></i>
                        المستندات
                    </div>

                    ${documentsHtml}

                </div>


                <!-- الخدمات -->
                <div class="office-detail-section">

                    <div class="office-detail-section-title">
                        <i class="ti ti-list"></i>
                        خدمات المكتب
                    </div>

                    ${servicesHtml}

                </div>

            </div>


            <div class="office-details-footer">

                <x-ui.button type="button"
                    class="btn-pri"
                    onclick="closeOfficeDetailsModal()"
                >
                    إغلاق
                </x-ui.button>

            </div>

        </div>
    `;

            document.body.appendChild(modal);

            if (window.Flowbite && window.Flowbite.Modal) {
                modal.__fbModal = new window.Flowbite.Modal(modal, { backdrop: false, closable: true });
                modal.__fbModal.show();
            }

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeOfficeDetailsModal();
                }
            });
        }


        function closeOfficeDetailsModal() {

            const modal = document.getElementById('office-details-modal');

            if (!modal) return;

            if (modal.__fbModal) {
                modal.__fbModal.hide();
            }

            modal.remove();
        }


        async function doVerifyOffice(id) {
            const office = (_offData || []).find(o => o.id === id);
            const accountTypes = Array.isArray(office?.account_types) ? office.account_types : null;
            const isCommissionBased = accountTypes
                ? accountTypes.includes('support_office')
                : (office?.subscription_type ?? 'commission') === 'commission';

            let rate = null;
            if (isCommissionBased) {
                const rateStr = prompt('حدّد نسبة عمولة المنصة من كل طلب (%) :', '10');
                if (rateStr === null) return;
                rate = parseFloat(rateStr);
                if (isNaN(rate) || rate < 0 || rate > 100) {
                    showToast('نسبة العمولة يجب أن تكون بين 0 و 100', 'error');
                    return;
                }
            }
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/offices/${id}/verify`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(rate === null ? {} : { commission_rate: rate }),
                });
                if (!res.ok) throw await res.json();
                showToast(isCommissionBased ? `تم اعتماد المكتب — عمولة ${rate}%` : 'تم اعتماد المكتب', 'success');
                await loadOffices();
                await loadOfficeStats();
            } catch (e) {
                showToast(e?.message || 'حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        async function doToggleOffice(id) {
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/offices/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                const d = await res.json();
                showToast(d.message || 'تم التحديث', 'success');
                await loadOffices();
                await loadOfficeStats();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        async function doDeleteOffice(id) {
            if (!confirm('هل أنت متأكد من حذف هذا المكتب؟ لن يمكن التراجع عن هذا الإجراء.')) return;
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/offices/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                const d = await res.json();
                showToast(d.message || 'تم الحذف', 'success');
                await loadOffices();
                await loadOfficeStats();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        /* ══════════════════════════════════════════════════════════════
           PERMISSIONS PAGE (supervisor only)
        ══════════════════════════════════════════════════════════════ */
        const PERMS_META = {
            approve_offices: {
                ar: 'اعتماد المكاتب',
                en: 'Approve Offices',
                desc_ar: 'السماح بقبول ورفض المكاتب المسجلة'
            },
            view_revenue: {
                ar: 'عرض الإيرادات',
                en: 'View Revenue',
                desc_ar: 'الوصول إلى تقارير وبيانات الإيرادات'
            },
            view_reports: {
                ar: 'عرض التقارير',
                en: 'View Reports',
                desc_ar: 'الوصول إلى التقارير الشهرية والإحصائيات'
            },
            manage_catalog: {
                ar: 'اضافة الجهات',
                en: 'Manage Catalog',
                desc_ar: 'إضافة وتعديل التصنيفات والخدمات'
            },
        };

        let _adminsData = [];

        async function loadAdmins() {
            const el = document.getElementById('admins-list');
            if (!el) return;
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/supervisor/admins`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    el.innerHTML = '<div style="padding:2rem;color:var(--red);">فشل في تحميل البيانات</div>';
                    return;
                }
                _adminsData = await res.json();
                renderAdminsList();
            } catch (e) {
                el.innerHTML = '<div style="padding:2rem;color:var(--red);">حدث خطأ</div>';
            }
        }

        function renderAdminsList() {
            const el = document.getElementById('admins-list');
            if (!el) return;
            if (!_adminsData.length) {
                el.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--t3);">لا يوجد مدراء بعد</div>';
                return;
            }

            el.innerHTML = _adminsData.map((a, i) => {
                const isSupervisor = a.role === 'supervisor';
                const isActive = a.is_active !== false;
                const perms = a.permissions || [];

                const permsHtml = isSupervisor ?
                    '<div style="padding:.7rem 0;color:var(--green);font-size:13px;font-weight:700;"><i class="ti ti-shield"></i> صلاحيات كاملة (سوبرفايزر)</div>' :
                    Object.entries(PERMS_META).map(([key, meta]) => `
          <div class="perm-row">
            <div class="perm-lbl">${meta.ar}<small>${meta.desc_ar}</small></div>
            <x-ui.label class="perm-toggle mb-0!">
              ${AMRTM_UI.checkbox({ checked: perms.includes(key), onchange: "togglePerm(" + a.id + ", '" + key + "', this.checked)" })}
              <span class="perm-slider"></span>
            </x-ui.label>
          </div>`).join('');

                return `<div class="admin-card" id="admin-card-${a.id}">
      <div class="admin-card-hd">
        <div class="admin-card-av">${(a.name||'A')[0]}</div>
        <div style="flex:1;">
          <div class="admin-card-nm">${a.name}</div>
          <div class="admin-card-em">${a.email}${a.phone ? ' · ' + a.phone : ''}</div>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;">
          <span style="padding:3px 10px;border-radius:20px;font-size:10.5px;font-weight:700;background:${isSupervisor?'rgba(106,27,154,.1)':'rgba(5,150,105,.1)'};color:${isSupervisor?'var(--purple)':'var(--pri)'};">
            ${isSupervisor ? 'سوبرفايزر' : 'مدير'}
          </span>
          ${!isActive ? '<span style="padding:3px 10px;border-radius:20px;font-size:10.5px;font-weight:700;background:rgba(220,38,38,.1);color:var(--red);">معطل</span>' : ''}
          ${!isSupervisor ? `<x-ui.button type="button" class="cat-act-btn tog${!isActive?' off':''} focus:ring-0!" onclick="doToggleAdmin(${a.id})">${isActive ? 'تعطيل' : 'تفعيل'}</x-ui.button>` : ''}
        </div>
      </div>
      ${permsHtml}
    </div>`;
            }).join('');
        }

        async function togglePerm(adminId, perm, isChecked) {
            try {
                const admin = _adminsData.find(a => a.id === adminId);
                if (!admin) return;
                let perms = [...(admin.permissions || [])];
                if (isChecked) {
                    if (!perms.includes(perm)) perms.push(perm);
                } else {
                    perms = perms.filter(p => p !== perm);
                }

                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/supervisor/admins/${adminId}/permissions`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        permissions: perms
                    }),
                });
                const d = await res.json();
                if (!res.ok) {
                    showToast(d.message || 'حدث خطأ', 'error');
                    await loadAdmins();
                    return;
                }
                admin.permissions = perms;
                showToast('تم تحديث الصلاحيات', 'success');
            } catch (e) {
                showToast('حدث خطأ', 'error');
                await loadAdmins();
            }
        }

        async function doToggleAdmin(id) {
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/supervisor/admins/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                const d = await res.json();
                showToast(d.message || 'تم', 'success');
                await loadAdmins();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        function showCreateAdminModal() {
            AMRTM_MODAL.open('create-admin-modal');
        }

        function closeCreateAdminModal() {
            AMRTM_MODAL.close('create-admin-modal');
            ['new-admin-name', 'new-admin-email', 'new-admin-phone', 'new-admin-pass'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        }

        async function doCreateAdmin() {
            const name = document.getElementById('new-admin-name')?.value?.trim();
            const email = document.getElementById('new-admin-email')?.value?.trim();
            const phone = document.getElementById('new-admin-phone')?.value?.trim();
            const pass = document.getElementById('new-admin-pass')?.value;
            if (!name || !email || !pass) {
                showToast('يرجى ملء جميع الحقول المطلوبة', 'warning');
                return;
            }
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/supervisor/admins`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name,
                        email,
                        phone: phone || null,
                        password: pass
                    }),
                });
                const d = await res.json();
                if (!res.ok) {
                    showToast(d.message || 'حدث خطأ', 'error');
                    return;
                }
                showToast('تم إنشاء حساب المدير', 'success');
                closeCreateAdminModal();
                await loadAdmins();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        document.getElementById('create-admin-modal')?.addEventListener('click', e => {
            if (e.target === document.getElementById('create-admin-modal')) closeCreateAdminModal();
        });


        /* ══════════════════════════════════════════════════════════════
           CONTRACTS (loaded from database)
        ══════════════════════════════════════════════════════════════ */
        let _contractsData = [];
        let _contractsLoaded = false;

        function contractOfficeName(ar, en) {
            if (ar && String(ar).trim()) return String(ar).trim();
            if (en && String(en).trim()) return String(en).trim();
            return '';
        }

        function contractStatusInfo(st) {
            if (st === 'active') return { txt: 'ساري', cls: 'done' };
            if (st === 'expired') return { txt: 'منتهي', cls: 'rejected' };
            return { txt: 'مسودة', cls: 'draft' };
        }

        async function loadAdminContracts() {
            try {
                const res = await fetch(admApiBase() + '/admin/contracts', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF || '' },
                    credentials: 'same-origin'
                });
                if (!res.ok) { _contractsLoaded = true; renderContractsList(); return; }
                const data = await res.json();
                _contractsData = (Array.isArray(data) ? data : []).map(c => ({
                    id: c.id,
                    number: c.number,
                    type: c.type_name || c.type_category || '—',
                    type_category: c.type_category || '',
                    party1: contractOfficeName(c.party1_office_name, c.party1_office_name_en) || 'شركة آمر تم',
                    party2: contractOfficeName(c.party2_office_name, c.party2_office_name_en) || c.party_name || '—',
                    party2_office_name: contractOfficeName(c.party2_office_name, c.party2_office_name_en) || '',
                    company: c.party_name || contractOfficeName(c.party2_office_name, c.party2_office_name_en) || '',
                    phone: '',
                    email: c.party_email || '',
                    price: c.price,
                    start: c.start_date,
                    end: c.end_date,
                    status: c.status || 'draft',
                    description: c.description || '',
                    clauses_json: c.clauses_json || '[]',
                    second_party_email_sent_at: c.second_party_email_sent_at || null,
                    second_party_viewed_at: c.second_party_viewed_at || null,
                    created_at: c.created_at || null
                }));
                _contractsLoaded = true;
                renderContractsList();
            } catch (_) {
                _contractsLoaded = true;
                renderContractsList();
            }
        }

        function refreshContracts() {
            _contractsLoaded = false;
            loadAdminContracts();
        }

        function parseClauseField(v) {
            if (v == null) return '';
            if (typeof v === 'object') return v.name || v.description || '';
            const s = String(v);
            if (/^\{/.test(s.trim()) || /^\[/.test(s.trim())) {
                try { const o = JSON.parse(s); return o && (o.name || o.description || '') ? (o.name || o.description) : ''; } catch (_) {}
            }
            return String(s).replace(/[[\]"']/g, '');
        }

        function renderClauseItems(c) {
            let arr = [];
            try { const p = JSON.parse(c.clauses_json || '[]'); arr = Array.isArray(p) ? p : []; }
            catch (_) { arr = []; }
            if (!arr.length) {
                return '<div class="py-3 text-center text-[13px] text-slate-500">لا توجد بنود مضافة لهذا العقد</div>';
            }
            return arr.map((cl, i) => {
                const nm = parseClauseField(cl.name) || ('البند ' + (i + 1));
                const ds = parseClauseField(cl.description);
                return '<div class="border-t border-dashed border-emerald-900/10 py-2 first:border-t-0"><div class="mb-0.5 font-bold text-emerald-600">' + (i + 1) + '. ' + nm + '</div>' +
                    (ds ? '<div class="text-[13px] leading-[1.8] text-slate-700">' + ds + '</div>' : '') + '</div>';
            }).join('');
        }

        function signingStatus(c) {
            const firstSigned = '#fff';
            const firstTxt = c && c.created_at ? 'تم الإنشاء والتوقيع من قبل المكتب المنشئ' : 'لم يتم التوقيع بعد';
            let secondTxt, secondCls;
            if (c && c.second_party_viewed_at) {
                secondTxt = 'تم الإطلاع والتوقيع (عبر رابط العقد)';
                secondCls = '#fff';
            } else if (c && c.second_party_email_sent_at) {
                secondTxt = 'تم إرسال رابط العقد ولم يتم الإطلاع بعد';
                secondCls = '#ffb4ab';
            } else {
                secondTxt = 'لم يتم إرسال العقد للطرف الثاني بعد';
                secondCls = '#ffd7b8';
            }
            return { firstSigned, secondSigned: secondCls, firstTxt, secondTxt };
        }

        function viewContract(id) {
            const c = _contractsData.find(x => x.id === id);
            if (!c) return;
            const st = contractStatusInfo(c.status);
            const sign = signingStatus(c);
            document.getElementById('cv-number').textContent = c.number || '—';
            document.getElementById('cv-type').textContent = c.type || '—';
            document.getElementById('cv-party1').textContent = c.party1 || '—';
            document.getElementById('cv-party2').textContent = c.party2 || '—';
            document.getElementById('cv-company').textContent = c.company || '—';
            document.getElementById('cv-price').textContent = c.price != null ? Number(c.price).toLocaleString() + ' ريال' : '—';
            document.getElementById('cv-start').textContent = fmtDate(c.start);
            document.getElementById('cv-end').textContent = fmtDate(c.end);
            document.getElementById('cv-status').textContent = st.txt;
            document.getElementById('cv-status').className = 'req-st ' + st.cls;
            document.getElementById('cv-desc').textContent = c.description || '—';
            document.getElementById('cv-clauses').innerHTML = renderClauseItems(c);
            document.getElementById('cv-sign-first-txt').textContent = sign.firstTxt;
            document.getElementById('cv-sign-second-txt').textContent = sign.secondTxt;
            document.getElementById('cv-sign-second').className = (c.second_party_viewed_at ? 'flex items-center gap-2 rounded-lg bg-emerald-600/10 px-3 py-2 text-[13px] font-bold text-emerald-700' : (c.second_party_email_sent_at ? 'flex items-center gap-2 rounded-lg bg-amber-500/10 px-3 py-2 text-[13px] font-bold text-amber-600' : 'flex items-center gap-2 rounded-lg bg-red-600/10 px-3 py-2 text-[13px] font-bold text-red-700'));
            document.getElementById('cv-pdf-link').href = admApiBase() + '/admin/contracts/' + c.id + '/pdf';
            AMRTM_MODAL.open('contract-view-modal');
        }
        function closeContractView() {
            AMRTM_MODAL.close('contract-view-modal');
        }

        let _contractTypes = [];
        let _contractTypeIdByName = {};
        let _clauseIdByType = {};
        let _editingContractTypeName = null;

        let _clausesByType = {};

        function admApiBase() {
            return (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
        }

        function admApiHeaders() {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
            };
        }

        async function loadContractTypes() {
            try {
                const res = await fetch(admApiBase() + '/admin/contract-types', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF || '' },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const data = await res.json();
                _contractTypes = (Array.isArray(data) ? data : []).map(t => ({
                    id: t.id,
                    name: t.name,
                    price: t.price != null ? t.price : null,
                    category: t.category || ''
                }));
                _contractTypeIdByName = {};
                _contractTypes.forEach(t => { _contractTypeIdByName[t.name] = t.id; });
                renderContractTypeList();
            } catch (_) {}
        }

        async function loadClausesForType(name) {
            const typeId = _contractTypeIdByName[name];
            if (!typeId) {
                _clausesByType[name] = [];
                return;
            }
            try {
                const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId + '/clauses', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF || '' },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const data = await res.json();
                _clausesByType[name] = (Array.isArray(data) ? data : []).map(c => ({
                    id: c.id,
                    name: c.name,
                    desc: c.description || ''
                }));
                const idMap = {};
                _clausesByType[name].forEach((c, i) => idMap[i] = c.id);
                _clauseIdByType[name] = idMap;
            } catch (_) {}
        }

        let _activeContractType = null;

        function contractTab(name, btn) {
            document.querySelectorAll('#page-contracts .cat-tab').forEach(t => t.classList.remove('on'));
            document.querySelectorAll('#page-contracts .cat-tab-panel').forEach(p => p.classList.remove('on'));
            btn.classList.add('on');
            document.getElementById('contract-panel-' + name)?.classList.add('on');
            if (name === 'clauses') {
                backToContractTypes();
                if (!_contractTypes.length) loadContractTypes(); else renderContractTypeList();
            }
        }

        function renderContractsList() {
            const el = document.getElementById('contracts-list-body');
            if (!el) return;

            const activeCount = _contractsData.filter(c => c.status === 'active').length;
            const expiredCount = _contractsData.filter(c => c.status === 'expired').length;
            const draftCount = _contractsData.filter(c => c.status === 'draft').length;
            const aEl = document.getElementById('cnt-active');
            const eEl = document.getElementById('cnt-expired');
            const dEl = document.getElementById('cnt-draft');
            if (aEl) aEl.textContent = activeCount;
            if (eEl) eEl.textContent = expiredCount + draftCount;
            if (dEl) dEl.textContent = draftCount;

            const q = (document.getElementById('contract-search')?.value || '').trim().toLowerCase();
            const filtered = q ? _contractsData.filter(c => [
                c.number,
                c.phone,
                c.email,
                c.company,
                c.party1,
                c.party2,
                c.type
            ].some(v => (v || '').toString().toLowerCase().includes(q))) : _contractsData;

            if (!filtered.length) {
                el.innerHTML = `<div class="cat-empty">${q ? 'لا توجد نتائج مطابقة للبحث' : 'لا توجد عقود بعد'}</div>`;
                return;
            }

            el.innerHTML = filtered.map(c => {
                const st = contractStatusInfo(c.status);
                const pr = c.price || c.price === 0 ? riyalsOfl(c.price) : '—';
                return `<div class="cat-row contract-row">
      <div class="c-cell cat-nm" data-label="رقم العقد">${c.number}</div>
      <div class="c-cell cat-nm" data-label="نوع العقد">${c.type}</div>
      <div class="c-cell cat-nm" data-label="الطرف الأول">${c.party1 || '—'}</div>
      <div class="c-cell cat-nm" data-label="الطرف الثاني">${c.party2 || '—'}</div>
      <div class="c-cell contract-price" data-label="سعر الخدمة">${pr}</div>
      <div class="c-cell cat-sub" data-label="تاريخ البداية">${fmtDate(c.start)}</div>
      <div class="c-cell cat-sub" data-label="تاريخ النهاية">${fmtDate(c.end)}</div>
      <div class="c-cell" data-label="الحالة"><span class="req-st ${st.cls}">${st.txt}</span></div>
      <div class="c-cell c-actions cat-actions">
        <x-ui.button type="button" class="cat-act-btn view focus:ring-0!" onclick="viewContract(${c.id})" title="عرض العقد">عرض</x-ui.button>
      </div>
    </div>`;
            }).join('');

            if (q && _contractsData.length !== filtered.length) {
                el.insertAdjacentHTML('afterbegin',
                    `<div class="contract-search-hit">تم العثور على ${filtered.length} من ${_contractsData.length} عقد</div>`
                );
            }
        }

        function clearContractSearch() {
            const inp = document.getElementById('contract-search');
            if (inp) inp.value = '';
            renderContractsList();
        }

        /* ── Inline (no-overlay) create contract ── */
        let _createTypeId = null;
        let _createCustomClauses = [];

        function addContract() {
            cancelCreateContract();
            const panel = getCreatePanel();
            if (!panel) return;
            panel.style.display = 'block';
            const list = document.getElementById('contracts-list-body');
            if (list) list.style.display = 'none';
            if (!_contractTypes.length) {
                loadContractTypes().then(() => renderCreateTypes());
            } else {
                renderCreateTypes();
            }
        }

        function getCreatePanel() {
            return document.getElementById('contract-create-panel');
        }

        function renderCreateTypes(filterCat) {
            const grid = document.getElementById('cm-type-grid');
            if (!grid) return;
            const rows = (filterCat && filterCat !== 'all') ? _contractTypes.filter(t => (t.category || '') === filterCat) : _contractTypes;
            grid.innerHTML = rows.length ? rows.map(t => {
                const sel = _createTypeId === t.id ? 'active' : '';
                const clauseCount = (_clausesByType[t.name] || []).length;
                const pr = t.price != null && t.price !== '' ? riyalsOfl(t.price) : '';
                return `<div class="cm-tpl-card ${sel}" onclick="selectContractCreateType(${t.id}, '${escJs(t.name)}')">
                    <h4><i class="ti ti-file-text"></i> ${escJs(t.name)}</h4>
                    <span class="tpl-cat">${escJs(t.category || '')}${pr ? ' • ' + pr : ''}</span>
                    <span class="tpl-cat" style="display:block;color:var(--t3);margin-top:4px;">${clauseCount} بنود</span>
                </div>`;
            }).join('') : '<div class="cat-empty">لا توجد أنواع عقود متاحة</div>';

            const next = document.getElementById('cm-next-btn');
            if (next) next.disabled = !_createTypeId;
        }

        function filterCreateTypes(el) {
            document.querySelectorAll('#cm-type-filters .stab').forEach(s => s.classList.remove('active'));
            el.classList.add('active');
            renderCreateTypes(el.getAttribute('data-cat'));
        }

        function selectContractCreateType(id, name) {
            _createTypeId = id;
            document.getElementById('cm-selected-type').textContent = name;
            document.getElementById('cm-admin-clauses').innerHTML = '';
            const next = document.getElementById('cm-next-btn');
            if (next) next.disabled = false;
            renderCreateTypes(document.querySelector('#cm-type-filters .stab.active')?.getAttribute('data-cat'));
        }

        function cmGoStep(n) {
            if (n === 2) {
                if (!_createTypeId) { showToast('اختر نوع العقد أولاً', 'warning'); return; }
                renderAdminClauses();
                document.getElementById('cm-step-1').style.display = 'none';
                document.getElementById('cm-step-2').style.display = 'block';
                document.getElementById('cm-step-1-ind').style.background = 'var(--sur2)';
                document.getElementById('cm-step-1-ind').style.color = '#999';
                document.getElementById('cm-step-2-ind').style.background = 'var(--pri)';
                document.getElementById('cm-step-2-ind').style.color = '#fff';
                document.getElementById('cm-next-btn').style.display = 'none';
                document.getElementById('cm-submit-btn').style.display = '';
                document.getElementById('cm-back-btn').style.display = '';
                const typeName = _contractTypes.find(t => t.id === _createTypeId)?.name || '';
                if (typeName) {
                    const t = _contractTypes.find(x => x.id === _createTypeId);
                    const priceEl = document.getElementById('cm-price-input');
                    if (t && t.price != null && !priceEl.value) priceEl.value = t.price;
                }
            } else {
                document.getElementById('cm-step-1').style.display = 'block';
                document.getElementById('cm-step-2').style.display = 'none';
                document.getElementById('cm-step-1-ind').style.background = 'var(--pri)';
                document.getElementById('cm-step-1-ind').style.color = '#fff';
                document.getElementById('cm-step-2-ind').style.background = 'var(--sur2)';
                document.getElementById('cm-step-2-ind').style.color = '#999';
                document.getElementById('cm-next-btn').style.display = '';
                document.getElementById('cm-submit-btn').style.display = 'none';
                document.getElementById('cm-back-btn').style.display = 'none';
            }
        }

        function renderAdminClauses() {
            const container = document.getElementById('cm-admin-clauses');
            if (!container) return;
            const type = _contractTypes.find(t => t.id === _createTypeId);
            const clauses = type ? (_clausesByType[type.name] || []) : [];
            container.innerHTML = clauses.length ? clauses.map(c =>
                `<div class="cm-clause-item">
                    <div class="cl-name"><span>${escJs(c.name)}</span><span class="cl-lock"><i class="ti ti-lock"></i> أساسي</span></div>
                    <div class="cl-desc">${escJs(c.desc)}</div>
                </div>`
            ).join('') : '<div class="cat-empty" style="font-size:12.5px;padding:10px;">لا توجد بنود أساسية لهذا النوع</div>';
        }

        function showAddClauseForm() {
            document.getElementById('cm-add-clause-form').style.display = 'block';
            document.getElementById('cm-add-clause-btn').style.display = 'none';
            document.getElementById('cm-new-clause-name').focus();
        }

        function cancelAddClause() {
            document.getElementById('cm-add-clause-form').style.display = 'none';
            document.getElementById('cm-add-clause-btn').style.display = '';
            document.getElementById('cm-new-clause-name').value = '';
            document.getElementById('cm-new-clause-desc').value = '';
        }

        function confirmAddClause() {
            const name = document.getElementById('cm-new-clause-name').value?.trim();
            const desc = document.getElementById('cm-new-clause-desc').value?.trim();
            if (!name) { showToast('أدخل اسم البند', 'warning'); return; }
            _createCustomClauses.push({ name, desc: desc || '' });
            renderCustomClauses();
            cancelAddClause();
        }

        function renderCustomClauses() {
            const container = document.getElementById('cm-custom-clauses');
            container.innerHTML = _createCustomClauses.map((c, i) =>
                `<div class="cm-clause-item" style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                    <div style="flex:1">
                        <div class="cl-name">${escJs(c.name)}</div>
                        ${c.desc ? `<div class="cl-desc">${escJs(c.desc)}</div>` : ''}
                    </div>
                    <x-ui.button type="button" class="cat-act-btn del focus:ring-0!" onclick="removeCustomClause(${i})"><i class="ti ti-trash"></i></x-ui.button>
                </div>`
            ).join('');
        }

        function removeCustomClause(i) {
            _createCustomClauses.splice(i, 1);
            renderCustomClauses();
        }

        function cancelCreateContract() {
            const panel = getCreatePanel();
            if (panel) panel.style.display = 'none';
            const list = document.getElementById('contracts-list-body');
            if (list) list.style.display = '';
            _createTypeId = null;
            _createCustomClauses = [];
            document.getElementById('cm-party2-input').value = '';
            document.getElementById('cm-party2-email').value = '';
            document.getElementById('cm-start-input').value = '';
            document.getElementById('cm-end-input').value = '';
            document.getElementById('cm-price-input').value = '';
            document.getElementById('cm-desc-input').value = '';
            document.getElementById('cm-custom-clauses').innerHTML = '';
            cmGoStep(1);
        }

        async function submitNewContract() {
            const party2 = document.getElementById('cm-party2-input').value?.trim();
            const email = document.getElementById('cm-party2-email').value?.trim();
            const start = document.getElementById('cm-start-input').value;
            const end = document.getElementById('cm-end-input').value;
            const price = parseFloat(document.getElementById('cm-price-input').value);
            const desc = document.getElementById('cm-desc-input').value?.trim();

            if (!_createTypeId) { showToast('اختر نوع العقد أولاً', 'warning'); return; }
            if (!party2) { showToast('أدخل اسم الطرف الثاني', 'warning'); return; }
            if (!start || !end) { showToast('أدخل تاريخي البداية والنهاية', 'warning'); return; }
            if (end <= start) { showToast('تاريخ النهاية يجب أن يكون بعد تاريخ البداية', 'warning'); return; }

            const payload = {
                contract_type_id: _createTypeId,
                party_name: party2,
                party_email: email || null,
                start_date: start,
                end_date: end,
                price: isNaN(price) ? null : price,
                description: desc || null
            };

            try {
                const res = await fetch(admApiBase() + '/admin/contracts', {
                    method: 'POST',
                    headers: admApiHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    showToast(data.message || 'فشل إنشاء العقد', 'error');
                    return;
                }
                showToast('تم إنشاء العقد بنجاح', 'success');
                cancelCreateContract();
                refreshContracts();
            } catch (_) {
                showToast('حدث خطأ في الاتصال', 'error');
            }
        }

        function deleteContract(id) {
            if (!confirm('هل أنت متأكد من حذف هذا العقد؟')) return;
            _contractsData = _contractsData.filter(c => c.id !== id);
            renderContractsList();
            showToast('تم الحذف', 'success');
        }

        /* ── Clauses tab ── */
        function escJs(s) {
            return (s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        function renderContractTypeList() {
            const el = document.getElementById('contract-type-select');
            el.innerHTML = _contractTypes.map(t => {
                const count = (_clausesByType[t.name] || []).length;
                const pr = t.price != null && t.price !== '' ? riyalsOfl(t.price) : '';
                return `<div class="req-card" style="cursor:pointer;">
      <div class="req-hd" onclick="openContractType('${escJs(t.name)}')" style="cursor:pointer;">
        <div class="req-ico" style="background:rgba(5,150,105,.1);"><i class="ti ti-file-description" style="color:var(--pri)"></i></div>
        <div class="req-info">
          <div class="req-nm">${t.name}</div>
          <div class="req-meta"><span>${count} بند</span>${pr ? `<span style="color:var(--green);font-weight:700;">${pr}</span>` : ''}</div>
        </div>
        <x-ui.button type="button" class="cat-act-btn edit focus:ring-0!" onclick="event.stopPropagation();showContractTypeForm('${escJs(t.name)}')" title="تعديل اسم النوع وسعره">تعديل</x-ui.button>
        <x-ui.button type="button" class="cat-act-btn del focus:ring-0!" onclick="event.stopPropagation();deleteContractType('${escJs(t.name)}')" title="حذف النوع"><i class="ti ti-trash"></i></x-ui.button>
        <i class="ti ti-chevron-left" style="color:var(--t4);font-size:15px;"></i>
      </div>
    </div>`;
            }).join('');
            renderContractTypeOptions();
        }

        function toggleContractTypeForm() {
            const form = document.getElementById('contract-type-form');
            if (!form) return;
            const show = form.style.display === 'none';
            form.style.display = show ? 'block' : 'none';
            if (show) resetContractTypeForm();
        }

        function showContractTypeForm(name) {
            const t = _contractTypes.find(x => x.name === name);
            if (!t) return;
            _editingContractTypeName = name;
            const form = document.getElementById('contract-type-form');
            form.style.display = 'block';
            S('contract-type-form-ttl', 'تعديل نوع العقد');
            document.getElementById('ct-name').value = t.name;
            document.getElementById('ct-price').value = t.price != null ? t.price : '';
            const cancel = document.getElementById('contract-type-form-cancel');
            if (cancel) cancel.style.display = 'inline-flex';
            form.classList.add('editing');
            form.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
            document.getElementById('ct-name')?.focus();
        }

        function resetContractTypeForm() {
            _editingContractTypeName = null;
            document.getElementById('ct-name').value = '';
            document.getElementById('ct-price').value = '';
            S('contract-type-form-ttl', 'إضافة نوع عقد جديد');
            const cancel = document.getElementById('contract-type-form-cancel');
            if (cancel) cancel.style.display = 'none';
            const form = document.getElementById('contract-type-form');
            if (form) form.classList.remove('editing');
        }

        async function saveContractType() {
            const name = document.getElementById('ct-name')?.value?.trim();
            if (!name) {
                showToast('يرجى إدخال اسم نوع العقد', 'warning');
                document.getElementById('ct-name')?.focus();
                return;
            }
            const priceVal = document.getElementById('ct-price')?.value;
            const price = priceVal === '' || priceVal == null ? null : parseFloat(priceVal);

            try {
                showLoader();
                if (_editingContractTypeName) {
                    const oldName = _editingContractTypeName;
                    const typeId = _contractTypeIdByName[oldName];
                    const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId, {
                        method: 'PUT',
                        headers: admApiHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ name, price })
                    });
                    const d = await res.json();
                    if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                    showToast('تم تعديل نوع العقد', 'success');
                } else {
                    const res = await fetch(admApiBase() + '/admin/contract-types', {
                        method: 'POST',
                        headers: admApiHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ name, price })
                    });
                    const d = await res.json();
                    if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                    showToast('تم إضافة نوع العقد', 'success');
                }
            } catch (e) {
                showToast('حدث خطأ', 'error');
                return;
            } finally {
                hideLoader();
            }

            resetContractTypeForm();
            await loadContractTypes();
        }

        async function deleteContractType(name) {
            if (!confirm(`هل أنت متأكد من حذف نوع العقد «${name}»؟`)) return;
            const typeId = _contractTypeIdByName[name];
            try {
                showLoader();
                const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId, {
                    method: 'DELETE',
                    headers: admApiHeaders(),
                    credentials: 'same-origin'
                });
                const d = await res.json();
                if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                showToast('تم حذف نوع العقد', 'success');
            } catch (e) {
                showToast('حدث خطأ', 'error');
                return;
            } finally {
                hideLoader();
            }
            await loadContractTypes();
        }

        function openContractType(type) {
            _activeContractType = type;
            document.getElementById('contract-type-select').style.display = 'none';
            document.getElementById('contract-clauses-detail').style.display = 'block';
            S('clauses-type-ttl', type);
            const el = document.getElementById('clauses-list-body');
            if (el) el.innerHTML = '<div class="cat-empty">جارٍ تحميل البنود...</div>';
            loadClausesForType(type).then(() => {
                if (_activeContractType === type) renderClauses();
            });
        }

        function backToContractTypes() {
            document.getElementById('contract-type-select').style.display = 'flex';
            document.getElementById('contract-clauses-detail').style.display = 'none';
            _activeContractType = null;
        }

        let _editingClauseIndex = null;

        function renderClauses() {
            const el = document.getElementById('clauses-list-body');
            const clauses = _clausesByType[_activeContractType] || [];
            const allCk = document.getElementById('clause-check-all');
            const bulkBtn = document.getElementById('clause-bulk-del');
            if (!el) return;

            if (!clauses.length) {
                el.innerHTML = '<div class="cat-empty">لا توجد بنود بعد</div>';
                if (allCk) allCk.checked = false;
                if (bulkBtn) bulkBtn.disabled = true;
                updateBulkBtn();
                return;
            }

            el.innerHTML = clauses.map((c, i) => `
    <div class="cat-row clause-row">
      <div class="c-check"><x-ui.checkbox id="clause-${i}" onchange="syncClauseCheckAll()" class="clause-check focus:ring-0!" data-idx="${i}" /></div>
      <div class="c-name cat-nm" data-label="البند">${c.name}</div>
      <div class="c-desc cat-sub" data-label="الوصف">${c.desc}</div>
      <div class="c-actions cat-actions">
        <x-ui.button type="button" class="cat-act-btn edit focus:ring-0!" onclick="showClauseForm(${i})"><i class="ti ti-edit"></i></x-ui.button>
        <x-ui.button type="button" class="cat-act-btn del focus:ring-0!" onclick="removeClause(${i})"><i class="ti ti-trash"></i></x-ui.button>
      </div>
    </div>`).join('');

            if (allCk) allCk.checked = false;
            syncClauseCheckAll();
        }

        function showClauseForm(idx) {
            _editingClauseIndex = idx;
            const clauses = _clausesByType[_activeContractType] || [];
            const c = idx != null ? clauses[idx] : null;
            S('clause-form-ttl', c ? 'تعديل البند' : 'إضافة بند جديد');
            document.getElementById('cl-name').value = c ? c.name : '';
            document.getElementById('cl-desc').value = c ? c.desc : '';
            const cancel = document.getElementById('clause-form-cancel');
            if (cancel) cancel.style.display = c ? 'inline-flex' : 'none';
            const form = document.getElementById('clause-form');
            if (form) {
                form.classList.toggle('editing', !!c);
                form.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
                document.getElementById('cl-name')?.focus();
            }
        }

        function resetClauseForm() {
            _editingClauseIndex = null;
            document.getElementById('cl-name').value = '';
            document.getElementById('cl-desc').value = '';
            S('clause-form-ttl', 'إضافة بند جديد');
            const cancel = document.getElementById('clause-form-cancel');
            if (cancel) cancel.style.display = 'none';
            const form = document.getElementById('clause-form');
            if (form) form.classList.remove('editing');
        }

        async function saveClause() {
            if (!_activeContractType) return;
            const name = document.getElementById('cl-name')?.value?.trim();
            const desc = document.getElementById('cl-desc')?.value?.trim() || '';
            if (!name) {
                showToast('يرجى إدخال اسم البند', 'warning');
                document.getElementById('cl-name')?.focus();
                return;
            }
            const typeId = _contractTypeIdByName[_activeContractType];
            const editingId = (_editingClauseIndex != null && _clauseIdByType[_activeContractType]) ?
                _clauseIdByType[_activeContractType][_editingClauseIndex] : null;

            try {
                showLoader();
                if (editingId != null) {
                    const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId + '/clauses/' + editingId, {
                        method: 'PUT',
                        headers: admApiHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ name, description: desc })
                    });
                    const d = await res.json();
                    if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                } else {
                    const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId + '/clauses', {
                        method: 'POST',
                        headers: admApiHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ name, description: desc })
                    });
                    const d = await res.json();
                    if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                }
                showToast(editingId != null ? 'تم تعديل البند' : 'تم إضافة البند', 'success');
            } catch (e) {
                showToast('حدث خطأ', 'error');
                return;
            } finally {
                hideLoader();
            }

            resetClauseForm();
            await loadClausesForType(_activeContractType);
            renderClauses();
            renderContractTypeList();
        }

        async function removeClause(i) {
            if (!_activeContractType) return;
            if (!confirm('حذف هذا البند؟')) return;
            const typeId = _contractTypeIdByName[_activeContractType];
            const clauseId = _clauseIdByType[_activeContractType] ? _clauseIdByType[_activeContractType][i] : null;
            if (clauseId == null) return;
            try {
                showLoader();
                const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId + '/clauses/' + clauseId, {
                    method: 'DELETE',
                    headers: admApiHeaders(),
                    credentials: 'same-origin'
                });
                const d = await res.json();
                if (!res.ok) { showToast(d.message || 'حدث خطأ', 'error'); return; }
                showToast('تم الحذف', 'success');
            } catch (e) {
                showToast('حدث خطأ', 'error');
                return;
            } finally {
                hideLoader();
            }
            await loadClausesForType(_activeContractType);
            renderClauses();
            renderContractTypeList();
        }

        function toggleAllClauses() {
            const allCk = document.getElementById('clause-check-all');
            document.querySelectorAll('#clauses-list-body .clause-check').forEach(cb => cb.checked = !!allCk && allCk
                .checked);
            updateBulkBtn();
        }

        function syncClauseCheckAll() {
            const cbs = document.querySelectorAll('#clauses-list-body .clause-check');
            const allCk = document.getElementById('clause-check-all');
            if (allCk) allCk.checked = cbs.length > 0 && Array.from(cbs).every(cb => cb.checked);
            updateBulkBtn();
        }

        function selectedClauseIndices() {
            const idxs = [];
            document.querySelectorAll('#clauses-list-body .clause-check:checked').forEach(cb => idxs.push(parseInt(cb
                .dataset.idx)));
            return idxs;
        }

        function updateBulkBtn() {
            const btn = document.getElementById('clause-bulk-del');
            if (btn) btn.disabled = selectedClauseIndices().length === 0;
        }

        async function deleteSelectedClauses() {
            if (!_activeContractType) return;
            const idxs = selectedClauseIndices();
            if (!idxs.length) {
                showToast('لم يتم تحديد أي بنود', 'warning');
                return;
            }
            if (!confirm(`هل أنت متأكد من حذف ${idxs.length} بند؟`)) return;
            const typeId = _contractTypeIdByName[_activeContractType];
            const idMap = _clauseIdByType[_activeContractType] || {};
            try {
                showLoader();
                for (let i = 0; i < idxs.length; i++) {
                    const clauseId = idMap[idxs[i]];
                    if (clauseId == null) continue;
                    const res = await fetch(admApiBase() + '/admin/contract-types/' + typeId + '/clauses/' + clauseId, {
                        method: 'DELETE',
                        headers: admApiHeaders(),
                        credentials: 'same-origin'
                    });
                    if (!res.ok) break;
                }
                showToast('تم حذف البنود المحددة', 'success');
            } catch (e) {
                showToast('حدث خطأ', 'error');
                return;
            } finally {
                hideLoader();
            }
            await loadClausesForType(_activeContractType);
            renderClauses();
            renderContractTypeList();
        }

        document.getElementById('contract-view-modal')?.addEventListener('click', e => {
            if (e.target === document.getElementById('contract-view-modal')) closeContractView();
        });


        /* ══════════════════════════════════════════════════════════════
           USERS PAGE
        ══════════════════════════════════════════════════════════════ */
        let _usrFilter = 'all',
            _usrPage = 1,
            _usrSearch = '',
            _usrData = [],
            _usrMeta = {};
        let _balUserId = null,
            _usrSearchTimer = null;

        function filterUsers(f, btn) {
            _usrFilter = f;
            _usrPage = 1;
            document.querySelectorAll('#page-users .req-filters .rf-btn').forEach(b => b.classList.remove('on'));
            if (btn) btn.classList.add('on');
            loadUsers();
        }

        function debounceUserSearch(v) {
            _usrSearch = v;
            _usrPage = 1;
            clearTimeout(_usrSearchTimer);
            _usrSearchTimer = setTimeout(loadUsers, 380);
        }

        async function loadUserStats() {
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/users/stats`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const d = await res.json();
                S('usr-sc-total', d.total || 0);
                S('usr-sc-active', d.active || 0);
                S('usr-sc-banned', d.banned || 0);
                S('usr-sc-new', d.newThisMonth || 0);
            } catch (_) {}
        }

        async function loadUsers() {
            const el = document.getElementById('usr-list');
            if (!el) return;
            el.innerHTML = '<div style="text-align:center;padding:2.5rem;color:var(--t3);">جارٍ التحميل...</div>';
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const params = new URLSearchParams({
                    page: _usrPage
                });
                if (_usrFilter !== 'all') params.set('status', _usrFilter === 'active' ? 'active' : 'banned');
                if (_usrSearch) params.set('search', _usrSearch);
                const res = await fetch(`${base}/admin/users?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    el.innerHTML =
                        '<div style="padding:2rem;text-align:center;color:var(--red);">فشل في تحميل البيانات</div>';
                    return;
                }
                const d = await res.json();
                _usrData = d.data || [];
                _usrMeta = {
                    current_page: d.current_page || 1,
                    last_page: d.last_page || 1,
                    total: d.total || 0
                };
                renderUserList();
                renderUserPagination();
            } catch (e) {
                el.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--red);">حدث خطأ</div>';
            }
        }

        function renderUserList() {
            const el = document.getElementById('usr-list');
            if (!el) return;
            if (!_usrData.length) {
                el.innerHTML = '<div style="text-align:center;padding:3rem;color:var(--t3);">لا يوجد مستخدمون</div>';
                return;
            }
            el.innerHTML = _usrData.map(u => {
                const isActive = u.is_active !== false;
                const bal = parseFloat(u.balance || 0);
                const balCls = bal >= 0 ? 'pos' : 'neg';
                return `<div class="usr-card">
      <div class="usr-av">${(u.name||'?')[0]}</div>
      <div>
        <div class="usr-nm">${u.name}</div>
        <div class="usr-meta">${u.email}${u.phone?' · '+u.phone:''}${u.req_total?' · '+u.req_total+' طلب':''}</div>
        <div style="margin-top:4px;display:flex;gap:.4rem;flex-wrap:wrap;">
          <span style="padding:2px 8px;border-radius:20px;font-size:10.5px;font-weight:700;background:${isActive?'rgba(4,120,87,.1)':'rgba(220,38,38,.1)'};color:${isActive?'var(--green)':'var(--red)'};">${isActive?'نشط':'محظور'}</span>
          <span style="font-size:10.5px;color:var(--t3);">انضم: ${fmtDate(u.created_at)}</span>
        </div>
      </div>
      <div>
        <div class="usr-bal ${balCls}">${bal.toFixed(0)}</div>
        <div style="font-size:10px;color:var(--t3);text-align:center;">ر.س</div>
      </div>
      <div class="usr-actions">
        <x-ui.button type="button" class="cat-act-btn focus:ring-0!" style="background:rgba(5,150,105,.08);color:var(--pri);border-color:var(--b1);" onclick="showBalanceModal(${u.id},'${(u.name||'').replace(/'/g,'')}',${ bal })"><i class="ti ti-wallet"></i></x-ui.button>
        <x-ui.button type="button" class="cat-act-btn tog${!isActive?' off':''} focus:ring-0!" onclick="doToggleUser(${u.id})">${isActive?'حظر':'تفعيل'}</x-ui.button>
      </div>
    </div>`;
            }).join('');
        }

        function renderUserPagination() {
            const existing = document.getElementById('usr-pag');
            if (existing) existing.remove();
            if (!_usrMeta.last_page || _usrMeta.last_page <= 1) return;
            const pag = document.createElement('div');
            pag.id = 'usr-pag';
            pag.style.cssText = 'display:flex;gap:.5rem;justify-content:center;margin-top:1rem;flex-wrap:wrap;';
            const cur = _usrMeta.current_page || 1,
                last = _usrMeta.last_page || 1;
            let html = '';
            if (cur > 1) html +=
                `<x-ui.button type="button" onclick="_usrPage=${cur-1};loadUsers()" class="focus:ring-0!" style="height:36px;padding:0 12px;border-radius:8px;border:1.5px solid var(--b1);background:transparent;color:var(--t2);font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">السابق</x-ui.button>`;
            for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
                const on = i === cur;
                html +=
                    `<x-ui.button type="button" onclick="_usrPage=${i};loadUsers()" class="focus:ring-0!" style="width:36px;height:36px;border-radius:8px;border:1.5px solid ${on?'var(--pri)':'var(--b1)'};background:${on?'var(--pri)':'transparent'};color:${on?'#fff':'var(--t2)'};font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">${i}</x-ui.button>`;
            }
            if (cur < last) html +=
                `<x-ui.button type="button" onclick="_usrPage=${cur+1};loadUsers()" class="focus:ring-0!" style="height:36px;padding:0 12px;border-radius:8px;border:1.5px solid var(--b1);background:transparent;color:var(--t2);font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;">التالي</x-ui.button>`;
            pag.innerHTML = html;
            document.getElementById('usr-list')?.insertAdjacentElement('afterend', pag);
        }

        async function doToggleUser(id) {
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/users/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                const d = await res.json();
                showToast(d.message || 'تم', 'success');
                await loadUsers();
                await loadUserStats();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        function showBalanceModal(id, name, balance) {
            _balUserId = id;
            S('bal-user-name', name);
            S('bal-current', parseFloat(balance).toFixed(2) + ' ر.س');
            document.getElementById('bal-amount').value = '';
            document.getElementById('bal-reason').value = '';
            document.querySelector('input[name="bal-type"][value="charge"]').checked = true;
            updateBalTypeUI();
            AMRTM_MODAL.open('balance-modal');
        }

        function closeBalanceModal() {
            AMRTM_MODAL.close('balance-modal');
            _balUserId = null;
        }

        function updateBalTypeUI() {
            const isCharge = document.querySelector('input[name="bal-type"]:checked')?.value === 'charge';
            document.getElementById('bal-submit-btn').style.background = isCharge ?
                'linear-gradient(135deg,#047857,#2E7D32)' : 'linear-gradient(135deg,#dc2626,#D32F2F)';
        }
        async function doAdjustBalance() {
            if (!_balUserId) return;
            const amount = parseFloat(document.getElementById('bal-amount')?.value);
            const type = document.querySelector('input[name="bal-type"]:checked')?.value;
            const reason = document.getElementById('bal-reason')?.value?.trim();
            if (!amount || amount <= 0) {
                showToast('يرجى إدخال مبلغ صحيح', 'warning');
                return;
            }
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/users/${_balUserId}/balance`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        amount,
                        type,
                        reason: reason || null
                    }),
                });
                const d = await res.json();
                if (!res.ok) {
                    showToast(d.message || 'حدث خطأ', 'error');
                    return;
                }
                showToast(d.message || 'تم', 'success');
                closeBalanceModal();
                await loadUsers();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }
        document.getElementById('balance-modal')?.addEventListener('click', e => {
            if (e.target === document.getElementById('balance-modal')) closeBalanceModal();
        });

        function exportUsersCSV() {
            if (!_usrData.length) {
                showToast('لا توجد بيانات للتصدير', 'warning');
                return;
            }
            const rows = _usrData.map(u => ({
                ID: u.id,
                Name: u.name,
                Email: u.email,
                Phone: u.phone,
                Balance: u.balance,
                Requests: u.req_total,
                Status: u.is_active ? 'Active' : 'Banned',
                Joined: u.created_at,
            }));
            exportCSV(rows, 'users_export.csv');
        }

        /* ══════════════════════════════════════════════════════════════
           ANALYTICS PAGE
        ══════════════════════════════════════════════════════════════ */
        let _analyticsData = {};

        async function loadAnalytics() {
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/analytics?months=6`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    showToast('خطأ في تحميل التحليلات', 'error');
                    return;
                }
                _analyticsData = await res.json();
                renderAnalytics();
            } catch (e) {
                showToast('حدث خطأ', 'error');
            }
        }

        function renderAnalytics() {
            const d = _analyticsData;
            if (!d || !d.monthly) return;

            S('an-revenue', parseFloat(d.total_revenue || 0).toFixed(0));
            S('an-rate', (d.completion_rate || 0) + '%');
            S('an-rej-rate', (d.rejection_rate || 0) + '%');

            // Revenue chart
            const monthly = d.monthly || [];
            const maxRev = Math.max(...monthly.map(m => m.revenue), 1);
            document.getElementById('an-rev-chart').innerHTML = monthly.map(m => {
                const h = Math.max(Math.round((m.revenue / maxRev) * 110), 4);
                return `<div class="flex flex-1 flex-col items-center gap-1">
      <div class="text-[9px] font-bold text-slate-700">${parseFloat(m.revenue).toFixed(0)}</div>
      <div class="min-h-[4px] w-full rounded-t-[5px] bg-gradient-to-b from-[#059669] to-[#16a34a] transition-[height] duration-500" style="height:${h}px;"></div>
      <div class="w-full max-w-full truncate text-center text-[9px] text-slate-500">${(m.label||'').replace(' ','\n')}</div>
    </div>`;
            }).join('');

            // Requests chart
            const maxReq = Math.max(...monthly.map(m => m.requests), 1);
            document.getElementById('an-req-chart').innerHTML = monthly.map(m => {
                const h = Math.max(Math.round((m.requests / maxReq) * 110), 4);
                return `<div class="flex flex-1 flex-col items-center gap-1">
      <div class="text-[9px] font-bold text-slate-700">${m.requests}</div>
      <div class="min-h-[4px] w-full rounded-t-[5px] transition-[height] duration-500" style="height:${h}px;background:linear-gradient(180deg,#0277BD,#16a34a);"></div>
      <div class="w-full max-w-full truncate text-center text-[9px] text-slate-500">${(m.label||'').replace(' ','\n')}</div>
    </div>`;
            }).join('');

            // Top services
            const tops = d.top_services || [];
            const maxCnt = Math.max(...tops.map(s => s.count), 1);
            document.getElementById('an-top-svcs').innerHTML = tops.map(s => `
    <div class="flex items-center gap-3 border-b border-emerald-900/5 py-2.5 last:border-b-0">
      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background:${s.bg||'rgba(5,150,105,.1)'}">
        ${renderIcon(s.icon, s.color||'#059669','ti-file-text')}
      </div>
      <div class="min-w-0 flex-1">
        <div class="mb-1 text-[13px] font-bold text-slate-900">${lang==='ar'?s.name_ar:s.name_en}</div>
        <div class="h-2 overflow-hidden rounded-full bg-slate-50"><div class="h-full rounded-full transition-[width] duration-700" style="width:${Math.round((s.count/maxCnt)*100)}%;background:${s.color||'var(--pri)'}"></div></div>
      </div>
      <div class="min-w-[80px] text-left">
        <div class="text-[13px] font-bold text-slate-900">${s.count} طلب</div>
        <div class="text-[11px] font-bold text-emerald-700">${parseFloat(s.revenue||0).toFixed(0)} ر.س</div>
      </div>
    </div>`).join('');
        }

        function exportAnalyticsCSV() {
            const monthly = _analyticsData.monthly || [];
            if (!monthly.length) {
                showToast('لا توجد بيانات', 'warning');
                return;
            }
            exportCSV(monthly.map(m => ({
                Month: m.label,
                Revenue: m.revenue,
                Requests: m.requests,
                NewUsers: m.users
            })), 'analytics_export.csv');
        }

        /* ══════════════════════════════════════════════════════════════
           ACTIVITY LOGS PAGE
        ══════════════════════════════════════════════════════════════ */
        let _logFilter = 'all',
            _logPage = 1,
            _logSearch = '',
            _logSearchTimer = null;

        function filterLogs(f, btn) {
            _logFilter = f;
            _logPage = 1;
            document.querySelectorAll('#page-logs .req-filters .rf-btn').forEach(b => b.classList.remove('on'));
            if (btn) btn.classList.add('on');
            loadLogs();
        }

        function debounceLogSearch(v) {
            _logSearch = v;
            _logPage = 1;
            clearTimeout(_logSearchTimer);
            _logSearchTimer = setTimeout(loadLogs, 380);
        }

        async function loadLogs() {
            const el = document.getElementById('log-list');
            if (!el) return;
            el.innerHTML = '<div class="px-4 py-10 text-center text-sm text-slate-500">جارٍ التحميل...</div>';
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const params = new URLSearchParams({
                    page: _logPage
                });
                if (_logFilter !== 'all') params.set('log_type', _logFilter);
                if (_logSearch) params.set('search', _logSearch);
                const res = await fetch(`${base}/admin/logs?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    el.innerHTML =
                        '<div class="px-4 py-8 text-center text-sm text-red-600">فشل في تحميل البيانات</div>';
                    return;
                }
                const d = await res.json();
                renderLogList(d.data || [], d);
                renderLogPagination(d);
            } catch (e) {
                el.innerHTML = '<div class="px-4 py-8 text-center text-sm text-red-600">حدث خطأ</div>';
            }
        }

        function renderLogList(logs, meta) {
            const el = document.getElementById('log-list');
            if (!el) return;
            if (!logs.length) {
                el.innerHTML = '<div class="px-4 py-12 text-center text-sm text-slate-500">لا توجد سجلات</div>';
                return;
            }

            const typeConf = {
                status_change: {
                    icon: 'ti-refresh',
                    color: '#0277BD',
                    bg: 'rgba(2,119,189,.1)',
                    label: 'تغيير الحالة'
                },
                admin_note: {
                    icon: 'ti-message',
                    color: '#6A1B9A',
                    bg: 'rgba(106,27,154,.1)',
                    label: 'ملاحظة إدارية'
                },
                info_request: {
                    icon: 'ti-info-circle',
                    color: '#E65100',
                    bg: 'rgba(230,81,0,.1)',
                    label: 'طلب معلومات'
                },
            };

            el.innerHTML = logs.map(l => {
                const conf = typeConf[l.log_type] || {
                    icon: 'ti-activity',
                    color: '#999',
                    bg: 'rgba(0,0,0,.05)',
                    label: l.log_type
                };
                const stLabel = l.status ? (stInfo(l.status)?.label || l.status) : '';
                return `<div class="mb-2 grid grid-cols-[36px_1fr_auto] items-start gap-3 rounded-xl border border-emerald-900/10 bg-white px-5 py-3.5 shadow-sm shadow-emerald-900/5">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" style="background:${conf.bg}"><i class="ti ${conf.icon} text-base" style="color:${conf.color}"></i></div>
      <div class="min-w-0">
        <div class="text-[13px] font-bold text-slate-900">${conf.label}${stLabel ? ' → ' + stLabel : ''}</div>
        <div class="mt-0.5 text-[11.5px] text-slate-500">
          <span>طلب: <strong class="font-bold text-slate-700">${l.ref_number}</strong></span>
          <span class="mx-1.5">·</span>
          <span>عميل: ${l.client_name}</span>
          <span class="mx-1.5">·</span>
          <span>مدير: ${l.admin_name}</span>
        </div>
        ${l.note ? `<div class="mt-1 text-[11.5px] italic text-slate-700">"${l.note}"</div>` : ''}
      </div>
      <div class="whitespace-nowrap text-[10.5px] text-slate-500">${fmtDate(l.created_at)}</div>
    </div>`;
            }).join('');
        }

        function renderLogPagination(meta) {
            const existing = document.getElementById('log-pag');
            if (existing) existing.remove();
            if (!meta.last_page || meta.last_page <= 1) return;
            const pag = document.createElement('div');
            pag.id = 'log-pag';
            pag.className = 'mt-4 flex flex-wrap items-center justify-center gap-2';
            const cur = meta.current_page || 1,
                last = meta.last_page || 1;
            let html = '';
            if (cur > 1) html +=
                `<x-ui.button type="button" onclick="_logPage=${cur-1};loadLogs()" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">السابق</x-ui.button>`;
            for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
                const on = i === cur;
                const cls = on
                    ? 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-600 bg-emerald-600 text-white'
                    : 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent text-slate-700 hover:bg-emerald-600/5';
                html +=
                    `<x-ui.button type="button" onclick="_logPage=${i};loadLogs()" class="${cls} text-[13px] font-bold focus:ring-0!">${i}</x-ui.button>`;
            }
            if (cur < last) html +=
                `<x-ui.button type="button" onclick="_logPage=${cur+1};loadLogs()" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">التالي</x-ui.button>`;
            pag.innerHTML = html;
            document.getElementById('log-list')?.insertAdjacentElement('afterend', pag);
        }

        /* ══════════════════════════════════════════════════════════════
           OFFICE FINANCIAL REPORT
        ══════════════════════════════════════════════════════════════ */
        let _oflData = null;

        async function loadOfficeFinancial() {
            const from = document.getElementById('ofl-from')?.value || '';
            const to = document.getElementById('ofl-to')?.value || '';
            let url = `${AMRTM_API_BASE}/admin/office-financial`;
            const params = [];
            if (from) params.push('from=' + from);
            if (to) params.push('to=' + to);
            if (params.length) url += '?' + params.join('&');

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || document.querySelector('meta[name="csrf-token"]')
                            ?.content
                    }
                });
                const json = await res.json();
                if (!json.summary) return;
                _oflData = json;

                const s = json.summary;
                S('ofl-total', s.total_requests ?? '—');
                S('ofl-gross', riyalsOfl(s.total_gross));
                S('ofl-comm', riyalsOfl(s.total_commission));
                S('ofl-net', riyalsOfl(s.total_net));
                S('ofl-completed', s.completed_requests ?? '—');

                // By-office table (API keys: name_ar, name_en, commission_rate, req_count, gross, commission, net, completed)
                const tbody = document.getElementById('ofl-by-office');
                if (tbody) {
                    if (!json.by_office?.length) {
                        tbody.innerHTML =
                            `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--t3)">لا توجد بيانات</td></tr>`;
                    } else {
                        tbody.innerHTML = json.by_office.map(o => `
          <tr>
            <td style="padding:.65rem .8rem;font-weight:700;">${esc(lang==='ar' ? o.name_ar : (o.name_en||o.name_ar))}</td>
            <td style="padding:.65rem .8rem;text-align:center;">${o.commission_rate ?? 0}%</td>
            <td style="padding:.65rem .8rem;text-align:center;">${o.req_count ?? 0}</td>
            <td style="padding:.65rem .8rem;">${riyalsOfl(o.gross)}</td>
            <td style="padding:.65rem .8rem;color:var(--blue);font-weight:700;">${riyalsOfl(o.commission)}</td>
            <td style="padding:.65rem .8rem;">${riyalsOfl(o.net)}</td>
            <td style="padding:.65rem .8rem;text-align:center;">${o.completed ?? 0}</td>
          </tr>`).join('');
                    }
                }

                // Monthly table (API keys: month, req_count, gross, commission)
                const mtbody = document.getElementById('ofl-monthly');
                if (mtbody) {
                    if (!json.monthly?.length) {
                        mtbody.innerHTML =
                            `<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--t3)">لا توجد بيانات</td></tr>`;
                    } else {
                        mtbody.innerHTML = json.monthly.map(m => `
          <tr>
            <td style="padding:.65rem .8rem;font-weight:700;">${esc(m.month)}</td>
            <td style="padding:.65rem .8rem;">${m.req_count ?? 0}</td>
            <td style="padding:.65rem .8rem;">${riyalsOfl(m.gross)}</td>
            <td style="padding:.65rem .8rem;color:var(--blue);font-weight:700;">${riyalsOfl(m.commission)}</td>
          </tr>`).join('');
                    }
                }
            } catch (e) {
                console.error('loadOfficeFinancial:', e);
            }
        }

        function riyalsOfl(v) {
            if (v == null || v === '' || v === '—') return '—';
            return Number(v).toLocaleString('ar-SA', {
                style: 'currency',
                currency: 'SAR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function esc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                '&quot;');
        }

        function exportOfficeFinancialCSV() {
            if (!_oflData) {
                if (window.AmrtmNotify) AmrtmNotify.error('يرجى تحميل البيانات أولاً'); else alert('يرجى تحميل البيانات أولاً');
                return;
            }
            const rows = (_oflData.by_office || []).map(o => ({
                'اسم المكتب': lang === 'ar' ? o.name_ar : (o.name_en || o.name_ar),
                'نسبة العمولة %': o.commission_rate,
                'إجمالي الطلبات': o.req_count,
                'إجمالي القيمة': o.gross,
                'عمولة المنصة': o.commission,
                'صافي للمكتب': o.net,
                'طلبات مكتملة': o.completed,
            }));
            if (!rows.length) {
                if (window.AmrtmNotify) AmrtmNotify.warning('لا توجد بيانات للتصدير'); else alert('لا توجد بيانات للتصدير');
                return;
            }
            exportCSV(rows, `office-financial-${new Date().toISOString().slice(0,10)}.csv`);
        }

        /* ══════════════════════════════════════════════════════════════
           FINANCE LEDGER (الحركة المالية)
        ══════════════════════════════════════════════════════════════ */
        let _finData = { transactions: [], pagination: {} };
        let _finPage = 1;
        const FIN_TYPE_META = {
            charge:  { ar: 'شحن رصيد',  en: 'Top-up',  cls: 'bg-emerald-700/10 text-emerald-700' },
            payment: { ar: 'خصم / دفع',  en: 'Payment', cls: 'bg-red-600/10 text-red-600' },
            refund:  { ar: 'استرداد',   en: 'Refund',  cls: 'bg-emerald-600/10 text-emerald-600' },
        };

        async function loadAdminFinance(page) {
            _finPage = Math.max(1, page || _finPage || 1);
            showLoader();
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const from = document.getElementById('fin-from')?.value || '';
                const to = document.getElementById('fin-to')?.value || '';
                const type = document.getElementById('fin-type')?.value || 'all';
                const search = document.getElementById('fin-search')?.value?.trim() || '';
                const params = new URLSearchParams({ page: _finPage });
                if (from) params.set('from', from);
                if (to) params.set('to', to);
                if (type && type !== 'all') params.set('type', type);
                if (search) params.set('search', search);

                const res = await fetch(`${base}/admin/finance?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF || '' },
                    credentials: 'same-origin',
                });
                const d = await res.json().catch(() => null);
                if (!res.ok || !d || !d.transactions) {
                    const tbody = document.getElementById('fin-tbody');
                    if (tbody) tbody.innerHTML =
                        `<tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">تعذّر تحميل الحركة المالية</td></tr>`;
                    return;
                }

                _finData = d;

                const s = d.summary || {};
                S('fin-total', riyalsOfl(s.total_revenue));
                S('fin-week', riyalsOfl(s.this_week));
                S('fin-avg', riyalsOfl(s.avg_order));
                S('fin-pend', riyalsOfl(s.pending));

                renderFinRows(d.transactions);
                renderFinPager(d.pagination);
            } catch (e) {
                console.error('loadAdminFinance:', e);
                showToast('تعذّر تحميل الحركة المالية', 'error');
            } finally {
                hideLoader();
            }
        }

        function renderFinRows(rows) {
            const tbody = document.getElementById('fin-tbody');
            if (!tbody) return;
            if (!rows.length) {
                tbody.innerHTML =
                    `<tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد معاملات مطابقة</td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map(t => {
                const m = FIN_TYPE_META[t.type] || { ar: t.type, en: t.type, cls: 'bg-slate-100 text-slate-700' };
                const desc = (lang === 'ar' ? t.description_ar : (t.description_en || t.description_ar)) || '—';
                const sign = t.type === 'payment' ? '−' : '+';
                const amt = riyalsOfl(t.amount);
                const stTxt = t.status === 'completed' ? (lang === 'ar' ? 'مكتمل' : 'Completed')
                    : (t.status === 'pending' ? (lang === 'ar' ? 'معلقة' : 'Pending')
                        : (lang === 'ar' ? 'فشل' : 'Failed'));
                const stCls = t.status === 'completed' ? 'bg-emerald-700/10 text-emerald-700'
                    : (t.status === 'pending' ? 'bg-yellow-600/10 text-yellow-600' : 'bg-red-600/10 text-red-600');
                const amtCls = t.type === 'payment' ? 'text-red-600' : (t.type === 'refund' ? 'text-emerald-600' : 'text-emerald-700');
                return `<tr class="border-b border-emerald-900/5">
                  <td class="px-3.5 py-2.5 font-mono text-xs font-bold text-slate-700">${esc(t.ref)}</td>
                  <td class="px-3.5 py-2.5">
                    <div class="font-bold text-slate-900">${esc(t.client)}</div>
                    ${t.email ? `<div class="text-xs text-slate-500">${esc(t.email)}</div>` : ''}
                  </td>
                  <td class="px-3.5 py-2.5 text-slate-700">${esc(desc)}</td>
                  <td class="px-3.5 py-2.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold ${m.cls}">${esc(m[lang] || m.ar)}</span></td>
                  <td class="whitespace-nowrap px-3.5 py-2.5 font-extrabold" dir="ltr"><span class="${amtCls}">${sign} ${amt.replace(/ر\.س\.?/g, 'ر.س')}</span></td>
                  <td class="px-3.5 py-2.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold ${stCls}">${esc(stTxt)}</span></td>
                  <td class="whitespace-nowrap px-3.5 py-2.5 text-xs text-slate-500">${esc((t.created_at || '').replace('T', ' '))}</td>
                </tr>`;
            }).join('');
        }

        function renderFinPager(pg) {
            const el = document.getElementById('fin-pager');
            if (!el) return;
            if (!pg || pg.last_page <= 1) {
                el.innerHTML = `<span class="text-xs text-slate-500">${pg?.total ?? 0} معاملة</span>`;
                return;
            }
            const cur = pg.page,
                last = pg.last_page;
            let html =
                `<span class="text-xs text-slate-500">صفحة ${cur} من ${last}</span><div class="flex flex-wrap gap-2">`;
            if (cur > 1) html +=
                `<x-ui.button type="button" onclick="loadAdminFinance(${cur - 1})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">السابق</x-ui.button>`;
            for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) {
                const on = i === cur;
                const cls = on
                    ? 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-600 bg-emerald-600 text-white'
                    : 'h-9 w-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent text-slate-700 hover:bg-emerald-600/5';
                html +=
                    `<x-ui.button type="button" onclick="loadAdminFinance(${i})" class="${cls} text-[13px] font-bold focus:ring-0!">${i}</x-ui.button>`;
            }
            if (cur < last) html +=
                `<x-ui.button type="button" onclick="loadAdminFinance(${cur + 1})" class="h-9 rounded-lg border-[1.5px] border-emerald-900/10 bg-transparent px-3 text-[13px] font-bold text-slate-700 transition hover:bg-emerald-600/5 focus:ring-0!">التالي</x-ui.button>`;
            el.innerHTML = html + '</div>';
        }

        function exportFinanceCSV() {
            if (!_finData.transactions?.length) {
                showToast('لا توجد بيانات للتصدير', 'warning');
                return;
            }
            const rows = _finData.transactions.map(t => ({
                'المرجع': t.ref,
                'العميل': t.client,
                'البريد': t.email,
                'الوصف': lang === 'ar' ? t.description_ar : (t.description_en || t.description_ar),
                'النوع': t.type,
                'المبلغ': t.amount,
                'الحالة': t.status,
                'التاريخ': t.created_at,
            }));
            exportCSV(rows, `finance-${new Date().toISOString().slice(0, 10)}.csv`);
        }

        /* ══════════════════════════════════════════════════════════════
           MANUAL BALANCE CHARGE (شحن رصيد يدوي)
        ══════════════════════════════════════════════════════════════ */
        let _mcUserId = null;
        let _mcResults = [];
        let _mcUsers = new Map();
        let _mcHi = -1;
        let _mcSearchTimer = null;
        let _mcSearchSeq = 0;

        function mcUnsetSelected() {
            _mcUserId = null;
            _mcHi = -1;
            const input = document.getElementById('mc-search');
            const caret = document.getElementById('mc-caret');
            const clearBtn = document.getElementById('mc-clear-btn');
            if (input) { input.style.borderColor = ''; input.style.background = ''; }
            if (caret) caret.style.display = '';
            if (clearBtn) clearBtn.style.display = 'none';
        }

        function mcCloseDropdown() {
            const el = document.getElementById('mc-results');
            if (el) el.style.display = 'none';
        }

        function mcOpenDropdown() {
            if (_mcUserId) return;
            const input = document.getElementById('mc-search');
            searchMchargeClients(input?.value?.trim() || '');
        }

        function mcSearchInput() {
            if (_mcUserId) mcUnsetSelected();
            clearTimeout(_mcSearchTimer);
            const input = document.getElementById('mc-search');
            _mcSearchTimer = setTimeout(() => searchMchargeClients(input?.value?.trim() || ''), 350);
        }

        function clearMcClient() {
            mcUnsetSelected();
            const input = document.getElementById('mc-search');
            if (input) { input.value = ''; input.focus(); }
            searchMchargeClients('');
        }

        function mcHighlight(dir) {
            if (!_mcResults.length) return;
            _mcHi = Math.min(_mcResults.length - 1, Math.max(0, (_mcHi < 0 ? 0 : _mcHi) + dir));
            renderMcResults(_mcResults);
        }

        function mcPickHighlighted() {
            const u = _mcResults[(_mcHi >= 0 ? _mcHi : 0)];
            if (u) pickMchargeClient(u.id);
        }

        function openManualCharge() {
            mcUnsetSelected();
            _mcResults = [];
            _mcUsers.clear();
            _mcHi = -1;
            const input = document.getElementById('mc-search');
            const results = document.getElementById('mc-results');
            const inner = document.getElementById('mc-results-inner');
            if (input) input.value = '';
            if (results) results.style.display = 'none';
            if (inner) inner.innerHTML = '';
            const amount = document.getElementById('mc-amount');
            const reason = document.getElementById('mc-reason');
            if (amount) amount.value = '';
            if (reason) reason.value = '';
            document.querySelector('#manual-charge-modal input[name="mc-type"][value="charge"]').checked = true;
            updateMcTypeUI();
            AMRTM_MODAL.open('manual-charge-modal');
            setTimeout(() => { input?.focus(); searchMchargeClients(''); }, 80);
        }

        function closeManualCharge() {
            AMRTM_MODAL.close('manual-charge-modal');
            mcUnsetSelected();
            _mcResults = [];
            mcCloseDropdown();
        }

        function renderMcResults(list) {
            const el = document.getElementById('mc-results');
            const inner = document.getElementById('mc-results-inner');
            if (!el || !inner) return;
            if (!list.length) {
                inner.innerHTML = `<div class="px-4 py-3 text-center text-[13px] text-slate-500">لا يوجد عملاء مطابقون</div>`;
                el.style.display = 'block';
                return;
            }
            inner.innerHTML = list.map((u, i) => `
                <x-ui.button type="button" onclick="pickMchargeClient(${u.id})"
                    class="w-full cursor-pointer border-0 border-b border-emerald-900/5 ${i === _mcHi ? 'bg-slate-50' : 'bg-white'} px-3.5 py-2.5 text-start transition-colors focus:ring-0!"
                    style="${i === _mcHi ? 'outline:2px solid var(--pri,#059669);outline-offset:-2px;' : ''}">
                    <span class="flex w-full items-center justify-between gap-2.5">
                        <span class="min-w-0">
                            <span class="block truncate text-[13px] font-bold text-slate-900">${esc(u.name)}</span>
                            <span class="block truncate text-xs text-slate-500">${esc(u.email || '')}${u.phone ? ' · ' + esc(u.phone) : ''}</span>
                        </span>
                        <span class="shrink-0 text-xs font-extrabold text-emerald-600">${riyalsOfl(u.balance)}</span>
                    </span>
                </x-ui.button>`).join('');
            el.style.display = 'block';
        }

        async function searchMchargeClients(q) {
            const seq = ++_mcSearchSeq;
            const trim = (q || '').trim();
            const el = document.getElementById('mc-results');
            const inner = document.getElementById('mc-results-inner');
            if (el) el.style.display = 'block';
            if (inner) inner.innerHTML = `<div style="padding:.9rem 1rem;text-align:center;font-size:13px;color:var(--t3);">جارٍ البحث...</div>`;
            try {
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const qs = trim ? `?search=${encodeURIComponent(trim)}` : '';
                const res = await fetch(`${base}/admin/users${qs}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.AMRTM_CSRF || '' },
                    credentials: 'same-origin',
                });
                const d = await res.json();
                if (seq !== _mcSearchSeq) return;
                const list = (d && d.data) || [];
                list.forEach(u => _mcUsers.set(u.id, u));
                _mcResults = list.slice(0, 8);
                _mcHi = _mcResults.length ? 0 : -1;
                renderMcResults(_mcResults);
            } catch (e) {
                if (seq !== _mcSearchSeq) return;
                console.error('searchMchargeClients:', e);
                if (inner) inner.innerHTML = `<div style="padding:.9rem 1rem;text-align:center;font-size:13px;color:#dc2626;">تعذّر البحث</div>`;
            }
        }

        function pickMchargeClient(id) {
            const u = _mcUsers.get(id) || _mcResults.find(x => x.id === id);
            if (!u) return;
            _mcUserId = id;
            const input = document.getElementById('mc-search');
            const caret = document.getElementById('mc-caret');
            const clearBtn = document.getElementById('mc-clear-btn');
            if (input) {
                input.value = u.name + (u.email ? ' — ' + u.email : '');
                input.style.borderColor = 'var(--pri)';
                input.style.background = 'var(--sur2)';
                input.blur();
            }
            if (caret) caret.style.display = 'none';
            if (clearBtn) clearBtn.style.display = '';
            mcCloseDropdown();
        }

        function updateMcTypeUI() {
            const isCharge = document.querySelector('#manual-charge-modal input[name="mc-type"]:checked')?.value === 'charge';
            const btn = document.getElementById('mc-submit-btn');
            if (btn) btn.style.background = isCharge ?
                'linear-gradient(135deg,#047857,#2E7D32)' : 'linear-gradient(135deg,#dc2626,#D32F2F)';
        }

        async function doManualCharge() {
            if (!_mcUserId) {
                showToast('اختر العميل أولاً — ابحث ثم اضغط على اسمه', 'warning');
                return;
            }
            const amount = parseFloat(document.getElementById('mc-amount')?.value);
            if (!amount || amount <= 0) {
                showToast('يرجى إدخال مبلغ صحيح', 'warning');
                return;
            }
            const type = document.querySelector('#manual-charge-modal input[name="mc-type"]:checked')?.value;
            const reason = document.getElementById('mc-reason')?.value?.trim() || null;
            try {
                showLoader();
                const base = (window.AMRTM_API_BASE || '/api').replace(/\/$/, '');
                const res = await fetch(`${base}/admin/users/${_mcUserId}/balance`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.AMRTM_CSRF || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ amount, type, reason }),
                });
                const d = await res.json();
                if (!res.ok) {
                    showToast(d.message || 'حدث خطأ', 'error');
                    return;
                }
                showToast(d.message || 'تم', 'success');
                closeManualCharge();
                loadAdminFinance(1);
            } catch (e) {
                console.error('doManualCharge:', e);
                showToast('حدث خطأ', 'error');
            } finally {
                hideLoader();
            }
        }

        document.getElementById('manual-charge-modal')?.addEventListener('click', e => {
            if (e.target === document.getElementById('manual-charge-modal')) closeManualCharge();
        });

        document.addEventListener('click', e => {
            const combo = document.getElementById('mc-combo');
            if (combo && !combo.contains(e.target)) mcCloseDropdown();
        });

        /* ══════════════════════════════════════════════════════════════
           COLOR PICKER
        ══════════════════════════════════════════════════════════════ */
        (function() {
            const PALETTE = [{
                    n: 'نيلي',
                    h: '#059669',
                    r: '26,35,126'
                },
                {
                    n: 'أزرق',
                    h: '#16a34a',
                    r: '21,101,192'
                },
                {
                    n: 'سماوي',
                    h: '#0277BD',
                    r: '2,119,189'
                },
                {
                    n: 'فيروزي',
                    h: '#00695C',
                    r: '0,105,92'
                },
                {
                    n: 'أخضر',
                    h: '#047857',
                    r: '27,94,32'
                },
                {
                    n: 'أخضر فاتح',
                    h: '#2E7D32',
                    r: '46,125,50'
                },
                {
                    n: 'أحمر',
                    h: '#dc2626',
                    r: '198,40,40'
                },
                {
                    n: 'برتقالي',
                    h: '#E65100',
                    r: '230,81,0'
                },
                {
                    n: 'ذهبي',
                    h: '#F57F17',
                    r: '245,127,23'
                },
                {
                    n: 'بنفسجي',
                    h: '#6A1B9A',
                    r: '106,27,154'
                },
                {
                    n: 'وردي',
                    h: '#880E4F',
                    r: '136,14,79'
                },
                {
                    n: 'رمادي',
                    h: '#37474F',
                    r: '55,71,79'
                },
            ];

            function initCP(rowId, labelId, colorId, bgId) {
                const row = document.getElementById(rowId);
                const lbl = document.getElementById(labelId);
                const cin = document.getElementById(colorId);
                const bin = document.getElementById(bgId);
                if (!row) return;
                PALETTE.forEach(function(c) {
                    const sw = document.createElement('div');
                    sw.className = 'cp-swatch';
                    sw.style.background = c.h;
                    sw.title = c.n;
                    sw.addEventListener('click', function() {
                        row.querySelectorAll('.cp-swatch').forEach(b => b.classList.remove('on'));
                        sw.classList.add('on');
                        cin.value = c.h;
                        bin.value = 'rgba(' + c.r + ',.1)';
                        if (lbl) lbl.textContent = '✓ ' + c.n;
                    });
                    row.appendChild(sw);
                });
            }

            window.cpReset = function(rowId, labelId) {
                const row = document.getElementById(rowId);
                const lbl = document.getElementById(labelId);
                if (row) row.querySelectorAll('.cp-swatch').forEach(b => b.classList.remove('on'));
                if (lbl) lbl.textContent = '';
            };

            document.addEventListener('DOMContentLoaded', function() {
                initCP('cat-cp-row', 'cat-cp-label', 'cat-color', 'cat-bg');
                initCP('ent-cp-row', 'ent-cp-label', 'ent-color', 'ent-bg');
            });
        })();



        function previewEntityImage(input) {

            const preview = document.getElementById('ent-image-preview');

            if (!input.files.length) {

                preview.src = '';
                preview.style.display = 'none';
                return;

            }

            const reader = new FileReader();

            reader.onload = function(e) {

                preview.src = e.target.result;
                preview.style.display = 'block';

            };

            reader.readAsDataURL(input.files[0]);

        }

        async function loadOfficeSpecialties(type = '') {

            try {

                let url = '/amrtm/office/specialties';

                if (type) {
                    url += '?office_type=' + encodeURIComponent(type);
                }

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'فشل تحميل التخصصات'
                    );
                }

                const list = document.getElementById(
                    'office-specialties-list'
                );

                if (!list) return;

                if (!result.data?.length && !Array.isArray(result)) {
                    list.innerHTML = `
                <div class="cat-empty">
                    لا توجد تخصصات
                </div>
            `;
                    return;
                }

                const specialties = Array.isArray(result) ?
                    result :
                    result.data;

                if (!specialties.length) {
                    list.innerHTML = `
                <div class="cat-empty">
                    لا توجد تخصصات
                </div>
            `;
                    return;
                }

                const officeTypes = {
                    law: 'محاماة',
                    services: 'تعقيب وخدمات',
                    customs: 'جمارك',
                    accounting: 'محاسبين',
                    engineering: 'هندسة',
                    freelance: 'أصحاب مهن'
                };

                list.innerHTML = specialties.map(s => `
            <div class="cat-row"
                 style="grid-template-columns:140px 1fr 1fr 80px 120px;">

                <div>
                    ${officeTypes[s.office_type] || s.office_type}
                </div>

                <div>
                    ${s.name_ar || '-'}
                </div>

                <div>
                    ${s.name_en || '-'}
                </div>

                <div>
                    ${s.is_active
                        ? '<span class="status-active">نشط</span>'
                        : '<span class="status-inactive">موقوف</span>'
                    }
                </div>

                <div>
                    <x-ui.button type="button"
                        class="btn-sm focus:ring-0!"
                        onclick="deleteOfficeSpecialty(${s.id})">
                        <i class="ti ti-trash"></i>
                    </x-ui.button>
                </div>

            </div>
        `).join('');

            } catch (error) {

                console.error(error);

                const list = document.getElementById(
                    'office-specialties-list'
                );

                if (list) {
                    list.innerHTML = `
                <div class="cat-empty">
                    حدث خطأ أثناء تحميل التخصصات
                </div>
            `;
                }
            }
        }

        function renderOfficeSpecialties(data) {

            const container =
                document.getElementById('office-specialties-list');

            if (!container) return;

            if (!data || !data.length) {

                container.innerHTML = `
            <div class="cat-empty">
                لا توجد تخصصات حتى الآن
            </div>
        `;

                return;
            }


            const officeTypes = {

                law: 'محاماة',

                services: 'تعقيب وخدمات',

                customs: 'جمارك',

                accounting: 'محاسبين',

                engineering: 'هندسة',

                freelance: 'أصحاب مهن'

            };


            container.innerHTML = data.map(specialty => `

        <div
            class="cat-row"
            style="
                grid-template-columns:
                140px
                1fr
                1fr
                80px
                120px;
            "
        >

            <div>
                ${officeTypes[specialty.office_type] || specialty.office_type}
            </div>


            <div style="font-weight:700;">
                ${escapeHtml(specialty.name_ar)}
            </div>


            <div>
                ${escapeHtml(specialty.name_en || '-')}
            </div>


            <div>

                <span class="status-badge ${
                    specialty.is_active
                        ? 'active'
                        : 'inactive'
                }">

                    ${
                        specialty.is_active
                            ? 'نشط'
                            : 'متوقف'
                    }

                </span>

            </div>


            <div
                style="
                    display:flex;
                    gap:5px;
                    justify-content:flex-start;
                "
            >

                <x-ui.button
                    type="button"
                    class="btn-icon focus:ring-0!"
                    onclick="toggleOfficeSpecialty(${specialty.id})"
                    title="تغيير الحالة"
                >
                    <i class="ti ti-power"></i>
                </x-ui.button>

            </div>

        </div>

    `).join('');
        }

        function escapeHtml(value) {

            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        async function createOfficeSpecialty() {

            const officeType =
                document.getElementById('specialty-office-type').value;

            const nameAr =
                document.getElementById('specialty-name-ar').value.trim();

            const nameEn =
                document.getElementById('specialty-name-en').value.trim();


            if (!officeType) {

                if (window.AmrtmNotify) AmrtmNotify.warning('اختر نوع المكتب'); else alert('اختر نوع المكتب');

                return;
            }


            if (!nameAr) {

                if (window.AmrtmNotify) AmrtmNotify.warning('اكتب اسم التخصص بالعربي'); else alert('اكتب اسم التخصص بالعربي');

                return;
            }


            try {

                const response = await fetch(
                    '/amrtm/office/specialties', {
                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-Requested-With': 'XMLHttpRequest',

                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).getAttribute('content')

                        },

                        body: JSON.stringify({

                            office_type: officeType,

                            name_ar: nameAr,

                            name_en: nameEn || null,

                            is_active: true

                        })

                    }
                );


                const result =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        result.message ||
                        'حدث خطأ أثناء الحفظ'
                    );

                }


                if (window.AmrtmNotify) AmrtmNotify.success('تم إضافة التخصص بنجاح'); else alert('تم إضافة التخصص بنجاح');


                document.getElementById(
                    'specialty-name-ar'
                ).value = '';


                document.getElementById(
                    'specialty-name-en'
                ).value = '';


                loadOfficeSpecialties(
                    document.getElementById(
                        'specialty-filter-type'
                    ).value
                );


            } catch (error) {

                console.error(error);

                if (window.AmrtmNotify) AmrtmNotify.error(error.message); else alert(error.message);

            }
        }

        async function deleteOfficeSpecialty(id) {

            if (!confirm('هل أنت متأكد من حذف هذا التخصص؟')) {
                return;
            }

            try {

                const response = await fetch(
                    `/amrtm/office/specialties/${id}`, {
                        method: 'DELETE',

                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).getAttribute('content')
                        }
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'حدث خطأ أثناء حذف التخصص'
                    );
                }

                if (window.AmrtmNotify) AmrtmNotify.success(result.message || 'تم حذف التخصص بنجاح'); else alert(result.message || 'تم حذف التخصص بنجاح');

                loadOfficeSpecialties(
                    document.getElementById(
                        'specialty-filter-type'
                    ).value
                );

            } catch (error) {

                console.error(error);

                if (window.AmrtmNotify) AmrtmNotify.error(error.message || 'حدث خطأ أثناء الحذف'); else alert(error.message || 'حدث خطأ أثناء الحذف');
            }
        }
        /* ══════════════════════════════════════════════════════════════
           CSV EXPORT HELPER
        ══════════════════════════════════════════════════════════════ */
        function exportCSV(data, filename) {
            if (!data.length) return;
            const keys = Object.keys(data[0]);
            const csv = [keys.join(','), ...data.map(row =>
                keys.map(k => JSON.stringify(row[k] ?? '')).join(',')
            )].join('\n');
            const blob = new Blob(['﻿' + csv], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            URL.revokeObjectURL(url);
        }

        /* ══════════════════════════════════════════════════════════════
           OFFICE CUSTOM SERVICES APPROVAL
        ══════════════════════════════════════════════════════════════ */
        async function loadOfficeApprovals() {
            const list = document.getElementById('off-apr-list');
            if (!list) return;

            list.innerHTML = '<div class="cat-empty" style="padding:36px">جارٍ تحميل الطلبات...</div>';

            try {
                const response = await fetch('/api/admin/office-services/pending', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'فشل تحميل الطلبات');

                const pact = document.getElementById('off-apr-count');
                if (pact) { pact.textContent = result.length; }
                const wrap = document.getElementById('off-apr-count-wrap');
                if (wrap) wrap.style.display = result.length ? '' : 'none';

                renderOfficeApprovals(result);
            } catch (error) {
                console.error(error);
                list.innerHTML = '<div class="cat-empty">حدث خطأ أثناء تحميل الطلبات</div>';
            }
        }

        function renderSvcFieldDefs(fields) {
            if (!Array.isArray(fields) || !fields.length) return '';
            const typeLabels = {
                text: 'نص قصير', textarea: 'نص طويل', number: 'رقم', email: 'بريد إلكتروني',
                tel: 'رقم جوال', date: 'تاريخ', select: 'قائمة منسدلة', radio: 'اختيار واحد',
                checkbox: 'مربع اختيار', file: 'رفع ملف'
            };
            return `
                <div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                    <div class="mb-2 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> الحقول المخصصة</div>
                    <div class="grid grid-cols-1 gap-1.5">
                        ${fields.map(f => `
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-[--t1]">${escapeHtml(f.label_ar || f.key || '')}</span>
                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500">${typeLabels[f.type] || f.type || ''}</span>
                                ${f.required ? `<span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-500">مطلوب</span>` : ''}
                                ${f.options?.length ? `<span class="text-[10px] text-[--t3]">(${f.options.length} خيار${f.options.length === 1 ? '' : 'ات'})</span>` : ''}
                            </div>`).join('')}
                    </div>
                </div>`;
        }

        function renderOfficeApprovals(data) {
            const container = document.getElementById('off-apr-list');
            if (!container) return;

            if (!data.length) {
                container.innerHTML = `
                    <div class="rounded-2xl bg-white p-10 shadow-[--sh]" style="text-align:center">
                        <i class="ti ti-badge-check text-4xl" style="color:var(--pri)"></i>
                        <p class="mt-3 text-sm font-bold text-[--t2]">لا توجد خدمات بانتظار الموافقة الآن</p>
                        <p class="mt-1 text-xs text-[--t3]">كل الخدمات المخصصة الجديدة من المكاتب ستظهر هنا للمراجعة</p>
                    </div>`;
                return;
            }

            container.innerHTML = data.map(svc => `
                <div class="mb-4 rounded-2xl bg-white p-5 shadow-[--sh]" style="transition:all .3s ease">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-black text-[--t1]">${escapeHtml(svc.name_ar)}</span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">${escapeHtml(svc.name_en || '')}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-600">
                                    <i class="ti ti-clock"></i> بانتظار الموافقة
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[--t3]">
                                <span class="inline-flex items-center gap-1"><i class="ti ti-building"></i> ${escapeHtml(svc.office_name || '—')}</span>
                                ${svc.specialty_name ? `<span class="inline-flex items-center gap-1"><i class="ti ti-adjustments"></i> ${escapeHtml(svc.specialty_name)}</span>` : ''}
                                ${svc.entity_name ? `<span class="inline-flex items-center gap-1"><i class="ti ti-building-bank"></i> ${escapeHtml(svc.entity_name)}</span>` : ''}
                                <span class="inline-flex items-center gap-1"><i class="ti ti-cash"></i> ${svc.price} ر.س</span>
                                ${svc.duration ? `<span class="inline-flex items-center gap-1"><i class="ti ti-clock-hour"></i> ${escapeHtml(svc.duration)}</span>` : ''}
                                <span class="inline-flex items-center gap-1"><i class="ti ti-calendar"></i> ${escapeHtml(svc.created_at || '')}</span>
                            </div>
                            ${svc.description_ar ? `<p class="mt-2 text-xs leading-6 text-[--t3]"><i class="ti ti-align-justified"></i> ${escapeHtml(svc.description_ar)}</p>` : ''}
                            ${renderSvcFieldDefs(svc.custom_fields) || (svc.requirements ? `
                                <div class="mt-3 rounded-xl border border-[--b1] bg-[--sur2] p-3">
                                    <div class="mb-1 text-[11px] font-bold text-[--t2]"><i class="ti ti-list-check"></i> المتطلبات</div>
                                    <div class="whitespace-pre-wrap text-xs leading-6 text-[--t3]">${escapeHtml(svc.requirements)}</div>
                                </div>` : '')}
                            <div id="reject-box-${svc.id}" style="display:none;margin-top:12px">
                                ${xuiLabel('سبب الرفض (يظهر للمكتب)')}
                                ${xuiTextarea('', `id="reject-reason-${svc.id}" rows="2" placeholder="اكتب سبب الرفض بوضوح..."`)}
                                <div class="mt-2 flex gap-2" style="direction:ltr">
                                    <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-red-600 px-4 text-xs font-bold text-white hover:bg-red-700"
                                        onclick="confirmRejectOfficeService(${svc.id})">
                                        <i class="ti ti-x"></i> تأكيد الرفض
                                    </x-ui.button>
                                    <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-slate-100 px-4 text-xs font-bold text-slate-600 hover:bg-slate-200"
                                        onclick="toggleRejectBox(${svc.id}, false)">
                                        إلغاء
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg bg-[--pri] px-4 text-xs font-bold text-white shadow-sm hover:opacity-90"
                                onclick="approveSelectedService(${svc.id})">
                                <i class="ti ti-check"></i> موافقة
                            </x-ui.button>
                            <x-ui.button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-red-200 px-4 text-xs font-bold text-red-600 hover:bg-red-50"
                                onclick="toggleRejectBox(${svc.id}, true)">
                                <i class="ti ti-x"></i> رفض
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toggleRejectBox(id, show) {
            const box = document.getElementById('reject-box-' + id);
            if (box) box.style.display = show ? '' : 'none';
            if (show === false) {
                const ta = document.getElementById('reject-reason-' + id);
                if (ta) ta.value = '';
            }
        }

        async function approveSelectedService(id) {
            try {
                const response = await fetch('/api/admin/office-services/' + id + '/approve', {
                    method: 'PUT',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'فشل الموافقة');
                if (typeof showToast !== 'undefined') showToast(result.message || 'تمت الموافقة', 'success');
                loadOfficeApprovals();
            } catch (error) {
                console.error(error);
                if (typeof showToast !== 'undefined') showToast(error.message, 'error');
            }
        }

        async function confirmRejectOfficeService(id) {
            const reason = (document.getElementById('reject-reason-' + id)?.value || '').trim();
            if (!reason) {
                if (typeof showToast !== 'undefined') showToast('أدخل سبب الرفض أولاً', 'error');
                return;
            }
            try {
                const response = await fetch('/api/admin/office-services/' + id + '/reject', {
                    method: 'PUT',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason })
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'فشل الرفض');
                if (typeof showToast !== 'undefined') showToast(result.message || 'تم الرفض', 'error');
                loadOfficeApprovals();
            } catch (error) {
                console.error(error);
                if (typeof showToast !== 'undefined') showToast(error.message, 'error');
            }
        }

        /* RUN - deferred to end of script so all let declarations have initialized */
        init();
    </script>
    <script>
        window.ICON_PICKER_API = '{{ route('amrtm.api.admin.icons.list') }}';
    </script>
    <script src="{{ asset('js/platform-business/icon-picker.js') }}"></script>

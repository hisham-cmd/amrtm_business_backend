@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                <div class="page" id="page-permissions">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]" id="perms-pg-ttl">إدارة الصلاحيات</div>
                            <div class="mt-1 text-sm text-[--t3]" id="perms-pg-sub">منح وسحب صلاحيات المدراء</div>
                        </div>
                        <x-ui.button type="button" variant="primary"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-[--pri]! px-4 text-sm font-bold! text-white shadow-sm transition hover:bg-[--pri2]! focus:outline-none focus:ring-2! focus:ring-[--b2]!"
                            onclick="showCreateAdminModal()"><i class="ti ti-user-plus"></i><span>إضافة مدير</span></x-ui.button>
                    </div>
                    <div class="overflow-hidden rounded-2xl bg-white shadow-[--sh]">
                        <div id="admins-list">
                            <div class="px-4 py-12 text-center text-sm text-[--t3]">جارٍ التحميل...</div>
                        </div>
                    </div>
                </div>


@endsection


@extends('update_service.dashboard.admin.layout')

@section('admin-content')
                <div class="page" id="page-settings">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-black text-[--t1]" id="set-ttl">الإعدادات</div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-[--sh] sm:max-w-[520px]">
                        <div class="mb-6 text-sm font-black text-[--t1]" id="set-profile-ttl">الملف الشخصي</div>
                        <div class="space-y-4">
                            <div>
                                <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!" id="set-lbl-nm">الاسم</x-ui.label>
                                <x-ui.input type="text" value="مدير النظام"
                                    class="h-11 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                            </div>
                            <div>
                                <x-ui.label class="mb-1.5 block text-xs! font-bold! text-[--t2]!" id="set-lbl-em">البريد الإلكتروني</x-ui.label>
                                <x-ui.input type="email" value="admin@amrtm.com.sa"
                                    class="h-11 w-full rounded-lg border border-[--b1]! bg-white px-3! text-sm text-[--t1]! focus:outline-none focus:ring-2! focus:ring-[--b2]! focus:border-[--b1]!" />
                            </div>
                            <x-ui.button class="btn-pri" style="align-self:flex-start;" id="set-save"><i
                                    class="ti ti-check"></i><span id="set-save-l">حفظ التغييرات</span></x-ui.button>
                        </div>
                    </div>
                </div>

@endsection


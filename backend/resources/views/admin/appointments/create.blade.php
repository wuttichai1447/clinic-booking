@extends('admin.layout')
@section('title', 'จองแทนลูกค้า')
@section('content')
<div class="mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold">จองแทนลูกค้า</h1>
    <p class="text-sm text-slate-600 mt-1">
        แนะนำ <strong>ชำระหน้าร้านแล้ว</strong> สำหรับ walk-in / โทรจองที่จ่ายเงินแล้ว —
        เลือก <strong>รอชำระทีหลัง</strong> เมื่อลูกค้าจะจ่ายออนไลน์หรือโอนภายหลัง
    </p>
</div>

<form method="POST" action="{{ route('admin.appointments.store') }}" id="admin-booking-form" class="bg-white rounded-xl border p-5 sm:p-6 max-w-2xl space-y-5 shadow-sm">
    @csrf

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold text-slate-800">นัดหมาย</legend>
        <div>
            <label class="text-sm font-medium">คลินิก</label>
            <select name="clinic_id" id="clinic_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
                <option value="">— เลือกคลินิก —</option>
                @foreach ($clinics as $c)
                    <option value="{{ $c->id }}" @selected(old('clinic_id') === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">บริการ</label>
                <select name="service_id" id="service_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
                    <option value="">— เลือกคลินิกก่อน —</option>
                    @foreach ($services as $s)
                        <option
                            value="{{ $s->id }}"
                            data-clinic-id="{{ $s->clinic_id }}"
                            data-price="{{ $s->price }}"
                            data-duration="{{ $s->duration }}"
                            @selected(old('service_id') === $s->id)
                            hidden
                            disabled
                        >{{ $s->name }} (฿{{ number_format($s->price) }})</option>
                    @endforeach
                </select>
                <p id="service-price" class="text-xs text-slate-500 mt-1"></p>
                <p id="service-hint" class="text-xs text-amber-600 mt-1 hidden"></p>
            </div>
            <div>
                <label class="text-sm font-medium">นักบำบัด</label>
                <select name="therapist_id" id="therapist_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
                    <option value="">— เลือกคลินิกก่อน —</option>
                    @foreach ($therapists as $t)
                        <option
                            value="{{ $t->id }}"
                            data-clinic-id="{{ $t->clinic_id }}"
                            @selected(old('therapist_id') === $t->id)
                            hidden
                            disabled
                        >{{ $t->name }}</option>
                    @endforeach
                </select>
                <p id="therapist-hint" class="text-xs text-slate-500 mt-1"></p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">วันที่</label>
                <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
            </div>
            <div>
                <label class="text-sm font-medium">เวลา</label>
                <select name="time_slot_id" id="time_slot_id" class="w-full border rounded-lg px-3 py-2 mt-1" required>
                    <option value="">— เลือกวันที่และนักบำบัด —</option>
                </select>
                <p id="slots-hint" class="text-xs text-slate-500 mt-1"></p>
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">รหัสโปรโมชั่น (ไม่บังคับ)</label>
            <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น SUMMER10">
        </div>
    </fieldset>

    <fieldset class="space-y-4 border-t pt-5">
        <legend class="text-sm font-semibold text-slate-800">ข้อมูลลูกค้า</legend>
        <div>
            <label class="text-sm font-medium">ชื่อ-นามสกุล</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">เบอร์โทร</label>
                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full border rounded-lg px-3 py-2 mt-1" required placeholder="08xxxxxxxx">
            </div>
            <div>
                <label class="text-sm font-medium">อีเมล (ไม่บังคับ)</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full border rounded-lg px-3 py-2 mt-1">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">หมายเหตุ</label>
            <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="เช่น จองทางโทรศัพท์">{{ old('notes') }}</textarea>
        </div>
    </fieldset>

    <fieldset class="space-y-3 border-t pt-5">
        <legend class="text-sm font-semibold text-slate-800">การชำระเงิน</legend>
        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
            <input type="radio" name="payment_mode" value="counter" class="mt-1" @checked(old('payment_mode', 'counter') === 'counter')>
            <span>
                <span class="font-medium block">ชำระหน้าร้านแล้ว</span>
                <span class="text-xs text-slate-600">ยืนยันการจองทันที — เหมาะ walk-in / รับเงินสด–บัตรแล้ว</span>
            </span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
            <input type="radio" name="payment_mode" value="later" class="mt-1" @checked(old('payment_mode') === 'later')>
            <span>
                <span class="font-medium block">รอชำระทีหลัง</span>
                <span class="text-xs text-slate-600">สถานะรอชำระ — ลูกค้าจ่ายออนไลน์/โอนที่หน้าเว็บ ({{ $frontendUrl }})</span>
            </span>
        </label>
        <div id="counter-method-wrap" class="pl-1">
            <label class="text-sm font-medium">วิธีชำระหน้าร้าน</label>
            <select name="counter_method" class="w-full sm:w-auto border rounded-lg px-3 py-2 mt-1">
                <option value="cash" @selected(old('counter_method', 'cash') === 'cash')>เงินสด</option>
                <option value="card" @selected(old('counter_method') === 'card')>บัตร</option>
                <option value="promptpay" @selected(old('counter_method') === 'promptpay')>พร้อมเพย์</option>
            </select>
        </div>
    </fieldset>

    @include('admin.partials.form-actions', [
        'cancelUrl' => route('admin.appointments.index'),
        'submitLabel' => 'บันทึกการจอง',
        'cancelLabel' => 'ยกเลิกและกลับรายการจอง',
    ])
</form>
@endsection

@push('scripts')
<script>
(function () {
    const slotsUrl = @json($slotsUrl);
    const clinicEl = document.getElementById('clinic_id');
    const serviceEl = document.getElementById('service_id');
    const therapistEl = document.getElementById('therapist_id');
    const dateEl = document.getElementById('date');
    const slotEl = document.getElementById('time_slot_id');
    const priceEl = document.getElementById('service-price');
    const serviceHint = document.getElementById('service-hint');
    const therapistHint = document.getElementById('therapist-hint');
    const slotsHint = document.getElementById('slots-hint');
    const counterWrap = document.getElementById('counter-method-wrap');
    const paymentRadios = document.querySelectorAll('input[name="payment_mode"]');
    const oldSlot = @json(old('time_slot_id'));

    /** @type {Map<string, number>} therapistId -> available slot count */
    const therapistSlotCounts = new Map();

    function optionMatchesClinic(opt, clinicId, sharedServices) {
        if (!clinicId) return false;
        const cid = opt.dataset.clinicId || '';
        if (sharedServices) {
            return cid === '' || cid === clinicId;
        }
        return cid === clinicId;
    }

    function filterOptions(selectEl, clinicId, placeholderEmpty, sharedServices) {
        let visible = 0;
        const current = selectEl.value;

        selectEl.querySelectorAll('option[data-clinic-id]').forEach(function (opt) {
            const match = optionMatchesClinic(opt, clinicId, sharedServices);
            opt.hidden = !match;
            opt.disabled = !match;
            if (match) visible++;
        });

        const stillValid = current && [...selectEl.options].some(function (o) {
            return o.value === current && !o.disabled && !o.hidden;
        });
        if (!stillValid) {
            selectEl.value = '';
        }

        const first = selectEl.querySelector('option[value=""]');
        if (first) {
            first.textContent = !clinicId
                ? '— เลือกคลินิกก่อน —'
                : (visible ? '— เลือก —' : placeholderEmpty);
        }

        return visible;
    }

    function onClinicChange() {
        const clinicId = clinicEl.value;
        const serviceCount = filterOptions(serviceEl, clinicId, '— ไม่มีบริการในคลินิกนี้ —', true);
        const therapistCount = filterOptions(therapistEl, clinicId, '— ไม่มีนักบำบัดในคลินิกนี้ —', false);

        serviceHint.classList.toggle('hidden', serviceCount > 0 || !clinicId);
        serviceHint.textContent = serviceCount === 0 && clinicId
            ? 'ยังไม่มีบริการ — เพิ่มที่เมนู บริการ (หรือตั้งเป็นบริการร่วมทุกสาขา)'
            : '';

        therapistHint.textContent = clinicId
            ? `แสดงนักบำบัดทั้งหมด ${therapistCount} คนในคลินิกนี้`
            : '';

        onServiceChange();
        refreshTherapistLabels();
        loadSlots();
    }

    function onServiceChange() {
        const opt = serviceEl.selectedOptions[0];
        if (!opt || !opt.dataset.price) {
            priceEl.textContent = '';
            return;
        }
        const price = Number(opt.dataset.price);
        const duration = opt.dataset.duration;
        priceEl.textContent = `ราคา ฿${price.toLocaleString('th-TH')} · ${duration} นาที`;
    }

    async function refreshTherapistLabels() {
        const clinicId = clinicEl.value;
        const date = dateEl.value;
        therapistSlotCounts.clear();

        if (!clinicId || !date) {
            therapistEl.querySelectorAll('option[data-clinic-id]').forEach(function (opt) {
                if (!opt.hidden) {
                    const base = opt.textContent.replace(/\s*\(.*\)$/, '');
                    opt.textContent = base;
                }
            });
            return;
        }

        const therapists = [...therapistEl.querySelectorAll('option[data-clinic-id]')].filter(function (o) {
            return !o.hidden && o.dataset.clinicId === clinicId;
        });

        await Promise.all(therapists.map(async function (opt) {
            const base = opt.textContent.replace(/\s*\(.*\)$/, '');
            try {
                const q = new URLSearchParams({ therapistId: opt.value, date: date, clinicId: clinicId });
                const res = await fetch(`${slotsUrl}?${q}`);
                const slots = await res.json();
                const count = slots.filter(function (s) { return s.available; }).length;
                therapistSlotCounts.set(opt.value, count);
                opt.textContent = count > 0
                    ? `${base} (${count} ช่วงว่าง)`
                    : `${base} (ไม่ว่าง/ลา/วันหยุด)`;
            } catch (e) {
                opt.textContent = base;
            }
        }));
    }

    async function loadSlots() {
        const therapistId = therapistEl.value;
        const date = dateEl.value;
        const clinicId = clinicEl.value;

        slotEl.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';

        if (!therapistId || !date) {
            placeholder.textContent = '— เลือกวันที่และนักบำบัด —';
            slotEl.appendChild(placeholder);
            slotsHint.textContent = '';
            return;
        }

        placeholder.textContent = 'กำลังโหลด...';
        slotEl.appendChild(placeholder);

        try {
            const q = new URLSearchParams({ therapistId: therapistId, date: date, clinicId: clinicId });
            const res = await fetch(`${slotsUrl}?${q}`);
            const slots = await res.json();
            const available = slots.filter(function (s) { return s.available; });

            slotEl.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = available.length ? '— เลือกเวลา —' : '— ไม่มีช่วงเวลาว่าง —';
            slotEl.appendChild(opt0);

            available.forEach(function (s) {
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = s.time;
                if (oldSlot === s.id) o.selected = true;
                slotEl.appendChild(o);
            });

            slotsHint.textContent = available.length
                ? `${available.length} ช่วงเวลาว่างสำหรับนักบำบัดที่เลือก`
                : 'ลองนักบำบัดคนอื่นหรือเปลี่ยนวันที่';
        } catch (e) {
            slotEl.innerHTML = '';
            const err = document.createElement('option');
            err.value = '';
            err.textContent = 'โหลดช่วงเวลาไม่สำเร็จ';
            slotEl.appendChild(err);
        }
    }

    function toggleCounterMethod() {
        const counter = document.querySelector('input[name="payment_mode"]:checked')?.value === 'counter';
        counterWrap.style.display = counter ? 'block' : 'none';
    }

    clinicEl.addEventListener('change', onClinicChange);
    serviceEl.addEventListener('change', onServiceChange);
    therapistEl.addEventListener('change', loadSlots);
    dateEl.addEventListener('change', function () {
        refreshTherapistLabels();
        loadSlots();
    });

    paymentRadios.forEach(function (r) {
        r.addEventListener('change', toggleCounterMethod);
    });

    toggleCounterMethod();
    onClinicChange();
})();
</script>
@endpush

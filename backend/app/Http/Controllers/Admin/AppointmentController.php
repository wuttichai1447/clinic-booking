<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Services\AdminBookingService;
use App\Services\AppointmentLifecycleService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
        protected AppointmentLifecycleService $lifecycle,
        protected AdminBookingService $adminBooking,
    ) {}

    public function create(): View
    {
        $clinics = Clinic::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('is_active', true)
            ->with('clinic:id,name')
            ->orderBy('name')
            ->get();

        $therapists = Therapist::query()
            ->where('is_active', true)
            ->with('clinic:id,name')
            ->orderBy('name')
            ->get();

        return view('admin.appointments.create', [
            'clinics' => $clinics,
            'services' => $services,
            'therapists' => $therapists,
            'serviceMeta' => $services->mapWithKeys(fn (Service $s) => [
                $s->id => [
                    'price' => $s->price,
                    'duration' => $s->duration,
                    'clinicId' => $s->clinic_id,
                ],
            ]),
            'slotsUrl' => url('/api/v1/slots'),
            'frontendUrl' => config('app.frontend_url', 'http://localhost:3000'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'clinic_id' => 'required|string|exists:clinics,id',
            'service_id' => 'required|string|exists:services,id',
            'therapist_id' => 'required|string|exists:therapists,id',
            'date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'promo_code' => 'nullable|string|max:50',
            'payment_mode' => 'required|in:counter,later',
            'counter_method' => 'required_if:payment_mode,counter|nullable|in:cash,card,promptpay',
        ]);

        try {
            $appointment = $this->adminBooking->create($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $msg = $data['payment_mode'] === 'counter'
            ? 'จองแทนลูกค้าสำเร็จ — ชำระหน้าร้านแล้ว (ยืนยันการจอง)'
            : 'จองแทนลูกค้าสำเร็จ — รอลูกค้าชำระเงิน (สถานะ awaiting_payment)';

        return redirect()
            ->route('admin.appointments.edit', $appointment)
            ->with('success', $msg);
    }

    public function index(Request $request): View
    {
        $query = Appointment::with(['clinic', 'service', 'therapist'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('phone')) {
            $query->where('customer_phone', 'like', '%'.$request->string('phone').'%');
        }

        return view('admin.appointments.index', [
            'appointments' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'status' => $request->string('status'),
            'phone' => $request->string('phone'),
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        $appointment->load(['clinic', 'service', 'therapist', 'payments']);

        return view('admin.appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,awaiting_payment,awaiting_verification,confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($data);

        return redirect()->route('admin.appointments.index')->with('success', 'อัปเดตการจองแล้ว');
    }

    public function confirmPayment(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status !== 'awaiting_verification') {
            return back()->with('error', 'การจองนี้ไม่ได้รอการยืนยันชำระเงิน');
        }

        try {
            $this->payments->confirmManual($appointment);
            $this->payments->submitToPartners($appointment->fresh());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'ยืนยันการชำระเงินแล้ว — สถานะ confirmed');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        try {
            $this->lifecycle->cancel(
                $appointment,
                $request->user(),
                $request->string('reason')->toString() ?: 'ยกเลิกโดยแอดมิน',
                true
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'ยกเลิกการจองแล้ว');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|string',
        ]);

        try {
            $this->lifecycle->reschedule(
                $appointment,
                $data['date'],
                $data['time_slot_id'],
                $request->user(),
                true
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'เลื่อนนัดแล้ว');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()->route('admin.appointments.index')->with('success', 'ลบการจองแล้ว');
    }
}

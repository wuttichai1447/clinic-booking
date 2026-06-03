<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\StaffLeave;
use App\Models\Therapist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $query = StaffLeave::with(['therapist.clinic'])->orderByDesc('start_date');

        if ($request->filled('therapist_id')) {
            $query->where('therapist_id', $request->string('therapist_id'));
        }

        if ($request->filled('clinic_id')) {
            $query->whereHas('therapist', fn ($q) => $q->where('clinic_id', $request->string('clinic_id')));
        }

        return view('admin.staff-leaves.index', [
            'leaves' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'therapists' => Therapist::with('clinic')->orderBy('name')->get(),
            'clinics' => Clinic::orderBy('name')->get(),
            'filterTherapistId' => $request->string('therapist_id')->toString(),
            'filterClinicId' => $request->string('clinic_id')->toString(),
            'leaveTypes' => StaffLeave::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('admin.staff-leaves.form', [
            'leave' => new StaffLeave([
                'start_date' => now(),
                'end_date' => now(),
                'leave_type' => 'annual',
            ]),
            'therapists' => Therapist::with('clinic')->where('is_active', true)->orderBy('name')->get(),
            'leaveTypes' => StaffLeave::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        StaffLeave::create($this->validated($request));

        return redirect()->route('admin.staff-leaves.index')->with('success', 'บันทึกการลาแล้ว — ช่วงเวลาของนักบำบัดจะไม่เปิดจองทันที');
    }

    public function edit(StaffLeave $staff_leave): View
    {
        return view('admin.staff-leaves.form', [
            'leave' => $staff_leave,
            'therapists' => Therapist::with('clinic')->orderBy('name')->get(),
            'leaveTypes' => StaffLeave::TYPES,
        ]);
    }

    public function update(Request $request, StaffLeave $staff_leave): RedirectResponse
    {
        $staff_leave->update($this->validated($request));

        return redirect()->route('admin.staff-leaves.index')->with('success', 'อัปเดตการลาแล้ว');
    }

    public function destroy(StaffLeave $staff_leave): RedirectResponse
    {
        $staff_leave->delete();

        return redirect()->route('admin.staff-leaves.index')->with('success', 'ลบรายการลาแล้ว');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'therapist_id' => 'required|uuid|exists:therapists,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => ['required', 'string', Rule::in(array_keys(StaffLeave::TYPES))],
            'note' => 'nullable|string|max:500',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicHolidayController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClinicHoliday::with('clinic')->orderByDesc('date');

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->string('clinic_id'));
        }

        return view('admin.holidays.index', [
            'holidays' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'clinics' => Clinic::orderBy('name')->get(),
            'filterClinicId' => $request->string('clinic_id')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.holidays.form', [
            'holiday' => new ClinicHoliday,
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ClinicHoliday::create($this->validated($request));

        return redirect()->route('admin.holidays.index')->with('success', 'เพิ่มวันหยุดแล้ว — ช่วงเวลาว่างทันที');
    }

    public function edit(ClinicHoliday $holiday): View
    {
        return view('admin.holidays.form', [
            'holiday' => $holiday,
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ClinicHoliday $holiday): RedirectResponse
    {
        $holiday->update($this->validated($request));

        return redirect()->route('admin.holidays.index')->with('success', 'บันทึกวันหยุดแล้ว');
    }

    public function destroy(ClinicHoliday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')->with('success', 'ลบวันหยุดแล้ว');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'clinic_id' => 'nullable|uuid|exists:clinics,id',
            'date' => 'required|date',
            'name' => 'nullable|string|max:255',
        ]);

        $data['clinic_id'] = filled($data['clinic_id'] ?? null) ? $data['clinic_id'] : null;

        return $data;
    }
}

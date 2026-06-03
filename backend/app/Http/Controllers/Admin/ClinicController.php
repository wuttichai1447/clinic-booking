<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function index(): View
    {
        return view('admin.clinics.index', [
            'clinics' => Clinic::orderBy('name')->paginate(config('admin.per_page')),
        ]);
    }

    public function create(): View
    {
        return view('admin.clinics.form', ['clinic' => new Clinic(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Clinic::create($data);

        return redirect()->route('admin.clinics.index')->with('success', 'เพิ่มคลินิกแล้ว');
    }

    public function edit(Clinic $clinic): View
    {
        return view('admin.clinics.form', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic): RedirectResponse
    {
        $clinic->update($this->validated($request, $clinic->id));

        return redirect()->route('admin.clinics.index')->with('success', 'บันทึกคลินิกแล้ว');
    }

    public function destroy(Clinic $clinic): RedirectResponse
    {
        $clinic->delete();

        return redirect()->route('admin.clinics.index')->with('success', 'ลบคลินิกแล้ว');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        $data = $request->validate([
            'id' => 'required|string|max:50|unique:clinics,id'.($ignoreId ? ','.$ignoreId : ''),
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:30',
            'image' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

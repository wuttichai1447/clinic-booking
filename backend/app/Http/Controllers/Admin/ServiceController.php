<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::with('clinic')->orderBy('name');

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->string('clinic_id'));
        }

        return view('admin.services.index', [
            'services' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'clinics' => Clinic::orderBy('name')->get(),
            'clinicId' => $request->string('clinic_id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', [
            'service' => new Service(['is_active' => true, 'duration' => 60]),
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('admin.services.index')->with('success', 'เพิ่มบริการแล้ว');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', [
            'service' => $service,
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request, $service->id));

        return redirect()->route('admin.services.index')->with('success', 'บันทึกบริการแล้ว');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'ลบบริการแล้ว');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        $data = $request->validate([
            'id' => 'required|string|max:50|unique:services,id'.($ignoreId ? ','.$ignoreId : ''),
            'clinic_id' => 'required|string|exists:clinics,id',
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:15|max:480',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

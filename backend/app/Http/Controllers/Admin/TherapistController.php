<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Therapist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TherapistController extends Controller
{
    public function index(Request $request): View
    {
        $query = Therapist::with('clinic')->orderBy('name');

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->string('clinic_id'));
        }

        return view('admin.therapists.index', [
            'therapists' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'clinics' => Clinic::orderBy('name')->get(),
            'clinicId' => $request->string('clinic_id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.therapists.form', [
            'therapist' => new Therapist(['is_active' => true]),
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Therapist::create($this->validated($request));

        return redirect()->route('admin.therapists.index')->with('success', 'เพิ่มนักบำบัดแล้ว');
    }

    public function edit(Therapist $therapist): View
    {
        return view('admin.therapists.form', [
            'therapist' => $therapist,
            'clinics' => Clinic::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Therapist $therapist): RedirectResponse
    {
        $therapist->update($this->validated($request, $therapist->id));

        return redirect()->route('admin.therapists.index')->with('success', 'บันทึกนักบำบัดแล้ว');
    }

    public function destroy(Therapist $therapist): RedirectResponse
    {
        if ($therapist->image && str_starts_with($therapist->image, '/storage/')) {
            $path = str_replace('/storage/', '', $therapist->image);
            Storage::disk('public')->delete($path);
        }
        $therapist->delete();

        return redirect()->route('admin.therapists.index')->with('success', 'ลบนักบำบัดแล้ว');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        $data = $request->validate([
            'id' => 'required|string|max:50|unique:therapists,id'.($ignoreId ? ','.$ignoreId : ''),
            'clinic_id' => 'required|string|exists:clinics,id',
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:5120',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('therapists', 'public');
            $data['image'] = Storage::url($path);
        } elseif (empty($data['image'])) {
            unset($data['image']);
        }

        unset($data['image_file']);

        return $data;
    }
}

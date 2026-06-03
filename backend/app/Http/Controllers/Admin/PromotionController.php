<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', [
            'promotions' => Promotion::orderByDesc('created_at')->paginate(config('admin.per_page')),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', [
            'promotion' => new Promotion(['is_active' => true, 'type' => 'percent', 'min_amount' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Promotion::create($this->validated($request));

        return redirect()->route('admin.promotions.index')->with('success', 'เพิ่มโปรโมชั่นแล้ว');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($this->validated($request, $promotion->id));

        return redirect()->route('admin.promotions.index')->with('success', 'บันทึกโปรโมชั่นแล้ว');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'ลบโปรโมชั่นแล้ว');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code'.($ignoreId ? ','.$ignoreId : ''),
            'title' => 'required|string|max:255',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer|min:1',
            'min_amount' => 'nullable|integer|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $data['min_amount'] = $data['min_amount'] ?? 0;

        return $data;
    }
}

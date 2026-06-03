<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function findValidCode(string $code, int $subtotal): Promotion
    {
        $promotion = Promotion::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (! $promotion) {
            throw ValidationException::withMessages(['code' => ['ไม่พบรหัสโปรโมชั่น']]);
        }

        if (! $promotion->isValidNow()) {
            throw ValidationException::withMessages(['code' => ['รหัสโปรโมชั่นหมดอายุหรือใช้งานไม่ได้']]);
        }

        if ($subtotal < $promotion->min_amount) {
            throw ValidationException::withMessages([
                'code' => ['ยอดขั้นต่ำ ฿'.number_format($promotion->min_amount).' สำหรับรหัสนี้'],
            ]);
        }

        return $promotion;
    }

    public function calculate(int $subtotal, Promotion $promotion): array
    {
        $discount = $promotion->type === 'percent'
            ? (int) floor($subtotal * $promotion->value / 100)
            : min($promotion->value, $subtotal);

        $final = max(0, $subtotal - $discount);

        return [
            'promotionId' => $promotion->id,
            'promotionCode' => $promotion->code,
            'promotionTitle' => $promotion->title,
            'subtotal' => $subtotal,
            'discountAmount' => $discount,
            'amount' => $final,
        ];
    }
}

<p>สวัสดี {{ $appointment->customer_name }},</p>
<p>การจอง {{ $appointment->id }} ถูกยกเลิกแล้ว</p>
@if($appointment->refund_amount)
<p>นโยบายคืนเงิน: ฿{{ number_format($appointment->refund_amount) }} ({{ $appointment->refund_status }})</p>
@endif

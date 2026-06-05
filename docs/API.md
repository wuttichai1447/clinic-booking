# API (Laravel) — `/api/v1`

เอกสารนี้เป็น “สรุปเร็ว” ของ endpoints ที่ระบบใช้งานจริง (อ้างอิงจาก `FULLSTACK.md`)

## Public (ไม่ต้อง login)

- `GET /clinics` — รายการคลินิก
- `GET /services?clinicId=` — บริการ
- `GET /therapists?clinicId=` — นักบำบัด
- `GET /slots?therapistId=&date=` — ช่วงเวลา

## Auth (ลูกค้า)

- `POST /auth/register`
- `POST /auth/login`
- `GET /auth/me` (Bearer token)
- `POST /auth/logout`

OAuth:

- `GET /auth/google/redirect` → callback → redirect ไป Nuxt `/auth/callback?token=...`
- `GET /auth/facebook/redirect`

## Appointments / Booking

- `POST /appointments` — สร้างการจอง (สถานะเริ่มต้น `awaiting_payment`)
- `GET /appointments?phone=` — ค้นหาการจองตามเบอร์ (legacy)
- `GET /me/appointments` — การจองของฉัน (ต้อง login)
- `GET /appointments/{id}/invoice` — ใบสรุปก่อนชำระเงิน

## Payments

- `GET /payments/config` — ช่องทางชำระ (Stripe/โอน/dev)
- `POST /appointments/{id}/payments/intent` — สร้าง Stripe Payment Intent
- `POST /appointments/{id}/payments/confirm` — ยืนยันหลังชำระสำเร็จ (Stripe)
- `POST /appointments/{id}/payments/submit-manual` — ส่งหลักฐานโอน/พร้อมเพย์ (รอแอดมิน)
- `POST /appointments/{id}/payments/dev-complete` — ชำระเงินจำลอง (เมื่อ `PAYMENT_DEV_MODE=true`)
- `POST /stripe/webhook` — Stripe webhook

## Promotions

- `POST /promotions/validate` — ตรวจโค้ด + คำนวณราคา
- `POST /appointments/{id}/apply-promo` — ใส่โค้ดหลังจอง (ต้อง login)

## Partners (optional)

- `POST /partners/submit` — ส่งข้อมูลให้ partner หลังชำระเงินสำเร็จ (เมื่อเปิด `PARTNERS_API_ENABLED`)

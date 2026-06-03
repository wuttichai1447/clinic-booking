# แผนทำให้ระบบจบ

## ระบบลูกค้า (Nuxt — http://localhost:3000)

- [x] จอง 7 ขั้น → API Laravel
- [x] ชำระเงินโหมด dev (`POST .../payments/dev-complete`)
- [x] การจองของฉัน (ค้นหาเบอร์โทร)
- [ ] ทดสอบ E2E: จองครบ flow แล้วเห็นแถวใน DBeaver ตาราง `appointments`
- [ ] (ทางเลือก) Stripe Elements บนหน้าชำระเงิน

**รันพร้อมกัน**

```powershell
# Terminal 1
cd backend
php artisan serve

# Terminal 2 (root)
pnpm dev
```

ไฟล์ `.env` ที่ root ต้องมี `NUXT_PUBLIC_API_BASE=/api/v1`

## ระบบแอดมิน (Laravel Blade)

- [x] เข้าสู่ระบบ `/admin/login`
- [x] CRUD คลินิก / บริการ / นักบำบัด
- [x] ดูและแก้สถานะการจอง
- [ ] ทดสอบ login ด้วย `admin@booking.local` / `password`

## ฐานข้อมูล (DBeaver)

- ไฟล์: `backend/database/database.sqlite`
- หลังจอง: `SELECT * FROM appointments ORDER BY created_at DESC;`

## Partners API

```http
POST http://127.0.0.1:8000/api/v1/partners/submit
```

ดูรายละเอียดใน `FULLSTACK.md`

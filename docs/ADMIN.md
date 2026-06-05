# Admin (Laravel Blade)

## URL

- Local: `http://127.0.0.1:8000/admin/login`
- Production: `{RENDER_URL}/admin/login`

## Credentials

- ใช้ `ADMIN_EMAIL` / `ADMIN_PASSWORD` จาก environment
- Local default (จาก `backend/.env.example` และ seeder):
  - Email: `admin@booking.local`
  - Password: `password`

## ฟีเจอร์หลัก (โดยรวม)

จากโครงสร้างใน `backend/resources/views/admin/`:

- Dashboard: สถิติ + กราฟ (Chart.js)
- จัดการข้อมูล: คลินิก / บริการ / นักบำบัด
- จัดการการจอง: ดู/แก้สถานะ + ยืนยันการชำระเงินกรณีโอน
- โปรโมชั่น: validate/apply promo
- วันหยุดคลินิก / ลานักบำบัด (ถ้าถูกเปิดใช้ในเมนู)

## ปัญหาที่พบบ่อย

### Login ไม่ได้ทั้งที่แก้ `ADMIN_PASSWORD` แล้ว

สาเหตุที่เจอบ่อย:

- user แอดมินถูก seed ไปแล้ว และการเปลี่ยน `.env` ไม่ได้เปลี่ยนรหัสใน DB อัตโนมัติ

แนวทาง:

- รัน seed ใหม่ (กรณี dev):

```powershell
cd backend
php artisan db:seed --class=AdminUserSeeder
```

หรือถ้าต้องการ reset ข้อมูลทั้งหมด (dev เท่านั้น):

```powershell
php artisan migrate:fresh --seed
```

### Production: จำไม่ได้ว่าแอดมินตั้งรหัสไว้เท่าไหร่

- ตรวจใน Render → Service → Environment ว่าตั้ง `ADMIN_EMAIL` / `ADMIN_PASSWORD` เป็นอะไร

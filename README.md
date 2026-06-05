# Clinic Booking (Nuxt + Laravel)

โปรเจกต์จองคิวคลินิกแบบ Full‑Stack:

- **Frontend**: Nuxt (อยู่ที่ `app/`) — หน้าจอง, ชำระเงิน, โปรไฟล์/การจองของฉัน
- **Backend**: Laravel (อยู่ที่ `backend/`) — REST API (`/api/v1/*`) + **Admin (Blade)** (`/admin/login`)

## เอกสาร

- `docs/SETUP.md` — รัน Local (Backend + Frontend)
- `docs/ENV.md` — รายการ Environment Variables ที่ใช้จริง
- `docs/ADMIN.md` — คู่มือแอดมิน + ปัญหาที่พบบ่อย
- `docs/API.md` — สรุป API endpoints
- `docs/DEPLOYMENT.md` — Deploy (Vercel + Render + Neon)

เอกสารฉบับยาวเดิม:

- `FULLSTACK.md`
- `DEPLOY-VERCEL.md`

## Quick start (Local)

### Backend (Laravel)

```powershell
cd backend
composer install
copy .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
php artisan serve
```

### Frontend (Nuxt)

```powershell
# ที่ root โปรเจกต์
pnpm install
copy .env.example .env
pnpm dev
```

## Security / ข้อควรระวัง

- **ห้าม commit ไฟล์ `.env`** (ทั้ง root และ `backend/.env`) เพราะมี secrets
- ไฟล์ cache เช่น `backend/storage/framework/views/*` ไม่ควรอยู่ใน git (ถูก ignore แล้วใน `backend/.gitignore`)

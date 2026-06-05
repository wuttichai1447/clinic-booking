# Setup (Local) — Clinic Booking

โปรเจกต์นี้เป็น **Nuxt (Frontend)** + **Laravel (Backend + Admin)**:

- Frontend: `http://localhost:3000`
- Backend/API: `http://127.0.0.1:8000`
- Admin (Blade): `http://127.0.0.1:8000/admin/login`

> หมายเหตุ: คู่มือนี้ยึดตามค่าเริ่มต้นใน `backend/.env.example` (ใช้ SQLite)

## Prerequisites

- Node.js + pnpm (ดู `package.json` ระบุ `pnpm@10.x`)
- PHP 8.2+
- Composer 2.x

### Windows (XAMPP) — extension ที่ต้องเปิด

ก่อน `composer install` ให้เปิด `zip` ใน `php.ini`:

```ini
extension=zip
```

ตรวจว่าเปิดแล้ว:

```powershell
php -m | findstr zip
```

## Backend (Laravel)

```powershell
cd backend

# ติดตั้ง dependencies
$env:COMPOSER_PROCESS_TIMEOUT=0
composer install

# ตั้งค่า env
copy .env.example .env
php artisan key:generate

# สร้าง SQLite file
New-Item -ItemType File -Path database\database.sqlite -Force

# migrate + seed
php artisan migrate --seed

# run
php artisan serve
```

### Admin login (local)

- Email: `admin@booking.local`
- Password: `password`

> ค่า default มาจาก `ADMIN_EMAIL` / `ADMIN_PASSWORD` ใน `backend/.env` (และ fallback ใน seeder)

## Frontend (Nuxt)

```powershell
# ที่ root โปรเจกต์
pnpm install

# ตั้งค่า env (ถ้ามี .env.example)
copy .env.example .env

pnpm dev
```

## ตรวจสุขภาพระบบ

- Backend: เปิด `http://127.0.0.1:8000/up` ต้องได้ 200
- Admin: เปิด `http://127.0.0.1:8000/admin/login`
- Frontend: เปิด `http://localhost:3000`

## Troubleshooting

### composer install ไม่ผ่านเพราะ ext-zip

- เปิด `extension=zip` ใน `php.ini` แล้วลองใหม่

### อยากใช้ MySQL แทน SQLite

- เปลี่ยน `DB_CONNECTION` และใส่ `DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD` ใน `backend/.env`
- รัน `php artisan migrate:fresh --seed`

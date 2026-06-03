# Full-Stack Clinic Booking

โปรเจกต์แบบ **Nuxt 4 (Frontend)** + **Laravel 11 (Backend + Admin + Payment)**

## สถาปัตยกรรม

```
Booking/
├── app/                 # Nuxt — หน้าจอง, ชำระเงิน, การจองของฉัน
├── server/api/mock/     # Mock เดิม (ใช้เมื่อยังไม่รัน Laravel)
└── backend/             # Laravel API + Admin (Blade)
    ├── routes/api.php   # /api/v1/*
    └── resources/views/admin/  # แอดมิน /admin/login
```

## API Endpoints (Laravel)

| Method | Path | คำอธิบาย |
|--------|------|----------|
| GET | `/api/v1/clinics` | รายการคลินิก |
| GET | `/api/v1/services?clinicId=` | บริการ |
| GET | `/api/v1/therapists?clinicId=` | นักบำบัด |
| GET | `/api/v1/slots?therapistId=&date=` | ช่วงเวลา |
| POST | `/api/v1/appointments` | สร้างการจอง (สถานะ `awaiting_payment`) |
| GET | `/api/v1/payments/config` | การตั้งค่าช่องทางชำระ (Stripe / โอน / dev) |
| POST | `/api/v1/appointments/{id}/payments/intent` | สร้าง Payment Intent (Stripe บัตร) |
| POST | `/api/v1/appointments/{id}/payments/confirm` | ยืนยัน Stripe หลังชำระสำเร็จ |
| POST | `/api/v1/appointments/{id}/payments/submit-manual` | ส่งหลักฐานโอน/พร้อมเพย์ (รอแอดมิน) |
| POST | `/api/v1/appointments/{id}/payments/dev-complete` | ชำระเงินจำลอง (เมื่อ `PAYMENT_DEV_MODE=true`) |
| POST | `/api/v1/stripe/webhook` | Stripe webhook (`payment_intent.succeeded`) |
| POST | `/api/v1/auth/register` | สมัครสมาชิกลูกค้า |
| POST | `/api/v1/auth/login` | เข้าสู่ระบบลูกค้า (ได้ token) |
| GET | `/api/v1/auth/me` | ข้อมูลผู้ใช้ (Bearer token) |
| POST | `/api/v1/auth/logout` | ออกจากระบบ |
| GET | `/api/v1/me/appointments` | การจองของลูกค้าที่ล็อกอิน |
| GET | `/api/v1/appointments?phone=` | ค้นหาการจองตามเบอร์ (แบบเดิม ไม่ต้องล็อกอิน) |
| GET | `/api/v1/auth/google/redirect` | OAuth Google → callback → redirect ไป Nuxt `/auth/callback?token=` |
| GET | `/api/v1/auth/facebook/redirect` | OAuth Facebook (เหมือน Google) |
| POST | `/api/v1/promotions/validate` | ตรวจรหัสโปรโมชั่น + คำนวณราคา |
| POST | `/api/v1/appointments/{id}/apply-promo` | ใส่โค้ดหลังจอง (ต้องล็อกอิน) |
| GET | `/api/v1/appointments/{id}/invoice` | ใบสรุปก่อนชำระเงิน (Stripe-ready flag) |
| POST | `/api/v1/partners/submit` | Partners API ตามเอกสาร |

## ติดตั้ง Backend (Laravel)

### ข้อกำหนด

- PHP 8.2+ (เปิด extension ใน `C:\xampp\php\php.ini`)
- Composer 2.x
- SQLite (ค่าเริ่มต้น) หรือ MySQL

### เปิด PHP extension (ทำก่อน `composer install`)

แก้ไฟล์ `C:\xampp\php\php.ini` เอา `;` ออกจาก:

```ini
extension=zip
```

ตรวจ: `php -m | findstr zip` ต้องเห็น `zip`

**แอดมิน Filament** ต้องเปิด `extension=intl` เพิ่ม (ทำทีหลังเมื่อ API รันได้แล้ว)

### ขั้นตอน

```powershell
cd backend

# 1. ติดตั้ง dependencies
$env:COMPOSER_PROCESS_TIMEOUT=0
composer install

# 2. ตั้งค่า environment
copy .env.example .env
php artisan key:generate

# 3. สร้างฐานข้อมูล SQLite
New-Item -ItemType File -Path database\database.sqlite -Force

# 4. Migrate + seed ข้อมูลจาก mock
php artisan migrate --seed

# 5. รัน API
php artisan serve
```

- **Admin (Blade):** http://localhost:8000/admin/login  
  - Email: `admin@booking.local`  
  - Password: `password`  
  - จัดการ: คลินิก, บริการ, นักบำบัด, การจอง, **โปรโมชั่น** (ส่วนลด)  
  - นักบำบัด: อัปโหลดรูปจากเครื่อง (`image_file`) — รัน `php artisan storage:link`  
  - แดชบอร์ด: สถิติ + กราฟ Chart.js  
  - Filament (ทางเลือก): เปิด `extension=intl` แล้ว `composer require filament/filament:^3.3`

### ชำระเงินจริง (Stripe + โอน/พร้อมเพย์)

ใน `backend/.env`:

```env
PAYMENT_DEV_MODE=false
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
PAYMENT_BANK_ACCOUNT=123-4-56789-0
PAYMENT_PROMPTPAY_ID=0812345678
```

Frontend `.env`:

```env
NUXT_PUBLIC_STRIPE_KEY=pk_test_...
```

**Flow บัตรเครดิต:** Payment Intent → Stripe Payment Element → `POST .../confirm` (+ webhook สำรอง)

**Flow โอน/พร้อมเพย์:** ลูกค้ากรอกเลขอ้างอิง → สถานะ `awaiting_verification` → แอดมินกด **ยืนยันการชำระเงิน** ที่ `/admin/appointments/{id}/edit`

**ทดสอบ webhook ในเครื่อง:**

```powershell
stripe listen --forward-to http://127.0.0.1:8000/api/v1/stripe/webhook
```

### โหมด Dev (ชำระเงินจำลอง)

```env
PAYMENT_DEV_MODE=true
```

กดชำระแล้วยืนยันทันที (ไม่ผ่าน Stripe / ไม่รอแอดมิน)

### OAuth (Google / Facebook)

ใน `backend/.env` (ดู `.env.example`):

```env
FRONTEND_URL=http://localhost:3000
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/v1/auth/google/callback
FACEBOOK_CLIENT_ID=...
FACEBOOK_CLIENT_SECRET=...
FACEBOOK_REDIRECT_URI=http://127.0.0.1:8000/api/v1/auth/facebook/callback
```

Nuxt ใช้ `NUXT_LARAVEL_URL` สำหรับปุ่ม Social login → redirect ไป Laravel

### โปรโมชั่น (ทดสอบ)

Seed สร้างโค้ด `WELCOME10` (ลด 10%, ขั้นต่ำ ฿500) — จัดการเพิ่มที่ `/admin/promotions`

### Partners API

```env
PARTNERS_API_ENABLED=true
PARTNERS_API_URL=https://partner.example.com
PARTNERS_API_KEY=your-key
```

หลังชำระเงินสำเร็จ ระบบจะส่งข้อมูลไป `POST {PARTNERS_API_URL}/api/v1/partners/submit`

## ติดตั้ง Frontend (Nuxt)

```powershell
# ที่ root โปรเจกต์
pnpm install
copy .env.example .env

# รัน Laravel ก่อน (port 8000) แล้วรัน Nuxt
pnpm dev
```

Nuxt `routeRules` proxy `/api/v1/**` → Laravel; SSR ใช้ `NUXT_API_BACKEND` โดยตรง (ดู `nuxt.config.ts`)

ถ้ายังไม่มี Laravel ให้ตั้ง `NUXT_PUBLIC_API_BASE=/api/mock` ชั่วคราว

## Flow การจอง (Full-Stack)

1. เลือกคลินิก → บริการ → วันที่ → นักบำบัด → เวลา  
2. กรอกข้อมูล + รหัสโปรโมชั่น (ถ้ามี) → **POST /appointments** → สถานะ `awaiting_payment`  
3. **ใบสรุปก่อนชำระ** (`GET /appointments/{id}/invoice`) — พิมพ์/PDF  
4. ชำระเงิน → **Stripe** (บัตร) / **โอน+แอดมินยืนยัน** / **dev-complete** (ทดสอบ)  
5. สรุปการจอง — สถานะ `confirmed`  
6. ลูกค้าที่ล็อกอิน: ดูรายการที่ `/my-bookings` (`GET /me/appointments`)  
7. Admin จัดการที่ `/admin` (คลินิก, โปรโมชั่น, การจอง)

## แอดมิน (Filament)

- จัดการ **คลินิก** (CRUD)
- จัดการ **การจอง** (ดู/แก้สถานะ)
- ขยายเพิ่ม: Service, Therapist, Payment resources

## แอดมิน Blade (ใช้งานได้แล้ว)

เข้า http://127.0.0.1:8000/admin/login — จัดการคลินิก / บริการ / นักบำบัด / การจอง

โค้ด Filament เดิมอยู่ที่ `backend/optional/filament/` (ย้ายออกจาก `app/` เพื่อไม่ให้ IDE error จนกว่าจะติดตั้ง package)

## ติดตั้งแอดมิน Filament (ทางเลือก — ทำหลัง API รันได้)

1. เปิด `extension=intl` ใน `php.ini` → ตรวจ `php -m | findstr intl`
2. รัน:

```powershell
cd backend
composer require filament/filament:^3.3
php artisan filament:install --panels
```

3. เข้า http://127.0.0.1:8000/admin

## วันหยุดคลินิก (แอดมิน)

- เมนู **วันหยุด** ที่ `/admin/holidays`
- เลือกคลินิกเฉพาะสาขา หรือเว้นว่าง = หยุดทุกสาขา
- วันที่ตรงกับรายการจะ **ไม่แสดง slot** ใน API `/slots` และจองไม่ได้ทันที

## ลานักบำบัด (แอดมิน)

- เมนู **ลานักบำบัด** ที่ `/admin/staff-leaves`
- เลือกนักบำบัด + ช่วงวันที่ + ประเภท (ลาพักร้อน / ลาป่วย / ลากิจ)
- วันที่นักบำบัดลาจะ **ไม่แสดงเวลาว่าง** ของคนนั้น (จองนัดกับนักบำบัดคนนั้นไม่ได้)

## แจ้งเตือน (ntfy / Telegram / SMS / อีเมล)

> **LINE Notify ยุตให้บริการแล้ว**

| ช่องทาง | ผู้รับ | ตั้งค่า |
|--------|--------|--------|
| **ntfy** (แนะนำ dev) | แอดมิน บนมือถือ | `NTFY_*` + แอป [ntfy](https://ntfy.sh) |
| **Telegram Bot** | แอดมิน | `TELEGRAM_*` |
| อีเมล | ลูกค้า + แอดมิน | `MAIL_*` (smtp ส่งจริง) |
| **SMS HTTP** | ลูกค้า | `SMS_*` |

### ทดสอบ

```powershell
cd backend
php artisan config:clear
php artisan booking:notify-test
```

### ntfy (แอดมิน — ใช้งานได้ทันที)

1. ติดตั้งแอป **ntfy** (iOS/Android)
2. Subscribe ตาม topic ใน `.env` เช่น `clinic-booking-wuttichai`
3. `NTFY_ENABLED=true`

```env
NTFY_ENABLED=true
NTFY_TOPIC=clinic-booking-wuttichai
NTFY_SERVER=https://ntfy.sh
```

### อีเมลจริง (Gmail ตัวอย่าง)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=app-password-16-chars
MAIL_ENCRYPTION=tls
ADMIN_NOTIFY_EMAIL=your@gmail.com
```

### Telegram / SMS

```env
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=...
TELEGRAM_CHAT_ID=...

SMS_ENABLED=true
SMS_API_URL=https://your-sms-provider.example/send
```

## ความปลอดภัย API (rate limit)

| Route | จำกัด |
|-------|--------|
| `POST /auth/login`, `/auth/register` | 10 ครั้ง/นาที ต่อ IP |
| `POST /appointments` | 15 ครั้ง/นาที ต่อ IP หรือ user |
| API ทั่วไป | 120 ครั้ง/นาที |

## กันการจองซ้ำ (slot lock)

การสร้างจองใช้ `DB::transaction` + `lockForUpdate()` บนแถวที่ชน slot — ลด race เมื่อจองพร้อมกัน

## แจ้งเตือนก่อนนัด (ลูกค้า)

```powershell
# รันครั้งเดียว (ทดสอบ)
php artisan booking:send-reminders

# Production: ตั้ง cron ทุกนาที
# * * * * * cd /path/to/backend && php artisan schedule:run
```

- แจ้งล่วงหน้า **24 ชม.** และ **2 ชม.** (เฉพาะ `confirmed`)
- อีเมล (ถ้ามี) + SMS (ถ้า `SMS_ENABLED`)
- `.env`: `BOOKING_REMINDERS_ENABLED`, `BOOKING_REMINDER_DAY_HOURS`, `BOOKING_REMINDER_SHORT_HOURS`

## ทดสอบ

```powershell
cd backend
composer test

# E2E (ต้องมี PAYMENT_DEV_MODE=true ที่ backend)
cd ..
pnpm exec playwright install chromium
pnpm run test:e2e
```

Playwright จะรัน Laravel + Nuxt อัตโนมัติ (หรือใช้ server ที่รันอยู่แล้วในเครื่อง)

## Deploy ฟรี (Vercel + Render)

ดูคู่มือละเอียด: **[DEPLOY-VERCEL.md](./DEPLOY-VERCEL.md)**

- **Vercel** — หน้าลูกค้า Nuxt
- **Render** — Laravel API + แอดมิน (ฟรี, อาจ cold start)
- **Neon** — PostgreSQL ฟรี
- **cron-job.org** — เรียกแจ้งเตือนก่อนนัด

## แก้ error `composer install`

| Error | วิธีแก้ |
|--------|--------|
| `ext-intl` missing | ตอนนี้ **ไม่บังคับ** แล้ว — ติด API ก่อนด้วย `composer install` ธรรมดา |
| `zip extension` missing | เปิด `extension=zip` ใน `php.ini` |

# Deploy ฟรี: Vercel (หน้าลูกค้า Nuxt) + Render (Laravel API + แอดมิน)

> **Vercel รัน Nuxt ได้ดี แต่รัน Laravel ไม่เหมาะ** — ใช้ Render (ฟรี) เป็น backend คู่กัน

## สถาปัตยกรรม

```
ลูกค้า  →  https://your-app.vercel.app          (Nuxt — Vercel)
API     →  https://clinic-booking-api.onrender.com   (Laravel — Render)
แอดมิน  →  https://clinic-booking-api.onrender.com/admin
DB      →  Neon PostgreSQL (ฟรี) หรือ Render Postgres
Cron    →  cron-job.org (ฟรี) เรียก reminder ทุก 15 นาที
```

---

## ขั้นที่ 1 — ฐานข้อมูลฟรี (Neon)

1. สมัคร [neon.tech](https://neon.tech) → สร้าง project
2. คัดลอก **Connection string** (PostgreSQL) เช่น  
   `postgresql://user:pass@ep-xxx.neon.tech/neondb?sslmode=require`

---

## ขั้นที่ 2 — Backend บน Render (ฟรี)

1. Push โค้ดขึ้น GitHub
2. สมัคร [render.com](https://render.com) → **New → Blueprint**
3. เลือก repo นี้ → Render อ่าน `render.yaml`
4. ตั้ง **Environment Variables** (สำคัญ):

| ตัวแปร | ตัวอย่าง |
|--------|----------|
| `APP_URL` | `https://clinic-booking-api.onrender.com` |
| `FRONTEND_URL` | `https://your-app.vercel.app` |
| `DATABASE_URL` | connection string จาก Neon |
| `ADMIN_EMAIL` | อีเมลแอดมิน |
| `ADMIN_PASSWORD` | รหัสผ่านแข็งแรง |
| `MAIL_*` | SMTP Gmail (แจ้งเตือน) |
| `ADMIN_NOTIFY_EMAIL` | อีเมลรับแจ้งเตือน |
| `STRIPE_*` | keys จริง (ถ้าใช้) |
| `GOOGLE_*` | OAuth redirect ชี้ Render URL |
| `PAYMENT_DEV_MODE` | `false` |

5. Deploy รอจน **Live** → ทดสอบ `https://xxx.onrender.com/up`

6. Seed ข้อมูลครั้งแรก (Render Shell หรือ local ชี้ DATABASE_URL):

```bash
php artisan db:seed
```

7. **แอดมิน:** `https://xxx.onrender.com/admin/login`

> Render แผนฟรี **หลับได้** หลังไม่มี traffic ~15 นาที — เปิดครั้งแรกอาจช้า 30–60 วิ

---

## ขั้นที่ 3 — Frontend บน Vercel (ฟรี)

1. สมัคร [vercel.com](https://vercel.com) → **Add New Project**
2. Import repo GitHub เดียวกัน
3. Framework: **Nuxt** (auto-detect)
4. Root Directory: **`/`** (root โปรเจกต์ ไม่ใช่ backend)
5. Environment Variables:

| ตัวแปร | ค่า |
|--------|-----|
| `NUXT_PUBLIC_API_BASE` | `https://clinic-booking-api.onrender.com/api/v1` |
| `NUXT_API_BACKEND` | `https://clinic-booking-api.onrender.com/api/v1` |
| `NUXT_LARAVEL_URL` | `https://clinic-booking-api.onrender.com` |
| `NUXT_PUBLIC_STRIPE_KEY` | `pk_live_...` หรือ test |

6. Deploy → ได้ URL เช่น `https://booking-xxx.vercel.app`

7. กลับไป Render อัปเดต `FRONTEND_URL` เป็น URL Vercel จริง → **Redeploy**

---

## ขั้นที่ 4 — OAuth Google (ถ้าใช้)

Google Cloud Console → Authorized redirect URIs:

```
https://clinic-booking-api.onrender.com/api/v1/auth/google/callback
```

ใน Render `.env`:

```env
GOOGLE_REDIRECT_URI=https://clinic-booking-api.onrender.com/api/v1/auth/google/callback
FRONTEND_URL=https://your-app.vercel.app
```

---

## ขั้นที่ 5 — แจ้งเตือนก่อนนัด (cron ฟรี)

Render แผนฟรีไม่มี cron ในตัว — ใช้ [cron-job.org](https://cron-job.org):

- URL: `https://clinic-booking-api.onrender.com/internal/cron/reminders?key=CRON_KEY`
- หรือ header `X-Cron-Key: CRON_KEY`
- ทุก **15 นาที**

`CRON_KEY` ดูใน Render Environment (auto-generate จาก `render.yaml`)

---

## ขั้นที่ 6 — ทดสอบหลัง deploy

- [ ] เปิด Vercel URL → เลือกคลินิกได้
- [ ] จองครบ flow → ชำระเงิน
- [ ] แอดมิน Render URL → login → ดูการจอง
- [ ] อีเมลแจ้งเตือนทดสอบ `php artisan booking:notify-test` (ผ่าน Render Shell)
- [ ] Stripe webhook ชี้ `https://xxx.onrender.com/api/v1/stripe/webhook`

---

## ข้อจำกัดแผนฟรี

| บริการ | ข้อจำกัด |
|--------|----------|
| Vercel | bandwidth โดยรวมพอ demo; SSR ใช้ serverless |
| Render | sleep เมื่อ idle; cold start ช้า |
| Neon | 512MB storage ฟรี — พอ MVP |
| cron-job.org | ฟรี ~ทุก 1 นาที |

---

## ไฟล์ที่เพิ่มใน repo

- `vercel.json` — ตั้งค่า Vercel
- `render.yaml` — Blueprint Render
- `backend/Dockerfile` + `docker-entrypoint.sh` — รัน Laravel บน Render

---

## ทางเลือก: โฮสต์ทั้งหมดฟรีที่เดียว

ถ้าไม่ต้องการ Render — ใช้ **Oracle Cloud Free VPS** รัน Laravel + Nuxt เอง (ย сложнее แต่ไม่ sleep) — ดู `FULLSTACK.md`

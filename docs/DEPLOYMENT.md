# Deployment — Vercel (Nuxt) + Render (Laravel) + Neon (Postgres)

คู่มือแบบ step-by-step (สรุปจาก `render.yaml` และ `DEPLOY-VERCEL.md`)

## ภาพรวม

- Frontend (Nuxt): Vercel
- Backend (Laravel + Admin): Render (Blueprint อ่าน `render.yaml`)
- Database: Neon PostgreSQL (แนะนำ) แล้วใส่ `DATABASE_URL` ให้ Render
- Cron: cron-job.org เรียก reminders (เพราะ Render free ไม่มี cron ในตัว)

## 1) Neon (Database)

สร้าง PostgreSQL ที่ Neon แล้วคัดลอก connection string:

- `DATABASE_URL=postgresql://.../neondb?sslmode=require`

## 2) Render (Backend)

1. Push repo ขึ้น GitHub
2. Render → New → Blueprint → เลือก repo นี้ (Render จะอ่าน `render.yaml`)
3. ตั้งค่า Environment Variables ที่ `sync: false` ใน `render.yaml` อย่างน้อย:
   - `APP_URL` = Render URL จริง
   - `FRONTEND_URL` = Vercel URL
   - `DATABASE_URL` = Neon connection string
   - `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NOTIFY_EMAIL`
4. Deploy แล้วทดสอบ:
   - `{RENDER_URL}/up` ต้องได้ 200
   - `{RENDER_URL}/admin/login` ต้องเข้าได้

หมายเหตุ:

- `render.yaml` มี `preDeployCommand` รัน `php artisan migrate` + `db:seed` อัตโนมัติ
- แผนฟรี Render มี cold start (เปิดครั้งแรกอาจช้า ~30–60 วินาที)

## 3) Vercel (Frontend)

1. Vercel → Add New Project → import repo นี้
2. ตั้ง Environment Variables:
   - `NUXT_PUBLIC_API_BASE={RENDER_URL}/api/v1`
   - `NUXT_API_BACKEND={RENDER_URL}/api/v1`
   - `NUXT_LARAVEL_URL={RENDER_URL}`
   - `NUXT_PUBLIC_STRIPE_KEY=pk_...` (ถ้าใช้ Stripe)
3. Deploy แล้วกลับไป Render อัปเดต `FRONTEND_URL` ให้ตรง → redeploy

## 4) Cron reminders (cron-job.org)

เรียก URL ภายใน backend:

- `https://{RENDER_HOST}/internal/cron/reminders?key=CRON_KEY`

โดย `CRON_KEY` ถูก generate ใน Render จาก `render.yaml`

## 5) OAuth redirect URIs (ถ้าใช้)

ตัวอย่าง Google:

- `GOOGLE_REDIRECT_URI=https://{RENDER_HOST}/api/v1/auth/google/callback`

## เช็คลิสต์หลัง deploy

- [ ] `{RENDER_URL}/up` ได้ 200
- [ ] Vercel หน้าเว็บโหลดได้และเรียก API ได้
- [ ] Login admin ได้
- [ ] จองครบ flow ได้ (รวม payment mode ที่เลือก)

# Environment Variables (ที่ใช้จริง)

เอกสารนี้สรุปตัวแปรสำคัญที่พบใน `render.yaml`, `backend/.env.example`, และคู่มือ deploy

> ห้าม commit `.env` ที่มี secrets (Gmail app password, Stripe secret, OAuth secret ฯลฯ)

## Backend (`backend/.env`)

### Core

- `APP_URL`: URL ของ backend (local: `http://127.0.0.1:8000`, production: Render URL)
- `FRONTEND_URL`: URL ของ frontend (local: `http://localhost:3000`, production: Vercel URL)

### Database

Local (แนะนำเริ่มด้วย SQLite):

- `DB_CONNECTION=sqlite`

Production (ตาม `render.yaml`):

- `DATABASE_URL`: Neon connection string (ควรมี `?sslmode=require`)
- `DB_CONNECTION=pgsql`
- `DB_SSLMODE=require`

### Admin login

- `ADMIN_EMAIL`
- `ADMIN_PASSWORD`
- `ADMIN_NOTIFY_EMAIL` (อีเมลรับแจ้งเตือนฝั่งแอดมิน)

> Admin ใช้ `ADMIN_EMAIL` / `ADMIN_PASSWORD` ไม่ใช่ `MAIL_PASSWORD`

### Payment (Stripe / โอน / dev)

- `PAYMENT_DEV_MODE`: `true/false`
- `STRIPE_KEY`: publishable key (ใช้กับ frontend ด้วย)
- `STRIPE_SECRET`: secret key (backend เท่านั้น)
- `STRIPE_WEBHOOK_SECRET`
- `PAYMENT_BANK_NAME`
- `PAYMENT_BANK_ACCOUNT_NAME`
- `PAYMENT_BANK_ACCOUNT`
- `PAYMENT_PROMPTPAY_ID`

### Notifications

Email (SMTP):

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

Push / Chat (optional):

- `NTFY_ENABLED`, `NTFY_TOPIC`, `NTFY_SERVER`
- `TELEGRAM_ENABLED`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`

SMS (optional):

- `SMS_ENABLED`, `SMS_API_URL`, `SMS_API_KEY`

### OAuth

- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI`

### Cron (reminders)

- `CRON_KEY` (Render blueprint generate)
- `BOOKING_REMINDERS_ENABLED`
- `BOOKING_REMINDER_DAY_HOURS`
- `BOOKING_REMINDER_SHORT_HOURS`
- `BOOKING_REMINDER_WINDOW_MINUTES`

## Frontend (root `.env`)


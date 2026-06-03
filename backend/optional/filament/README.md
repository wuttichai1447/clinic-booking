# Filament (ทางเลือก)

โค้ดนี้ย้ายออกจาก `app/` เพราะยังไม่ได้ `composer require filament/filament` (ต้องเปิด `extension=intl`)

แอดมินที่ใช้งานได้ตอนนี้: **Blade** ที่ `/admin/login`

เมื่อติดตั้ง Filament แล้ว ให้ย้ายกลับ:

- `app-Filament` → `app/Filament`
- `Providers-Filament` → `app/Providers/Filament`

แล้วรัน `composer require filament/filament:^3.3`

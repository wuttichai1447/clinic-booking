<script setup lang="ts">
definePageMeta({ layout: 'default' })
useHead({ title: 'คู่มือผู้ใช้' })
</script>

<template>
  <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
    <div class="flex flex-col gap-2 mb-6">
      <h1 class="text-xl sm:text-2xl font-bold">
        คู่มือผู้ใช้
      </h1>
      <p class="text-sm text-muted">
        วิธีจอง · ชำระเงิน · ดูการจอง · เลื่อนนัด/ยกเลิก
      </p>
    </div>

    <div class="space-y-4">
      <UCard>
        <template #header>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-calendar-plus"
              class="size-4 text-primary"
            />
            <p class="font-semibold">
              วิธีจองนัดหมาย
            </p>
          </div>
        </template>

        <ol class="list-decimal pl-5 space-y-1 text-sm leading-6">
          <li>กดเมนู <strong>จอง</strong> (หน้าแรก)</li>
          <li>เลือก <strong>คลินิก</strong> → <strong>บริการ</strong> → <strong>วันที่</strong></li>
          <li>เลือก <strong>นักบำบัด</strong> → <strong>เวลา</strong></li>
          <li>กรอกข้อมูลผู้รับบริการ และ (ถ้ามี) ใส่ <strong>โค้ดส่วนลด</strong></li>
          <li>ตรวจสอบ <strong>สรุปก่อนชำระ</strong> แล้วกดไปขั้นตอนชำระเงิน</li>
        </ol>

        <template #footer>
          <div class="flex flex-wrap gap-2">
            <UButton
              to="/"
              size="sm"
            >
              ไปหน้าจอง
            </UButton>
            <UButton
              to="/login"
              size="sm"
              variant="outline"
            >
              เข้าสู่ระบบ (แนะนำ)
            </UButton>
          </div>
        </template>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-credit-card"
              class="size-4 text-primary"
            />
            <p class="font-semibold">
              การชำระเงิน (มีหลายแบบ)
            </p>
          </div>
        </template>

        <div class="space-y-3 text-sm leading-6">
          <div class="rounded-lg border border-default bg-muted/30 px-3 py-2">
            <p class="font-medium mb-1">
              บัตรเครดิต/เดบิต (Stripe)
            </p>
            <p class="text-muted">
              ทำรายการในหน้าชำระเงิน ระบบจะพากลับมาที่หน้าเดิมและแสดงผลสำเร็จ/ไม่สำเร็จให้ทราบ
            </p>
          </div>

          <div class="rounded-lg border border-default bg-muted/30 px-3 py-2">
            <p class="font-medium mb-1">
              โอนเงิน / พร้อมเพย์
            </p>
            <p class="text-muted">
              ส่งหลักฐาน/ข้อมูลการโอนแล้วสถานะจะเป็น <strong>รอยืนยันชำระ</strong> จนกว่าแอดมินจะยืนยัน
            </p>
          </div>
        </div>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-list-checks"
              class="size-4 text-primary"
            />
            <p class="font-semibold">
              ดูรายการ “การจองของฉัน”
            </p>
          </div>
        </template>

        <div class="space-y-2 text-sm leading-6">
          <p>
            ไปที่หน้า <strong>การจองของฉัน</strong> เพื่อดูสถานะการจองทั้งหมด (แนะนำให้ล็อกอินเพื่อผูกการจองกับบัญชี)
          </p>
          <ul class="list-disc pl-5 space-y-1">
            <li>ดูสถานะ: รอชำระเงิน / รอยืนยันชำระ / ยืนยันแล้ว / ยกเลิก</li>
            <li>ดาวน์โหลดใบเสร็จ (เมื่อสถานะ “ยืนยันแล้ว”)</li>
            <li>ค้นหาด้วยเบอร์โทรได้ (กรณีไม่ได้ล็อกอินไว้ในตอนจอง)</li>
          </ul>
        </div>

        <template #footer>
          <UButton
            to="/my-bookings"
            size="sm"
            variant="outline"
          >
            ไปหน้า การจองของฉัน
          </UButton>
        </template>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-refresh-ccw"
              class="size-4 text-primary"
            />
            <p class="font-semibold">
              เลื่อนนัด / ยกเลิก
            </p>
          </div>
        </template>

        <div class="space-y-2 text-sm leading-6">
          <p>
            ในหน้า “การจองของฉัน” คุณสามารถกด <strong>เลื่อนนัด</strong> หรือ <strong>ยกเลิกการจอง</strong> ได้ตามเงื่อนไข
          </p>
          <div class="rounded-lg border border-default bg-muted/30 px-3 py-2">
            <p class="font-medium mb-1">
              นโยบายการยกเลิก/คืนเงิน (โดยสรุป)
            </p>
            <p class="text-muted">
              ล่วงหน้า 24 ชม. คืนเงินเต็ม · 12–24 ชม. คืนบางส่วน · ภายใน 2 ชม. ก่อนนัดไม่คืน
              (ระบบจะแจ้งยอดคืนก่อนยืนยัน)
            </p>
          </div>
        </div>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-message-circle-question"
              class="size-4 text-primary"
            />
            <p class="font-semibold">
              ปัญหาที่พบบ่อย
            </p>
          </div>
        </template>

        <div class="space-y-3 text-sm leading-6">
          <div>
            <p class="font-medium">
              ชำระเงินแล้วแต่ยังไม่อัปเดตสถานะ
            </p>
            <p class="text-muted">
              หากเป็นโอน/พร้อมเพย์ สถานะจะเป็น “รอยืนยันชำระ” จนกว่าแอดมินจะยืนยัน
            </p>
          </div>
          <div>
            <p class="font-medium">
              หาเลขที่การจองไม่เจอ
            </p>
            <p class="text-muted">
              ไปที่ “การจองของฉัน” แล้วค้นหาด้วยเบอร์โทรที่ใช้จอง
            </p>
          </div>
        </div>
      </UCard>
    </div>
  </div>
</template>

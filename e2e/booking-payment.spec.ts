import { test, expect } from '@playwright/test'

test.describe('จองนัด → ชำระเงิน (dev mode)', () => {
  test('ครบ flow ตั้งแต่เลือกคลินิกจนชำระสำเร็จ', async ({ page }) => {
    await page.goto('/')

    await expect(page.getByRole('heading', { name: 'จองนัดหมาย' })).toBeVisible()

    await page.getByRole('button', { name: /สาขาสยาม/ }).click()
    await page.getByRole('button', { name: 'ถัดไป' }).click()

    await page.getByRole('button', { name: 'นวดบำบัด' }).click()
    await page.getByRole('button', { name: 'ถัดไป' }).click()

    const futureDay = page.locator('[role="gridcell"]:not([aria-disabled="true"])').last()
    await futureDay.click()
    await page.getByRole('button', { name: 'ถัดไป' }).click()

    await page.getByRole('button', { name: /คุณสมชาย/ }).click()
    await page.getByRole('button', { name: 'ถัดไป' }).click()

    await page.getByRole('button', { name: '09:00' }).click()
    await page.getByRole('button', { name: 'ถัดไป' }).click()

    await page.getByPlaceholder('กรอกชื่อ-นามสกุล').fill('E2E Tester')
    await page.getByPlaceholder('กรอกเบอร์โทรศัพท์').fill('0898765432')
    await page.locator('input[type="checkbox"]').check()
    await page.getByRole('button', { name: 'ยืนยันการจอง' }).click()

    await expect(page.getByText('ใบสรุปรายการจอง')).toBeVisible({ timeout: 30_000 })
    await page.getByRole('button', { name: 'ดำเนินการชำระเงิน' }).click()

    await expect(page.getByRole('heading', { name: 'ชำระเงิน' })).toBeVisible()
    await page.getByRole('button', { name: 'ดำเนินการชำระด้วยบัตร' }).click()

    await expect(page.getByRole('button', { name: 'ดูการจองของฉัน' })).toBeVisible({ timeout: 45_000 })
  })
})

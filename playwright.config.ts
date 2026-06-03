import { defineConfig, devices } from '@playwright/test'

const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:3000'

export default defineConfig({
  testDir: 'e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  timeout: 120_000,
  expect: { timeout: 15_000 },
  use: {
    ...devices['Desktop Chrome'],
    baseURL,
    trace: 'on-first-retry'
  },
  webServer: [
    {
      command: 'php artisan migrate:fresh --seed --force && php artisan serve --port=8000',
      cwd: 'backend',
      url: 'http://127.0.0.1:8000',
      reuseExistingServer: !process.env.CI,
      timeout: 120_000,
      env: {
        APP_ENV: 'local',
        PAYMENT_DEV_MODE: 'true',
        QUEUE_CONNECTION: 'sync',
        CACHE_STORE: 'array'
      }
    },
    {
      command: 'pnpm dev --port 3000',
      url: 'http://127.0.0.1:3000',
      reuseExistingServer: !process.env.CI,
      timeout: 180_000,
      env: {
        NUXT_API_BACKEND: 'http://127.0.0.1:8000/api/v1',
        NUXT_PUBLIC_API_BASE: '/api/v1',
        NUXT_LARAVEL_URL: 'http://127.0.0.1:8000'
      }
    }
  ]
})

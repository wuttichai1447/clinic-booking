<script setup lang="ts">
const config = useRuntimeConfig()
const base = (config.public.laravelUrl as string).replace(/\/$/, '')

function go(provider: 'google' | 'facebook') {
  window.location.href = `${base}/api/v1/auth/${provider}/redirect`
}
</script>

<template>
  <div class="space-y-3">
    <p class="text-center text-sm text-muted">
      หรือเข้าสู่ระบบด้วย
    </p>
    <UButton
      block
      size="lg"
      variant="outline"
      icon="i-simple-icons-google"
      @click="go('google')"
    >
      Google
    </UButton>
    <UButton
      block
      size="lg"
      variant="outline"
      color="primary"
      icon="i-simple-icons-facebook"
      @click="go('facebook')"
    >
      Facebook
    </UButton>
  </div>
</template>

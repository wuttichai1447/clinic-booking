<script setup lang="ts">
const route = useRoute()
const { authStore } = useAuth()

useHead({ title: 'เข้าสู่ระบบ' })

onMounted(async () => {
  const error = route.query.error as string | undefined
  const token = route.query.token as string | undefined

  if (error) {
    await navigateTo(`/login?error=${error}`)
    return
  }

  if (token) {
    authStore.setToken(token)
    const { fetchMe } = useAuth()
    const user = await fetchMe()
    if (user) {
      await navigateTo('/my-bookings')
    } else {
      await navigateTo('/login?error=oauth_failed')
    }
  } else {
    await navigateTo('/login')
  }
})
</script>

<template>
  <div class="flex justify-center p-12">
    <LoadingSpinner />
    <p class="ml-4 text-muted">
      กำลังเข้าสู่ระบบ...
    </p>
  </div>
</template>

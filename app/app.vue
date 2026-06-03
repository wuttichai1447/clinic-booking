<script setup>
useHead({
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, viewport-fit=cover' }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'th'
  }
})

const currentYear = useState('footer-year', () => new Date().getFullYear())

const title = 'ระบบจองคลินิกกายภาพบำบัด'
const description = 'จองนัดหมายคลินิกกายภาพบำบัดได้อย่างง่ายดาย'

useSeoMeta({
  title,
  description,
  ogTitle: title,
  ogDescription: description
})
</script>

<template>
  <UApp>
    <Header />

    <UMain class="min-h-[calc(100dvh-7rem)]">
      <NuxtPage />
    </UMain>

    <UFooter>
      <template #left>
        <p class="text-sm text-muted">
          ระบบจองคลินิก • © {{ currentYear }}
        </p>
      </template>
    </UFooter>
  </UApp>
</template>

<script setup lang="ts">
defineProps<{
  message: string
  title?: string
}>()
</script>

<template>
  <UAlert
    color="error"
    :title="title || 'เกิดข้อผิดพลาด'"
    :description="message"
    icon="i-lucide-alert-circle"
  />
</template>

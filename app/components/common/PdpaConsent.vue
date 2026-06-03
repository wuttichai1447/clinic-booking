<script setup lang="ts">
const model = defineModel<boolean>({ required: true })
</script>

<template>
  <label class="flex items-start gap-3 text-sm text-slate-300 cursor-pointer">
    <input
      v-model="model"
      type="checkbox"
      class="mt-1 size-4 rounded border-slate-600"
    >
    <span>ฉันยอมรับนโยบายความเป็นส่วนตัว (PDPA) และข้อกำหนดการใช้บริการ</span>
  </label>
</template>

<script setup lang="ts">
import type { Service } from '~/types/booking'

defineProps<{
  services: Service[]
  selectedService: Service | null
  loading?: boolean
}>()

const emit = defineEmits<{
  select: [service: Service]
}>()

const serviceIcons: Record<string, string> = {
  'service-1': 'i-lucide-hand',
  'service-2': 'i-lucide-shoulder',
  'service-3': 'i-lucide-user-round',
  'service-4': 'i-lucide-spa',
  'service-5': 'i-lucide-radio'
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">
      เลือกบริการ
    </h3>
    <div
      v-if="loading"
      class="flex justify-center p-8"
    >
      <LoadingSpinner />
    </div>
    <div
      v-else
      class="grid gap-4 sm:grid-cols-2"
    >
      <button
        v-for="service in services"
        :key="service.id"
        type="button"
        :class="[
          'group relative flex overflow-hidden rounded-xl border-2 text-left transition-all duration-300',
          'hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
          selectedService?.id === service.id
            ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/30'
            : 'border-border bg-card hover:border-primary/50'
        ]"
        @click="emit('select', service)"
      >
        <div class="relative h-32 w-32 shrink-0 overflow-hidden bg-muted">
          <img
            v-if="service.image"
            :src="resolveMediaUrl(service.image)"
            :alt="service.name"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
          >
          <div
            v-else
            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/20 to-primary/5"
          >
            <UIcon
              :name="serviceIcons[service.id] || 'i-lucide-spa'"
              class="size-10 text-primary/60"
            />
          </div>
        </div>
        <div class="flex flex-1 flex-col justify-between p-4">
          <div>
            <h4 class="font-semibold text-foreground group-hover:text-primary">
              {{ service.name }}
            </h4>
            <p class="mt-1 flex items-center gap-1.5 text-sm text-muted">
              <UIcon
                name="i-lucide-clock"
                class="size-4 shrink-0"
              />
              {{ service.duration }} นาที
            </p>
          </div>
          <div class="mt-3 flex items-center justify-between">
            <span
              :class="[
                'rounded-lg px-3 py-1.5 text-lg font-bold',
                selectedService?.id === service.id ? 'bg-primary text-primary-foreground' : 'bg-primary/10 text-primary'
              ]"
            >
              ฿{{ service.price }}
            </span>
            <UIcon
              v-if="selectedService?.id === service.id"
              name="i-lucide-check-circle"
              class="size-6 text-primary"
            />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

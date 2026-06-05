<script setup lang="ts">
const route = useRoute()
const { authStore, logout } = useAuth()

const mobileOpen = ref(false)

watch(() => route.path, () => {
  mobileOpen.value = false
})

async function onLogout() {
  mobileOpen.value = false
  await logout()
  await navigateTo('/')
}

function linkClass(path: string) {
  return route.path === path
    ? 'bg-primary/10 text-primary font-medium'
    : 'text-foreground hover:bg-muted/50'
}

function isActive(path: string) {
  return route.path === path
}

const userMenuItems = computed(() => {
  const name = authStore.user?.name?.trim() || '—'
  return [
    [
      { label: name, type: 'label' as const, disabled: true },
      {
        label: 'โปรไฟล์',
        icon: 'i-lucide-user',
        to: '/profile'
      },
      {
        label: 'การจองของฉัน',
        icon: 'i-lucide-list-checks',
        to: '/my-bookings'
      }
    ],
    [
      {
        label: 'ออกจากระบบ',
        icon: 'i-lucide-log-out',
        color: 'error' as const,
        onSelect: () => { void onLogout() }
      }
    ]
  ]
})

const navLinkClass = (path: string) =>
  [
    'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
    isActive(path)
      ? 'text-primary bg-primary/10'
      : 'text-muted hover:text-foreground hover:bg-muted/40'
  ].join(' ')
</script>

<template>
  <header class="sticky top-0 z-50 border-b border-border/80 bg-background/90 backdrop-blur-md supports-[backdrop-filter]:bg-background/75 shadow-sm">
    <div class="container mx-auto flex h-14 items-center gap-3 px-4 sm:px-6">
      <NuxtLink
        to="/"
        class="flex items-center gap-2 min-w-0 shrink-0 group"
      >
        <span class="flex size-8 items-center justify-center rounded-lg bg-primary/15 text-primary">
          <UIcon
            name="i-lucide-stethoscope"
            class="size-4"
          />
        </span>
        <span class="truncate text-sm sm:text-base font-bold text-foreground group-hover:text-primary transition-colors">
          ระบบจองคลินิก
        </span>
      </NuxtLink>

      <nav class="hidden md:flex flex-1 items-center justify-center gap-0.5">
        <NuxtLink
          to="/"
          :class="navLinkClass('/')"
        >
          <UIcon
            name="i-lucide-calendar-plus"
            class="size-4 opacity-70"
          />
          จอง
        </NuxtLink>
        <NuxtLink
          to="/help"
          :class="navLinkClass('/help')"
        >
          <UIcon
            name="i-lucide-circle-help"
            class="size-4 opacity-70"
          />
          คู่มือ
        </NuxtLink>
        <NuxtLink
          v-if="authStore.ready && authStore.isLoggedIn"
          to="/my-bookings"
          :class="navLinkClass('/my-bookings')"
        >
          <UIcon
            name="i-lucide-clipboard-list"
            class="size-4 opacity-70"
          />
          การจองของฉัน
        </NuxtLink>
      </nav>

      <div class="hidden md:flex items-center gap-2 ml-auto shrink-0">
        <template v-if="authStore.ready && authStore.isLoggedIn">
          <UDropdownMenu
            :items="userMenuItems"
            :content="{ align: 'end' }"
            :ui="{ content: 'min-w-48' }"
          >
            <UButton
              variant="ghost"
              color="neutral"
              square
              size="md"
              icon="i-lucide-circle-user"
              aria-label="บัญชีผู้ใช้"
              class="rounded-full"
            />
          </UDropdownMenu>
        </template>
        <template v-else-if="authStore.ready">
          <NuxtLink
            to="/login"
            :class="navLinkClass('/login')"
          >
            เข้าสู่ระบบ
          </NuxtLink>
          <UButton
            to="/register"
            size="sm"
            color="primary"
          >
            สมัครสมาชิก
          </UButton>
        </template>

        <UButton
          href="/admin/login"
          external
          variant="ghost"
          color="neutral"
          size="sm"
          icon="i-lucide-shield"
          class="hidden lg:inline-flex"
          aria-label="แอดมิน"
        />
      </div>

      <div class="flex md:hidden items-center gap-2 ml-auto">
        <UButton
          variant="ghost"
          color="neutral"
          square
          :icon="mobileOpen ? 'i-lucide-x' : 'i-lucide-menu'"
          aria-label="เมนู"
          @click="mobileOpen = !mobileOpen"
        />
      </div>
    </div>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <nav
        v-if="mobileOpen"
        class="md:hidden border-t border-border bg-background px-4 py-3 space-y-1 shadow-lg"
      >
        <NuxtLink
          to="/"
          class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
          :class="linkClass('/')"
        >
          <UIcon
            name="i-lucide-calendar-plus"
            class="size-4 shrink-0"
          />
          จองนัดหมาย
        </NuxtLink>
        <NuxtLink
          to="/help"
          class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
          :class="linkClass('/help')"
        >
          <UIcon
            name="i-lucide-circle-help"
            class="size-4 shrink-0"
          />
          คู่มือผู้ใช้
        </NuxtLink>
        <NuxtLink
          v-if="authStore.ready && authStore.isLoggedIn"
          to="/my-bookings"
          class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
          :class="linkClass('/my-bookings')"
        >
          <UIcon
            name="i-lucide-list-checks"
            class="size-4 shrink-0"
          />
          การจองของฉัน
        </NuxtLink>
        <NuxtLink
          v-if="authStore.ready && authStore.isLoggedIn"
          to="/profile"
          class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
          :class="linkClass('/profile')"
        >
          <UIcon
            name="i-lucide-user"
            class="size-4 shrink-0"
          />
          โปรไฟล์
        </NuxtLink>
        <template v-if="authStore.ready && authStore.isLoggedIn">
          <button
            type="button"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-left text-error hover:bg-error/10 transition-colors"
            @click="onLogout"
          >
            <UIcon
              name="i-lucide-log-out"
              class="size-4 shrink-0"
            />
            ออกจากระบบ
          </button>
        </template>
        <template v-else-if="authStore.ready">
          <NuxtLink
            to="/login"
            class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
            :class="linkClass('/login')"
          >
            <UIcon
              name="i-lucide-log-in"
              class="size-4 shrink-0"
            />
            เข้าสู่ระบบ
          </NuxtLink>
          <NuxtLink
            to="/register"
            class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm transition-colors"
            :class="linkClass('/register')"
          >
            <UIcon
              name="i-lucide-user-plus"
              class="size-4 shrink-0"
            />
            สมัครสมาชิก
          </NuxtLink>
        </template>
        <a
          href="/admin/login"
          class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-muted hover:bg-muted/50 transition-colors"
        >
          <UIcon
            name="i-lucide-shield"
            class="size-4 shrink-0"
          />
          แอดมิน
        </a>
      </nav>
    </Transition>
  </header>
</template>

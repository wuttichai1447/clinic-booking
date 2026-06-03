export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  if (import.meta.client) {
    authStore.hydrate()
  }

  if (!authStore.isLoggedIn) {
    return navigateTo({
      path: '/login',
      query: { redirect: to.fullPath }
    })
  }
})

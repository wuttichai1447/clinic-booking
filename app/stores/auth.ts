import { defineStore } from 'pinia'
import type { AuthUser } from '~/types/auth'
import { clearAuthSession, loadAuthToken, loadAuthUser, saveAuthSession } from '~/utils/authToken'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as AuthUser | null,
    ready: false
  }),

  getters: {
    isLoggedIn: state => !!state.token && !!state.user
  },

  actions: {
    hydrate() {
      if (!import.meta.client) return
      this.token = loadAuthToken()
      this.user = loadAuthUser<AuthUser>()
      this.ready = true
    },

    setToken(token: string) {
      this.token = token
      if (import.meta.client) {
        localStorage.setItem('auth:token', token)
      }
    },

    setSession(token: string, user: AuthUser) {
      this.token = token
      this.user = user
      saveAuthSession(token, user)
      this.ready = true
    },

    clear() {
      this.token = null
      this.user = null
      clearAuthSession()
    }
  }
})

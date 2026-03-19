import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user         = ref(null)
  const token        = ref(null)
  const tenantDomain = ref(null)
  const loading      = ref(false)
  const error        = ref(null)

  // ── Getters ──────────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin         = computed(() => user.value?.role === 'admin')
  const currentTenant   = computed(() => tenantDomain.value)

  // ── Actions ───────────────────────────────────────────────────────────────────
  function loadFromStorage() {
    token.value        = localStorage.getItem('token')
    tenantDomain.value = localStorage.getItem('tenant_domain')
    const stored       = localStorage.getItem('user')
    if (stored) {
      try { user.value = JSON.parse(stored) } catch { user.value = null }
    }
  }

  function setTenant(domain) {
    tenantDomain.value = domain
    localStorage.setItem('tenant_domain', domain)
  }

  async function register(data) {
    loading.value = true
    error.value   = null
    try {
      const res  = await authApi.register(data)
      _setSession(res.data)
      return res.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Registration failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function login(data) {
    loading.value = true
    error.value   = null
    try {
      const res = await authApi.login(data)
      _setSession(res.data)
      return res.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Login failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch { /* ignore */ }
    _clearSession()
  }

  async function fetchMe() {
    try {
      const res  = await authApi.me()
      user.value = res.data.user
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch { _clearSession() }
  }

  function _setSession(data) {
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  function _clearSession() {
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  return {
    user, token, tenantDomain, loading, error,
    isAuthenticated, isAdmin, currentTenant,
    loadFromStorage, setTenant, register, login, logout, fetchMe,
  }
})

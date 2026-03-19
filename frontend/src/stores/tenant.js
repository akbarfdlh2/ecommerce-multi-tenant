import { defineStore } from 'pinia'
import { ref } from 'vue'
import { tenantsApi } from '@/api/tenants'

export const useTenantStore = defineStore('tenant', () => {
  const tenants   = ref([])
  const dashboard = ref(null)
  const loading   = ref(false)

  async function fetchTenants() {
    loading.value = true
    try {
      const res  = await tenantsApi.list()
      tenants.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  async function registerTenant(data) {
    const res = await tenantsApi.register(data)
    tenants.value.push(res.data.data)
    return res.data.data
  }

  async function fetchDashboard() {
    loading.value = true
    try {
      const res      = await tenantsApi.dashboard()
      dashboard.value = res.data
    } finally {
      loading.value = false
    }
  }

  return { tenants, dashboard, loading, fetchTenants, registerTenant, fetchDashboard }
})

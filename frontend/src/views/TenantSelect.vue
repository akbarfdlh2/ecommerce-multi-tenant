<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-600 to-primary-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">AFDA eCommerce</h1>
        <p class="text-gray-500 mt-2">Pilih toko untuk melanjutkan</p>
      </div>

      <!-- Tenant List -->
      <div v-if="tenantStore.loading" class="text-center py-8">
        <LoadingSpinner />
      </div>

      <div v-else-if="tenantStore.tenants.length" class="space-y-3 mb-6">
        <button
          v-for="tenant in tenantStore.tenants"
          :key="tenant._id"
          @click="selectTenant(tenant)"
          class="w-full text-left p-4 rounded-xl border-2 border-gray-200 hover:border-primary-500 hover:bg-primary-50 transition-all duration-200 group"
        >
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-gray-900 group-hover:text-primary-600">{{ tenant.name }}</h3>
              <p class="text-sm text-gray-500">{{ tenant.domain }}</p>
            </div>
            <span class="badge bg-blue-100 text-blue-700">{{ tenant.plan }}</span>
          </div>
        </button>
      </div>

      <div v-else class="text-center py-6 text-gray-500">
        Belum ada toko terdaftar.
      </div>

      <div class="border-t pt-4 text-center">
        <RouterLink to="/setup" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
          + Daftarkan Toko Baru
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTenantStore } from '@/stores/tenant'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router      = useRouter()
const tenantStore = useTenantStore()
const authStore   = useAuthStore()

onMounted(() => tenantStore.fetchTenants())

function selectTenant(tenant) {
  authStore.setTenant(tenant.domain)
  router.push({ name: 'products' })
}
</script>

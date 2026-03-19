<template>
  <div class="p-8 max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/products" class="text-gray-500 hover:text-gray-700">←</RouterLink>
      <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Produk' : 'Tambah Produk' }}</h1>
    </div>

    <div v-if="loadingProduct" class="card p-8 animate-pulse"><div class="h-4 bg-gray-200 rounded w-1/3"></div></div>

    <form v-else @submit.prevent="submit" class="card p-6 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
          <input v-model="form.name" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
          <input v-model.number="form.price" type="number" min="0" step="1000" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
          <input v-model.number="form.stock" type="number" min="0" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
          <input v-model="form.category" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
          <input v-model="form.sku" class="input" placeholder="Opsional" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram)</label>
          <input v-model.number="form.weight" type="number" min="0" class="input" />
        </div>
        <div class="flex items-center gap-2 pt-5">
          <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
          <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea v-model="form.description" class="input" rows="4"></textarea>
        </div>
      </div>

      <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Produk') }}
        </button>
        <RouterLink to="/admin/products" class="btn-secondary">Batal</RouterLink>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'

const route         = useRoute()
const router        = useRouter()
const productsStore = useProductsStore()

const isEdit       = computed(() => !!route.params.id)
const loading      = ref(false)
const loadingProduct = ref(false)
const error        = ref('')
const success      = ref('')

const form = ref({
  name: '', price: 0, stock: 0, category: '',
  description: '', sku: '', weight: 0, is_active: true,
})

onMounted(async () => {
  if (isEdit.value) {
    loadingProduct.value = true
    try {
      const product = await productsStore.fetchProduct(route.params.id)
      form.value = {
        name: product.name, price: product.price, stock: product.stock,
        category: product.category, description: product.description || '',
        sku: product.sku || '', weight: product.weight || 0, is_active: product.is_active,
      }
    } finally {
      loadingProduct.value = false
    }
  }
})

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    if (isEdit.value) {
      await productsStore.updateProduct(route.params.id, form.value)
      success.value = 'Produk berhasil diperbarui!'
    } else {
      await productsStore.createProduct(form.value)
      success.value = 'Produk berhasil ditambahkan!'
      setTimeout(() => router.push('/admin/products'), 1000)
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal menyimpan produk'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Manajemen Produk</h1>
      <RouterLink to="/admin/products/create" class="btn-primary">+ Tambah Produk</RouterLink>
    </div>

    <div class="card overflow-hidden">
      <div class="p-4 border-b flex gap-3">
        <input v-model="search" @input="onSearch" type="text" class="input w-64" placeholder="Cari produk..." />
      </div>

      <div v-if="productsStore.loading" class="p-8 text-center"><LoadingSpinner /></div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-left">Produk</th>
            <th class="px-4 py-3 text-left">Kategori</th>
            <th class="px-4 py-3 text-right">Harga</th>
            <th class="px-4 py-3 text-right">Stok</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="product in productsStore.items" :key="product._id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900">{{ product.name }}</td>
            <td class="px-4 py-3 text-gray-500">{{ product.category }}</td>
            <td class="px-4 py-3 text-right">{{ formatPrice(product.price) }}</td>
            <td class="px-4 py-3 text-right" :class="product.stock <= 3 ? 'text-red-600 font-medium' : ''">
              {{ product.stock }}
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="badge">
                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                <RouterLink :to="`/admin/products/${product._id}`" class="text-primary-600 hover:underline text-xs">Edit</RouterLink>
                <button @click="deleteProduct(product)" class="text-red-600 hover:underline text-xs">Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="!productsStore.items.length">
            <td colspan="6" class="px-4 py-12 text-center text-gray-500">Tidak ada produk.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProductsStore } from '@/stores/products'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const productsStore = useProductsStore()
const search        = ref('')
let   searchTimeout = null

onMounted(() => productsStore.fetchProducts())

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (search.value.length >= 2) {
      productsStore.searchProducts(search.value)
    } else if (!search.value) {
      productsStore.fetchProducts()
    }
  }, 400)
}

async function deleteProduct(product) {
  if (!confirm(`Hapus produk "${product.name}"?`)) return
  await productsStore.removeProduct(product._id)
}

const formatPrice = (p) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p)
</script>

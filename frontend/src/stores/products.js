import { defineStore } from 'pinia'
import { ref } from 'vue'
import { productsApi } from '@/api/products'

export const useProductsStore = defineStore('products', () => {
  const items       = ref([])
  const current     = ref(null)
  const pagination  = ref({ total: 0, per_page: 12, current_page: 1, last_page: 1 })
  const loading     = ref(false)
  const error       = ref(null)
  const categories  = ref([])

  async function fetchProducts(params = {}) {
    loading.value = true
    error.value   = null
    try {
      const res     = await productsApi.list(params)
      items.value   = res.data.data
      pagination.value = {
        total:        res.data.total,
        per_page:     res.data.per_page,
        current_page: res.data.current_page,
        last_page:    res.data.last_page,
      }
      // Extract unique categories
      categories.value = [...new Set(items.value.map(p => p.category).filter(Boolean))]
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to load products'
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(id) {
    loading.value = true
    error.value   = null
    try {
      const res    = await productsApi.show(id)
      current.value = res.data.data
      return current.value
    } catch (e) {
      error.value = e.response?.data?.message || 'Product not found'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createProduct(data) {
    const res = await productsApi.create(data)
    items.value.unshift(res.data.data)
    return res.data.data
  }

  async function updateProduct(id, data) {
    const res     = await productsApi.update(id, data)
    const updated = res.data.data
    const index   = items.value.findIndex(p => p._id === id)
    if (index !== -1) items.value[index] = updated
    if (current.value?._id === id) current.value = updated
    return updated
  }

  async function removeProduct(id) {
    await productsApi.remove(id)
    items.value = items.value.filter(p => p._id !== id)
  }

  async function searchProducts(q) {
    loading.value = true
    try {
      const res   = await productsApi.search(q)
      items.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  return {
    items, current, pagination, loading, error, categories,
    fetchProducts, fetchProduct, createProduct, updateProduct, removeProduct, searchProducts,
  }
})

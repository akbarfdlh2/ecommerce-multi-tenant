import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cartApi } from '@/api/cart'

export const useCartStore = defineStore('cart', () => {
  const cart    = ref({ items: [], total: 0 })
  const loading = ref(false)
  const error   = ref(null)

  // ── Getters ───────────────────────────────────────────────────────────────────
  const itemCount = computed(() =>
    (cart.value.items || []).reduce((sum, i) => sum + i.quantity, 0)
  )
  const isEmpty = computed(() => !cart.value.items?.length)

  // ── Actions ───────────────────────────────────────────────────────────────────
  async function fetchCart() {
    loading.value = true
    try {
      const res  = await cartApi.get()
      cart.value = res.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to load cart'
    } finally {
      loading.value = false
    }
  }

  async function addItem(productId, quantity = 1) {
    loading.value = true
    error.value   = null
    try {
      const res  = await cartApi.addItem(productId, quantity)
      cart.value = res.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to add item'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateItem(itemId, quantity) {
    try {
      const res  = await cartApi.updateItem(itemId, quantity)
      cart.value = res.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to update item'
      throw e
    }
  }

  async function removeItem(itemId) {
    try {
      const res  = await cartApi.removeItem(itemId)
      cart.value = res.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Failed to remove item'
    }
  }

  async function clearCart() {
    await cartApi.clear()
    cart.value = { items: [], total: 0 }
  }

  function reset() {
    cart.value  = { items: [], total: 0 }
    error.value = null
  }

  return {
    cart, loading, error, itemCount, isEmpty,
    fetchCart, addItem, updateItem, removeItem, clearCart, reset,
  }
})

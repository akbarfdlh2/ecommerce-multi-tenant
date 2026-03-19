import api from './axios'

export const cartApi = {
  get:        ()                      => api.get('/cart'),
  addItem:    (productId, quantity)   => api.post('/cart/items', { product_id: productId, quantity }),
  updateItem: (itemId, quantity)      => api.put(`/cart/items/${itemId}`, { quantity }),
  removeItem: (itemId)                => api.delete(`/cart/items/${itemId}`),
  clear:      ()                      => api.delete('/cart'),
}

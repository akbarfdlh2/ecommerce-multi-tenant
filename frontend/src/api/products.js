import api from './axios'

export const productsApi = {
  list:    (params)      => api.get('/products', { params }),
  search:  (q)           => api.get('/products/search', { params: { q } }),
  show:    (id)          => api.get(`/products/${id}`),
  create:  (data)        => api.post('/products', data),
  update:  (id, data)    => api.put(`/products/${id}`, data),
  remove:  (id)          => api.delete(`/products/${id}`),
  uploadImage: (id, file) => {
    const form = new FormData()
    form.append('image', file)
    return api.post(`/products/${id}/image`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
}

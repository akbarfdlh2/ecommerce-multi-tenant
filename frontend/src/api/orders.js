import api from './axios'

export const ordersApi = {
  checkout:     (data) => api.post('/orders', data),
  myOrders:     (params) => api.get('/orders', { params }),
  show:         (id)     => api.get(`/orders/${id}`),
  adminList:    (params) => api.get('/admin/orders', { params }),
  updateStatus: (id, data) => api.put(`/admin/orders/${id}`, data),
}

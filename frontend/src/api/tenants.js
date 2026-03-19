import api from './axios'

export const tenantsApi = {
  list:     ()     => api.get('/tenants'),
  register: (data) => api.post('/tenants/register', data),
  dashboard: ()    => api.get('/admin/dashboard'),
}

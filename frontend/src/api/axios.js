import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// ── Request interceptor: inject auth token + tenant domain ────────────────────
api.interceptors.request.use((config) => {
  const token  = localStorage.getItem('token')
  const tenant = localStorage.getItem('tenant_domain')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  if (tenant) {
    config.headers['X-Tenant-Domain'] = tenant
  }

  return config
})

// ── Response interceptor: handle 401 globally ─────────────────────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

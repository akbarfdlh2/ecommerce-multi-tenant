import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// ── Lazy-loaded views ──────────────────────────────────────────────────────────
const TenantSelect  = () => import('@/views/TenantSelect.vue')
const Login         = () => import('@/views/auth/Login.vue')
const Register      = () => import('@/views/auth/Register.vue')
const ProductList   = () => import('@/views/shop/ProductList.vue')
const ProductDetail = () => import('@/views/shop/ProductDetail.vue')
const CartView      = () => import('@/views/shop/CartView.vue')
const Checkout      = () => import('@/views/shop/Checkout.vue')
const OrderList     = () => import('@/views/shop/OrderList.vue')
const AdminLayout   = () => import('@/views/admin/AdminLayout.vue')
const Dashboard     = () => import('@/views/admin/Dashboard.vue')
const Products      = () => import('@/views/admin/Products.vue')
const ProductForm   = () => import('@/views/admin/ProductForm.vue')
const AdminOrders   = () => import('@/views/admin/AdminOrders.vue')
const TenantSetup   = () => import('@/views/TenantSetup.vue')

const routes = [
  // ── Public ──────────────────────────────────────────────────────────────────
  { path: '/',         name: 'home',          component: TenantSelect },
  { path: '/setup',    name: 'tenant-setup',  component: TenantSetup },
  { path: '/login',    name: 'login',         component: Login },
  { path: '/register', name: 'register',      component: Register },

  // ── Shop (require auth) ───────────────────────────────────────────────────
  {
    path: '/shop',
    meta: { requiresTenant: true },
    children: [
      { path: '',          name: 'products',       component: ProductList },
      { path: 'product/:id', name: 'product',      component: ProductDetail },
      { path: 'cart',      name: 'cart',            component: CartView,  meta: { requiresAuth: true } },
      { path: 'checkout',  name: 'checkout',        component: Checkout,  meta: { requiresAuth: true } },
      { path: 'orders',    name: 'orders',          component: OrderList, meta: { requiresAuth: true } },
    ],
  },

  // ── Admin (require admin role) ─────────────────────────────────────────────
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true, requiresTenant: true },
    children: [
      { path: '',                name: 'dashboard',     component: Dashboard },
      { path: 'products',        name: 'admin-products',component: Products },
      { path: 'products/create', name: 'product-create',component: ProductForm },
      { path: 'products/:id',    name: 'product-edit',  component: ProductForm },
      { path: 'orders',          name: 'admin-orders',  component: AdminOrders },
    ],
  },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// ── Navigation guards ──────────────────────────────────────────────────────────
router.beforeEach((to) => {
  const auth   = useAuthStore()
  const tenant = localStorage.getItem('tenant_domain')

  if (to.meta.requiresTenant && !tenant) {
    return { name: 'home' }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'products' }
  }
})

export default router

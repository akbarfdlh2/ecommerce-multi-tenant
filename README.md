# AFDA Multi-Tenant eCommerce Platform

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-7.0-47A248?style=flat&logo=mongodb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)

Platform eCommerce multi-tenant menggunakan **Laravel 11**, **Vue.js 3**, dan **MongoDB**, dengan isolasi database penuh per tenant.

## Arsitektur

```
┌─────────────────────────────────────────────────────────┐
│                     Docker Compose                       │
│                                                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐  │
│  │  Nginx   │  │ PHP-FPM  │  │  Vue.js  │  │ Mongo  │  │
│  │ :8000    │──│ Laravel  │  │  :5173   │  │ :27017 │  │
│  └──────────┘  └────┬─────┘  └──────────┘  └───┬────┘  │
│                     │                           │        │
│              ┌──────┴───────────────────────────┤        │
│              │  ecommerce_central (tenant list) │        │
│              │  tenant_elektronik_jaya           │        │
│              │  tenant_fashion_cantik            │        │
│              │  tenant_<slug>  (per tenant)      │        │
│              └───────────────────────────────────┘        │
└─────────────────────────────────────────────────────────┘
```

### Multi-Tenancy Flow

```
HTTP Request
    │
    ▼
[Nginx]
    │
    ▼
[IdentifyTenant Middleware]
    ├── Baca header: X-Tenant-Domain
    ├── Cari tenant di ecommerce_central.tenants
    ├── Switch MongoDB connection → tenant_{slug}
    └── Lanjutkan ke controller
```

## Tech Stack

| Layer      | Teknologi                              |
|------------|----------------------------------------|
| Backend    | Laravel 11 + PHP 8.3                   |
| Database   | MongoDB 7.0 (multi-database)           |
| ODM        | mongodb/laravel-mongodb ^4.1           |
| Auth       | Laravel Sanctum (token-based)          |
| Frontend   | Vue.js 3 + Vite + Pinia + Tailwind CSS |
| Container  | Docker + Docker Compose                |
| Web Server | Nginx                                  |

## Cara Menjalankan

### Prasyarat
- Docker Desktop
- Docker Compose v2+

### Quick Start

```bash
# Clone repo
git clone https://github.com/akbarfdlh2/ecommerce-multi-tenant.git
cd ecommerce-multi-tenant

# First-time setup (build + seed)
make init

# Atau manual:
docker-compose up -d --build
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan db:seed
```

### URL Akses

| Service       | URL                          |
|---------------|------------------------------|
| Frontend      | http://localhost:5173        |
| Backend API   | http://localhost:8000/api    |
| Mongo Express | http://localhost:8081        |

### Jalankan dengan Docker Image dari GHCR

Docker image tersedia di GitHub Container Registry:

```bash
# Pull image
docker pull ghcr.io/akbarfdlh2/ecommerce-app:latest
docker pull ghcr.io/akbarfdlh2/ecommerce-frontend:latest
```

## API Endpoints

### Public
```
GET  /api/health
GET  /api/tenants
POST /api/tenants/register
```

### Auth (perlu header: X-Tenant-Domain)
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout    [auth required]
GET  /api/auth/me        [auth required]
```

### Products
```
GET    /api/products          [auth]
GET    /api/products/{id}     [auth]
GET    /api/products/search   [auth]
POST   /api/products          [admin]
PUT    /api/products/{id}     [admin]
DELETE /api/products/{id}     [admin]
```

### Cart
```
GET    /api/cart                  [auth]
POST   /api/cart/items            [auth]
PUT    /api/cart/items/{itemId}   [auth]
DELETE /api/cart/items/{itemId}   [auth]
DELETE /api/cart                  [auth]
```

### Orders
```
POST /api/orders            [auth] — checkout
GET  /api/orders            [auth]
GET  /api/orders/{id}       [auth]
GET  /admin/orders          [admin]
PUT  /admin/orders/{id}     [admin]
GET  /admin/dashboard       [admin]
```

## Akun Demo (Setelah Seeding)

### Toko 1: Elektronik Jaya
- **Domain:** `elektronik-jaya.localhost`
- **Admin:** admin@elektronik-jaya.com / password123
- **Customer:** customer@elektronik-jaya.com / password123

### Toko 2: Fashion Cantik
- **Domain:** `fashion-cantik.localhost`
- **Admin:** admin@fashion-cantik.com / password123
- **Customer:** customer@fashion-cantik.com / password123

## Contoh Request API

```bash
# 1. Daftar toko baru
curl -X POST http://localhost:8000/api/tenants/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Toko Saya","owner_email":"admin@tokosaya.com"}'

# 2. Login ke toko (gunakan domain dari response di atas)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Domain: toko-saya.localhost" \
  -d '{"email":"admin@tokosaya.com","password":"password123"}'

# 3. Lihat produk
curl http://localhost:8000/api/products \
  -H "X-Tenant-Domain: elektronik-jaya.localhost" \
  -H "Authorization: Bearer <token>"
```

## Testing

```bash
# Jalankan semua test
make test

# Unit test saja
make test-unit

# Feature test saja
make test-feature
```

## Struktur Database

### Central DB (`ecommerce_central`)
```
tenants:
  _id, name, slug, domain, database_name, owner_email, plan, is_active, settings
```

### Tenant DB (`tenant_{slug}`)
```
users:    _id, name, email, password, role, is_active
products: _id, name, slug, description, price, stock, category, images, is_active
carts:    _id, user_id, items[], total
orders:   _id, order_number, user_id, items[], subtotal, total, status, ...
```

## Isolasi Data

Setiap tenant menggunakan **database MongoDB yang terpisah**. Middleware `IdentifyTenant` secara otomatis:
1. Membaca header `X-Tenant-Domain`
2. Mencari tenant di database central
3. Mengganti koneksi MongoDB ke database tenant tersebut
4. Semua query selanjutnya terisolasi di database tenant

## Perintah Makefile

```bash
make help          # Lihat semua perintah
make up            # Jalankan containers
make down          # Hentikan containers
make init          # Setup pertama kali
make seed          # Jalankan seeders
make test          # Jalankan tests
make shell-app     # Masuk ke container PHP
make shell-mongo   # Masuk ke MongoDB shell
make logs          # Lihat logs
```

---

*Dikerjakan untuk PT AFDA Technology Solution — Eng Mohab Mamdouh*

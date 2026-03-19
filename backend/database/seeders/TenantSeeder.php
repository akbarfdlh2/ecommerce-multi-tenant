<?php

namespace Database\Seeders;

use App\Models\Central\Tenant;
use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function __construct(protected TenantDatabaseManager $dbManager) {}

    public function run(): void
    {
        $tenants = [
            [
                'name'          => 'Toko Elektronik Jaya',
                'slug'          => 'elektronik-jaya',
                'domain'        => 'elektronik-jaya.localhost',
                'database_name' => 'tenant_elektronik_jaya',
                'owner_email'   => 'admin@elektronik-jaya.com',
                'plan'          => 'pro',
                'admin'         => ['name' => 'Admin Jaya', 'email' => 'admin@elektronik-jaya.com', 'password' => 'password123'],
            ],
            [
                'name'          => 'Toko Fashion Cantik',
                'slug'          => 'fashion-cantik',
                'domain'        => 'fashion-cantik.localhost',
                'database_name' => 'tenant_fashion_cantik',
                'owner_email'   => 'admin@fashion-cantik.com',
                'plan'          => 'free',
                'admin'         => ['name' => 'Admin Fashion', 'email' => 'admin@fashion-cantik.com', 'password' => 'password123'],
            ],
        ];

        foreach ($tenants as $tenantData) {
            $adminData = $tenantData['admin'];
            unset($tenantData['admin']);

            // Create or update tenant in central DB
            $tenant = Tenant::updateOrCreate(
                ['slug' => $tenantData['slug']],
                $tenantData
            );

            $this->command->info("Seeding tenant: {$tenant->name}");

            // Switch to tenant database and seed data
            $this->dbManager->initialize($tenant);

            // Create admin user
            User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name'     => $adminData['name'],
                    'email'    => $adminData['email'],
                    'password' => $adminData['password'],
                    'role'     => 'admin',
                ]
            );

            // Create sample customer
            User::updateOrCreate(
                ['email' => 'customer@' . $tenant->slug . '.com'],
                [
                    'name'     => 'Pelanggan Sample',
                    'email'    => 'customer@' . $tenant->slug . '.com',
                    'password' => 'password123',
                    'role'     => 'customer',
                ]
            );

            // Create sample products
            $this->seedProducts($tenant->slug);
        }

        $this->command->info('Seeding complete!');
    }

    protected function seedProducts(string $slug): void
    {
        $productsByTenant = [
            'elektronik-jaya' => [
                ['name' => 'Laptop ASUS VivoBook 15', 'price' => 8500000, 'stock' => 10, 'category' => 'Laptop',
                 'description' => 'Laptop ringan dengan prosesor Intel Core i5 generasi terbaru.'],
                ['name' => 'Samsung Galaxy A54', 'price' => 4200000, 'stock' => 25, 'category' => 'Smartphone',
                 'description' => 'Smartphone Android dengan kamera 50MP dan baterai 5000mAh.'],
                ['name' => 'Headphone Sony WH-1000XM5', 'price' => 3800000, 'stock' => 15, 'category' => 'Audio',
                 'description' => 'Headphone noise-cancelling premium dari Sony.'],
                ['name' => 'Monitor LG 24" IPS', 'price' => 2100000, 'stock' => 8, 'category' => 'Monitor',
                 'description' => 'Monitor Full HD 24 inch dengan panel IPS.'],
                ['name' => 'Keyboard Mechanical Logitech', 'price' => 850000, 'stock' => 30, 'category' => 'Aksesoris',
                 'description' => 'Keyboard mechanical dengan switch tactile untuk produktivitas.'],
            ],
            'fashion-cantik' => [
                ['name' => 'Dress Batik Modern', 'price' => 285000, 'stock' => 50, 'category' => 'Dress',
                 'description' => 'Dress batik modern dengan motif kontemporer, tersedia berbagai ukuran.'],
                ['name' => 'Hijab Pashmina Premium', 'price' => 125000, 'stock' => 100, 'category' => 'Hijab',
                 'description' => 'Hijab pashmina berbahan premium, halus dan nyaman dipakai.'],
                ['name' => 'Tas Ransel Casual', 'price' => 320000, 'stock' => 40, 'category' => 'Tas',
                 'description' => 'Tas ransel casual dengan bahan canvas berkualitas.'],
                ['name' => 'Sepatu Sneakers Wanita', 'price' => 450000, 'stock' => 35, 'category' => 'Sepatu',
                 'description' => 'Sepatu sneakers wanita dengan sol nyaman untuk aktivitas sehari-hari.'],
                ['name' => 'Kemeja Flanel Pria', 'price' => 195000, 'stock' => 60, 'category' => 'Kemeja',
                 'description' => 'Kemeja flanel pria dengan motif kotak-kotak stylish.'],
            ],
        ];

        $products = $productsByTenant[$slug] ?? [];

        foreach ($products as $productData) {
            $productData['slug']     = \Illuminate\Support\Str::slug($productData['name']);
            $productData['is_active'] = true;
            Product::updateOrCreate(['slug' => $productData['slug']], $productData);
        }
    }
}

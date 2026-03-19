<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Manages MongoDB connection switching for multi-tenancy.
 * Each call to switchTo() re-configures the 'mongodb_tenant' connection
 * and purges the resolver cache so the next query uses the new database.
 */
class TenantDatabaseManager
{
    // ── Core: switch active tenant connection ─────────────────────────────────

    public function switchTo(string $databaseName): void
    {
        // Update runtime config for the tenant connection
        config([
            'database.connections.mongodb_tenant.database' => $databaseName,
        ]);

        // Purge the existing connection so Laravel re-creates it with new config
        DB::purge('mongodb_tenant');
        DB::reconnect('mongodb_tenant');

        // Set default connection so Eloquent models using 'mongodb_tenant' see the change
        DB::setDefaultConnection('mongodb_tenant');
    }

    // ── Initialize a new tenant's database with collections & indexes ─────────

    public function initialize(Tenant $tenant): void
    {
        $this->switchTo($tenant->database_name);

        $mongo = DB::connection('mongodb_tenant')->getMongoClient();
        $db    = $mongo->selectDatabase($tenant->database_name);

        // Create collections explicitly (MongoDB creates lazily but this ensures indexes)
        $this->createCollection($db, 'users', [
            ['key' => ['email' => 1], 'unique' => true, 'name' => 'email_unique'],
        ]);

        $this->createCollection($db, 'products', [
            ['key' => ['slug' => 1],     'unique' => true, 'name' => 'slug_unique'],
            ['key' => ['category' => 1], 'name'   => 'category_idx'],
            ['key' => ['is_active' => 1, 'stock' => -1], 'name' => 'active_stock_idx'],
        ]);

        $this->createCollection($db, 'carts', [
            ['key' => ['user_id' => 1], 'unique' => true, 'name' => 'user_id_unique'],
        ]);

        $this->createCollection($db, 'orders', [
            ['key' => ['order_number' => 1], 'unique' => true, 'name' => 'order_number_unique'],
            ['key' => ['user_id' => 1],      'name'   => 'user_id_idx'],
            ['key' => ['status' => 1],       'name'   => 'status_idx'],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function createCollection(\MongoDB\Database $db, string $name, array $indexes = []): void
    {
        // MongoDB auto-creates collections, but we still ensure indexes
        $collection = $db->selectCollection($name);

        foreach ($indexes as $index) {
            $key     = $index['key'];
            $options = array_filter([
                'unique' => $index['unique'] ?? false,
                'name'   => $index['name']   ?? null,
            ]);

            try {
                $collection->createIndex($key, $options);
            } catch (\Throwable $e) {
                // Index may already exist — safe to ignore
                logger()->debug("Index creation skipped for $name: " . $e->getMessage());
            }
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Builder as MongoBuilder;
use MongoDB\Laravel\Query\Builder as MongoQueryBuilder;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'mongodb_central';
    protected $collection = 'personal_access_tokens';
    protected $primaryKey = '_id';
    protected $keyType = 'string';

    protected $casts = [
        'abilities'    => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function getTable(): string
    {
        return $this->collection;
    }

    protected function newBaseQueryBuilder(): MongoQueryBuilder
    {
        $connection = $this->getConnection();

        return new MongoQueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }

    public function newEloquentBuilder($query): MongoBuilder
    {
        return new MongoBuilder($query);
    }

    public static function findToken($token): ?static
    {
        \Illuminate\Support\Facades\Log::info('[findToken] called', ['token_prefix' => substr($token ?? '', 0, 30)]);

        if (!str_contains($token, '|')) {
            \Illuminate\Support\Facades\Log::info('[findToken] no pipe, searching by hash');
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $plainText] = explode('|', $token, 2);
        \Illuminate\Support\Facades\Log::info('[findToken] split', ['id' => $id, 'id_len' => strlen($id)]);

        try {
            $instance = static::where('_id', new ObjectId($id))->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[findToken] ObjectId error', ['error' => $e->getMessage()]);
            return null;
        }

        \Illuminate\Support\Facades\Log::info('[findToken] DB lookup result', ['found' => $instance !== null]);

        if (!$instance) {
            return null;
        }

        $hashMatch = hash_equals($instance->token, hash('sha256', $plainText));
        \Illuminate\Support\Facades\Log::info('[findToken] hash match', ['match' => $hashMatch]);

        return $hashMatch ? $instance : null;
    }

    public function getKey(): string
    {
        $key = $this->getAttribute($this->primaryKey);

        return $key instanceof ObjectId ? (string) $key : (string) $key;
    }

    public function tokenable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        $relation = parent::tokenable();
        \Illuminate\Support\Facades\Log::info('[tokenable] accessing', [
            'tokenable_type' => $this->tokenable_type,
            'tokenable_id'   => $this->tokenable_id,
            'default_conn'   => config('database.default'),
        ]);
        return $relation;
    }

    public function getParentRelation(): ?Relation
    {
        return null;
    }
}

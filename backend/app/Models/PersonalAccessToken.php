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
        if (!str_contains($token, '|')) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $plainText] = explode('|', $token, 2);

        try {
            $instance = static::where('_id', new ObjectId($id))->first();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$instance) {
            return null;
        }

        return hash_equals($instance->token, hash('sha256', $plainText)) ? $instance : null;
    }

    public function getKey(): string
    {
        $key = $this->getAttribute($this->primaryKey);

        return $key instanceof ObjectId ? (string) $key : (string) $key;
    }

    public function getParentRelation(): ?Relation
    {
        return null;
    }
}

<?php

namespace App\Traits;

use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Builder;

trait GenericUsesCipherSweet
{
    use UsesCipherSweet;

    public function updateBlindIndexes(): void
    {
        foreach (static::$cipherSweetEncryptedRow->getAllBlindIndexes($this->getAttributes()) as $name => $blindIndex) {
            DB::connection($this->connectionName)->table('blind_indexes')->upsert([
                'value' => $blindIndex,
                'indexable_type' => $this->getMorphClass(),
                'indexable_id' => $this->getKey(),
                'name' => $name,
            ], [
                'indexable_type',
                'indexable_id',
                'name',
            ]);
        }
    }

    public function deleteBlindIndexes(): void
    {
        DB::connection($this->connectionName)
            ->table('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->where('indexable_id', $this->getKey())
            ->delete();
    }

    private function buildBlindQuery(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        $blindIndexValue = static::$cipherSweetEncryptedRow
            ->getBlindIndex($indexName, [$column => $value]);

        return $query->select(DB::raw(1))
            ->from('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->whereColumn('indexable_id', $this->getTable() . '.' . $this->getKeyName())
            ->where('name', $indexName)
            ->where('value', $blindIndexValue);
    }
}
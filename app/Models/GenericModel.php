<?php

namespace App\Models;

use App\Helpers\Table\TableHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GenericModel extends Model
{    
    use HasUuids;

    protected $guarded = [];

    public $timestamps = false;

    public static ?Table $tableInstance = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function genericQuery(string | Table $data): Builder
    {
        if(is_string($data)) {
            return (new static)->setTable(TableHelper::uuidToTableName($data))->newQuery();
        } else {
            self::$tableInstance = $data;
            return (new static)->setTable(TableHelper::uuidToTableName($data->id))->newQuery();
        }
    }
}
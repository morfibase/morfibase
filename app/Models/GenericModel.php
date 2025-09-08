<?php

namespace App\Models;

use App\Helpers\Collection\CollectionHelper;
use App\Helpers\StaticInstances\StaticUser;
use App\Traits\GenericUsesCipherSweet;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\Constants;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GenericModel extends Model implements CipherSweetEncrypted
{
    use GenericUsesCipherSweet;
    
    protected $guarded = [];

    public $timestamps = false;

    public static ?Collection $collection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        // if(self::$collection != null) {
        //     foreach(self::$collection['schema'] as $field) {
        //         $col = $field['data']['db_column_name'];

        //         $encryptedRow
        //             ->addField($col, Constants::TYPE_OPTIONAL_TEXT)
        //             ->addBlindIndex($col, new BlindIndex("{$col}_bi"));
        //     }
        // }
    }

    public static function genericQuery(string | Collection $data): Builder
    {
        if(is_string($data)) {
            return (new static)->setTable(CollectionHelper::uuidToTableName($data))->newQuery();
        } else {
            self::$collection = $data;
            return (new static)->setTable(CollectionHelper::uuidToTableName($data->id))->newQuery();
        }
    }
}
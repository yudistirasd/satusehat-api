<?php

namespace Satusehat\Integration\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated This model is no longer used and will be removed in v3.0.0.
 * Token storage has been moved to Laravel Cache.
 */
class SatusehatToken extends Model
{
    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        $connection = config('satusehatintegration.database_connection_satusehat');

        if (! empty($connection)) {
            $this->setConnection($connection);
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.token_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'token';

    public $incrementing = false;

    protected $casts = ['environment' => 'string', 'client_id' => 'string', 'token' => 'string'];
}

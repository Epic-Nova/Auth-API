<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticationProviderScriptFunctionBlacklist extends Model
{
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AuthenticationProviderScriptsFunctionsBlacklist';

    protected $primaryKey = 'AuthenticationProviderScriptFunctionName';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
        ];
    }
}

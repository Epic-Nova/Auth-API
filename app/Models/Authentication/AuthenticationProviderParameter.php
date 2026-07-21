<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationProviderParameter extends Model
{
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AuthenticationProvidersParameters';

    protected $primaryKey = 'AuthenticationProviderParameterIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsParameterRequired' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }

    public function authenticationProvider(): BelongsTo
    {
        return $this->belongsTo(
            AuthenticationProvider::class,
            'AuthenticationProviderIdentifier',
            'AuthenticationProviderIdentifier'
        );
    }

    public function attachableData(): BelongsTo
    {
        return $this->belongsTo(AttachableData::class, 'AttachableDataName', 'AttachableDataName');
    }
}

<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachedAuthenticationProvider extends Model
{
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AttachedAuthenticationProviders';

    protected $primaryKey = 'AttachedAuthenticationProviderIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsPrimary' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }

    public function authenticatable(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'AuthenticatableIdentifier', 'AuthenticatableIdentifier');
    }

    public function authenticationProvider(): BelongsTo
    {
        return $this->belongsTo(
            AuthenticationProvider::class,
            'AuthenticationProviderIdentifier',
            'AuthenticationProviderIdentifier'
        );
    }
}

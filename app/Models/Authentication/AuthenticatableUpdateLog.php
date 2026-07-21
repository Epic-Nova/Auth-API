<?php

namespace App\Models\Authentication;

use Database\Factories\AuthenticatableUpdateLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticatableUpdateLog extends Model
{
    /** @use HasFactory<AuthenticatableUpdateLogFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AuthenticatableUpdateLogs';

    protected $primaryKey = 'AuthenticatableUpdateLogIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bAreValuesSecret' => 'boolean',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function authenticatable(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'AuthenticatableIdentifier', 'AuthenticatableIdentifier');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'UpdateMadeBy', 'AuthenticatableIdentifier');
    }
}

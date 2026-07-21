<?php

namespace App\Models\Authentication;

use Database\Factories\ApprovalEntityDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalEntityData extends Model
{
    /** @use HasFactory<ApprovalEntityDataFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'ApprovalEntityData';

    protected $primaryKey = 'ApprovalEntityDataIdentifier';

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

    public function approvalEntity(): BelongsTo
    {
        return $this->belongsTo(ApprovalEntity::class, 'ApprovalEntityIdentifier', 'ApprovalEntityIdentifier');
    }

    public function authenticatable(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'AuthenticatableIdentifier', 'AuthenticatableIdentifier');
    }
}

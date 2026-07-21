<?php

namespace App\Models\Authentication;

use Database\Factories\ApprovalEntityParameterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalEntityParameter extends Model
{
    /** @use HasFactory<ApprovalEntityParameterFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'ApprovalEntityParameters';

    protected $primaryKey = 'ApprovalEntityParameterIdentifier';

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

    public function attachableData(): BelongsTo
    {
        return $this->belongsTo(AttachableData::class, 'AttachableDataValueReference', 'AttachableDataName');
    }
}

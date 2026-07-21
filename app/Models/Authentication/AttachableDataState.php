<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachableDataState extends Model
{
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AttachableDataStates';

    protected $primaryKey = 'AttachableDataStateIdentifier';

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

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(AttachableData::class, 'AttachableDataParameterName', 'AttachableDataName');
    }

    public function effectedParameter(): BelongsTo
    {
        return $this->belongsTo(
            AttachableData::class,
            'AttachableDataEffectedParameterName',
            'AttachableDataName'
        );
    }
}

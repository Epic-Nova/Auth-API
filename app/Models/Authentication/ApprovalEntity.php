<?php

namespace App\Models\Authentication;

use App\Traits\Relations\ApprovalEntityRelationsTrait;
use Database\Factories\ApprovalEntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalEntity extends Model
{
    /** @use HasFactory<ApprovalEntityFactory> */
    use ApprovalEntityRelationsTrait;

    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'ApprovalEntities';

    protected $primaryKey = 'ApprovalEntityIdentifier';

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

<?php

namespace App\Models\Authentication;

use Database\Factories\AccountNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountNote extends Model
{
    /** @use HasFactory<AccountNoteFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AccountNotes';

    protected $primaryKey = 'AccountNoteIdentifier';

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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'AccountIdentifier', 'AccountIdentifier');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'invoice_id',
        'amount_paid',
        'method',        // CARD | FPX | CASH (example)
        'payment_date',
        'gateway',       // optional (internal gateway name)
        'gateway_ref',   // optional (transaction reference)
        'status',        // success | failed | pending
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount_paid'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

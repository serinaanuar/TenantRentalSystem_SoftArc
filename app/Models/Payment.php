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
        'method',       // card | bank_transfer | cash (example)
        'payment_date',
        'gateway',      // e.g. "Stripe", "ToyyibPay" (optional)
        'gateway_ref',  // transaction reference (optional)
        'status',       // success | failed | pending
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount_paid'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'price',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

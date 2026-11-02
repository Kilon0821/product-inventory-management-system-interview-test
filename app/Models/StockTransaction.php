<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    /**
     *
     * @var string
     */
    protected $table = 'stock_transactions';

    /**
     */
    const CREATED_AT = 'create_at';
    const UPDATED_AT = null;

    /**
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity',
        'action',
        'create_at',
    ];

    /**
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
        'quantity' => 'integer',
        'create_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'barcode', 'category_id', 'quantity', 'price',
        'location', 'aisle', 'image', 'description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Low-stock limit comes from Settings > Barcode Settings (default 10)
    public static function lowThreshold(): int
    {
        return max(1, (int) Setting::get('low_stock_threshold', 10));
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', static::lowThreshold());
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '>', 0)->where('quantity', '<=', static::lowThreshold());
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    // Low + out of stock together (used by the dashboard and the bell icon)
    public function scopeNeedsRestock($query)
    {
        return $query->where('quantity', '<=', static::lowThreshold());
    }

    public function getStatusKeyAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out';
        }

        return $this->quantity <= static::lowThreshold() ? 'low' : 'in';
    }

    public function getStatusAttribute(): string
    {
        return match ($this->status_key) {
            'out' => 'Out of Stock',
            'low' => 'Low Stock',
            default => 'In Stock',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('uploads/products/' . $this->image) : null;
    }
}
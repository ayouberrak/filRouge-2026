<?php

namespace App\Modules\Marketplace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'image',
    ];

    public function orders()
    {
        return $this->hasMany(OrderModel::class, 'product_id');
    }
}

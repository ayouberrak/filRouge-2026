<?php

namespace App\Modules\Marketplace\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    use HasFactory;

    protected $table = 'marketplace_orders';

    protected $fillable = [
        'user_id',
        'product_id',
        'price_at_purchase',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}

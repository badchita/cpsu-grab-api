<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Order extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'order_reference_number',
        'customer_id',
        'vendor_id',
        'driver_id',
        'restaurant_id',
        'total_payment',
        'order_status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = [
            'PENDING' => ['ACCEPTED', 'CANCELLED'],
            'ACCEPTED' => ['PREPARING', 'CANCELLED'],
            'PREPARING' => ['READY'],
            'READY' => ['PICKED_UP'],
            'PICKED_UP' => ['IN_TRANSIT'],
            'IN_TRANSIT' => ['DELIVERED'],
        ];

        return isset($allowedTransitions[$this->order_status]) &&
            in_array($newStatus, $allowedTransitions[$this->order_status]);
    }
}

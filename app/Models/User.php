<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Address;
use App\Models\Restaurant;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'middle_name',
        'contact_number',
        'user_type',
        'date_of_birth',
        'status',
        'gender',
        'is_onboarded',
        'gcash_qr_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {

            // Delete address
            $user->address()?->delete();

            // Delete restaurant + products if vendor
            if ($user->restaurant) {
                $user->restaurant->products()->delete();
                $user->restaurant->delete();
            }

            // Delete carts
            $user->carts()->delete();

            // Delete orders where user is customer
            $user->customerOrders()->delete();

            // Delete orders where user is vendor
            $user->vendorOrders()->delete();

            // Delete orders where user is driver
            $user->driverOrders()->delete();
        });
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    public function customerOrders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function vendorOrders()
    {
        return $this->hasMany(Order::class, 'vendor_id');
    }

    public function driverOrders()
    {
        return $this->hasMany(Order::class, 'driver_id');
    }
}

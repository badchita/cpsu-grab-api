<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'orderReferenceNumber' => $this->order_reference_number,
            'totalPayment' => $this->total_payment,
            'orderStatus'  => $this->order_status,

            'customer'  => UsersResource::make($this->whenLoaded('customer')),
            'vendor'    => UsersResource::make($this->whenLoaded('vendor')),
            'driver'    => UsersResource::make($this->whenLoaded('driver')),
            'restaurant' => RestaurantResource::make($this->whenLoaded('restaurant')),
            'carts'     => CartResource::collection($this->whenLoaded('carts')),

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

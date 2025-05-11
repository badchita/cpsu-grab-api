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
            'id'             => $this->id,
            'totalPayment'   => $this->total_payment,
            'orderStatus'    => $this->order_status,
            'customer'       => new UsersResource($this->whenLoaded('customer')),
            'vendor'         => new UsersResource($this->whenLoaded('vendor')),
            'driver'         => new UsersResource($this->whenLoaded('driver')),
            'restaurant'     => new RestaurantResource($this->whenLoaded('restaurant')),
            'carts'          => CartResource::collection($this->whenLoaded('carts')),
            'createdAt'      => $this->created_at,
            'updatedAt'      => $this->updated_at,
        ];
    }
}

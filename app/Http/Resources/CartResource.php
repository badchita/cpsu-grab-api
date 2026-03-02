<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'id'        => $this->id,
            'userId'    => $this->user_id,
            'productId' => $this->product_id,
            'quantity'  => $this->quantity,
            'isCheckedOut'  => $this->is_checked_out,
            'product'   => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

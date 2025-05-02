<?php

namespace App\Http\Resources;

use App\Models\Addresses;
use App\Models\Parents;
use App\Models\Subjects;
use App\Models\Teachers;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
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
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'middleName' => $this->middle_name,
            'email' => $this->email,
            'userType' => $this->user_type,
            'status' => $this->status,
            'contactNumber' => $this->contact_number,
            'dateOfBirth' => $this->date_of_birth,
            'gender' => $this->gender,
            'isOnboarded' => $this->is_onboarded,
            'address' => $this->whenLoaded('address', function () {
                return [
                    'id' => $this->address->id,
                    'street' => $this->address->street,
                    'barangay' => $this->address->barangay,
                    'city' => $this->address->city,
                    'province' => $this->address->province,
                    'postalCode' => $this->address->postal_code,
                    'country' => $this->address->country,
                    'building' => $this->address->building,
                    'landmark' => $this->address->landmark,
                    'latitude' => $this->address->latitude,
                    'longitude' => $this->address->longitude,
                ];
            }),
        ];
    }
}

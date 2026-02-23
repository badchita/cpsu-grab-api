<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $authId = Auth::id();

        $isUserOne = $this->user_one_id === $authId;

        $otherUser = $isUserOne
            ? $this->userTwo
            : $this->userOne;

        $unreadCount = $isUserOne
            ? $this->user_one_unread
            : $this->user_two_unread;

        return [
            'id' => $this->id,

            'otherUser' => [
                'id' => $otherUser?->id,
                'firstName' => $otherUser?->first_name,
                'lastName' => $otherUser?->last_name,
                'email' => $otherUser?->email,
            ],

            'messages' => MessageResource::collection($this->messages),

            'unreadCount' => $unreadCount,

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

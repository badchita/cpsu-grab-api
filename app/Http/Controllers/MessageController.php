<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function findOrCreateConversation($currentUserId, $otherUserId)
    {
        $conversation = Conversation::where(function ($q) use ($currentUserId, $otherUserId) {
            $q->where('user_one_id', $currentUserId)->where('user_two_id', $otherUserId);
        })->orWhere(function ($q) use ($currentUserId, $otherUserId) {
            $q->where('user_one_id', $otherUserId)->where('user_two_id', $currentUserId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $currentUserId,
                'user_two_id' => $otherUserId,
            ]);
        }

        return $conversation;
    }

    public function sendMessage(Request $request)
    {
        $authId = $request->currentUserId;
        $receiverId = $request->receiver_id;

        $conversation = $this->findOrCreateConversation($authId, $receiverId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $authId,
            'message' => $request->message,
        ]);

        // Increment unread count
        if ($conversation->user_one_id == $receiverId) {
            $conversation->increment('user_one_unread');
        } else {
            $conversation->increment('user_two_unread');
        }

        // Send OneSignal push
        // $this->sendOneSignalPush($receiverId, $request->message);

        return response()->json($message);
    }
}

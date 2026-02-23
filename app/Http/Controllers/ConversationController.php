<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function getConversations($id)
    {
        $authId = Auth::id();

        $conversations = Conversation::where('user_one_id', $authId)
            ->orWhere('user_two_id', $authId)
            ->with([
                'userOne:id,first_name,last_name,email',
                'userTwo:id,first_name,last_name,email',
                'messages' => fn($q) => $q->latest()->limit(1)
            ])
            ->get();

        return ConversationResource::collection($conversations);
    }

    public function getMessages($id)
    {
        $conversation = Conversation::with(['messages', 'userOne', 'userTwo'])->find($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        return new ConversationResource($conversation);
    }
}

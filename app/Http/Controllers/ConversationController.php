<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function getConversations($id)
    {;
        return Conversation::where('user_one_id', $id)
            ->orWhere('user_two_id', $id)
            ->with(['messages' => fn($q) => $q->latest()->limit(1)])
            ->get();
    }
}

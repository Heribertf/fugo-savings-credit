<?php

namespace App\Http\Controllers;

use App\Events\AgentTyping;
use App\Events\SupportMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    public function index()
    {
        return view('support.chat');
    }

    public function getMessages()
    {
        $messages = SupportMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'content' => $message->content,
                    'type' => $message->sender_type,
                    'timestamp' => $message->created_at->toDateTimeString()
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = new SupportMessage([
            'content' => $request->message,
            'user_id' => Auth::id(),
            'sender_type' => 'user'
        ]);

        $message->save();

        broadcast(new SupportMessageSent($message))->toOthers();

        return response()->json(['success' => true]);
    }

    public function typing(Request $request)
    {
        $request->validate([
            'typing' => 'required|boolean',
        ]);

        broadcast(new AgentTyping(
            Auth::id(),
            $request->typing
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    // public function sendMessage(Request $request)
    // {
    //     $message = SupportMessage::create([
    //         'user_id' => auth()->id(),
    //         'message' => $request->message,
    //         'sender' => 'user',
    //     ]);

    //     // Trigger Pusher event
    //     $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
    //         'cluster' => env('PUSHER_APP_CLUSTER'),
    //         'useTLS' => true,
    //     ]);

    //     $pusher->trigger('support-channel', 'new-message', $message);

    //     return response()->json(['success' => true]);
    // }

    // public function getMessages()
    // {
    //     $messages = SupportMessage::where('user_id', auth()->id())->get();
    //     return response()->json($messages);
    // }
}

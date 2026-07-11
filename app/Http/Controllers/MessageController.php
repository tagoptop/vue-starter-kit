<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Send a new message.
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Check authorization
        if ($conversation->admin_id !== $request->user()->id && $conversation->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Only admin/staff can message customers
        if ($request->user()->role === 'customer') {
            abort(403, 'Customers cannot send messages');
        }

        $request->validate([
            'body' => ['required_without:file', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240'], // 10MB max
        ]);

        $filePath = null;
        $fileName = null;

        // Handle file upload if provided
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $storagePath = $file->store('messages/' . $conversation->id, 'public');
            $filePath = '/storage/' . $storagePath;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'body' => $request->body ?? '',
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        // Update conversation's last_message_at
        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', 'Message sent successfully');
    }

    /**
     * Delete a message (only sender can delete).
     */
    public function destroy(Request $request, Message $message)
    {
        if ($message->sender_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Delete file if exists
        if ($message->file_path) {
            $filePath = str_replace('/storage/', '', $message->file_path);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully');
    }
}

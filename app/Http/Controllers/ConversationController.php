<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index(Request $request)
    {
        // Admin/Staff can only see conversations they're part of
        if (!in_array($request->user()->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized');
        }

        $conversations = Conversation::where('admin_id', $request->user()->id)
            ->orWhere('customer_id', $request->user()->id)
            ->with(['admin', 'customer', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return Inertia::render('messenger/index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Request $request, Conversation $conversation)
    {
        // Check authorization
        if ($conversation->admin_id !== $request->user()->id && $conversation->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Only admin/staff can initiate conversations
        if ($request->user()->role === 'customer' && $conversation->customer_id === $request->user()->id) {
            abort(403, 'Customers cannot view conversations');
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();
        $otherUser = $conversation->admin_id === $request->user()->id ? $conversation->customer : $conversation->admin;

        return Inertia::render('messenger/show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => $otherUser,
        ]);
    }

    /**
     * Start a new conversation with a customer.
     */
    public function create(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized');
        }

        $customers = User::where('role', 'customer')->get(['id', 'name', 'email']);

        return Inertia::render('messenger/create', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a new conversation.
     */
    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = User::findOrFail($request->customer_id);

        // Check if customer exists and is a customer role
        if ($customer->role !== 'customer') {
            abort(403, 'Can only message customers');
        }

        // Check if conversation already exists
        $existing = Conversation::where('admin_id', $request->user()->id)
            ->where('customer_id', $request->customer_id)
            ->first();

        if ($existing) {
            return redirect()->route('conversations.show', $existing)->with('info', 'Conversation already exists');
        }

        $conversation = Conversation::create([
            'admin_id' => $request->user()->id,
            'customer_id' => $request->customer_id,
            'subject' => $request->subject,
        ]);

        return redirect()->route('conversations.show', $conversation);
    }
}

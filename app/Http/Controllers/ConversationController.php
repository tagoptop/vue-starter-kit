<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ConversationController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index(Request $request): View
    {
        $conversations = Conversation::where('admin_id', $request->user()->id)
            ->orWhere('customer_id', $request->user()->id)
            ->with(['admin', 'customer', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('messenger.index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Request $request, Conversation $conversation): View
    {
        // Check authorization
        if ($conversation->admin_id !== $request->user()->id && $conversation->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();
        $otherUser = $conversation->admin_id === $request->user()->id ? $conversation->customer : $conversation->admin;

        return view('messenger.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => $otherUser,
        ]);
    }

    /**
     * Start a new conversation with a customer.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $isCustomer = $user->role === 'customer';
        $participants = $isCustomer
            ? User::whereIn('role', ['admin', 'staff'])->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")->orderBy('id')->get(['id', 'name', 'email', 'role'])
            : User::where('role', 'customer')->get(['id', 'name', 'email', 'role']);

        $defaultSupportContact = $isCustomer ? $participants->first() : null;

        return view('messenger.create', [
            'participants' => $participants,
            'defaultSupportContact' => $defaultSupportContact,
            'isCustomer' => $isCustomer,
        ]);
    }

    /**
     * Store a new conversation.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'customer') {
            $request->validate([
                'participant_id' => ['nullable', 'exists:users,id'],
                'subject' => ['nullable', 'string', 'max:255'],
            ]);

            $participant = $request->filled('participant_id')
                ? User::findOrFail($request->participant_id)
                : $this->resolveDefaultSupportContact();

            if (! $participant) {
                return back()->withErrors(['participant_id' => 'No support contact is available right now.']);
            }

            if (! in_array($participant->role, ['admin', 'staff'], true)) {
                abort(403, 'Customers can only message admin or staff users');
            }

            $adminId = $participant->id;
            $customerId = $user->id;
        } else {
            $request->validate([
                'participant_id' => ['required', 'exists:users,id'],
                'subject' => ['nullable', 'string', 'max:255'],
            ]);

            $participant = User::findOrFail($request->participant_id);

            if ($participant->role !== 'customer') {
                abort(403, 'Can only message customers');
            }

            $adminId = $user->id;
            $customerId = $participant->id;
        }

        $existing = Conversation::where('admin_id', $adminId)
            ->where('customer_id', $customerId)
            ->first();

        if ($existing) {
            return redirect()->route('conversations.show', $existing)->with('info', 'Conversation already exists');
        }

        $conversation = Conversation::create([
            'admin_id' => $adminId,
            'customer_id' => $customerId,
            'subject' => $request->subject,
        ]);

        return redirect()->route('conversations.show', $conversation);
    }

    private function resolveDefaultSupportContact(): ?User
    {
        return User::whereIn('role', ['admin', 'staff'])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first(['id', 'name', 'email', 'role']);
    }
}

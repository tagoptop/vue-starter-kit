@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2>Messages</h2>
            <p class="text-muted">Your conversations</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('conversations.create') }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> {{ auth()->user()->role === 'customer' ? 'Contact Support' : 'New Message' }}
            </a>
        </div>
    </div>

    @if($conversations->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted">No conversations yet. {{ auth()->user()->role === 'customer' ? 'Start a support chat when you need help.' : 'Start a new conversation to begin messaging.' }}</p>
                <a href="{{ route('conversations.create') }}" class="btn btn-primary">{{ auth()->user()->role === 'customer' ? 'Contact Support' : 'Start Conversation' }}</a>
            </div>
        </div>
    @else
        <div class="list-group">
            @foreach($conversations as $conversation)
                <a href="{{ route('conversations.show', $conversation->id) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                {{ $conversation->subject }}
                            </h6>
                            <p class="mb-1 text-muted small">
                                @if($conversation->latestMessage)
                                    {{ Str::limit($conversation->latestMessage->body, 50) }}
                                    @if($conversation->latestMessage->file_name)
                                        📎 {{ $conversation->latestMessage->file_name }}
                                    @endif
                                @else
                                    No messages yet
                                @endif
                            </p>
                            <small class="text-muted">
                                With {{ auth()->id() === $conversation->customer_id ? $conversation->admin->name : $conversation->customer->name }}
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">
                                @if($conversation->last_message_at)
                                    {{ $conversation->last_message_at->diffForHumans() }}
                                @else
                                    Just now
                                @endif
                            </small>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

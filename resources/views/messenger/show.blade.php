@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Conversation Header -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $conversation->subject }}</h4>
                        <small class="text-muted">With {{ $conversation->customer->name }}</small>
                    </div>
                    <a href="{{ route('conversations.index') }}" class="btn btn-sm btn-secondary">Back</a>
                </div>
            </div>

            <!-- Messages -->
            <div class="card mb-3" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                <div class="card-body">
                    @if($conversation->messages->isEmpty())
                        <p class="text-muted text-center py-5">No messages yet. Start the conversation!</p>
                    @else
                        @foreach($conversation->messages as $message)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong>{{ $message->sender->name }}</strong>
                                    <small class="text-muted">{{ $message->created_at->format('M d, Y g:i A') }}</small>
                                </div>
                                <div class="card border-light bg-light mb-2">
                                    <div class="card-body">
                                        <p class="mb-0">{{ $message->body }}</p>
                                        @if($message->file_path)
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $message->file_path) }}" class="btn btn-sm btn-outline-primary" download="{{ $message->file_name }}">
                                                    <i class="bi bi-download"></i> {{ $message->file_name }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($message->sender_id === auth()->id())
                                    <form method="post" action="{{ route('messages.destroy', $message->id) }}" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Send Message Form -->
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="post" action="{{ route('messages.store', $conversation->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="body" class="form-label">Message</label>
                            <textarea id="body" name="body" class="form-control @error('body') is-invalid @enderror" rows="3" placeholder="Type your message...">{{ old('body') }}</textarea>
                            @error('body')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                            <input id="attachment" type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" />
                            <small class="text-muted d-block mt-2">Max file size: 10MB</small>
                            @error('attachment')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.querySelector('.card[style*="overflow-y"]');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endsection

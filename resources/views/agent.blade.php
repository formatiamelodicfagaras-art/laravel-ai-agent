@extends('layout')

@section('title', 'AI Agent - Style AI Agent')

@section('styles')
<style>
    .agent-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 200px);
    }

    .chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .btn-clear {
        padding: 0.5rem 1rem;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-clear:hover {
        background: #c0392b;
    }

    .chat-history {
        flex: 1;
        overflow-y: auto;
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        min-height: 400px;
    }

    .chat-message {
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
    }

    .message-question {
        align-items: flex-end;
    }

    .message-answer {
        align-items: flex-start;
    }

    .message-bubble {
        max-width: 70%;
        padding: 1rem;
        border-radius: 12px;
        word-wrap: break-word;
    }

    .message-question .message-bubble {
        background: #3498db;
        color: white;
    }

    .message-answer .message-bubble {
        background: #ecf0f1;
        color: #2c3e50;
        max-width: 90%;
        line-height: 1.6;
    }

    /* Markdown formatting styles */
    .message-answer .message-bubble h1 {
        font-size: 1.5rem;
        margin: 1rem 0 0.5rem 0;
        color: #2c3e50;
    }

    .message-answer .message-bubble h2 {
        font-size: 1.3rem;
        margin: 1rem 0 0.5rem 0;
        color: #34495e;
    }

    .message-answer .message-bubble h3 {
        font-size: 1.1rem;
        margin: 0.8rem 0 0.4rem 0;
        color: #34495e;
    }

    .message-answer .message-bubble table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
        background: white;
    }

    .message-answer .message-bubble table th {
        background: #3498db;
        color: white;
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
    }

    .message-answer .message-bubble table td {
        padding: 0.75rem;
        border-bottom: 1px solid #ddd;
    }

    .message-answer .message-bubble table tr:hover {
        background: #f8f9fa;
    }

    .message-answer .message-bubble strong {
        font-weight: 700;
        color: #2c3e50;
    }

    .message-answer .message-bubble hr {
        border: none;
        border-top: 2px solid #ddd;
        margin: 1.5rem 0;
    }

    .message-answer .message-bubble .warning {
        color: #e74c3c;
        font-weight: bold;
    }

    .message-answer .message-bubble pre {
        background: #f4f4f4;
        padding: 1rem;
        border-radius: 4px;
        overflow-x: auto;
    }

    .message-answer .message-bubble code {
        background: #f4f4f4;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }

    .message-time {
        font-size: 0.75rem;
        color: #95a5a6;
        margin-top: 0.25rem;
    }

    .empty-chat {
        text-align: center;
        color: #95a5a6;
        padding: 3rem;
    }

    .chat-input-form {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .input-group {
        display: flex;
        gap: 1rem;
    }

    .input-group textarea {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        font-family: inherit;
        resize: vertical;
        min-height: 60px;
    }

    .input-group textarea:focus {
        outline: none;
        border-color: #3498db;
    }

    .btn-ask {
        padding: 0.75rem 2rem;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
        align-self: flex-end;
    }

    .btn-ask:hover {
        background: #229954;
    }
</style>
@endsection

@section('content')
<div class="agent-container">
    <div class="chat-header">
        <h2>AI Agent - Întreabă despre datele tale</h2>
        @if(count($chatHistory) > 0)
        <form action="{{ url('/agent/clear') }}" method="POST">
            @csrf
            <button type="submit" class="btn-clear">Șterge istoricul</button>
        </form>
        @endif
    </div>

    <div class="chat-history">
        @if(count($chatHistory) === 0)
            <div class="empty-chat">
                <p>Nu există conversații încă. Pune prima întrebare!</p>
            </div>
        @else
            @foreach($chatHistory as $chat)
                <div class="chat-message message-question">
                    <div class="message-bubble">{{ $chat['question'] }}</div>
                    <div class="message-time">{{ $chat['timestamp'] }}</div>
                </div>
                <div class="chat-message message-answer">
                    <div class="message-bubble">{!! nl2br(e($chat['answer'])) !!}</div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="chat-input-form">
        <form action="{{ url('/agent') }}" method="POST">
            @csrf
            <div class="input-group">
                <textarea name="question" placeholder="Pune o întrebare despre datele din Excel..." required autofocus></textarea>
                <button type="submit" class="btn-ask">Întreabă</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-scroll to bottom of chat history
    document.addEventListener('DOMContentLoaded', function() {
        const chatHistory = document.querySelector('.chat-history');
        chatHistory.scrollTop = chatHistory.scrollHeight;

        // Submit form on Enter (but allow Shift+Enter for new lines)
        const textarea = document.querySelector('textarea[name="question"]');
        if (textarea) {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });
        }

        // Format warning text with red color
        document.querySelectorAll('.message-answer .message-bubble').forEach(bubble => {
            // Replace warning emoji with styled text
            let html = bubble.innerHTML;

            // Make text red and bold where there's ⚠️ emoji
            html = html.replace(/⚠️/g, '<span class="warning">⚠️</span>');

            // Mark values followed by warning as warning text
            html = html.replace(/(\*\*[^*]+\*\*)\s*⚠️/g, '<span class="warning">$1</span> ⚠️');

            // Replace warning phrases with styled version
            html = html.replace(/(valoare estimată|nu a fost specificat[ăa])/gi, '<span class="warning">$1</span>');

            bubble.innerHTML = html;
        });
    });
</script>
@endsection

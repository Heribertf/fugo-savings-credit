@extends('layouts.app')

@section('title', 'Support - Chat with an Agent')

@push('styles')
    <style>
        .chat-container {
            height: 500px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
        }

        .message-user {
            background: #007bff;
            color: white;
            margin-left: auto;
        }

        .message-agent {
            background: #e9ecef;
            color: #212529;
        }

        .chat-input {
            padding: 15px;
            background: #fff;
            border-top: 1px solid #dee2e6;
        }

        .typing-indicator {
            font-size: 0.875rem;
            color: #6c757d;
            font-style: italic;
            margin-bottom: 10px;
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chat Support</h5>
                        <span class="badge bg-success" id="status">Online</span>
                    </div>
                    <div class="card-body chat-container">
                        <div class="chat-messages" id="chat-messages">
                            <!-- Messages will be appended here -->
                        </div>
                        <div class="typing-indicator" id="typing-indicator">
                            Agent is typing...
                        </div>
                        <div class="chat-input">
                            <form id="chat-form">
                                @csrf
                                <div class="input-group">
                                    <input type="text" class="form-control" id="message-input"
                                        placeholder="Type your message..." required>
                                    <button class="btn btn-primary" type="submit">
                                        Send
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script src="https://js.pusher.com/8.0/pusher.min.js"></script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Pusher
            let pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                encrypted: true
            });

            // Subscribe to the channel
            const channel = pusher.subscribe('support-chat.{{ Auth::id() }}');
            const chatMessages = document.getElementById('chat-messages');
            const messageForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const typingIndicator = document.getElementById('typing-indicator');

            // Listen for new messages
            channel.bind('new-message', function(data) {
                console.log('New message received:', data);
                appendMessage(data.message, data.type);
            });

            // Listen for typing indicator
            channel.bind('typing', function(data) {
                if (data.typing) {
                    typingIndicator.style.display = 'block';
                } else {
                    typingIndicator.style.display = 'none';
                }
            });

            // Handle message submission
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const message = messageInput.value.trim();
                if (!message) return;

                // Send message to server
                fetch('/support/send-message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: message
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            messageInput.value = '';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Handle typing events
            let typingTimeout;
            messageInput.addEventListener('input', function() {
                clearTimeout(typingTimeout);

                // Emit typing event
                fetch('/support/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        typing: true
                    })
                });

                // Clear typing indicator after 2 seconds of no input
                typingTimeout = setTimeout(() => {
                    fetch('/support/typing', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            typing: false
                        })
                    });
                }, 2000);
            });

            // function appendMessage(message, type) {
            //     const messageDiv = document.createElement('div');
            //     messageDiv.classList.add('message', type === 'user' ? 'message-user' : 'message-agent');
            //     messageDiv.textContent = message;

            //     chatMessages.appendChild(messageDiv);
            //     chatMessages.scrollTop = chatMessages.scrollHeight;
            // }

            function appendMessage(message, type) {
                // console.log('Appending message:', message, 'Type:', type);
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', type === 'user' ? 'message-user' : 'message-agent');
                messageDiv.textContent = message;

                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }


            // Load previous messages
            fetch('/support/get-messages')
                .then(response => response.json())
                .then(data => {
                    data.messages.forEach(message => {
                        appendMessage(message.content, message.type);
                    });
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
@endpush

@extends('layouts.admin')

@section('title', 'Support Chat Management')

@push('styles')
    <style>
        .chat-list {
            height: 600px;
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
        }

        .chat-messages {
            height: 500px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 20px;
        }

        .chat-item {
            cursor: pointer;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.3s;
        }

        .chat-item:hover {
            background-color: #f8f9fa;
        }

        .chat-item.active {
            background-color: #e9ecef;
        }

        .chat-item.unread {
            background-color: #e7f3ff;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
        }

        .message-user {
            background: #e9ecef;
            margin-right: auto;
        }

        .message-agent {
            background: #007bff;
            color: white;
            margin-left: auto;
        }

        .user-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-online {
            background-color: #28a745;
        }

        .status-offline {
            background-color: #dc3545;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 p">
        <div class="row">
            <!-- Chat List Sidebar -->
            <div class="col-md-4 col-lg-3 p-0">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Active Chats</h5>
                    </div>
                    <div class="chat-list" id="chat-list">
                        <!-- Chat list items will be dynamically added here -->
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-md-8 col-lg-9">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center" id="chat-header">
                        <div class="d-flex align-items-center">
                            <span class="user-status" id="user-status"></span>
                            <h5 class="mb-0" id="current-user">Select a chat</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm me-2" id="view-user-info">
                                View User Info
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="end-chat">
                                End Chat
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="chat-messages" id="chat-messages">
                            <!-- Messages will be dynamically added here -->
                        </div>
                        <div class="p-3 bg-white">
                            <form id="agent-chat-form" class="mb-0">
                                @csrf
                                <div class="input-group">
                                    <input type="text" class="form-control" id="agent-message-input"
                                        placeholder="Type your message..." disabled>
                                    <button class="btn btn-primary" type="submit" disabled>
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

    <!-- User Info Modal -->
    <div class="modal fade" id="userInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="user-info-content">
                        <!-- User information will be dynamically added here -->
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
            let currentUserId = null;
            let pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                encrypted: true
            });

            // Function to load active chats
            function loadActiveChats() {
                fetch('/admin/support/active-chats')
                    .then(response => response.json())
                    .then(data => {
                        const chatList = document.getElementById('chat-list');
                        chatList.innerHTML = '';

                        data.chats.forEach(chat => {
                            const chatItem = document.createElement('div');
                            chatItem.className =
                                `chat-item ${chat.unread ? 'unread' : ''} ${chat.user_id === currentUserId ? 'active' : ''}`;
                            chatItem.setAttribute('data-user-id', chat.user_id);

                            chatItem.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="user-status ${chat.online ? 'status-online' : 'status-offline'}"></span>
                                    <strong>${chat.user_name}</strong>
                                </div>
                                ${chat.unread ? '<span class="badge bg-danger">' + chat.unread_count + '</span>' : ''}
                            </div>
                            <small class="text-muted">${chat.last_message}</small>
                        `;

                            chatItem.addEventListener('click', () => loadChat(chat.user_id));
                            chatList.appendChild(chatItem);
                        });
                    });
            }

            // Function to load chat messages
            function loadChat(userId) {
                currentUserId = userId;

                // Enable input and button
                document.getElementById('agent-message-input').disabled = false;
                document.querySelector('#agent-chat-form button').disabled = false;

                // Load messages
                fetch(`/admin/support/messages/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        const messagesContainer = document.getElementById('chat-messages');
                        messagesContainer.innerHTML = '';

                        // Update header
                        document.getElementById('current-user').textContent = data.user_name;
                        document.getElementById('user-status').className =
                            `user-status ${data.online ? 'status-online' : 'status-offline'}`;

                        // Add messages
                        data.messages.forEach(message => appendMessage(message));
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    });

                // Subscribe to user's channel
                const channel = pusher.subscribe(`private-support-chat.${userId}`);

                channel.bind('new-message', function(data) {
                    appendMessage(data);
                    // Mark message as read
                    fetch(`/admin/support/mark-read/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                });
            }

            // Handle agent message submission
            document.getElementById('agent-chat-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const input = document.getElementById('agent-message-input');
                const message = input.value.trim();

                if (!message || !currentUserId) return;

                fetch('/admin/support/send-message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            user_id: currentUserId,
                            message: message
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            input.value = '';
                        }
                    });
            });

            // Function to append message
            function appendMessage(message) {
                const messagesContainer = document.getElementById('chat-messages');
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', message.type === 'user' ? 'message-user' : 'message-agent');

                messageDiv.innerHTML = `
                <div class="message-content">${message.content}</div>
                <small class="text-muted">${new Date(message.timestamp).toLocaleTimeString()}</small>
            `;

                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Handle user info button
            document.getElementById('view-user-info').addEventListener('click', function() {
                if (!currentUserId) return;

                fetch(`/admin/support/user-info/${currentUserId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('user-info-content').innerHTML = `
                        <p><strong>Name:</strong> ${data.name}</p>
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>Joined:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                        <p><strong>Total Savings:</strong> ${data.total_savings}</p>
                        <p><strong>Active Loans:</strong> ${data.active_loans}</p>
                    `;

                        new bootstrap.Modal(document.getElementById('userInfoModal')).show();
                    });
            });

            // Handle end chat button
            document.getElementById('end-chat').addEventListener('click', function() {
                if (!currentUserId) return;

                if (confirm('Are you sure you want to end this chat session?')) {
                    fetch(`/admin/support/end-chat/${currentUserId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                currentUserId = null;
                                document.getElementById('chat-messages').innerHTML = '';
                                document.getElementById('current-user').textContent = 'Select a chat';
                                document.getElementById('agent-message-input').disabled = true;
                                document.querySelector('#agent-chat-form button').disabled = true;
                                loadActiveChats();
                            }
                        });
                }
            });

            // Load active chats initially
            loadActiveChats();

            // Refresh active chats periodically
            setInterval(loadActiveChats, 30000);

            // Listen for new chat notifications
            const notificationChannel = pusher.subscribe('admin-notifications');
            notificationChannel.bind('new-chat', function(data) {
                // Show notification
                const notification = new Notification('New Support Chat', {
                    body: `New chat request from ${data.user_name}`,
                    icon: '/path/to/notification-icon.png'
                });

                // Reload active chats
                loadActiveChats();
            });

            // Request notification permission
            if (Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
        });
    </script>
@endpush

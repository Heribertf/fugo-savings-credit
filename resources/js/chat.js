// resources/js/chat.js

class ChatManager {
    constructor() {
        this.messageContainer = document.getElementById('chat-messages');
        this.messageForm = document.getElementById('chat-form');
        this.messageInput = document.getElementById('message-input');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.userId = document.querySelector('meta[name="user-id"]').content;
        this.setupEventListeners();
        this.initializeEcho();
    }

    setupEventListeners() {
        if (this.messageForm) {
            this.messageForm.addEventListener('submit', (e) => this.handleMessageSubmit(e));
        }

        if (this.messageInput) {
            this.messageInput.addEventListener('input', _.debounce(() => this.handleTyping(), 300));
        }
    }

    initializeEcho() {
        // Listen for messages on private channel
        window.Echo.private(`support-chat.${this.userId}`)
            .listen('SupportMessageSent', (e) => {
                this.appendMessage(e.message);
                this.playNotificationSound();
            })
            .listen('AgentTyping', (e) => {
                this.handleTypingIndicator(e.typing);
            });
    }

    handleMessageSubmit(e) {
        e.preventDefault();
        const message = this.messageInput.value.trim();
        if (!message) return;

        fetch('/support/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.messageInput.value = '';
                    this.appendMessage({
                        content: message,
                        type: 'user',
                        timestamp: new Date()
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to send message. Please try again.',
                    icon: 'error'
                });
            });
    }

    handleTyping() {
        fetch('/support/typing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ typing: true })
        });
    }

    handleTypingIndicator(isTyping) {
        this.typingIndicator.style.display = isTyping ? 'block' : 'none';
    }

    appendMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', `message-${message.type}`);

        messageDiv.innerHTML = `
            <div class="message-content">${this.escapeHtml(message.content)}</div>
            <small class="text-muted">${moment(message.timestamp).format('LT')}</small>
        `;

        this.messageContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    scrollToBottom() {
        this.messageContainer.scrollTop = this.messageContainer.scrollHeight;
    }

    playNotificationSound() {
        const audio = new Audio('/path/to/notification-sound.mp3');
        audio.play().catch(e => console.log('Audio playback failed:', e));
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('chat-messages')) {
        window.chatManager = new ChatManager();
    }
});
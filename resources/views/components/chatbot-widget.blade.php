@props(['csrfToken' => null])

<div id="chatbot-widget" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Button -->
    <button id="chatbot-toggle" class="bg-neon-cyan hover:bg-neon-cyan text-dark-base rounded-full w-14 h-14 flex items-center justify-center shadow-[0_0_20px_rgba(0,240,255,0.4)] hover:shadow-[0_0_40px_rgba(0,240,255,0.8)] transition-all duration-200 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </button>

    <!-- Chat Panel -->
    <div id="chatbot-panel" class="hidden absolute bottom-16 right-0 w-80 h-96 bg-white rounded-lg shadow-2xl border border-blue-200 flex flex-col max-w-[calc(100vw-2rem)] sm:max-w-sm">
        <!-- Header -->
        <div class="bg-neon-cyan text-dark-base px-3 py-3 rounded-t-lg flex items-center justify-between shadow-[0_0_20px_rgba(0,240,255,0.4)] relative overflow-hidden min-h-[3rem]">
            <!-- Left: Chat Icon -->
            <div class="flex items-center justify-center w-8 h-8 bg-dark-base/20 rounded-full backdrop-blur-sm flex-shrink-0 shadow-[0_0_10px_rgba(0,240,255,0.3)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>

            <!-- Center: Logo -->
            <div class="flex-1 flex justify-center px-2">
                <img
                    src="{{ asset('images/partners_logo/DevThugs_Logo.png') }}"
                    alt="DevThugs Logo"
                    class="h-6 sm:h-8 w-auto object-contain max-w-[120px]"
                >
            </div>

            <!-- Right: Close Button -->
            <button id="chatbot-close" class="flex items-center justify-center w-8 h-8 bg-dark-base/20 rounded-full backdrop-blur-sm hover:bg-dark-base/30 transition-colors flex-shrink-0 shadow-[0_0_10px_rgba(0,240,255,0.3)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Messages Container -->
        <div id="chatbot-messages" class="flex-1 p-3 overflow-y-auto bg-gray-50">
            <div class="text-center text-gray-500 text-sm py-4">
                Hi, how can I help you?
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden px-3 py-2">
            <div class="flex items-center space-x-2">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span class="text-xs text-gray-500">Typing...</span>
            </div>
        </div>

        <!-- Input Form -->
        <div class="p-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <form id="chatbot-form" class="flex space-x-2">
                @csrf
                <input type="hidden" name="session_token" id="session-token">
                <input
                    type="text"
                    id="chatbot-input"
                    name="message"
                    placeholder="Type your message..."
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-neon-cyan focus:border-neon-cyan transition-colors"
                    maxlength="1000"
                    required
                >
                <button
                    type="submit"
                    id="chatbot-send"
                    class="bg-neon-cyan hover:bg-neon-cyan text-dark-base px-4 py-2 rounded-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_0_20px_rgba(0,240,255,0.4)] hover:shadow-[0_0_40px_rgba(0,240,255,0.8)] hover:scale-105 active:scale-95"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const panel = document.getElementById('chatbot-panel');
    const closeBtn = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');
    const typingIndicator = document.getElementById('typing-indicator');
    const sessionTokenInput = document.getElementById('session-token');

    let sessionToken = localStorage.getItem('chatbot_session_token');

    // Toggle chat panel
    toggleBtn.addEventListener('click', function() {
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            input.focus();
            if (sessionToken) {
                sessionTokenInput.value = sessionToken;
                loadPreviousMessages();
            }
        }
    });

    // Close chat panel
    closeBtn.addEventListener('click', function() {
        panel.classList.add('hidden');
    });

    // Send message
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const message = input.value.trim();
        if (!message) return;

        // Disable input while sending
        input.disabled = true;
        sendBtn.disabled = true;

        // Add user message to UI
        addMessage('user', message);
        input.value = '';

        // Show typing indicator
        typingIndicator.classList.remove('hidden');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        try {
            console.log('Sending chatbot request:', {
                url: '/api/chatbot/message',
                message: message,
                session_token: sessionToken
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                addMessage('assistant', 'Security token missing. Please refresh the page and try again.');
                return;
            }

            const response = await fetch('/api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message: message,
                    session_token: sessionToken
                })
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));

            const data = await response.json();
            console.log('Response data:', data);

            if (response.ok && data.success) {
                // Store session token
                if (data.session_token && !sessionToken) {
                    sessionToken = data.session_token;
                    sessionTokenInput.value = sessionToken;
                    localStorage.setItem('chatbot_session_token', sessionToken);
                    console.log('New session token stored:', sessionToken);
                }

                // Add bot response
                addMessage('assistant', data.reply, data.source_type);
            } else {
                // Handle specific error codes
                if (response.status === 422) {
                    console.error('Validation error:', data);
                    addMessage('assistant', 'Your message seems to be too long or invalid. Please try a shorter message.');
                } else if (response.status === 419) {
                    console.error('CSRF token error');
                    addMessage('assistant', 'Session expired. Please refresh the page and try again.');
                } else if (response.status === 500) {
                    console.error('Server error:', data);
                    addMessage('assistant', data.reply || 'Sorry, there was a server error. Please try again later.');
                } else {
                    console.error('Unknown error:', data);
                    addMessage('assistant', data.reply || 'Sorry, there was an unexpected error. Please try again.');
                }
            }
        } catch (error) {
            console.error('Network error:', error);
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                addMessage('assistant', 'Network connection failed. Please check your internet connection and try again.');
            } else {
                addMessage('assistant', 'Sorry, I\'m having trouble connecting right now. Please try again later.');
            }
        } finally {
            // Hide typing indicator and re-enable input
            typingIndicator.classList.add('hidden');
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        }
    });

    // Enter key submits form
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    function addMessage(role, content, sourceType = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-3 ${role === 'user' ? 'text-right' : 'text-left'}`;

        const bubbleClass = role === 'user'
            ? 'bg-neon-cyan text-dark-base shadow-[0_0_15px_rgba(0,240,255,0.4)]'
            : 'bg-white text-gray-800 border border-gray-200';

        messageDiv.innerHTML = `
            <div class="inline-block max-w-xs px-3 py-2 rounded-lg ${bubbleClass} shadow-sm">
                ${escapeHtml(content)}
                ${sourceType ? `<div class="text-xs opacity-70 mt-1">${sourceType}</div>` : ''}
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function loadPreviousMessages() {
        if (!sessionToken) return;

        try {
            const response = await fetch(`/api/chatbot/session/${sessionToken}/messages`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                // Clear welcome message
                messagesContainer.innerHTML = '';

                data.messages.forEach(msg => {
                    addMessage(msg.role, msg.message, msg.source_type);
                });
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush

<style>
#chatbot-panel {
    backdrop-filter: blur(10px);
}
</style>
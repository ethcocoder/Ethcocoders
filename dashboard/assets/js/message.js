/**
 * ETHCO CODERS - Message JavaScript (renamed from chat.js)
 */
(function() {
    'use strict';

    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const receiverId = document.querySelector('input[name="receiver_id"]')?.value;
    const currentUserId = parseInt(document.body.getAttribute('data-user-id') || '0');
    const formAction = chatForm ? chatForm.getAttribute('action') : 'messages_api.php';

    const ChatClient = {
        loadedMessageIds: new Set(),
        pollingInterval: null,
        isPolling: false,
        lastMessageId: null,
        pollMs: 3000,
        init() {
            if (!(chatForm && messageInput && chatMessages && receiverId)) return;
            this.loadInitial();
            this.startPolling();
            this.bindEvents();
        },
        bindEvents() {
            chatForm.addEventListener('submit', (e) => { e.preventDefault(); this.sendMessage(); return false; });
            const sendBtn = document.getElementById('sendMessageBtn');
            if (sendBtn) sendBtn.addEventListener('click', (e) => { e.preventDefault(); this.sendMessage(); });
            messageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this.sendMessage(); }});
            const reloadBtn = document.getElementById('reloadMessagesBtn');
            if (reloadBtn) reloadBtn.addEventListener('click', (e) => { e.preventDefault(); this.refresh(); });
        },
        loadInitial() {
            const form = new FormData();
            form.append('action', 'conversation');
            form.append('user_id', receiverId);
            fetch('messages_api.php', { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form })
                .then(r => r.json())
                .then(data => {
                    if (!(data && data.success && Array.isArray(data.messages))) return;
                    chatMessages.innerHTML = '';
                    data.messages.forEach(msg => {
                        this.addMessageToUI(msg, parseInt(msg.sender_id) === currentUserId);
                        const mid = parseInt(msg.id || msg.message_id);
                        if (!isNaN(mid)) { this.loadedMessageIds.add(mid); this.lastMessageId = mid; }
                    });
                    this.scrollToBottom();
                })
                .catch(e => console.error('Initial load error:', e));
        },
        startPolling() {
            if (this.pollingInterval) return;
            this.pollingInterval = setInterval(() => { if (!this.isPolling) this.refresh(); }, this.pollMs);
        },
        refresh() {
            if (this.isPolling) return; this.isPolling = true;
            const form = new FormData();
            form.append('action', 'conversation');
            form.append('user_id', receiverId);
            if (this.lastMessageId) form.append('since_id', String(this.lastMessageId));
            fetch('messages_api.php', { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: form })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success && Array.isArray(data.messages)) {
                        if (data.messages.length > 0) {
                            data.messages.forEach(msg => {
                                const mid = parseInt(msg.id || msg.message_id);
                                if (!this.loadedMessageIds.has(mid)) {
                                    this.addMessageToUI(msg, parseInt(msg.sender_id) === currentUserId);
                                    if (!isNaN(mid)) { this.loadedMessageIds.add(mid); this.lastMessageId = mid; }
                                }
                            });
                            this.scrollToBottom();
                        }
                    }
                })
                .catch(e => console.error('Poll error:', e))
                .finally(() => { this.isPolling = false; });
        },
        sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return false;
            const submitBtn = document.getElementById('sendMessageBtn');
            if (!submitBtn) return false;
            const originalText = submitBtn.innerHTML; submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            const tempId = 'temp_' + Date.now();
            this.renderPending(tempId, message);
            const formData = new FormData(chatForm);
            fetch(formAction, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success && data.message_data) {
                        this.replacePending(tempId, data.message_data);
                        const mid = parseInt(data.message_data.id);
                        if (!isNaN(mid)) { this.loadedMessageIds.add(mid); this.lastMessageId = mid; }
                    } else {
                        this.markPendingError(tempId, message);
                        messageInput.value = message;
                    }
                })
                .catch(() => { this.markPendingError(tempId, message); messageInput.value = message; })
                .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = originalText; });
            messageInput.value = '';
        },
        addMessageToUI(msg, isSent) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            messageDiv.setAttribute('data-message-id', msg.id || msg.message_id || 'unknown');
            if (msg.created_at) messageDiv.setAttribute('data-created-at', msg.created_at);
            const timeText = this.formatMessageTime(msg.created_at);
            const safeText = this.escapeHtml(msg.message).replace(/\n/g, '<br>');
            const statusIcon = isSent ? '<i class="fas fa-check-double"></i>' : '';
            messageDiv.innerHTML = `
                <div class="message-content">
                    <p>${safeText}</p>
                    <div class="message-time">
                        <span class="time-text">${timeText}</span>
                        ${isSent ? `<span class="message-status sent">${statusIcon}</span>` : ''}
                    </div>
                </div>
            `;
            chatMessages.appendChild(messageDiv);
        },
        renderPending(tempId, text) {
            const el = document.createElement('div');
            el.className = 'message sent message-pending';
            el.setAttribute('data-message-id', tempId);
            el.innerHTML = `
                <div class="message-content pending">
                    <p>${this.escapeHtml(text)}</p>
                    <div class="message-time"><span class="time-text">now</span><span class="message-status pending"><i class="fas fa-clock"></i></span></div>
                </div>
            `;
            chatMessages.appendChild(el);
            this.scrollToBottom();
        },
        replacePending(tempId, realMessage) {
            const el = chatMessages.querySelector(`[data-message-id="${tempId}"]`);
            if (!el) { this.addMessageToUI(realMessage, true); return; }
            el.setAttribute('data-message-id', realMessage.id);
            el.setAttribute('data-created-at', realMessage.created_at);
            const timeText = this.formatMessageTime(realMessage.created_at);
            el.querySelector('.message-content').classList.remove('pending');
            el.querySelector('.message-content').innerHTML = `
                <p>${this.escapeHtml(realMessage.message)}</p>
                <div class="message-time"><span class="time-text">${timeText}</span><span class="message-status sent"><i class="fas fa-check-double"></i></span></div>
            `;
        },
        markPendingError(tempId, text) {
            const el = chatMessages.querySelector(`[data-message-id="${tempId}"]`);
            if (!el) return;
            el.classList.add('message-error');
            el.querySelector('.message-content').innerHTML = `
                <p style="opacity:.8;">${this.escapeHtml(text)}</p>
                <div class="message-time"><span class="text-danger small">Failed to send</span></div>
            `;
        },
        scrollToBottom() {
            chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: 'smooth' });
        },
        escapeHtml(text) { const d = document.createElement('div'); d.textContent = text || ''; return d.innerHTML; },
        formatMessageTime(dateString) {
            if (!dateString) return 'now';
            const date = new Date(dateString); if (isNaN(date.getTime())) return 'now';
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
        }
    };

    ChatClient.init();
})();



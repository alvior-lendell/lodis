export function initItChatbot() {
    const toggleBtn = document.getElementById('it-chatbot-toggle-btn');
    const linkTrigger = document.getElementById('it-chat-link-trigger');
    const closeBtn = document.getElementById('it-chat-close-btn');
    const chatWindow = document.getElementById('it-chat-window');
    const openIcon = document.getElementById('it-chat-open-icon');
    const closeIcon = document.getElementById('it-chat-close-icon');
    const chatForm = document.getElementById('it-chat-form');
    const chatInput = document.getElementById('it-chat-input');
    const chatMessages = document.getElementById('it-chat-messages');
    const typingIndicator = document.getElementById('it-chat-typing');

    if (!toggleBtn || !chatWindow) return;

    let isOpen = false;

    const openChat = () => {
        isOpen = true;
        chatWindow.classList.remove('hidden');
        chatWindow.classList.add('flex');
        setTimeout(() => {
            chatWindow.classList.remove('scale-95', 'opacity-0');
            chatWindow.classList.add('scale-100', 'opacity-100');
            openIcon?.classList.add('hidden');
            closeIcon?.classList.remove('hidden');
            chatInput?.focus();
        }, 10);
    };

    const closeChat = () => {
        isOpen = false;
        chatWindow.classList.remove('scale-100', 'opacity-100');
        chatWindow.classList.add('scale-95', 'opacity-0');
        openIcon?.classList.remove('hidden');
        closeIcon?.classList.add('hidden');
        setTimeout(() => {
            chatWindow.classList.remove('flex');
            chatWindow.classList.add('hidden');
        }, 300);
    };

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        isOpen ? closeChat() : openChat();
    });

    linkTrigger?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        openChat();
    });

    closeBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        closeChat();
    });

    const appendMessage = (sender, text) => {
        if (!chatMessages) return;
        const wrapper = document.createElement('div');
        wrapper.className = sender === 'user' 
            ? 'flex items-start justify-end gap-2.5' 
            : 'flex items-start gap-2.5';

        if (sender === 'user') {
            wrapper.innerHTML = `
                <div class="bg-brand text-white p-3 rounded-2xl rounded-tr-sm shadow-sm max-w-[85%]">
                    <p>${text}</p>
                </div>
            `;
        } else {
            wrapper.innerHTML = `
                <div class="w-7 h-7 rounded-xl bg-brand text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-sm">
                    IT
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-sm border border-slate-200/80 shadow-sm text-slate-700 max-w-[85%] space-y-2">
                    ${text}
                </div>
            `;
        }

        chatMessages.appendChild(wrapper);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    const processBotReply = (userQuery) => {
        const query = userQuery.toLowerCase();
        typingIndicator?.classList.remove('hidden');
        typingIndicator?.classList.add('flex');
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

        setTimeout(() => {
            typingIndicator?.classList.remove('flex');
            typingIndicator?.classList.add('hidden');

            if (query.includes('password') || query.includes('reset')) {
                appendMessage('bot', `
                    <p class="font-bold">Password Reset Guide:</p>
                    <p>1. Go to your <a href="${window.location.origin}/profile#password" class="text-brand font-bold underline">Profile Settings</a> to change active passwords.</p>
                    <p>2. If locked out, use the <b>Forgot Password</b> link on the sign-in screen.</p>
                    <p class="text-[11px] text-slate-500">Passcodes expire every 90 days as per enterprise security policy.</p>
                `);
            } else if (query.includes('access') || query.includes('role') || query.includes('system')) {
                appendMessage('bot', `
                    <p class="font-bold">System Access Requests:</p>
                    <p>Access permissions (LODISv2, LOPIS, LORIS, etc.) are granted by Superadmins or System Administrators.</p>
                    <p>Please send your employee ID and system role requirement to <a href="mailto:it-support@lendell.ph?subject=System%20Access%20Request" class="text-brand font-bold underline">it-support@lendell.ph</a>.</p>
                `);
            } else if (query.includes('email') || query.includes('contact') || query.includes('human') || query.includes('ticket')) {
                appendMessage('bot', `
                    <p class="font-bold">Contact IT Helpdesk:</p>
                    <p>• <b>Email:</b> <a href="mailto:it-support@lendell.ph" class="text-brand font-bold underline">it-support@lendell.ph</a></p>
                    <p>• <b>Internal Hotline:</b> Ext. 108 / 109</p>
                    <p>• <b>Schedule:</b> Mon-Fri (8:00 AM - 6:00 PM PST)</p>
                `);
            } else {
                appendMessage('bot', `
                    <p>Thanks for reaching out! For specific issues, you can submit a support ticket directly to our team.</p>
                    <p><a href="mailto:it-support@lendell.ph?subject=Support%20Request%3A%20${encodeURIComponent(userQuery)}" class="inline-block mt-1 px-3 py-1 rounded-lg bg-brand text-white font-bold text-[11px]">Click here to send email ticket</a></p>
                `);
            }
        }, 800);
    };

    chatForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = chatInput?.value.trim();
        if (!text) return;

        appendMessage('user', text);
        if (chatInput) chatInput.value = '';
        processBotReply(text);
    });

    document.querySelectorAll('.quick-prompt-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const promptText = btn.getAttribute('data-prompt');
            if (promptText) {
                appendMessage('user', promptText);
                processBotReply(promptText);
            }
        });
    });
}

// Auto-run across initial load and DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initItChatbot);
} else {
    initItChatbot();
}
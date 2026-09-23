<!-- Floating IT Support Chatbot Widget -->
<div id="it-chatbot-widget" class="fixed bottom-5 right-5 z-[9999] flex flex-col items-end pointer-events-none">

    <!-- Chat Window Container -->
    <div id="it-chat-window" class="pointer-events-auto hidden w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden flex-col transition-all duration-300 transform scale-95 opacity-0 origin-bottom-right mb-3">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-4 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-2xl bg-brand/20 border border-brand/40 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5 text-brand-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-slate-900"></span>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white flex items-center gap-1.5">
                        LODIS Helpdesk Assistant
                    </h3>
                    <p class="text-[10px] text-slate-400">Automated IT Support • Online</p>
                </div>
            </div>
            <button type="button" id="it-chat-close-btn" class="p-1.5 rounded-xl hover:bg-white/10 text-slate-400 hover:text-white transition focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Chat Messages Area -->
        <div id="it-chat-messages" class="p-4 h-80 overflow-y-auto space-y-3 bg-slate-50/60 text-xs">
            
            <!-- Bot Welcome Message -->
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-xl bg-brand text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-sm">
                    IT
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-sm border border-slate-200/80 shadow-sm text-slate-700 max-w-[85%] space-y-2">
                    <p>Hello! I am your LODISv2 Helpdesk Virtual Assistant.</p>
                    <p class="text-[11px] text-slate-500">How can I assist you today?</p>
                </div>
            </div>

            <!-- Quick Action Options -->
            <div id="it-quick-suggestions" class="pl-9 space-y-1.5">
                <button type="button" data-prompt="I need to reset my password" class="quick-prompt-btn block w-full text-left px-3 py-1.5 rounded-xl bg-white hover:bg-brand-light border border-slate-200 text-brand text-[11px] font-bold transition">
                    🔒 Reset My Password
                </button>
                <button type="button" data-prompt="Request system access or role update" class="quick-prompt-btn block w-full text-left px-3 py-1.5 rounded-xl bg-white hover:bg-brand-light border border-slate-200 text-brand text-[11px] font-bold transition">
                    🔑 Request System Access
                </button>
                <button type="button" data-prompt="Report a technical issue or bug" class="quick-prompt-btn block w-full text-left px-3 py-1.5 rounded-xl bg-white hover:bg-brand-light border border-slate-200 text-brand text-[11px] font-bold transition">
                    🐛 Report System Issue
                </button>
                <button type="button" data-prompt="Contact human IT support agent" class="quick-prompt-btn block w-full text-left px-3 py-1.5 rounded-xl bg-white hover:bg-brand-light border border-slate-200 text-brand text-[11px] font-bold transition">
                    ✉️ Email IT Helpdesk
                </button>
            </div>

        </div>

        <!-- Typing Indicator Container -->
        <div id="it-chat-typing" class="hidden px-4 py-1.5 bg-slate-50/60 text-[10px] text-slate-400 font-medium italic items-center gap-1.5">
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span>
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
            <span class="ml-1">Assistant is typing...</span>
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-white border-t border-slate-200/80">
            <form id="it-chat-form" class="flex items-center gap-2">
                <input type="text" id="it-chat-input" placeholder="Type a message or issue..." 
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-100 border border-transparent focus:border-brand focus:bg-white text-xs text-slate-800 placeholder-slate-400 focus:outline-none transition">
                <button type="submit" id="it-chat-send-btn" 
                    class="p-2 rounded-xl bg-brand text-white hover:bg-brand-hover transition shrink-0 focus:outline-none shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>

    <!-- Floating Toggle Launcher Button -->
    <button type="button" id="it-chatbot-toggle-btn" class="pointer-events-auto w-12 h-12 rounded-full bg-brand hover:bg-brand-hover text-white shadow-xl flex items-center justify-center transition-all duration-300 transform hover:scale-105 active:scale-95 border-2 ring-4 ring-brand/20 border-white focus:outline-none">
        <svg id="it-chat-open-icon" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <svg id="it-chat-close-icon" class="w-6 h-6 hidden transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

</div>
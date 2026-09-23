<!-- Footer Component -->
<footer class="bg-white border-t border-slate-200/80 mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            
            <!-- Brand & Copyright -->
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 text-slate-500 text-center sm:text-left">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/LODISv2.png') }}" alt="LODISv2" class="h-5 w-auto opacity-75 object-contain">
                </div>
                <span class="hidden sm:inline text-slate-300">•</span>
                <span>&copy; {{ date('Y') }} Lendell Outsourcing Solutions, Inc. All rights reserved.</span>
            </div>

            <!-- System Operational Status Badge & Version -->
            <div class="flex items-center gap-4 text-slate-500">
                <div id="system-status-badge" class="flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200/80 font-medium transition-colors duration-300">
                    <span id="footer-global-status-dot" class="w-2 h-2 rounded-full {{ $footerIsOperational ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <span id="footer-global-status-text" class="text-[11px] text-slate-600">{{ $footerStatusLabel }}</span>
                </div>
                <span class="text-slate-300">•</span>
                <span class="font-mono text-[11px] text-slate-400">v2.1.0</span>
            </div>

            <!-- Essential Links -->
            <div class="flex items-center gap-4 text-slate-500 font-medium">
                <a href="#" class="hover:text-brand transition">Privacy Policy</a>
                <a href="#" class="hover:text-brand transition">Terms of Service</a>
            </div>

        </div>
    </div>
</footer>
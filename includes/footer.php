    <!-- Mobile Bottom Navigation -->
    <div class="md:hidden mobile-bottom-nav">
        <div class="bg-slate-900/90 backdrop-blur-xl rounded-full p-2 flex justify-between items-center border border-white/10 shadow-2xl">
            <a href="index.php" class="w-14 h-14 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-white text-emerald-600' : 'text-slate-400'; ?> rounded-full flex items-center justify-center shadow-lg transition-transform active:scale-90">
                <i class="fas fa-home text-lg"></i>
            </a>
            <button onclick="document.querySelector('input[type=\'text\']').focus(); window.scrollTo({top: 0, behavior: 'smooth'});" 
                    class="w-14 h-14 text-slate-400 flex items-center justify-center transition-transform active:scale-95">
                <i class="fas fa-search text-lg"></i>
            </button>
            <button onclick="switchView('work')" 
                    class="w-14 h-14 text-slate-400 flex items-center justify-center transition-transform active:scale-95">
                <i class="fas fa-briefcase text-lg"></i>
            </button>
            <a href="profile.php" 
               class="w-14 h-14 <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-white text-emerald-600' : 'text-slate-400'; ?> rounded-full flex items-center justify-center shadow-lg transition-transform active:scale-95 font-black text-xs">
                <?php echo isset($_SESSION['user_id']) ? substr($_SESSION['name'], 0, 1) : '<i class="fas fa-user text-lg"></i>'; ?>
            </a>
        </div>
    </div>

    <!-- AI Chat Components -->
    <div id="ai-chat-modal" class="fixed inset-0 z-[110] hidden">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" onclick="toggleAIChat()"></div>
        <div class="absolute bottom-20 right-4 left-4 md:bottom-32 md:right-8 md:left-auto md:w-96 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden flex flex-col animate-in slide-in-from-bottom-10 duration-500">
            <!-- AI Header -->
            <div class="vibrant-gradient p-6 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-sm tracking-tight"><?php echo $t['ai_assistant'] ?? 'Bidding Assistant'; ?></h4>
                        <p class="text-[10px] text-white/70 font-bold uppercase tracking-widest"><?php echo $t['ai_online'] ?? 'Online & Ready'; ?></p>
                    </div>
                </div>
                <button onclick="toggleAIChat()" class="text-white/60 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Chat Body -->
            <div id="ai-chat-body" class="flex-1 p-6 space-y-4 max-h-[400px] overflow-y-auto no-scrollbar bg-slate-50/50">
                <div class="bg-white p-4 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm text-xs text-slate-600 font-medium leading-relaxed">
                    <?php echo $t['ai_greeting'] ?? "Hello! I'm your MbokaHub Assistant. Describe your project, and I'll help you estimate a fair budget."; ?>
                </div>
            </div>
            <!-- Chat Input -->
            <div class="p-4 bg-white border-t border-slate-100">
                <div class="relative">
                    <input type="text" id="ai-input" placeholder="<?php echo $t['ai_placeholder'] ?? 'Ask about job pricing...'; ?>" 
                           class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-xl px-4 py-3 text-xs font-bold outline-none transition-all pr-12">
                    <button onclick="sendAIQuery()" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center hover:bg-emerald-500 transition-all">
                        <i class="fas fa-paper-plane text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Assistant Bubble -->
    <div class="fixed bottom-24 right-6 md:bottom-8 md:right-8 z-[105]">
        <button onclick="toggleAIChat()" class="w-14 h-14 md:w-16 md:h-16 vibrant-gradient text-white rounded-2xl shadow-2xl flex items-center justify-center hover:rotate-12 transition-all group scale-100 hover:scale-110 active:scale-95">
            <i class="fas fa-comment-dots text-2xl"></i>
            <div class="absolute bottom-full right-0 mb-4 w-48 bg-slate-900 text-white text-xs p-3 rounded-xl opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 pointer-events-none">
                "<?php echo $t['ai_floating_desc'] ?? "Hi! I'm your MbokaHub AI. Need help finding a Fundi?"; ?>"
            </div>
        </button>
    </div>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <footer class="bg-white border-t border-slate-100 py-12 md:py-20 mt-20">
        <div class="max-w-7xl mx-auto px-4 md:px-6 text-center">
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4">© 2024 MbokaHub Artisan Marketplace</p>
            <div class="flex items-center justify-center gap-6">
                <a href="#" class="text-slate-400 hover:text-emerald-500 transition-colors"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-slate-400 hover:text-emerald-500 transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-slate-400 hover:text-emerald-500 transition-colors"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
/**
 * Communications Center - MbokaHub Admin
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';

$page_title = "Comms Control";
include 'includes/header.php';
?>

<header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
    <div>
        <h2 class="text-4xl font-black text-slate-900 tracking-tight">Comms Control</h2>
        <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">Global Broadcast & Support Hub</p>
    </div>
    
    <div class="flex items-center gap-3">
        <button class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-100 hover:scale-[1.02] transition-all flex items-center gap-3">
            <i class="fas fa-paper-plane text-xs"></i> New Broadcast
        </button>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        <!-- Message Queue Simulation -->
        <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
            <h3 class="text-xl font-black text-slate-900 mb-8">System Broadcasts</h3>
            
            <div class="space-y-6">
                <!-- Sample 1 -->
                <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 flex gap-6">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-bullhorn text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-sm font-black text-slate-900">Platform Update v1.2</span>
                            <span class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-md">LIVE</span>
                        </div>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">Introducing the new TVET institution portals for better fundi verification and tracking...</p>
                        <p class="text-[10px] text-slate-400 font-bold mt-4 uppercase tracking-widest">Sent to: All Registered Users • 2 days ago</p>
                    </div>
                </div>

                <!-- Sample 2 -->
                <div class="p-6 bg-white border border-slate-100 rounded-[2rem] flex gap-6">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-sm font-black text-slate-900">Scheduled Maintenance</span>
                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">DRAFT</span>
                        </div>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">MbokaHub will be undergoing database optimization this Sunday at midnight...</p>
                        <p class="text-[10px] text-slate-400 font-bold mt-4 uppercase tracking-widest">Target: Selected Hirers • Oct 24</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Desk Placeholder -->
        <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center mb-10">
                <h3 class="text-xl font-black text-slate-900">Help Desk Queue</h3>
                <span class="px-4 py-1.5 bg-rose-50 text-rose-500 text-[10px] font-black rounded-xl">3 TICKETS URGENT</span>
            </div>
            
            <div class="py-12 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-6">
                    <i class="fas fa-comments-alt text-2xl"></i>
                </div>
                <p class="text-slate-400 font-bold">No active support threads</p>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="space-y-6">
        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white">
            <h4 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-8">Reach Metrics</h4>
            
            <div class="space-y-8">
                <div>
                    <div class="flex justify-between text-xs font-black mb-2 uppercase tracking-widest">
                        <span>Email Delivery</span>
                        <span class="text-emerald-500">99.8%</span>
                    </div>
                    <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: 99.8%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-black mb-2 uppercase tracking-widest">
                        <span>SMS Gateway</span>
                        <span class="text-amber-500">82.1%</span>
                    </div>
                    <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500" style="width: 82.1%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-black mb-2 uppercase tracking-widest">
                        <span>Push Notifications</span>
                        <span class="text-indigo-400">91.4%</span>
                    </div>
                    <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-400" style="width: 91.4%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 text-center">
                <p class="text-xs text-slate-400 mb-6 leading-relaxed">Integration via <strong>Postmark</strong> and <strong>Twilio</strong> for high-availability communication.</p>
                <button class="w-full py-4 bg-white/5 border border-white/10 text-white rounded-2xl font-bold text-sm hover:bg-white/10 transition-all">Gateway Settings</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

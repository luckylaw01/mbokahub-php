<?php
/**
 * Search Page - MbokaHub
 * Multi-entity, query-friendly live search for jobs and artisans.
 */
require_once 'includes/db_connect.php';
session_start();

$page_title = "Search Portal";
$current_lang = $_SESSION['current_lang'] ?? 'en';

if (!isset($t)) {
    require_once 'includes/translations.php';
    $t = $lang[$current_lang];
}

include 'includes/header.php';
?>

<main class="max-w-7xl mx-auto px-4 md:px-6 py-8 md:py-12">
    <!-- Search Header & Input -->
    <div class="max-w-3xl mx-auto text-center mb-10">
        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4 tracking-tight">Search <span class="text-emerald-500">MbokaHub</span></h2>
        <p class="text-slate-500 font-semibold mb-8 text-sm md:text-base">Find open jobs, project requests, and TVET certified experts instantly.</p>
        
        <div class="relative max-w-2xl mx-auto shadow-2xl shadow-slate-100 rounded-3xl overflow-hidden border-2 border-slate-100 bg-white focus-within:border-emerald-500/20 transition-all p-2 flex items-center">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-slate-400 text-lg">
                <i class="fas fa-search"></i>
            </div>
            <input type="text" id="live-search-input" autofocus placeholder="Search jobs by name or location, or search artisans by skill..." 
                   class="flex-1 bg-transparent px-2 py-4 text-sm font-bold text-slate-800 placeholder:text-slate-400 outline-none">
            <button onclick="performSearch()" class="px-6 py-4 bg-slate-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-600 transition-all">
                Search
            </button>
        </div>
    </div>

    <!-- Search Controls / Tabs -->
    <div class="flex justify-center mb-8 border-b border-slate-100 pb-4">
        <div class="bg-slate-200/50 p-1 rounded-2xl flex items-center gap-1">
            <button onclick="setSearchType('all')" id="tab-all" class="px-5 py-2.5 bg-white text-emerald-600 shadow-sm font-black text-xs uppercase tracking-wider rounded-xl transition-all">
                All Matches
            </button>
            <button onclick="setSearchType('jobs')" id="tab-jobs" class="px-5 py-2.5 text-slate-500 hover:text-slate-900 font-bold text-xs uppercase tracking-wider rounded-xl transition-all">
                Open Jobs
            </button>
            <button onclick="setSearchType('artisans')" id="tab-artisans" class="px-5 py-2.5 text-slate-500 hover:text-slate-900 font-bold text-xs uppercase tracking-wider rounded-xl transition-all">
                Artisans
            </button>
        </div>
    </div>

    <!-- Results Area -->
    <div id="search-initial-state" class="text-center py-20 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm max-w-4xl mx-auto animate-in fade-in duration-300">
        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl">
            <i class="fas fa-search-plus"></i>
        </div>
        <h3 class="text-xl font-black text-slate-900 mb-2">Type to Search</h3>
        <p class="text-slate-400 font-bold text-xs max-w-sm mx-auto leading-relaxed uppercase tracking-wider">Start typing to search matching jobs, skills, locations, and experts in real-time.</p>
    </div>

    <div id="search-results-container" class="hidden animate-in fade-in duration-500">
        <!-- Dual Column Grid when showing all, single column when filtered -->
        <div id="results-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Jobs Column -->
            <div id="jobs-column" class="space-y-6">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-2 px-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-sm"><i class="fas fa-briefcase"></i></span>
                    Jobs & Requests (<span id="jobs-count">0</span>)
                </h3>
                <div id="jobs-results-list" class="space-y-4">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Artisans Column -->
            <div id="artisans-column" class="space-y-6">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-2 px-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-sm"><i class="fas fa-user-tie"></i></span>
                    Experts & Fundis (<span id="artisans-count">0</span>)
                </h3>
                <div id="artisans-results-list" class="space-y-4">
                    <!-- Dynamic content -->
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Live Search Logic -->
<script>
let currentSearchType = 'all';
let searchTimeout = null;

const searchInput = document.getElementById('live-search-input');
const initialState = document.getElementById('search-initial-state');
const resultsContainer = document.getElementById('search-results-container');
const jobsColumn = document.getElementById('jobs-column');
const artisansColumn = document.getElementById('artisans-column');
const resultsGrid = document.getElementById('results-grid');

const jobsList = document.getElementById('jobs-results-list');
const artisansList = document.getElementById('artisans-results-list');

const jobsCount = document.getElementById('jobs-count');
const artisansCount = document.getElementById('artisans-count');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length === 0) {
        initialState.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 250); // Debounce
});

function setSearchType(type) {
    currentSearchType = type;
    
    // Manage tab styles
    const tabs = ['all', 'jobs', 'artisans'];
    tabs.forEach(t => {
        const btn = document.getElementById('tab-' + t);
        if (t === type) {
            btn.className = "px-5 py-2.5 bg-white text-emerald-600 shadow-sm font-black text-xs uppercase tracking-wider rounded-xl transition-all";
        } else {
            btn.className = "px-5 py-2.5 text-slate-500 hover:text-slate-900 font-bold text-xs uppercase tracking-wider rounded-xl transition-all";
        }
    });

    // Layout configuration
    if (type === 'jobs') {
        jobsColumn.classList.remove('hidden');
        artisansColumn.classList.add('hidden');
        resultsGrid.className = "grid grid-cols-1 max-w-3xl mx-auto";
    } else if (type === 'artisans') {
        jobsColumn.classList.add('hidden');
        artisansColumn.classList.remove('hidden');
        resultsGrid.className = "grid grid-cols-1 max-w-3xl mx-auto";
    } else {
        jobsColumn.classList.remove('hidden');
        artisansColumn.classList.remove('hidden');
        resultsGrid.className = "grid grid-cols-1 lg:grid-cols-2 gap-8";
    }
    
    performSearch();
}

function performSearch() {
    const q = searchInput.value.trim();
    if (q.length === 0) return;

    fetch(`ajax/live_search.php?q=${encodeURIComponent(q)}&type=${currentSearchType}&t=${Date.now()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderResults(data);
            } else {
                console.error(data.message);
            }
        })
        .catch(err => console.error('Live search error:', err));
}

function renderResults(data) {
    initialState.classList.add('hidden');
    resultsContainer.classList.remove('hidden');

    // 1. Render Jobs
    jobsList.innerHTML = '';
    const jobs = data.jobs || [];
    jobsCount.innerText = jobs.length;
    
    if (jobs.length === 0) {
        jobsList.innerHTML = `
            <div class="text-center p-8 bg-slate-50 rounded-[2rem] border border-slate-100 border-dashed">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No matching jobs found</p>
            </div>
        `;
    } else {
        jobs.forEach(job => {
            const urgencyBadge = job.urgency === 'emergency' 
                ? '<span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg text-[9px] font-black uppercase tracking-wider">Emergency</span>'
                : '<span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-wider">Standard</span>';
            
            jobsList.innerHTML += `
                <a href="get_job_details.php?id=${job.id}" class="block bg-white rounded-[2rem] border border-slate-100 p-6 hover:shadow-xl transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600">${job.cat_name || 'General'}</span>
                        <div class="flex gap-2">${urgencyBadge}</div>
                    </div>
                    <h4 class="font-black text-slate-900 mb-2 text-base leading-snug">${job.title}</h4>
                    <p class="text-xs text-slate-500 font-medium mb-4 line-clamp-2">${job.description}</p>
                    <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-location-dot"></i> ${job.location || 'Remote/TBD'}
                        </span>
                        <span class="text-xs font-black text-slate-900 bg-slate-50 px-3 py-1 rounded-xl">Ksh ${Number(job.budget_range).toLocaleString()}</span>
                    </div>
                </a>
            `;
        });
    }

    // 2. Render Artisans
    artisansList.innerHTML = '';
    const artisans = data.artisans || [];
    artisansCount.innerText = artisans.length;

    if (artisans.length === 0) {
        artisansList.innerHTML = `
            <div class="text-center p-8 bg-slate-50 rounded-[2rem] border border-slate-100 border-dashed">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No matching experts found</p>
            </div>
        `;
    } else {
        artisans.forEach(art => {
            const avatar = art.avatar_url || 'assets/images/profiles/default.png';
            
            // Generate skills pills
            let skillsPills = '';
            if (art.skills) {
                const skillsArr = art.skills.split(',').map(s => s.trim()).slice(0, 3);
                skillsArr.forEach(skill => {
                    if (skill) {
                        skillsPills += `<span class="bg-slate-50 text-slate-500 px-2 py-0.5 rounded text-[8px] font-black border border-slate-100">${skill}</span>`;
                    }
                });
            }

            artisansList.innerHTML += `
                <a href="fundi/portfolio/index.php?id=${art.id}" class="block bg-white rounded-[2rem] border border-slate-100 p-6 hover:shadow-xl transition-all shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 overflow-hidden shrink-0 shadow-sm border border-slate-50">
                            <img src="${avatar}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-black text-slate-900 text-sm truncate">${art.first_name} ${art.last_name}</h4>
                                <div class="flex items-center gap-0.5 text-amber-400 text-[10px] shrink-0 font-bold">
                                    <i class="fas fa-star"></i> ${Number(art.rating).toFixed(1)}
                                </div>
                            </div>
                            <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-2">${art.specialty || 'Expert'}</p>
                            <p class="text-xs text-slate-400 line-clamp-2 mb-3 leading-relaxed">${art.bio || ''}</p>
                            
                            <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                                <div class="flex gap-1 min-w-0 mr-2">${skillsPills}</div>
                                <span class="text-[9px] font-bold text-slate-400 shrink-0 uppercase tracking-tighter">
                                    <i class="fas fa-location-dot"></i> ${art.location || 'Kenya'}
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            `;
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>

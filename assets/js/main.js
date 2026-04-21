function toggleAIChat() {
    const modal = document.getElementById('ai-chat-modal');
    modal.classList.toggle('hidden');
    if (!modal.classList.contains('hidden')) {
        document.getElementById('ai-input').focus();
    }
}

function sendAIQuery() {
    const input = document.getElementById('ai-input');
    const body = document.getElementById('ai-chat-body');
    const query = input.value.trim();

    if (!query) return;

    // User message
    body.innerHTML += `
        <div class="flex justify-end p-2 px-0">
            <div class="bg-slate-900 text-white p-4 rounded-2xl rounded-tr-none text-xs font-bold max-w-[80%] animate-in slide-in-from-right-4">
                ${query}
            </div>
        </div>
    `;

    input.value = '';
    body.scrollTop = body.scrollHeight;

    // Simulate AI thinking
    const thinkingId = 'thinking-' + Date.now();
    body.innerHTML += `
        <div id="${thinkingId}" class="flex gap-2 p-3 text-slate-300 animate-pulse italic text-[10px] font-bold">
            <i class="fas fa-robot"></i> MbokaAI is brainstorming...
        </div>
    `;
    body.scrollTop = body.scrollHeight;

    setTimeout(() => {
        const thinkingEl = document.getElementById(thinkingId);
        if (thinkingEl) thinkingEl.remove();
        
        let response = "";
        const lowerQuery = query.toLowerCase();

        if (lowerQuery.includes('plumb') || lowerQuery.includes('sink')) {
            response = "For plumbing (like sinks or pipes), the standard budget on MbokaHub is KES 850 - 2,200 depending on the complexity. You should also ask the Fundi if they include basic materials!";
        } else if (lowerQuery.includes('electr') || lowerQuery.includes('light')) {
            response = "Electrical repairs usually range from KES 1,000 to 4,500. For wiring jobs, I recommend checking if the Fundi is Class D certified for safety.";
        } else if (lowerQuery.includes('help') || lowerQuery.includes('what can you do')) {
            response = "I'm your MbokaHub Assistant! I can help you estimate job costs, suggest categories, or explain how to vet a Fundi before hiring.";
        } else {
            response = "That's a great question! For that specific task, I suggest browsing our verified artisan list. Most Fundis respond within 15 minutes for standard queries.";
        }

        body.innerHTML += `
            <div class="bg-white p-4 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm text-xs text-slate-600 font-medium leading-relaxed animate-in slide-in-from-left-4">
                ${response}
            </div>
        `;
        body.scrollTop = body.scrollHeight;
    }, 1500);
}

function openPostJobWizard() {
    const modal = document.getElementById('info-modal');
    const content = document.getElementById('modal-content');
    const template = document.getElementById('job-wizard-template');
    
    modal.classList.remove('hidden');
    content.innerHTML = template.innerHTML;
}

let activeStep = 1;

function handleWizardNext() {
    if (activeStep < 3) {
        document.getElementById(`step-${activeStep}`).classList.add('hidden');
        activeStep++;
        document.getElementById(`step-${activeStep}`).classList.remove('hidden');
        updateWizardUI();
    }
}

function handleWizardPrev() {
    if (activeStep > 1) {
        document.getElementById(`step-${activeStep}`).classList.add('hidden');
        activeStep--;
        document.getElementById(`step-${activeStep}`).classList.remove('hidden');
        updateWizardUI();
    }
}

function updateWizardUI() {
    const prevBtn = document.getElementById('wizard-prev');
    const nextBtn = document.getElementById('wizard-next');
    const submitBtn = document.getElementById('wizard-submit');
    const indicator = document.getElementById('wizard-steps-indicator');

    // Button visibility
    prevBtn.classList.toggle('hidden', activeStep === 1);
    nextBtn.classList.toggle('hidden', activeStep === 3);
    submitBtn.classList.toggle('hidden', activeStep !== 3);

    // Indicators
    const steps = indicator.children;
    for (let i = 0; i < steps.length; i++) {
        steps[i].className = `w-8 h-1.5 rounded-full ${i + 1 <= activeStep ? 'bg-emerald-500' : 'bg-slate-200'}`;
    }
}

function submitJobRequest() {
    const submitBtn = document.getElementById('wizard-submit');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Posting...';

    const formData = new FormData();
    formData.append('title', document.getElementById('job-title').value);
    formData.append('category_id', document.getElementById('job-category').value);
    formData.append('description', document.getElementById('job-description').value);
    formData.append('location', document.getElementById('job-location').value);
    formData.append('budget_range', document.getElementById('job-budget').value);
    
    const urgency = document.querySelector('input[name="urgency"]:checked').value;
    formData.append('urgency', urgency);

    fetch('process_job_post.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('modal-content').innerHTML = `
                <div class="text-center py-10 animate-in zoom-in duration-500">
                    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 mb-2">Job Posted!</h2>
                    <p class="text-slate-500 mb-8 font-medium">Your request is now live in the MbokaHub market.</p>
                    <button onclick="location.reload()" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold shadow-lg">Done</button>
                </div>
            `;
        } else {
            alert('Error: ' + (data.error || 'Something went wrong'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function openFundiModal(id) {
    const modal = document.getElementById('info-modal');
    const content = document.getElementById('modal-content');
    modal.classList.remove('hidden');
    
    // Show loading
    content.innerHTML = `
        <div class="flex flex-col items-center py-20 text-slate-300">
            <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-emerald-500"></i>
            <p class="font-bold text-sm tracking-widest uppercase">Loading Fundi Details...</p>
        </div>`;

    fetch(`get_fundi_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<p class="text-rose-500 font-bold">${data.error}</p>`;
                return;
            }
            content.innerHTML = `
                <div class="flex items-center gap-6 mb-8">
                    <div class="w-20 h-20 vibrant-gradient rounded-[2rem] flex items-center justify-center text-white text-3xl font-bold shadow-xl">
                        ${data.first_name[0]}
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900">${data.first_name} ${data.last_name}</h2>
                        <p class="text-emerald-600 font-bold uppercase tracking-widest text-xs">${data.specialization}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-slate-50 p-4 rounded-2xl">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Rating</p>
                        <p class="font-bold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-star text-amber-500"></i> ${data.rating} / 5.0
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl">
                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Location</p>
                        <p class="font-bold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-location-dot text-emerald-500"></i> ${data.location}
                        </p>
                    </div>
                </div>

                <div class="mb-10">
                    <h4 class="font-bold text-slate-900 mb-3">About Expert</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">${data.bio}</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button class="flex-1 bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-emerald-600 transition-all shadow-xl shadow-slate-200">
                        Hire Now
                    </button>
                    <button class="flex-1 bg-white text-slate-900 border-2 border-slate-100 py-4 rounded-2xl font-bold hover:bg-slate-50 transition-all">
                        View Full Portfolio
                    </button>
                </div>
            `;
        });
}

function openJobModal(id) {
    const modal = document.getElementById('info-modal');
    const content = document.getElementById('modal-content');
    modal.classList.remove('hidden');
    
    content.innerHTML = `
        <div class="flex flex-col items-center py-20 text-slate-300">
            <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-emerald-500"></i>
            <p class="font-bold text-sm tracking-widest uppercase">Loading Job Info...</p>
        </div>`;

    fetch(`get_job_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<p class="text-rose-500 font-bold">${data.error}</p>`;
                return;
            }
            content.innerHTML = `
                <div class="flex items-center gap-5 mb-8">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 text-2xl shadow-sm">
                        <i class="fas ${data.icon_class}"></i>
                    </div>
                    <div>
                        <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">${data.title}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase">${data.category_name}</span>
                            ${data.urgency === 'emergency' ? '<span class="bg-rose-100 text-rose-600 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase animate-pulse">Emergency</span>' : ''}
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 text-white p-6 rounded-3xl flex justify-between items-center mb-8">
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Budget Range</p>
                        <p class="text-xl font-black">KES ${data.budget_range}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Location</p>
                        <p class="font-bold text-sm text-emerald-400">${data.location}</p>
                    </div>
                </div>

                <div class="mb-10">
                    <h4 class="font-bold text-slate-900 mb-3">Job Description</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">${data.description}</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button class="flex-1 bg-emerald-500 text-white py-4 rounded-2xl font-bold hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-emerald-100">
                        Apply to this Job
                    </button>
                    <button class="flex-1 bg-white text-slate-900 border-2 border-slate-100 py-4 rounded-2xl font-bold hover:bg-slate-50 transition-all">
                        Save for Later
                    </button>
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('info-modal').classList.add('hidden');
}

function switchView(view) {
    const hirerBtn = document.getElementById('btn-hire');
    const workBtn = document.getElementById('btn-work');
    const hirerView = document.getElementById('hirer-view');
    const fundiView = document.getElementById('fundi-view');

    if (view === 'hire') {
        hirerBtn.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        hirerBtn.classList.remove('text-slate-500', 'hover:bg-slate-100');
        workBtn.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        workBtn.classList.add('text-slate-500', 'hover:bg-slate-100');
        hirerView.classList.remove('hidden');
        fundiView.classList.add('hidden');
    } else {
        workBtn.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        workBtn.classList.remove('text-slate-500', 'hover:bg-slate-100');
        hirerBtn.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        hirerBtn.classList.add('text-slate-500', 'hover:bg-slate-100');
        fundiView.classList.remove('hidden');
        hirerView.classList.add('hidden');
    }
}

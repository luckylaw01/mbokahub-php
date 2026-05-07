
function openEditProfile() {
    document.getElementById('edit-profile-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditProfile() {
    document.getElementById('edit-profile-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openAddPortfolio() { openPortfolioModal('add_portfolio', 'Add Project'); }
function openAddExperience() { openPortfolioModal('add_experience', 'Add Experience'); }
function openAddCert() { openPortfolioModal('add_cert', 'Add Certification'); }
function openAddGig() { openPortfolioModal('add_gig', 'Create Quick Gig'); }

function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('avatar', input.files[0]);

        // Feedback
        const container = input.closest('.group').querySelector('div');
        const originalContent = container.innerHTML;
        container.innerHTML = '<i class="fas fa-circle-notch fa-spin text-emerald-500"></i>';

        fetch('ajax/update_profile.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
                container.innerHTML = originalContent;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Upload failed');
            container.innerHTML = originalContent;
        });
    }
}

const editProfileForm = document.getElementById('edit-profile-form');
if (editProfileForm) {
    editProfileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/update_profile.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Profile updated!');
                location.reload();
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Update failed');
        });
    });
}

// Portfolio & Professional Management JS
function openPortfolioModal(action, title) {
    document.getElementById('portfolio-modal').classList.remove('hidden');
    document.getElementById('portfolio-action').value = action;
    document.getElementById('modal-title').innerText = title;
    document.body.style.overflow = 'hidden';

    const fields = document.getElementById('dynamic-fields');
    const uploadArea = document.getElementById('project-upload-area');

    const titleLabel = document.getElementById('label-title');
    
    fields.innerHTML = '';
    uploadArea.classList.add('hidden');
    titleLabel.innerText = 'Title';

    if (action === 'add_portfolio') {
        uploadArea.classList.remove('hidden');
        titleLabel.innerText = 'Project Name';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description</label>
                <textarea name="description" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Completion Date</label>
                <input type="date" name="completion_date" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
        `;
    } else if (action === 'add_experience') {
        titleLabel.innerText = 'Position / Role';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Company / Workshop Name</label>
                <input type="text" name="company" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Start Date</label>
                    <input type="date" name="start_date" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">End Date (Optional)</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Description of Work</label>
                <textarea name="description" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    } else if (action === 'add_cert') {
        titleLabel.innerText = 'Certification Title';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Issuing Institution</label>
                <input type="text" name="institution" required class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Issue Date</label>
                <input type="date" name="issue_date" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
        `;
    } else if (action === 'add_gig') {
        uploadArea.classList.remove('hidden');
        titleLabel.innerText = 'Service Title (e.g. Toilet Repair)';
        fields.innerHTML = `
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Starting Price (KSh)</label>
                <input type="number" name="price" required placeholder="1500" class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none">
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-black uppercase text-slate-400 ml-2">Quick Description</label>
                <textarea name="description" placeholder="Short summary of what you offer..." class="w-full bg-slate-50 border-2 border-transparent focus:border-emerald-500/20 rounded-2xl p-4 text-sm font-bold outline-none"></textarea>
            </div>
        `;
    }
}

// Phase 3: Review & Job Management JS
function openReviewModal(jobId, jobTitle) {
    document.getElementById('review-job-id').value = jobId;
    document.getElementById('review-job-title').innerText = jobTitle;
    document.getElementById('review-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('review-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function setStar(val) {
    document.getElementById('review-rating-val').value = val;
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + i);
        if (i <= val) {
            star.classList.remove('text-slate-200');
            star.classList.add('text-amber-400');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-slate-200');
        }
    }
}

const reviewForm = document.getElementById('review-form');
if (reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // First Mark as Complete, then Submit Review
        fetch('ajax/update_job_status.php?t=' + Date.now(), {
            method: 'POST',
            body: new URLSearchParams({
                'job_id': formData.get('job_id'),
                'action': 'complete'
            })
        })
        .then(r => r.json())
        .then(statusResult => {
            if (statusResult.success) {
                // Now submit the review
                return fetch('ajax/submit_review.php?t=' + Date.now(), {
                    method: 'POST',
                    body: formData
                });
            }
            throw new Error(statusResult.message);
        })
        .then(r => r.json())
        .then(reviewResult => {
            if (reviewResult.success) {
                alert('Job completed and review posted!');
                location.reload();
            } else {
                alert(reviewResult.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'Error processing completion');
        });
    });
}

function cancelJob(jobId) {
    if (confirm('Are you sure you want to cancel this job?')) {
        const formData = new FormData();
        formData.append('job_id', jobId);
        formData.append('action', 'cancel');

        fetch('ajax/update_job_status.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        });
    }
}

function closePortfolioModal() {
    document.getElementById('portfolio-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copyPortfolioLink() {
    const userId = <?php echo $user_id; ?>;
    const url = window.location.href.split('?')[0].replace(/profile\.php$/, '') + 'fundi/portfolio/?id=' + userId;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Portfolio link copied to clipboard!');
        });
    } else {
        alert('Your shareable link: ' + url);
    }
}

function previewProjectImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('project-preview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

const portfolioForm = document.getElementById('portfolio-form');
if (portfolioForm) {
    portfolioForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/manage_portfolio.php?t=' + Date.now(), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Update failed');
        });
    });
}

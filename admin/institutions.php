<?php
/**
 * Institution Management - MbokaHub Admin
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';
$page_title = "Institution Management";
include 'includes/header.php';

// Fetch all institutions
$institutions = $pdo->query("SELECT * FROM institutions ORDER BY name ASC")->fetchAll();

// Fetch counts of fundis per institution
$stats_stmt = $pdo->query("SELECT institution_id, COUNT(*) as fundi_count FROM fundi_profiles WHERE institution_id IS NOT NULL GROUP BY institution_id");
$stats = $stats_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="flex justify-between items-center mb-10">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-amber-500 text-white rounded-[1.25rem] flex items-center justify-center shadow-lg shadow-amber-200">
            <i class="fas fa-university text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Partner Network</h2>
            <p class="text-slate-500 font-semibold flex items-center gap-2">
                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                Managing <?php echo count($institutions); ?> TVET Partners
            </p>
        </div>
    </div>
    
    <button onclick="openAddInstitutionModal()" class="flex items-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 hover:-translate-y-1 transition-all active:scale-95 group">
        <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
            <i class="fas fa-plus"></i>
        </span>
        Register Institution
    </button>
</div>

<!-- Institutions Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($institutions as $inst): ?>
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-slate-200/50 transition-all group">
        <div class="flex justify-between items-start mb-6">
            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-amber-50 group-hover:text-amber-500 transition-colors">
                <i class="fas fa-school text-2xl"></i>
            </div>
            <div class="flex gap-2">
                <button onclick='openEditInstModal(<?php echo json_encode($inst); ?>)' class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100">
                    <i class="fas fa-edit text-xs"></i>
                </button>
                <button onclick="deleteInstitution(<?php echo $inst['id']; ?>)" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all border border-transparent hover:border-rose-100">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
        </div>

        <h3 class="text-lg font-black text-slate-900 mb-1 group-hover:text-amber-600 transition-colors">
            <?php echo htmlspecialchars($inst['name']); ?>
        </h3>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-amber-500/50"></i>
            <?php echo htmlspecialchars($inst['location']); ?> • <?php echo $inst['type']; ?>
        </p>

        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Enrolled Fundis</p>
                <p class="text-xl font-black text-slate-900"><?php echo $stats[$inst['id']] ?? 0; ?></p>
            </div>
            <?php if($inst['is_partner']): ?>
            <div class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                Official Partner
            </div>
            <?php endif; ?>
        </div>

        <?php if($inst['website']): ?>
        <a href="<?php echo htmlspecialchars($inst['website']); ?>" target="_blank" class="mt-6 flex items-center justify-center gap-2 py-3 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-amber-500 transition-all">
            Visit Portal <i class="fas fa-external-link-alt text-[10px]"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Institution Modal -->
<div id="instModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-black text-slate-900">Register Institution</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="instForm" class="p-8">
            <input type="hidden" name="id" id="instId">
            <div class="space-y-2 mb-5">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Institution Name</label>
                <input type="text" name="name" id="instName" required placeholder="e.g. Kabete National Polytechnic" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-amber-500/20">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Type</label>
                    <select name="type" id="instType" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-amber-500/20 appearance-none">
                        <option value="TVET">TVET</option>
                        <option value="University">University</option>
                        <option value="College">College</option>
                        <option value="Vocational">Vocational</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Location / HQ</label>
                    <input type="text" name="location" id="instLocation" required placeholder="Nairobi" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-amber-500/20">
                </div>
            </div>
            
            <div class="space-y-2 mb-5">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Website URL</label>
                <input type="url" name="website" id="instWebsite" placeholder="https://..." class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-amber-500/20">
            </div>

            <div class="flex items-center gap-3 mb-8 px-1">
                <input type="checkbox" name="is_partner" id="instPartner" class="w-5 h-5 rounded-lg border-slate-200 text-amber-500 focus:ring-amber-500/20">
                <label for="instPartner" class="text-sm font-bold text-slate-600">Official MbokaHub Partner Institution</label>
            </div>

            <div class="flex gap-4">
                <button type="button" onclick="closeModal()" class="flex-1 py-4 bg-slate-50 text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-100 transition-colors">Cancel</button>
                <button type="submit" class="flex-2 px-8 py-4 bg-amber-500 text-white rounded-2xl font-black text-sm shadow-xl shadow-amber-100 hover:bg-amber-600 hover:-translate-y-0.5 transition-all">Save Records</button>
            </div>
        </form>
    </div>
</div>

<script>
const instModal = document.getElementById('instModal');
const instForm = document.getElementById('instForm');

function openAddInstitutionModal() {
    document.getElementById('modalTitle').innerText = 'Register Institution';
    document.getElementById('instId').value = '';
    instForm.reset();
    instModal.classList.remove('hidden');
}

function openEditInstModal(inst) {
    document.getElementById('modalTitle').innerText = 'Recalibrate Partner';
    document.getElementById('instId').value = inst.id;
    document.getElementById('instName').value = inst.name;
    document.getElementById('instType').value = inst.type;
    document.getElementById('instLocation').value = inst.location;
    document.getElementById('instWebsite').value = inst.website || '';
    document.getElementById('instPartner').checked = inst.is_partner == 1;
    instModal.classList.remove('hidden');
}

function closeModal() {
    instModal.classList.add('hidden');
}

instForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(instForm);
    
    try {
        const response = await fetch('ajax/manage_institution.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if(result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Institution Synced',
                text: 'Network records updated successfully',
                confirmButtonColor: '#f59e0b'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', result.message || 'Operation failed', 'error');
        }
    } catch (error) {
        Swal.fire('Fatal Error', 'Connection lost to Hub services', 'error');
    }
};

function deleteInstitution(id) {
    Swal.fire({
        title: 'Revoke Partnership?',
        text: 'This will remove the institution from our database. Linked fundis will return to "Unassociated" status.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, Revoke'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('ajax/delete_institution.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                const res = await response.json();
                if(res.success) {
                    location.reload();
                } else {
                    Swal.fire('Failed', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Revocation cycle failed', 'error');
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

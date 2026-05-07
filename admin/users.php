<?php
/**
 * User Management - MbokaHub Admin
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/translations.php';
$page_title = "User Management";
include 'includes/header.php';

// Handle delete/create via POST if not using separate AJAX endpoints
// (Though we have the modal and AJAX, this script handles the main view)
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="flex justify-between items-center mb-10">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-600 text-white rounded-[1.25rem] flex items-center justify-center shadow-lg shadow-indigo-200">
            <i class="fas fa-users-cog text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Identity Suite</h2>
            <p class="text-slate-500 font-semibold flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                Managing <?php echo count($users); ?> Hub Members
            </p>
        </div>
    </div>
    
    <button onclick="openAddUserModal()" class="flex items-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-2xl font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 hover:-translate-y-1 transition-all active:scale-95 group">
        <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
            <i class="fas fa-plus"></i>
        </span>
        Onboard Member
    </button>
</div>

<!-- Search & Filter Bar (Minimalistic) -->
<div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 mb-8 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[280px] relative group">
        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
        <input type="text" id="userSearch" placeholder="Search by name, email or role..." class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl text-slate-900 font-medium placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    </div>
    <div class="flex gap-2 p-1.5 bg-slate-50 rounded-2xl">
        <button class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white text-indigo-600 shadow-sm border border-slate-100">All</button>
        <button class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-white transition-all">Fundis</button>
        <button class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-white transition-all">Hirers</button>
        <button class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-white transition-all">Contractors</button>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-8 py-6 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Member Profile</th>
                    <th class="px-8 py-6 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">System Privileges</th>
                    <th class="px-8 py-6 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Email Address</th>
                    <th class="px-8 py-6 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach($users as $user): ?>
                <tr class="hover:bg-slate-50/30 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-slate-50 flex items-center justify-center text-indigo-500 font-black border border-indigo-100/50">
                                <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900">
                                    <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                                </p>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <?php 
                        $role_styles = [
                            'admin' => 'bg-rose-50 text-rose-600 border-rose-100',
                            'fundi' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'hirer' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'contractor' => 'bg-amber-50 text-amber-600 border-amber-100'
                        ];
                        $style = $role_styles[$user['role']] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                        ?>
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border <?php echo $style; ?>">
                            <?php echo $user['role']; ?>
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <span class="text-sm font-semibold text-slate-600"><?php echo htmlspecialchars($user['email']); ?></span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center justify-end gap-2">
                            <a href="view_user.php?id=<?php echo $user['id']; ?>" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 transition-all border border-transparent hover:border-indigo-100">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <button onclick='openEditModal(<?php echo json_encode($user); ?>)' class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all border border-transparent hover:border-rose-100">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-black text-slate-900">Add New User</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="userForm" class="p-8">
            <input type="hidden" name="user_id" id="userId">
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">First Name</label>
                    <input type="text" name="first_name" id="firstName" required class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Last Name</label>
                    <input type="text" name="last_name" id="lastName" required class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>
            
            <div class="space-y-2 mb-5">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                <input type="email" name="email" id="userEmail" required class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <div class="space-y-2 mb-5" id="passwordField">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                <input type="password" name="password" id="userPassword" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-indigo-500/20">
                <p class="text-[10px] text-slate-400 font-bold ml-1" id="passHint">Leave blank to keep existing</p>
            </div>

            <div class="space-y-2 mb-8">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Role / Account Type</label>
                <select name="role" id="userRole" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold focus:ring-2 focus:ring-indigo-500/20 appearance-none">
                    <option value="hirer">Hirer (Default)</option>
                    <option value="fundi">Fundi (Specialist)</option>
                    <option value="contractor">Contractor (Large Projects)</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="mb-8">
                <div class="flex items-center gap-4 bg-slate-50 p-6 rounded-[2rem] border-2 border-transparent transition-all hover:border-emerald-500/20 group cursor-pointer" onclick="document.getElementById('isVerified').click()">
                    <div class="relative">
                        <input type="checkbox" name="is_verified" id="isVerified" class="peer hidden" value="1">
                        <div class="w-12 h-6 bg-slate-200 rounded-full transition-colors peer-checked:bg-emerald-500 relative">
                            <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:left-7"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase text-slate-400">Trust status</p>
                        <p class="text-sm font-black text-slate-900">Verified Professional</p>
                    </div>
                    <i class="fas fa-certificate text-emerald-500 ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="button" onclick="closeModal()" class="flex-1 py-4 bg-slate-50 text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-100 transition-colors">Cancel</button>
                <button type="submit" class="flex-2 px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">Record Member</button>
            </div>
        </form>
    </div>
</div>

<script>
const userModal = document.getElementById('userModal');
const userForm = document.getElementById('userForm');

function openAddUserModal() {
    document.getElementById('modalTitle').innerText = 'Onboard New Member';
    document.getElementById('userId').value = '';
    userForm.reset();
    document.getElementById('passHint').classList.add('hidden');
    document.getElementById('userPassword').required = true;
    userModal.classList.remove('hidden');
}

function openEditModal(user) {
    document.getElementById('modalTitle').innerText = 'Recalibrate Member';
    document.getElementById('userId').value = user.id;
    document.getElementById('firstName').value = user.first_name || '';
    document.getElementById('lastName').value = user.last_name || '';
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('isVerified').checked = user.is_verified == 1;
    document.getElementById('passHint').classList.remove('hidden');
    document.getElementById('userPassword').required = false;
    userModal.classList.remove('hidden');
}

function closeModal() {
    userModal.classList.add('hidden');
}

userForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(userForm);
    
    try {
        const response = await fetch('ajax/manage_user.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if(result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Member Synced',
                text: 'Security records updated successfully',
                confirmButtonColor: '#4f46e5'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', result.message || 'Operation failed', 'error');
        }
    } catch (error) {
        Swal.fire('Fatal Error', 'Connection lost to Hub services', 'error');
    }
};

function deleteUser(id) {
    Swal.fire({
        title: 'Decommission Member?',
        text: 'This will revoke all system access indefinitely.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, Decommission'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('ajax/delete_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `user_id=${id}`
                });
                const res = await response.json();
                if(res.success) {
                    location.reload();
                } else {
                    Swal.fire('Failed', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Decommissioning cycle failed', 'error');
            }
        }
    });
}

// Simple live search
document.getElementById('userSearch').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>

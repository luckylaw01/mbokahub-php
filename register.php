<?php
/**
 * Auth Layout Wrapper
 * Blueprint: Standardized registration and login for Hirers/Fundis
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join MbokaHub | Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .vibrant-gradient { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full px-4 py-8 md:py-0">
        <!-- Brand -->
        <div class="flex flex-col items-center mb-8 md:mb-10">
            <div class="vibrant-gradient text-white p-3 md:p-4 rounded-[1.5rem] md:rounded-[2rem] shadow-2xl mb-4">
                <i class="fas fa-tools text-2xl md:text-3xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Mboka<span class="text-emerald-500">Hub</span></h1>
            <p class="text-slate-500 text-sm md:text-base font-medium mt-1 md:mt-2">Create your account to get started</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[3rem] shadow-2xl shadow-slate-200 border border-slate-50">
            <form action="process_register.php" method="POST" class="space-y-4 md:space-y-5">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1 md:space-y-2">
                        <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">First Name</label>
                        <input type="text" name="first_name" required 
                               class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                    </div>
                    <div class="space-y-1 md:space-y-2">
                        <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Last Name</label>
                        <input type="text" name="last_name" required 
                               class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="space-y-1 md:space-y-2">
                    <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Username</label>
                    <input type="text" name="user_name" id="user_name" required 
                           class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                </div>

                <div class="space-y-1 md:space-y-2">
                    <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                </div>

                <div class="space-y-1 md:space-y-2">
                    <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">I am a...</label>
                    <select name="role" class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all appearance-none cursor-pointer">
                        <option value="hirer">Hirer (I want to hire)</option>
                        <option value="fundi">Fundi (I am a worker)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1 md:space-y-2">
                        <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Password</label>
                        <input type="password" name="password" required 
                               class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                    </div>
                    <div class="space-y-1 md:space-y-2">
                        <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Confirm</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 md:py-5 rounded-xl md:rounded-[2rem] font-bold text-base md:text-lg shadow-xl hover:scale-[1.02] active:scale-95 transition-all mt-4">
                    Create Account
                </button>
            </form>

            <div class="mt-6 md:mt-8 text-center bg-slate-50 p-4 md:p-6 rounded-2xl md:rounded-[2rem]">
                <p class="text-slate-500 text-[10px] md:text-sm font-medium">Already have an account?</p>
                <a href="login.php" class="text-emerald-600 text-xs md:text-sm font-bold hover:underline">Sign In Instead</a>
            </div>
        </div>
    </div>

    <!-- Script for Username Suggestion -->
    <script>
        const firstNameInput = document.getElementsByName('first_name')[0];
        const lastNameInput = document.getElementsByName('last_name')[0];
        const userNameInput = document.getElementById('user_name');

        function suggestUsername() {
            const fname = firstNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            const lname = lastNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            
            if (fname && lname) {
                // Only suggest if the user hasn't manually edited the username yet
                // Or if it's currently empty
                if (!userNameInput.dataset.edited) {
                    userNameInput.value = `${fname}.${lname}${Math.floor(Math.random() * 99)}`;
                }
            }
        }

        firstNameInput.addEventListener('input', suggestUsername);
        lastNameInput.addEventListener('input', suggestUsername);
        
        userNameInput.addEventListener('input', () => {
            userNameInput.dataset.edited = "true";
        });
    </script>

</body>
</html>

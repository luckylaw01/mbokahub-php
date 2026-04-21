<?php
/**
 * Login Page
 * Blueprint: Standardized registration and login for Hirers/Fundis
 */
$registered_success = isset($_GET['registered']) && $_GET['registered'] === 'success';
$login_error = isset($_GET['error']) && $_GET['error'] === 'invalid';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MbokaHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .vibrant-gradient { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6 text-slate-900">

    <div class="max-w-md w-full px-4">
        <!-- Brand -->
        <div class="flex flex-col items-center mb-8 md:mb-10">
            <div class="vibrant-gradient text-white p-3 md:p-4 rounded-[1.5rem] md:rounded-[2rem] shadow-2xl mb-4 hover:rotate-12 transition-transform cursor-pointer">
                <i class="fas fa-tools text-2xl md:text-3xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Mboka<span class="text-emerald-500">Hub</span></h1>
            <p class="text-slate-500 text-sm md:text-base font-medium mt-1 md:mt-2">Welcome back!</p>
        </div>

        <!-- Feedback Messages -->
        <?php if ($registered_success): ?>
            <div class="mb-4 md:mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl text-xs md:text-sm font-bold shadow-sm">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <?php if ($login_error): ?>
            <div class="mb-4 md:mb-6 bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 rounded-xl text-xs md:text-sm font-bold shadow-sm">
                Invalid email or password.
            </div>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[3rem] shadow-2xl shadow-slate-200 border border-slate-50">
            <form action="process_login.php" method="POST" class="space-y-4 md:space-y-6">
                
                <div class="space-y-1 md:space-y-2">
                    <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 ml-2 md:ml-4">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                </div>

                <div class="space-y-1 md:space-y-2">
                    <div class="flex justify-between px-2 md:px-4">
                        <label class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400">Password</label>
                        <a href="#" class="text-[10px] md:text-xs font-bold text-emerald-600 hover:underline">Forgot?</a>
                    </div>
                    <input type="password" name="password" required 
                           class="w-full bg-slate-50 rounded-xl md:rounded-2xl px-5 md:px-6 py-3.5 md:py-4 text-sm md:text-base border-2 border-transparent focus:border-emerald-500/20 focus:outline-none transition-all">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center px-2 md:px-4">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember_me" class="sr-only">
                        <div class="w-5 h-5 md:w-6 md:h-6 border-2 border-slate-200 rounded-lg flex items-center justify-center transition-all group-hover:border-emerald-500 bg-white">
                            <i class="fas fa-check text-[10px] md:text-xs text-white opacity-0 transition-opacity"></i>
                        </div>
                        <span class="ml-3 text-xs md:text-sm font-bold text-slate-500 group-hover:text-slate-700">Remember Me</span>
                    </label>
                </div>

                <style>
                    input[type="checkbox"]:checked + div {
                        background-color: #10b981;
                        border-color: #10b981;
                    }
                    input[type="checkbox"]:checked + div i {
                        opacity: 1;
                    }
                </style>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 md:py-5 rounded-xl md:rounded-[2rem] font-bold text-base md:text-lg shadow-xl hover:scale-[1.02] active:scale-95 transition-all mt-2 md:mt-4">
                    Sign In
                </button>
            </form>

            <div class="mt-6 md:mt-8 text-center bg-slate-50 p-4 md:p-6 rounded-2xl md:rounded-[2rem]">
                <p class="text-slate-500 text-[10px] md:text-sm font-medium">New to MbokaHub?</p>
                <a href="register.php" class="text-emerald-600 text-xs md:text-sm font-bold hover:underline">Create an Account</a>
            </div>
        </div>
    </div>

</body>
</html>

<?php
require_once __DIR__ . '/includes/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /Blog/dashboard.php');
    exit;
}

$pageTitle = 'Sign in — Code & Canvas';
require_once __DIR__ . '/includes/header.php';

$storyCount = (int)$pdo->query("SELECT COUNT(*) FROM blogpost")->fetchColumn();
$userCount = (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
?>

<div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full grid lg:grid-cols-2 gap-12 items-center">
        <!-- Left side - Login Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="mb-8 text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    Welcome back
                </span>
                <h1 class="font-serif text-3xl font-bold tracking-tight text-charcoal">Sign in to your account</h1>
                <p class="text-sm text-charcoal/60 mt-2">Enter your credentials to continue reading and writing.</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-sm text-red-700">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="/Blog/auth/login_handler.php" class="bg-white p-8 rounded-2xl shadow-sm border border-taupe space-y-5">
                <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return'] ?? '/Blog/dashboard.php') ?>">
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Email or Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="identifier" placeholder="you@domain.com" required 
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required 
                               class="w-full pl-10 pr-11 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                        <button type="button" onclick="togglePasswordVisibility('password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/40 hover:text-charcoal p-1">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-dark text-white py-2.5 px-4 rounded-xl hover:bg-charcoal transition-all font-medium text-sm shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-charcoal/70">
                Don't have an account yet? 
                <a href="/Blog/register.php" class="text-accent font-semibold hover:underline">Create an account</a>
            </p>
        </div>
        
        <!-- Right side - Community highlights -->
        <div class="hidden lg:flex flex-col justify-center p-10 bg-white/60 border border-taupe rounded-3xl backdrop-blur-sm relative overflow-hidden">
            <div class="space-y-6">
                <span class="w-12 h-12 rounded-2xl bg-dark text-white flex items-center justify-center shadow-md">
                    <i data-lucide="feather" class="w-6 h-6"></i>
                </span>
                
                <h2 class="font-serif text-3xl font-bold text-charcoal leading-snug">
                    Where ideas, design, and code converge.
                </h2>
                
                <p class="text-charcoal/70 text-sm leading-relaxed">
                    Join an intimate community of designers, software engineers, and writers crafting mindful stories on the modern web.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-taupe">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="block font-serif text-2xl font-bold text-charcoal"><?= number_format($storyCount) ?></span>
                            <span class="text-xs text-charcoal/60">Published Stories</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="block font-serif text-2xl font-bold text-charcoal"><?= number_format($userCount) ?></span>
                            <span class="text-xs text-charcoal/60">Writers &amp; Readers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword 
        ? '<i data-lucide="eye-off" class="w-4 h-4"></i>' 
        : '<i data-lucide="eye" class="w-4 h-4"></i>';
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

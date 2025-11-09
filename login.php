<?php
require_once 'includes/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /Blog/dashboard.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="min-h-screen flex">
    <!-- Left side - Login Form -->
    <div class="flex-1 flex items-center justify-center px-4 relative z-10">
        <div class="max-w-md w-full">
            <h1 class="text-2xl font-bold mb-6">Login</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 rounded-lg border border-red-100">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-red-600"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="/Blog/auth/login_handler.php" class="bg-white p-8 rounded-2xl shadow-sm space-y-4">
                <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return'] ?? '/Blog/dashboard.php') ?>">
                <div class="space-y-4">
                    <input type="text" name="identifier" placeholder="Email or Username" required 
                           class="w-full px-4 py-2 border border-taupe rounded-lg">
                    
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Password" required 
                               class="w-full px-4 py-2 border border-taupe rounded-lg">
                        <button type="button" onclick="togglePassword('password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/50 hover:text-charcoal">
                            <svg class="w-5 h-5" id="password-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <button type="submit" class="w-full bg-dark text-white py-2 rounded-lg hover:bg-charcoal">
                        Sign in
                    </button>
                </div>
            </form>
            <p class="mt-4 text-sm">Don't have an account? <a href="/Blog/register.php" class="text-blue-600 hover:underline">Register</a></p>
        </div>
    </div>
    
    <!-- Right side - Welcome Text -->
    <div class="hidden lg:flex flex-1 items-center justify-center relative overflow-hidden">
        <!-- Gradient overlays -->
        <div class="absolute inset-0">
            <div class="absolute w-20 h-20 bg-accent/5 backdrop-blur-xl rounded-full top-20 left-20 animate-float-slow"></div>
            <div class="absolute w-32 h-32 bg-accent/5 backdrop-blur-xl rounded-full bottom-40 right-20 animate-float-medium"></div>
            <div class="absolute w-16 h-16 bg-accent/5 backdrop-blur-xl rounded-full top-40 right-40 animate-float-fast"></div>
        </div>
        
        <div class="relative text-charcoal max-w-md p-8">
            <h2 class="font-serif text-4xl mb-6">Welcome Back!</h2>
            <p class="text-charcoal/70 text-lg leading-relaxed mb-8">
                Continue your journey of sharing stories, connecting with fellow developers, 
                and being part of our growing community.
            </p>
            <div class="flex gap-6 text-charcoal/60">
                <div>
                    <span class="block font-serif text-2xl text-charcoal">
                        <?= $pdo->query("SELECT COUNT(*) FROM blogpost")->fetchColumn() ?>
                    </span>
                    <span>Stories Shared</span>
                </div>
                <div>
                    <span class="block font-serif text-2xl text-charcoal">
                        <?= $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn() ?>
                    </span>
                    <span>Community Members</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eye.innerHTML = `<path d="M3 3l18 18M10.5 10.677a2 2 0 002.823 2.823" stroke-width="2" stroke-linecap="round"/>
                        <path d="M7.362 7.561C5.68 8.74 4.279 10.42 3 12c1.274 4.057 5.065 7 9.542 7 1.99 0 3.842-.372 5.47-1.022M14.83 9.17C14.298 8.584 13.494 8 12 8c-2.474 0-4.35 2.01-4.35 4.5S9.526 17 12 17c.512 0 1.154-.228 1.967-.683" stroke-width="2" stroke-linecap="round"/>`;
    } else {
        input.type = 'password';
        eye.innerHTML = `<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" stroke-linecap="round"/>`;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

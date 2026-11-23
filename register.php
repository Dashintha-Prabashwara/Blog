<?php
require_once 'includes/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /Blog/dashboard.php');
    exit;
}

$pageTitle = 'Create Account — Code & Canvas';
require_once 'includes/header.php';

$storyCount = (int)$pdo->query("SELECT COUNT(*) FROM blogpost")->fetchColumn();
$userCount = (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
?>

<div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full grid lg:grid-cols-2 gap-12 items-center">
        <div class="w-full max-w-md mx-auto">
            <div class="mb-8 text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent mb-3">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    Get Started
                </span>
                <h1 class="font-serif text-3xl font-bold tracking-tight text-charcoal">Create your account</h1>
                <p class="text-sm text-charcoal/60 mt-2">Join Code &amp; Canvas to publish stories and join discussions.</p>
            </div>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl space-y-1">
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm mb-1">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>Please fix the following:</span>
                    </div>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p class="text-red-600 text-xs pl-6">• <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-sm text-red-700">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="/Blog/auth/register.php" id="registerForm" class="bg-white p-8 rounded-2xl shadow-sm border border-taupe space-y-4">
                <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return'] ?? '/Blog/dashboard.php') ?>">
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="username" placeholder="johndoe" required 
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30"
                               pattern=".{3,}"
                               oninput="validateForm()">
                    </div>
                    <span class="text-red-600 text-xs hidden mt-1" id="usernameError"></span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" placeholder="you@domain.com" required 
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30"
                               oninput="validateForm()">
                    </div>
                    <span class="text-red-600 text-xs hidden mt-1" id="emailError"></span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required 
                               class="w-full pl-10 pr-11 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30"
                               oninput="validateForm()">
                        <button type="button" onclick="togglePasswordVisibility('password', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/40 hover:text-charcoal p-1">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    
                    <div class="mt-3 p-3 bg-cream/50 rounded-xl border border-taupe/60 space-y-1.5 text-xs">
                        <div id="lengthCheck" class="flex items-center gap-2 text-charcoal/50 transition-colors">
                            <i data-lucide="circle" class="w-3.5 h-3.5"></i>
                            <span>At least 8 characters</span>
                        </div>
                        <div id="upperCheck" class="flex items-center gap-2 text-charcoal/50 transition-colors">
                            <i data-lucide="circle" class="w-3.5 h-3.5"></i>
                            <span>One uppercase letter</span>
                        </div>
                        <div id="lowerCheck" class="flex items-center gap-2 text-charcoal/50 transition-colors">
                            <i data-lucide="circle" class="w-3.5 h-3.5"></i>
                            <span>One lowercase letter</span>
                        </div>
                        <div id="numberCheck" class="flex items-center gap-2 text-charcoal/50 transition-colors">
                            <i data-lucide="circle" class="w-3.5 h-3.5"></i>
                            <span>One number</span>
                        </div>
                    </div>
                    <span class="text-red-600 text-xs hidden mt-1" id="passwordError"></span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-charcoal/80">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password_confirm" id="password_confirm" 
                               placeholder="••••••••" required 
                               class="w-full pl-10 pr-11 py-2.5 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30"
                               oninput="validateForm()">
                        <button type="button" onclick="togglePasswordVisibility('password_confirm', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/40 hover:text-charcoal p-1">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <span class="text-red-600 text-xs hidden mt-1" id="confirmError"></span>
                </div>

                <button type="submit" id="submitBtn" disabled
                        class="w-full py-2.5 px-4 bg-dark text-white rounded-xl hover:bg-charcoal font-medium text-sm shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    <span>Create Account</span>
                </button>
            </form>

            <p class="text-center mt-6 text-sm text-charcoal/70">
                Already have an account? 
                <a href="/Blog/login.php" class="text-accent font-semibold hover:underline">Sign in</a>
            </p>
        </div>
        
        <!-- Right side - Community highlights -->
        <div class="hidden lg:flex flex-col justify-center p-10 bg-white/60 border border-taupe rounded-3xl backdrop-blur-sm relative overflow-hidden">
            <div class="space-y-6">
                <span class="w-12 h-12 rounded-2xl bg-dark text-white flex items-center justify-center shadow-md">
                    <i data-lucide="feather" class="w-6 h-6"></i>
                </span>
                
                <h2 class="font-serif text-3xl font-bold text-charcoal leading-snug">
                    Share your knowledge with fellow builders.
                </h2>
                
                <ul class="space-y-3.5 text-sm text-charcoal/75 pt-2">
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-accent/10 text-accent flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span>Write beautiful markdown posts with live preview</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-accent/10 text-accent flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span>Organize stories with multi-topic taxonomy</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-accent/10 text-accent flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span>Receive likes, threaded comments, and real-time alerts</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-accent/10 text-accent flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                        <span>Analytics dashboard to monitor readership growth</span>
                    </li>
                </ul>

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
                            <span class="text-xs text-charcoal/60">Community Members</span>
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

function updateCheckItem(elementId, isValid, label) {
    const el = document.getElementById(elementId);
    if (!el) return;
    if (isValid) {
        el.className = 'flex items-center gap-2 text-green-700 transition-colors font-medium';
        el.innerHTML = `<i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-green-600"></i><span>${label}</span>`;
    } else {
        el.className = 'flex items-center gap-2 text-charcoal/50 transition-colors';
        el.innerHTML = `<i data-lucide="circle" class="w-3.5 h-3.5"></i><span>${label}</span>`;
    }
}

function validateForm() {
    const username = document.querySelector('input[name="username"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    const confirm = document.querySelector('input[name="password_confirm"]');
    const submitBtn = document.getElementById('submitBtn');
    let isValid = true;

    // Username validation
    if (username.value.trim().length < 3) {
        showError('usernameError', 'Username must be at least 3 characters long');
        isValid = false;
    } else {
        hideError('usernameError');
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        showError('emailError', 'Please enter a valid email address');
        isValid = false;
    } else {
        hideError('emailError');
    }

    // Password validation
    const hasLength = password.value.length >= 8;
    const hasUpper = /[A-Z]/.test(password.value);
    const hasLower = /[a-z]/.test(password.value);
    const hasNumber = /[0-9]/.test(password.value);
    
    updateCheckItem('lengthCheck', hasLength, 'At least 8 characters');
    updateCheckItem('upperCheck', hasUpper, 'One uppercase letter');
    updateCheckItem('lowerCheck', hasLower, 'One lowercase letter');
    updateCheckItem('numberCheck', hasNumber, 'One number');
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const isPasswordValid = hasLength && hasUpper && hasLower && hasNumber;
    if (!isPasswordValid && password.value.length > 0) {
        showError('passwordError', 'Password does not meet all requirements');
        isValid = false;
    } else {
        hideError('passwordError');
    }

    // Confirm password
    if (confirm.value.length > 0 && password.value !== confirm.value) {
        showError('confirmError', 'Passwords do not match');
        isValid = false;
    } else {
        hideError('confirmError');
    }

    if (!hasLength || !hasUpper || !hasLower || !hasNumber || password.value !== confirm.value || !emailRegex.test(email.value) || username.value.trim().length < 3) {
        isValid = false;
    }

    submitBtn.disabled = !isValid;
}

function showError(elementId, message) {
    const error = document.getElementById(elementId);
    if (!error) return;
    error.textContent = message;
    error.classList.remove('hidden');
}

function hideError(elementId) {
    const error = document.getElementById(elementId);
    if (!error) return;
    error.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', validateForm);
</script>

<?php require_once 'includes/footer.php'; ?>

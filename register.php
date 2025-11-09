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
    <div class="flex-1 flex items-center justify-center px-4 relative z-10">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <h1 class="font-serif text-3xl mb-2">Join the Community</h1>
                <p class="text-charcoal/70">Create your Code & Canvas account</p>
            </div>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z"/>
                        </svg>
                        <div class="flex-1">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <p class="text-red-700 text-sm mb-1">• <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/Blog/auth/register.php" id="registerForm" class="bg-white p-8 rounded-2xl shadow-sm">
                <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return'] ?? '/Blog/dashboard.php') ?>">
                <div class="space-y-4">
                    <div>
                        <input type="text" name="username" placeholder="Username" required 
                               class="w-full px-4 py-2 border border-taupe rounded-lg"
                               pattern=".{3,}"
                               oninput="validateForm()"
                               title="Username must be at least 3 characters long">
                        <span class="text-red-600 text-sm hidden" id="usernameError"></span>
                    </div>
                    
                    <div>
                        <input type="email" name="email" placeholder="Email" required 
                               class="w-full px-4 py-2 border border-taupe rounded-lg"
                               oninput="validateForm()">
                        <span class="text-red-600 text-sm hidden" id="emailError"></span>
                    </div>
                    
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Password" required 
                               class="w-full px-4 py-2 border border-taupe rounded-lg"
                               oninput="validateForm()">
                        <button type="button" onclick="togglePassword('password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/50 hover:text-charcoal">
                            <svg class="w-5 h-5" id="password-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <span class="text-red-600 text-sm hidden" id="passwordError"></span>
                        <div class="mt-2 space-y-1 text-sm text-charcoal/60">
                            <p id="lengthCheck" class="transition-colors">✗ At least 8 characters</p>
                            <p id="upperCheck" class="transition-colors">✗ One uppercase letter</p>
                            <p id="lowerCheck" class="transition-colors">✗ One lowercase letter</p>
                            <p id="numberCheck" class="transition-colors">✗ One number</p>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="password" name="password_confirm" id="password_confirm" 
                               placeholder="Confirm Password" required 
                               class="w-full px-4 py-2 border border-taupe rounded-lg"
                               oninput="validateForm()">
                        <button type="button" onclick="togglePassword('password_confirm')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal/50 hover:text-charcoal">
                            <svg class="w-5 h-5" id="password_confirm-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <span class="text-red-600 text-sm hidden" id="confirmError"></span>
                    </div>

                    <button type="submit" id="submitBtn" disabled
                            class="w-full py-2 px-4 bg-dark text-white rounded-lg hover:bg-charcoal disabled:opacity-50 disabled:cursor-not-allowed">
                        Create Account
                    </button>
                </div>
            </form>

            <p class="text-center mt-6 text-sm">
                Already have an account? 
                <a href="/Blog/login.php" class="text-accent hover:underline">Sign in</a>
            </p>
        </div>
    </div>
    
    <!-- Right side - Welcome Text -->
    <div class="hidden lg:flex flex-1 items-center justify-center relative overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
            <div class="absolute w-24 h-24 bg-accent/5 backdrop-blur-xl rounded-full top-32 left-32 animate-float-slow"></div>
            <div class="absolute w-40 h-40 bg-accent/5 backdrop-blur-xl rounded-full bottom-20 right-20 animate-float-medium"></div>
            <div class="absolute w-16 h-16 bg-accent/5 backdrop-blur-xl rounded-full top-20 right-32 animate-float-fast"></div>
        </div>
        
        <div class="relative text-charcoal max-w-md p-8">
            <h2 class="font-serif text-4xl mb-6">Join Our Community</h2>
            <p class="text-charcoal/70 text-lg leading-relaxed mb-8">
                Share your knowledge, experiences, and insights with a community of passionate 
                developers and designers. Start your journey today!
            </p>
            <ul class="space-y-4 text-charcoal/70">
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Write and share your stories
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Connect with fellow developers
                </li>
                <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Grow your professional network
                </li>
            </ul>
            <div class="flex gap-6 mt-8 text-charcoal/60">
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

<?php require_once 'includes/footer.php'; ?>

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

function validateForm() {
    const username = document.querySelector('input[name="username"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.querySelector('input[name="password"]');
    const confirm = document.querySelector('input[name="password_confirm"]');
    const submitBtn = document.getElementById('submitBtn');
    let isValid = true;

    // Username validation
    if (username.value.length < 3) {
        showError('usernameError', 'Username must be at least 3 characters long');
        isValid = false;
    } else {
        hideError('usernameError');
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
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
    
    // Update password requirement checks with colors
    document.getElementById('lengthCheck').innerHTML = 
        `${hasLength ? '✓' : '✗'} At least 8 characters`;
    document.getElementById('lengthCheck').className = 
        `transition-colors ${hasLength ? 'text-green-600' : 'text-charcoal/60'}`;

    document.getElementById('upperCheck').innerHTML = 
        `${hasUpper ? '✓' : '✗'} One uppercase letter`;
    document.getElementById('upperCheck').className = 
        `transition-colors ${hasUpper ? 'text-green-600' : 'text-charcoal/60'}`;

    document.getElementById('lowerCheck').innerHTML = 
        `${hasLower ? '✓' : '✗'} One lowercase letter`;
    document.getElementById('lowerCheck').className = 
        `transition-colors ${hasLower ? 'text-green-600' : 'text-charcoal/60'}`;

    document.getElementById('numberCheck').innerHTML = 
        `${hasNumber ? '✓' : '✗'} One number`;
    document.getElementById('numberCheck').className = 
        `transition-colors ${hasNumber ? 'text-green-600' : 'text-charcoal/60'}`;
    
    const isPasswordValid = hasLength && hasUpper && hasLower && hasNumber;
    if (!isPasswordValid) {
        showError('passwordError', 'Password does not meet requirements');
        isValid = false;
    } else {
        hideError('passwordError');
    }

    // Confirm password
    if (password.value !== confirm.value) {
        showError('confirmError', 'Passwords do not match');
        isValid = false;
    } else {
        hideError('confirmError');
    }

    // Update submit button state
    submitBtn.disabled = !isValid;
}

function showError(elementId, message) {
    const error = document.getElementById(elementId);
    error.textContent = message;
    error.classList.remove('hidden');
}

function hideError(elementId) {
    const error = document.getElementById(elementId);
    error.classList.add('hidden');
}

// Initialize validation on page load
document.addEventListener('DOMContentLoaded', validateForm);
</script>

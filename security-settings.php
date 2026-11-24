<?php
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl shadow-sm border border-taupe overflow-hidden">
        <!-- Tabs -->
        <div class="flex border-b border-taupe bg-cream/40">
            <a href="/Blog/account-settings.php" class="px-6 py-3.5 text-sm font-medium text-charcoal/60 hover:text-charcoal hover:bg-white/60 transition-colors flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Account</span>
            </a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3.5 text-sm font-medium text-charcoal/60 hover:text-charcoal hover:bg-white/60 transition-colors flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Profile</span>
            </a>
            <a href="/Blog/security-settings.php" class="px-6 py-3.5 text-sm font-medium border-b-2 border-dark text-dark bg-white flex items-center gap-2">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Security</span>
            </a>
        </div>

        <div class="p-8 space-y-10">
            <div>
                <h1 class="font-serif text-2xl font-bold mb-2">Security Settings</h1>
                <p class="text-sm text-charcoal/60">Manage your password and account security preferences.</p>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 text-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Change Password -->
            <form action="/Blog/api/update_password.php" method="POST" class="space-y-6 max-w-lg">
                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">Current Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="current_password" required 
                               class="w-full pl-10 pr-4 py-2.5 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">New Password</label>
                    <div class="relative">
                        <i data-lucide="key" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="new_password" required minlength="8" 
                               class="w-full pl-10 pr-4 py-2.5 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                    <p class="mt-1 text-xs text-charcoal/50">Must be at least 8 characters long.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">Confirm New Password</label>
                    <div class="relative">
                        <i data-lucide="key" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="confirm_password" required minlength="8" 
                               class="w-full pl-10 pr-4 py-2.5 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                </div>

                <button type="submit" class="px-6 py-2.5 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Update Password</span>
                </button>
            </form>

            <hr class="border-taupe">

            <!-- Danger Zone -->
            <div class="pt-2">
                <div class="rounded-xl border border-red-200 bg-red-50/50 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-red-900">Danger Zone</h3>
                            <p class="text-sm text-red-700/80 mt-1">Permanently delete your account and all published stories, drafts, and comments. This action cannot be undone.</p>
                            
                            <div class="mt-4" x-data="{ openModal: false, password: '', error: '', loading: false }">
                                <button type="button" @click="openModal = true" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors inline-flex items-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Delete Account</span>
                                </button>

                                <!-- Delete Confirmation Modal -->
                                <div x-show="openModal" 
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                     style="display: none;">
                                    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.away="openModal = false">
                                        <div class="flex items-center justify-between pb-2 border-b">
                                            <h4 class="font-serif text-lg text-red-600 font-bold flex items-center gap-2">
                                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                                Confirm Account Deletion
                                            </h4>
                                            <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                        <p class="text-sm text-gray-600">To confirm deletion, please enter your password. All your stories, drafts, likes, and comments will be permanently erased.</p>
                                        
                                        <div x-show="error" class="p-3 bg-red-50 text-red-600 text-sm rounded-lg" x-text="error"></div>
                                        
                                        <input type="password" x-model="password" placeholder="Your password" 
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-400">
                                        
                                        <div class="flex justify-end gap-3 pt-2">
                                            <button type="button" @click="openModal = false" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                                            <button type="button" 
                                                    :disabled="!password || loading"
                                                    @click="
                                                        loading = true; error = '';
                                                        fetch('/Blog/api/delete_account.php', {
                                                            method: 'POST',
                                                            headers: {'Content-Type': 'application/json'},
                                                            body: JSON.stringify({password: password})
                                                        })
                                                        .then(r => r.json())
                                                        .then(data => {
                                                            loading = false;
                                                            if (data.success) {
                                                                window.location.href = '/Blog/logout.php';
                                                            } else {
                                                                error = data.error || 'Failed to delete account';
                                                            }
                                                        })
                                                        .catch(err => {
                                                            loading = false;
                                                            error = 'An error occurred';
                                                        });
                                                    "
                                                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
                                                <span x-show="!loading">Permanently Delete</span>
                                                <span x-show="loading">Deleting...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

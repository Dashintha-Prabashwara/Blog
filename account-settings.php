<?php
require_once 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /Blog/logout.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl shadow-sm border border-taupe overflow-hidden">
        <!-- Tabs -->
        <div class="flex border-b border-taupe bg-cream/40">
            <a href="/Blog/account-settings.php" class="px-6 py-3.5 text-sm font-medium border-b-2 border-dark text-dark bg-white flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Account</span>
            </a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3.5 text-sm font-medium text-charcoal/60 hover:text-charcoal hover:bg-white/60 transition-colors flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Profile</span>
            </a>
            <a href="/Blog/security-settings.php" class="px-6 py-3.5 text-sm font-medium text-charcoal/60 hover:text-charcoal hover:bg-white/60 transition-colors flex items-center gap-2">
                <i data-lucide="shield" class="w-4 h-4"></i>
                <span>Security</span>
            </a>
        </div>

        <div class="p-8 space-y-8">
            <div>
                <h1 class="font-serif text-2xl font-bold mb-2">Account Settings</h1>
                <p class="text-sm text-charcoal/60">Update your account information, public display name, and author biography.</p>
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

            <form action="/Blog/api/update_account.php" method="POST" class="space-y-6 max-w-lg">
                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                               class="w-full pl-10 pr-4 py-2.5 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-charcoal/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                               class="w-full pl-10 pr-4 py-2.5 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-charcoal/80">Author Bio</label>
                    <div class="relative">
                        <textarea name="description" rows="4" 
                                  class="w-full p-4 border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30 resize-none text-sm"
                                  placeholder="Write a short bio for your public author profile..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                    </div>
                    <p class="mt-1 text-xs text-charcoal/50">This appears on your stories and public profile card.</p>
                </div>

                <button type="submit" class="px-6 py-2.5 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

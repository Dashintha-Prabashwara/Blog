<?php
require_once __DIR__ . '/includes/header.php';

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
            <a href="/Blog/account-settings.php" class="px-6 py-3.5 text-sm font-medium text-charcoal/60 hover:text-charcoal hover:bg-white/60 transition-colors flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Account</span>
            </a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3.5 text-sm font-medium border-b-2 border-dark text-dark bg-white flex items-center gap-2">
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
                <h1 class="font-serif text-2xl font-bold mb-2">Profile Picture</h1>
                <p class="text-sm text-charcoal/60">Upload a custom avatar for your author profile across stories and discussions.</p>
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

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-8 p-6 bg-cream/40 rounded-2xl border border-taupe">
                <div class="relative group">
                    <div class="w-32 h-32 rounded-2xl overflow-hidden bg-taupe border-2 border-white shadow-md">
                        <img src="<?= $user['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                             alt="Profile avatar" 
                             class="w-full h-full object-cover">
                    </div>
                </div>
                
                <div class="space-y-4">
                    <form id="imageForm" action="/Blog/api/update_profile_image.php" method="POST" enctype="multipart/form-data" class="hidden">
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp,image/gif" onchange="this.form.submit()">
                    </form>
                    
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="document.getElementById('profile_image').click()" 
                                class="px-5 py-2.5 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm inline-flex items-center gap-2">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Upload New Photo</span>
                        </button>
                        
                        <?php if (!empty($user['profile_image'])): ?>
                            <form action="/Blog/api/delete_profile_image.php" method="POST" onsubmit="return confirm('Remove your profile photo?');">
                                <button type="submit" class="px-4 py-2.5 border border-red-300 text-red-600 rounded-xl hover:bg-red-50 transition-colors text-sm font-medium inline-flex items-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Remove Photo</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-xs text-charcoal/60 leading-relaxed">
                        Recommended: Square JPG, PNG, or WEBP image, at least 400x400 pixels.<br>
                        Max file size: 5MB.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex justify-between items-center text-sm">
                <a href="/Blog/public-profile.php?id=<?= $user['id'] ?>" class="text-accent hover:underline inline-flex items-center gap-1.5 font-medium">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Preview Public Profile</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

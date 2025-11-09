<?php
require_once 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow">
        <!-- Tabs -->
        <div class="flex border-b">
            <a href="/Blog/account-settings.php" class="px-6 py-3 border-b-2 border-blue-500 font-medium">Account</a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Profile</a>
            <a href="/Blog/security-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Security</a>
        </div>

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Account Settings</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 text-green-600 rounded-lg">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-lg">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="/Blog/api/update_account.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                           class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Bio</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border rounded-lg resize-none"
                              placeholder="Tell us about yourself..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Brief description for your profile.</p>
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

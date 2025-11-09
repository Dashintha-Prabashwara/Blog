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
            <a href="/Blog/account-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Account</a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3 border-b-2 border-blue-500 font-medium">Profile</a>
            <a href="/Blog/security-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Security</a>
        </div>

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Profile Settings</h1>
            
            <!-- Profile Image -->
            <div class="mb-8">
                <div class="flex items-center gap-6">
                    <img src="<?= $user['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                         class="w-32 h-32 rounded-full object-cover">
                    
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <form id="imageForm" action="/Blog/api/update_profile_image.php" method="POST" enctype="multipart/form-data" class="hidden">
                                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="this.form.submit()">
                            </form>
                            <button onclick="document.getElementById('profile_image').click()" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Upload New Photo
                            </button>
                            <?php if ($user['profile_image']): ?>
                                <form action="/Blog/api/delete_profile_image.php" method="POST">
                                    <button type="submit" class="px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50">
                                        Remove Photo
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500">
                            Recommended: Square image, at least 400x400 pixels
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

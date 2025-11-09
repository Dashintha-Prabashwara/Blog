<?php
require_once 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="max-w-2xl mx-auto px-4 py-16">
    <h1 class="font-serif text-3xl mb-8">Profile Settings</h1>

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

    <!-- Profile Image -->
    <div class="mb-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-medium mb-4">Profile Picture</h2>
        <div class="flex items-center gap-6">
            <div class="relative">
                <img src="<?= $user['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                     alt="Profile" 
                     class="w-24 h-24 rounded-full object-cover">
                <button onclick="document.getElementById('profile_image').click()" 
                        class="absolute bottom-0 right-0 bg-white p-2 rounded-full shadow hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" 
                              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <form id="imageForm" action="/Blog/api/update_profile_image.php" method="POST" enctype="multipart/form-data" class="hidden">
                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Account Details -->
    <div class="mb-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-medium mb-4">Account Details</h2>
        <form action="/Blog/api/update_profile.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Update Profile
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-medium mb-4">Change Password</h2>
        <form action="/Blog/api/update_password.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Current Password</label>
                <input type="password" name="current_password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">New Password</label>
                <input type="password" name="new_password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Update Password
            </button>
        </form>
    </div>
</div>

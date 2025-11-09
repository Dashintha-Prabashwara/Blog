<?php
require_once 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow">
        <!-- Tabs -->
        <div class="flex border-b">
            <a href="/Blog/account-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Account</a>
            <a href="/Blog/profile-settings.php" class="px-6 py-3 text-gray-500 hover:text-gray-700">Profile</a>
            <a href="/Blog/security-settings.php" class="px-6 py-3 border-b-2 border-blue-500 font-medium">Security</a>
        </div>

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Security Settings</h1>
            
            <!-- Change Password -->
            <form action="/Blog/api/update_password.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Current Password</label>
                    <input type="password" name="current_password" required 
                           class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">New Password</label>
                    <input type="password" name="new_password" required 
                           class="w-full px-4 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-4 py-2 border rounded-lg">
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

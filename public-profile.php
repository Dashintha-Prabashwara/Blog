<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user data
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$userId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header('Location: /Blog/404.php');
    exit;
}

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM blogPost WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$posts = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto px-4 py-16">
    <!-- Profile Header -->
    <div class="mb-12 text-center">
        <img src="<?= $profileUser['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
             alt="<?= htmlspecialchars($profileUser['username']) ?>"
             class="w-32 h-32 rounded-full object-cover mx-auto mb-6">
        <h1 class="font-serif text-3xl mb-4"><?= htmlspecialchars($profileUser['username']) ?></h1>
        <?php if ($profileUser['description']): ?>
            <p class="text-charcoal/70 max-w-2xl mx-auto mb-6">
                <?= htmlspecialchars($profileUser['description']) ?>
            </p>
        <?php endif; ?>
        
        <!-- Show Follow Button only for other users -->
        <?php if (is_logged_in() && $profileUser['id'] != $_SESSION['user_id']): ?>
            <button onclick="toggleFollow(<?= $profileUser['id'] ?>, this)" 
                    class="px-6 py-2 mb-8 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">
                <?= isFollowing($profileUser['id']) ? 'Following' : 'Follow' ?>
            </button>
        <?php elseif (!is_logged_in()): ?>
            <a href="/Blog/login.php" class="inline-block px-6 py-2 mb-8 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">
                Follow
            </a>
        <?php endif; ?>
        
        <?php $stats = getUserStats($profileUser['id']); ?>
        <div class="flex justify-center gap-8 text-sm text-charcoal/70">
            <div>
                <span class="font-medium"><?= $stats['followers'] ?></span> followers
            </div>
            <div>
                <span class="font-medium"><?= $stats['total_likes'] ?></span> post likes
            </div>
            <div>
                <span class="font-medium"><?= $stats['comment_likes'] ?></span> comment likes
            </div>
            <div>
                <span class="font-medium"><?= $stats['comment_dislikes'] ?></span> comment dislikes
            </div>
        </div>
    </div>

    <!-- User's Posts -->
    <div class="grid sm:grid-cols-2 gap-8">
        <?php foreach ($posts as $post): ?>
            <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                <?php if ($post['image_url']): ?>
                    <div class="aspect-[16/9] overflow-hidden">
                        <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                             alt="" 
                             class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                <div class="p-6">
                    <h2 class="font-serif text-xl mb-4">
                        <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="hover:text-accent">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </h2>
                    <time class="text-sm text-charcoal/60">
                        <?= date('F j, Y', strtotime($post['created_at'])) ?>
                    </time>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add JavaScript at the end before footer -->
<script>
async function toggleFollow(userId, button) {
    try {
        const response = await fetch('/Blog/api/toggle_follow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        const data = await response.json();
        if (data.success) {
            button.textContent = data.following ? 'Following' : 'Follow';
            // Refresh the page to update follower count
            location.reload();
        }
    } catch (err) {
        console.error('Error:', err);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

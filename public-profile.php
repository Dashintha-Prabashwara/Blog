<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$userId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header('Location: /Blog/404.php');
    exit;
}

// Get user's published stories
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
    FROM blogpost p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$userId]);
$posts = $stmt->fetchAll();

$stats = getUserStats($profileUser['id']);
$pageTitle = htmlspecialchars($profileUser['username']) . ' — Code & Canvas Author';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    <!-- Profile Card Header -->
    <div class="bg-white rounded-3xl p-8 sm:p-12 border border-taupe shadow-sm text-center mb-12 relative overflow-hidden">
        <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl overflow-hidden bg-taupe border-4 border-white shadow-md mx-auto mb-6">
            <img src="<?= $profileUser['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                 alt="<?= htmlspecialchars($profileUser['username']) ?>"
                 class="w-full h-full object-cover">
        </div>
        
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal mb-2">
            <?= htmlspecialchars($profileUser['username']) ?>
        </h1>
        
        <p class="text-xs sm:text-sm text-charcoal/50 mb-4">
            Member since <?= date('F Y', strtotime($profileUser['created_at'])) ?>
        </p>

        <?php if (!empty($profileUser['description'])): ?>
            <p class="text-sm sm:text-base text-charcoal/70 max-w-xl mx-auto leading-relaxed mb-6">
                <?= htmlspecialchars($profileUser['description']) ?>
            </p>
        <?php endif; ?>
        
        <!-- Follow / Action Button -->
        <div class="flex items-center justify-center gap-3 mb-8">
            <?php if (is_logged_in() && (int)$profileUser['id'] !== (int)$_SESSION['user_id']): ?>
                <button onclick="toggleFollow(<?= (int)$profileUser['id'] ?>, this)" 
                        class="px-6 py-2.5 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span><?= isFollowing($profileUser['id']) ? 'Following' : 'Follow Author' ?></span>
                </button>
            <?php elseif (!is_logged_in()): ?>
                <a href="/Blog/login.php?return=<?= urlencode('/Blog/public-profile.php?id=' . $profileUser['id']) ?>" 
                   class="px-6 py-2.5 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Follow</span>
                </a>
            <?php elseif (is_logged_in() && (int)$profileUser['id'] === (int)$_SESSION['user_id']): ?>
                <a href="/Blog/account-settings.php" 
                   class="px-5 py-2.5 border border-taupe rounded-xl text-xs font-semibold text-charcoal hover:bg-cream transition-all flex items-center gap-1.5">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>Edit Profile</span>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Stats Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-8 border-t border-taupe max-w-2xl mx-auto text-center">
            <div>
                <span class="block font-serif text-2xl font-bold text-charcoal"><?= count($posts) ?></span>
                <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium">Stories</span>
            </div>
            <div>
                <span class="block font-serif text-2xl font-bold text-charcoal"><?= (int)($stats['followers'] ?? 0) ?></span>
                <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium">Followers</span>
            </div>
            <div>
                <span class="block font-serif text-2xl font-bold text-charcoal"><?= (int)($stats['total_likes'] ?? 0) ?></span>
                <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium">Story Likes</span>
            </div>
            <div>
                <span class="block font-serif text-2xl font-bold text-charcoal"><?= (int)($stats['total_comments'] ?? 0) ?></span>
                <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium">Comments</span>
            </div>
        </div>
    </div>

    <!-- Author's Stories Section -->
    <div>
        <div class="flex items-center gap-2.5 mb-8">
            <span class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                <i data-lucide="book-open" class="w-4 h-4"></i>
            </span>
            <h2 class="font-serif text-2xl font-bold text-charcoal">Stories by <?= htmlspecialchars($profileUser['username']) ?></h2>
        </div>

        <?php if (empty($posts)): ?>
            <div class="p-12 bg-white rounded-3xl border border-taupe text-center">
                <div class="w-12 h-12 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-3">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <p class="text-sm font-medium text-charcoal/60">This author hasn't published any stories yet.</p>
            </div>
        <?php else: ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-2xl overflow-hidden border border-taupe shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
                        <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="block">
                            <div class="aspect-[16/10] bg-taupe relative overflow-hidden">
                                <?php if (!empty($post['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-cream flex items-center justify-center text-charcoal/30">
                                        <i data-lucide="feather" class="w-8 h-8"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <?php if (!empty($post['topics'])): ?>
                                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                                        <?php foreach(array_slice(explode(',', $post['topics']), 0, 2) as $t): if(trim($t)): ?>
                                            <span class="px-2 py-0.5 bg-cream rounded-md text-[11px] font-medium text-charcoal/70 border border-taupe">
                                                <?= htmlspecialchars(trim($t)) ?>
                                            </span>
                                        <?php endif; endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="font-serif text-lg font-bold text-charcoal hover:text-accent transition-colors line-clamp-2 mb-2 leading-snug">
                                    <?= htmlspecialchars($post['title']) ?>
                                </h3>
                                <p class="text-xs sm:text-sm text-charcoal/70 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 120)) ?>...
                                </p>
                            </div>
                        </a>
                        
                        <div class="px-6 pb-6 pt-0 border-t border-taupe/40 mt-auto flex items-center justify-between text-xs text-charcoal/60">
                            <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 text-accent"></i>
                                    <?= (int)$post['like_count'] ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-charcoal/50"></i>
                                    <?= (int)$post['comment_count'] ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

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
            location.reload();
        } else {
            alert(data.error || 'Failed to update follow status');
        }
    } catch (err) {
        console.error(err);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

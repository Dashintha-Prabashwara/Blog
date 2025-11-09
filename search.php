<?php
require_once 'includes/header.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM blogpost p 
    JOIN user u ON p.user_id = u.id 
    WHERE p.title LIKE ? OR p.content LIKE ?
    ORDER BY p.created_at DESC
");
$stmt->execute(["%$query%", "%$query%"]);
$results = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:py-16">
    <div class="max-w-3xl mx-auto">
        <h1 class="font-serif text-2xl sm:text-3xl mb-2">Search Results</h1>
        <p class="text-charcoal/70 mb-8 sm:mb-12">
            <?= $query ? "Showing results for \"" . htmlspecialchars($query) . "\"" : "Browse all stories" ?>
        </p>

        <?php if ($results): ?>
            <div class="space-y-6 sm:space-y-8">
                <?php foreach ($results as $post): ?>
                    <article class="group">
                        <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="block bg-white p-6 rounded-2xl hover:shadow-lg transition-all">
                            <h2 class="font-serif text-xl mb-3"><?= htmlspecialchars($post['title']) ?></h2>
                            <p class="text-charcoal/70 mb-4 line-clamp-2"><?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...</p>
                            <div class="flex items-center gap-3 text-sm text-charcoal/60">
                                <span><?= htmlspecialchars($post['username']) ?></span>
                                <span>·</span>
                                <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <p class="text-charcoal/70">No results found. Try different keywords.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

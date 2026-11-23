<?php
require_once __DIR__ . '/includes/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
               (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
        FROM blogpost p 
        JOIN user u ON p.user_id = u.id 
        WHERE p.title LIKE ? OR p.content LIKE ? OR p.topics LIKE ?
        ORDER BY p.created_at DESC
    ");
    $param = "%$query%";
    $stmt->execute([$param, $param, $param]);
    $results = $stmt->fetchAll();
}

$pageTitle = ($query ? 'Search: ' . htmlspecialchars($query) : 'Search Stories') . ' — Code & Canvas';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
    <div class="mb-10 text-center sm:text-left">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent mb-2">
            <i data-lucide="search" class="w-3.5 h-3.5"></i>
            Search Engine
        </span>
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal">
            <?= $query !== '' ? 'Search results for "' . htmlspecialchars($query) . '"' : 'Search Stories' ?>
        </h1>
        <p class="text-xs sm:text-sm text-charcoal/60 mt-1">
            <?= $query !== '' ? count($results) . ' matching ' . (count($results) === 1 ? 'story' : 'stories') . ' found' : 'Find articles across all topics, titles, and body content.' ?>
        </p>

        <!-- Big Search Bar -->
        <form action="/Blog/search.php" method="GET" class="mt-6">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 text-charcoal/40 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="search" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search by title, topics, or keywords..." 
                       class="w-full pl-12 pr-28 py-3.5 text-sm bg-white border border-taupe rounded-2xl focus:outline-none focus:ring-2 focus:ring-accent/40 shadow-sm">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-5 py-2 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all">
                    Search
                </button>
            </div>
        </form>
    </div>

    <?php if ($query !== ''): ?>
        <?php if (!empty($results)): ?>
            <div class="space-y-4">
                <?php foreach ($results as $post): ?>
                    <article class="bg-white rounded-2xl p-6 border border-taupe shadow-sm hover:shadow-lg transition-all">
                        <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="block space-y-2">
                            <?php if (!empty($post['topics'])): ?>
                                <div class="flex flex-wrap gap-1.5 mb-1">
                                    <?php foreach(explode(',', $post['topics']) as $t): if(trim($t)): ?>
                                        <span class="px-2 py-0.5 bg-cream rounded-md text-[11px] font-medium text-charcoal/70 border border-taupe">
                                            <?= htmlspecialchars(trim($t)) ?>
                                        </span>
                                    <?php endif; endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h2 class="font-serif text-xl font-bold text-charcoal hover:text-accent transition-colors leading-snug">
                                <?= htmlspecialchars($post['title']) ?>
                            </h2>
                            <p class="text-xs sm:text-sm text-charcoal/70 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 180)) ?>...
                            </p>
                        </a>
                        
                        <div class="mt-4 pt-4 border-t border-taupe/40 flex items-center justify-between text-xs text-charcoal/60">
                            <div class="flex items-center gap-2">
                                <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                     alt="" class="w-6 h-6 rounded-full object-cover border border-taupe">
                                <span class="font-medium text-charcoal"><?= htmlspecialchars($post['username']) ?></span>
                                <span>•</span>
                                <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                            </div>
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
        <?php else: ?>
            <div class="p-16 bg-white rounded-3xl border border-taupe text-center">
                <div class="w-14 h-14 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-4">
                    <i data-lucide="search-x" class="w-7 h-7"></i>
                </div>
                <h3 class="font-serif text-xl font-bold text-charcoal mb-2">No results found</h3>
                <p class="text-sm text-charcoal/60 max-w-sm mx-auto mb-6">We couldn't find any stories matching "<?= htmlspecialchars($query) ?>". Try different or broader keywords.</p>
                <a href="/Blog/posts.php" class="px-5 py-2.5 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all">
                    Browse All Stories
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

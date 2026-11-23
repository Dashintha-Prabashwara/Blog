<?php 
require_once __DIR__ . '/includes/db.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Get selected topic from URL
$selectedTopic = isset($_GET['topic']) ? trim($_GET['topic']) : '';

// Extract all unique topics cleanly
$topicRows = $pdo->query("SELECT topics FROM blogpost WHERE topics IS NOT NULL AND topics != ''")->fetchAll(PDO::FETCH_COLUMN);
$allTopicsMap = [];
foreach ($topicRows as $row) {
    foreach (explode(',', $row) as $t) {
        $t = trim($t);
        if ($t !== '') {
            $allTopicsMap[$t] = true;
        }
    }
}
$topics = array_keys($allTopicsMap);
sort($topics);

// Posts query with topic filter
if ($selectedTopic !== '') {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blogpost WHERE topics LIKE ?");
    $countStmt->execute(["%" . $selectedTopic . "%"]);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
               (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
        FROM blogpost p 
        JOIN user u ON p.user_id = u.id 
        WHERE p.topics LIKE ?
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, "%" . $selectedTopic . "%", PDO::PARAM_STR);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $total = (int)$pdo->query("SELECT COUNT(*) FROM blogpost")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
               (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
        FROM blogpost p 
        JOIN user u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$posts = $stmt->fetchAll();
$totalPages = ceil($total / $perPage);

$pageTitle = ($selectedTopic ? htmlspecialchars($selectedTopic) . ' Stories' : 'All Stories') . ' — Code & Canvas';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
    <!-- Header with title & filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10 pb-6 border-b border-taupe">
        <div>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal">
                <?= $selectedTopic ? 'Stories in "' . htmlspecialchars($selectedTopic) . '"' : 'All Stories' ?>
            </h1>
            <p class="text-xs sm:text-sm text-charcoal/60 mt-1">
                Showing <?= count($posts) ?> of <?= $total ?> published <?= $total === 1 ? 'story' : 'stories' ?>
            </p>
        </div>
        
        <!-- Topics Filter Pills -->
        <div class="overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex items-center gap-2 min-w-max">
                <a href="/Blog/posts.php" 
                   class="px-4 py-1.5 text-xs font-semibold rounded-full border transition-colors <?= empty($selectedTopic) ? 'bg-dark text-white border-dark shadow-sm' : 'bg-white text-charcoal/70 border-taupe hover:border-dark' ?>">
                    All Topics
                </a>
                <?php foreach ($topics as $topic): ?>
                    <a href="?topic=<?= urlencode($topic) ?>" 
                       class="px-3.5 py-1.5 text-xs font-semibold rounded-full border transition-colors flex items-center gap-1 <?= $selectedTopic === $topic ? 'bg-dark text-white border-dark shadow-sm' : 'bg-white text-charcoal/70 border-taupe hover:border-dark' ?>">
                        <i data-lucide="tag" class="w-3 h-3 text-charcoal/40"></i>
                        <span><?= htmlspecialchars($topic) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Stories Grid -->
    <?php if (empty($posts)): ?>
        <div class="p-16 bg-white rounded-3xl border border-taupe text-center">
            <div class="w-14 h-14 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-4">
                <i data-lucide="inbox" class="w-7 h-7"></i>
            </div>
            <h3 class="font-serif text-xl font-bold text-charcoal mb-2">No stories found</h3>
            <p class="text-sm text-charcoal/60 max-w-sm mx-auto mb-6">
                <?= $selectedTopic ? 'No stories published under "' . htmlspecialchars($selectedTopic) . '" yet.' : 'There are no stories published yet.' ?>
            </p>
            <a href="/Blog/posts.php" class="px-5 py-2.5 bg-dark text-white text-xs font-semibold rounded-xl hover:bg-charcoal transition-all">
                Clear Filters
            </a>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts as $post): ?>
                <article class="group bg-white rounded-2xl overflow-hidden border border-taupe shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
                    <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="block">
                        <div class="aspect-[16/10] bg-taupe relative overflow-hidden">
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="w-full h-full bg-cream flex items-center justify-center text-charcoal/30">
                                    <i data-lucide="feather" class="w-10 h-10"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-6">
                            <?php if (!empty($post['topics'])): ?>
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    <?php 
                                    $tags = array_slice(explode(',', $post['topics']), 0, 2);
                                    foreach($tags as $t): if(trim($t)): ?>
                                        <span class="px-2 py-0.5 bg-cream rounded-md text-[11px] font-medium text-charcoal/70 border border-taupe">
                                            <?= htmlspecialchars(trim($t)) ?>
                                        </span>
                                    <?php endif; endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h3 class="font-serif text-xl font-bold text-charcoal group-hover:text-accent transition-colors line-clamp-2 mb-2 leading-snug">
                                <?= htmlspecialchars($post['title']) ?>
                            </h3>
                            
                            <p class="text-charcoal/70 text-xs sm:text-sm line-clamp-3 leading-relaxed mb-4">
                                <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 140)) ?>...
                            </p>
                        </div>
                    </a>

                    <div class="px-6 pb-6 pt-0 border-t border-taupe/40 mt-auto flex items-center justify-between text-xs text-charcoal/60">
                        <div class="flex items-center gap-2">
                            <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                 alt="<?= htmlspecialchars($post['username']) ?>"
                                 class="w-6 h-6 rounded-full object-cover border border-taupe">
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
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-12">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= $selectedTopic ? 'topic=' . urlencode($selectedTopic) . '&' : '' ?>page=<?= $i ?>" 
                   class="w-10 h-10 rounded-xl text-sm font-semibold flex items-center justify-center border transition-all <?= $i === $page ? 'bg-dark text-white border-dark shadow-sm' : 'bg-white text-charcoal border-taupe hover:border-dark' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

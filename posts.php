<?php 
require_once 'includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Get all unique topics - Fixed query
$stmt = $pdo->query("
    SELECT DISTINCT TRIM(topic) as topic 
    FROM (
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(topics, ',', n.n), ',', -1) topic
        FROM blogPost t CROSS JOIN (
            SELECT a.N + b.N * 10 + 1 n
            FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a,
                 (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
            ORDER BY n
        ) n
        WHERE n.n <= 1 + (LENGTH(topics) - LENGTH(REPLACE(topics, ',', '')))
    ) temp 
    WHERE topic != ''
    ORDER BY topic
");
$topics = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get selected topic from URL
$selectedTopic = isset($_GET['topic']) ? trim($_GET['topic']) : '';

// Modify the posts query to filter by topic if selected
$stmt = $pdo->prepare("
    SELECT p.*, u.username, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count
    FROM blogPost p 
    JOIN user u ON p.user_id = u.id 
    " . ($selectedTopic ? "WHERE p.topics LIKE ?" : "") . "
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?");

$params = $selectedTopic 
    ? ["%" . $selectedTopic . "%", $perPage, $offset]
    : [$perPage, $offset];
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get total posts for pagination
$total = $pdo->query("SELECT COUNT(*) FROM blogPost")->fetchColumn();
$totalPages = ceil($total / $perPage);
?>

<div class="max-w-7xl mx-auto px-2 sm:px-4 py-8 sm:py-16">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <h1 class="font-serif text-2xl sm:text-4xl">Stories</h1>
        
        <!-- Topics Filter - made scrollable on mobile -->
        <div class="overflow-x-auto -mx-2 px-2">
            <div class="flex gap-2 min-w-max pb-4">
                <a href="/Blog/posts.php" 
                   class="px-2 sm:px-3 py-1 text-xs sm:text-sm whitespace-nowrap rounded-full
                          <?= !$selectedTopic ? 'bg-dark text-white' : 'bg-cream text-charcoal/70 hover:bg-charcoal/10' ?> 
                          transition-colors">
                    All
                </a>
                <?php foreach ($topics as $topic): ?>
                    <a href="?topic=<?= urlencode($topic) ?>" 
                       class="px-2 sm:px-3 py-1 text-xs sm:text-sm whitespace-nowrap rounded-full 
                              <?= $selectedTopic === $topic ? 'bg-dark text-white' : 'bg-cream text-charcoal/70 hover:bg-charcoal/10' ?> 
                              transition-colors">
                        <?= htmlspecialchars($topic) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($posts as $post): ?>
            <article class="group">
                <a href="/Blog/post.php?id=<?= $post['id'] ?>" 
                   class="block bg-white rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="aspect-[4/3] relative overflow-hidden">
                        <?php if ($post['image_url']): ?>
                            <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 class="w-full h-[250px] object-cover"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="w-full h-[250px] bg-gray-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <!-- Add topics to each card -->
                        <?php if ($post['topics']): ?>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <?php foreach(explode(',', $post['topics']) as $topic): ?>
                                <?php if(trim($topic)): ?>
                                    <span class="px-2 py-0.5 bg-cream rounded-full text-xs text-charcoal/70">
                                        <?= htmlspecialchars(trim($topic)) ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <h3 class="font-serif text-xl mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="text-charcoal/70 mb-4 line-clamp-2">
                            <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                        </p>
                        <div class="flex items-center gap-3 text-sm text-charcoal/60">
                            <span><?= htmlspecialchars($post['username']) ?></span>
                            <span>·</span>
                            <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                            <!-- Add like count -->
                            <span>·</span>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span><?= $post['like_count'] ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-12">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" 
               class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-dark text-white' : 'bg-white text-charcoal hover:bg-charcoal/10' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

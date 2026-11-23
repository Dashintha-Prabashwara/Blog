<?php 
require_once __DIR__ . '/includes/db.php';

// Get all topics and sort them
$stmt = $pdo->query("SELECT DISTINCT topics FROM blogpost WHERE topics IS NOT NULL AND topics != ''");
$allTopics = [];
while ($row = $stmt->fetch()) {
    $topics = explode(',', $row['topics']);
    foreach ($topics as $topic) {
        $topic = trim($topic);
        if ($topic) {
            $allTopics[$topic] = strtoupper($topic[0]);
        }
    }
}
ksort($allTopics);

// Get selected topics from URL
$selectedTopics = !empty($_GET['topics']) ? array_filter(array_map('trim', explode(',', $_GET['topics']))) : [];
$selectedLetter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : null;

// Get filtered posts if topics are selected
$posts = [];
if (!empty($selectedTopics)) {
    $placeholders = str_repeat('? OR topics LIKE ', count($selectedTopics) - 1) . '?';
    $params = array_map(function($topic) {
        return '%' . $topic . '%';
    }, $selectedTopics);
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count 
        FROM blogpost p 
        JOIN user u ON p.user_id = u.id 
        WHERE topics LIKE {$placeholders}
        ORDER BY p.created_at DESC
    ");
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
}

// Group topics by first letter
$topicsByLetter = [];
foreach ($allTopics as $topic => $letter) {
    $topicsByLetter[$letter][] = $topic;
}
ksort($topicsByLetter);

$pageTitle = 'Topics Directory — Code & Canvas';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
    <div class="mb-10 pb-6 border-b border-taupe">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent mb-2">
            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
            Taxonomy
        </span>
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal">Topics Directory</h1>
        <p class="text-xs sm:text-sm text-charcoal/60 mt-1">Browse stories organized alphabetically by topic and category.</p>
    </div>

    <!-- A-Z Alphabet Filter Navigation -->
    <div class="flex flex-wrap gap-1.5 mb-10 pb-4 border-b border-taupe/60 overflow-x-auto">
        <a href="?" 
           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all <?= !$selectedLetter ? 'bg-dark text-white shadow-sm' : 'bg-white text-charcoal border border-taupe hover:border-dark' ?>">
            All
        </a>
        <?php foreach (range('A', 'Z') as $letter): 
            $hasTopics = isset($topicsByLetter[$letter]);
        ?>
            <a href="?<?= !empty($selectedTopics) ? 'topics=' . urlencode(implode(',', $selectedTopics)) . '&' : '' ?>letter=<?= $letter ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all <?= $selectedLetter === $letter ? 'bg-dark text-white shadow-sm' : ($hasTopics ? 'bg-white text-charcoal border border-taupe hover:border-dark' : 'bg-gray-100 text-gray-400 cursor-not-allowed pointer-events-none') ?>">
                <?= $letter ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Topics Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-14">
        <?php 
        if (empty($topicsByLetter)):
        ?>
            <div class="col-span-full bg-white p-12 rounded-3xl border border-taupe text-center">
                <div class="w-12 h-12 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-3">
                    <i data-lucide="tag" class="w-6 h-6"></i>
                </div>
                <p class="text-sm font-medium text-charcoal/60">No topics registered yet.</p>
            </div>
        <?php
        else:
            foreach ($topicsByLetter as $letter => $letterTopics):
                if ($selectedLetter && $letter !== $selectedLetter) continue;
        ?>
            <div id="letter-<?= $letter ?>" class="bg-white p-6 rounded-2xl border border-taupe shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-taupe/40">
                    <h2 class="text-2xl font-serif font-bold text-charcoal"><?= $letter ?></h2>
                    <span class="text-xs text-charcoal/40 font-semibold"><?= count($letterTopics) ?> <?= count($letterTopics) === 1 ? 'topic' : 'topics' ?></span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($letterTopics as $topic): 
                        $isSelected = in_array($topic, $selectedTopics);
                        if ($isSelected) {
                            $newTopics = array_diff($selectedTopics, [$topic]);
                        } else {
                            $newTopics = array_merge($selectedTopics, [$topic]);
                        }
                        $url = '?' . ($newTopics ? 'topics=' . urlencode(implode(',', $newTopics)) : '') . ($selectedLetter ? '&letter=' . $selectedLetter : '');
                    ?>
                        <a href="<?= $url ?>" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition-colors <?= $isSelected ? 'bg-dark text-white border-dark shadow-sm' : 'bg-cream/60 border-taupe text-charcoal/70 hover:border-dark' ?>">
                            <i data-lucide="tag" class="w-3 h-3 text-charcoal/40"></i>
                            <span><?= htmlspecialchars($topic) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Selected Topics Filter Bar -->
    <?php if (!empty($selectedTopics)): ?>
        <div class="bg-white p-6 rounded-2xl border border-taupe shadow-sm mb-10 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-charcoal/60 mr-2 flex items-center gap-1">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filtering Stories By:</span>
                </span>
                <?php foreach ($selectedTopics as $topic): 
                    $newTopics = array_diff($selectedTopics, [$topic]);
                    $removeUrl = '?' . ($newTopics ? 'topics=' . urlencode(implode(',', $newTopics)) : '') . ($selectedLetter ? '&letter=' . $selectedLetter : '');
                ?>
                    <a href="<?= $removeUrl ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-dark text-white rounded-full text-xs font-semibold hover:bg-charcoal transition-colors shadow-sm">
                        <span><?= htmlspecialchars($topic) ?></span>
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <a href="?<?= $selectedLetter ? 'letter=' . $selectedLetter : '' ?>" 
               class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                Clear all filters
            </a>
        </div>

        <!-- Filtered Posts List -->
        <div>
            <h3 class="font-serif text-2xl font-bold text-charcoal mb-6">Matching Stories (<?= count($posts) ?>)</h3>
            <?php if (empty($posts)): ?>
                <div class="p-12 bg-white rounded-3xl border border-taupe text-center">
                    <div class="w-12 h-12 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-3">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-medium text-charcoal/60">No stories match all selected topics.</p>
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
                                            <i data-lucide="feather" class="w-10 h-10"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-6">
                                    <h4 class="font-serif text-lg font-bold text-charcoal hover:text-accent transition-colors line-clamp-2 mb-2">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </h4>
                                    <p class="text-xs sm:text-sm text-charcoal/70 line-clamp-2 leading-relaxed">
                                        <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 120)) ?>...
                                    </p>
                                </div>
                            </a>
                            <div class="px-6 pb-6 pt-0 border-t border-taupe/40 mt-auto flex items-center justify-between text-xs text-charcoal/60">
                                <span><?= htmlspecialchars($post['username']) ?></span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 text-accent"></i>
                                    <?= (int)$post['like_count'] ?>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

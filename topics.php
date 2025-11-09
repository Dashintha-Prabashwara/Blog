<?php 
require_once 'includes/header.php';

// Get all topics and sort them
$stmt = $pdo->query("SELECT DISTINCT topics FROM blogPost WHERE topics IS NOT NULL");
$allTopics = [];
while($row = $stmt->fetch()) {
    $topics = explode(',', $row['topics']);
    foreach($topics as $topic) {
        $topic = trim($topic);
        if($topic) $allTopics[$topic] = strtoupper($topic[0]); // Store first letter
    }
}
ksort($allTopics); // Sort topics alphabetically

// Get selected topics from URL
$selectedTopics = isset($_GET['topics']) ? explode(',', $_GET['topics']) : [];

// Get selected letter from URL
$selectedLetter = isset($_GET['letter']) ? strtoupper($_GET['letter']) : null;

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
        FROM blogPost p 
        JOIN user u ON p.user_id = u.id 
        WHERE topics LIKE {$placeholders}
        ORDER BY p.created_at DESC
    ");
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
}

// Group topics by first letter
$topicsByLetter = [];
foreach($allTopics as $topic => $letter) {
    $topicsByLetter[$letter][] = $topic;
}
ksort($topicsByLetter);
?>

<div class="max-w-7xl mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl mb-8">Topics</h1>

    <!-- A-Z Navigation -->
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="?" 
           class="px-3 py-1 rounded <?= !$selectedLetter ? 'bg-dark text-white' : 'bg-white text-charcoal hover:bg-charcoal/10' ?>">
            All
        </a>
        <?php foreach(range('A', 'Z') as $letter): ?>
            <a href="?<?= !empty($selectedTopics) ? 'topics=' . urlencode(implode(',', $selectedTopics)) . '&' : '' ?>letter=<?= $letter ?>" 
               class="px-3 py-1 rounded <?= $selectedLetter === $letter ? 'bg-dark text-white' : (isset($topicsByLetter[$letter]) ? 'bg-white text-charcoal hover:bg-charcoal/10' : 'bg-gray-100 text-gray-400') ?>">
                <?= $letter ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Topics List -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <?php 
        foreach($topicsByLetter as $letter => $topics):
            // Skip if letter is selected and doesn't match
            if ($selectedLetter && $letter !== $selectedLetter) continue;
        ?>
            <div id="letter-<?= $letter ?>" class="bg-white p-6 rounded-xl">
                <h2 class="text-2xl font-serif mb-4"><?= $letter ?></h2>
                <div class="flex flex-wrap gap-2">
                    <?php foreach($topics as $topic): ?>
                        <a href="?<?php
                            if(in_array($topic, $selectedTopics)) {
                                $newTopics = array_diff($selectedTopics, [$topic]);
                                echo $newTopics ? 'topics=' . urlencode(implode(',', $newTopics)) : '';
                            } else {
                                $newTopics = array_merge($selectedTopics, [$topic]);
                                echo 'topics=' . urlencode(implode(',', $newTopics));
                            }
                            echo $selectedLetter ? '&letter=' . $selectedLetter : '';
                        ?>" 
                           class="px-3 py-1 rounded-full text-sm <?= in_array($topic, $selectedTopics) 
                               ? 'bg-dark text-white' 
                               : 'bg-cream text-charcoal/70 hover:bg-charcoal/10' ?> transition-colors">
                            <?= htmlspecialchars($topic) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Selected Topics and Posts -->
    <?php if (!empty($selectedTopics)): ?>
        <div class="flex items-center gap-4 mb-8">
            <h2 class="font-medium">Filtered by:</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach($selectedTopics as $topic): ?>
                    <a href="?<?php
                        $newTopics = array_diff($selectedTopics, [$topic]);
                        echo $newTopics ? 'topics=' . urlencode(implode(',', $newTopics)) : '';
                        echo $selectedLetter ? '&letter=' . $selectedLetter : '';
                    ?>"
                       class="px-3 py-1 bg-dark text-white rounded-full text-sm hover:bg-charcoal">
                        <?= htmlspecialchars($topic) ?> ×
                    </a>
                <?php endforeach; ?>
                <a href="?<?= $selectedLetter ? 'letter=' . $selectedLetter : '' ?>" 
                   class="text-red-600 hover:underline text-sm">Clear all</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Posts List -->
    <?php if (!empty($selectedTopics)): ?>
        <?php if ($posts): ?>
            <div class="space-y-8">
                <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="flex items-start gap-6 p-6">
                            <!-- Post Image -->
                            <?php if ($post['image_url']): ?>
                                <div class="w-48 h-32 rounded-lg overflow-hidden shrink-0">
                                    <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="w-full h-full object-cover">
                                </div>
                            <?php endif; ?>

                            <!-- Post Content -->
                            <div class="flex-1">
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <?php 
                                    $postTopics = explode(',', $post['topics']);
                                    foreach($postTopics as $topic): 
                                        if(trim($topic)):
                                    ?>
                                        <a href="?topic=<?= urlencode(trim($topic)) ?>" 
                                           class="px-3 py-1 bg-cream rounded-full text-sm text-charcoal/70 hover:bg-charcoal/10">
                                            <?= htmlspecialchars(trim($topic)) ?>
                                        </a>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                                
                                <h2 class="font-serif text-xl mb-3">
                                    <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="hover:text-accent">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h2>
                                
                                <div class="flex items-center gap-4 text-sm text-charcoal/60">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                             alt="<?= htmlspecialchars($post['username']) ?>"
                                             class="w-6 h-6 rounded-full object-cover">
                                        <span><?= htmlspecialchars($post['username']) ?></span>
                                    </div>
                                    <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                                    <!-- Add like count -->
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <span><?= $post['like_count'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center py-12 text-charcoal/70">
                No posts found for selected topics.
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-center py-12 text-charcoal/70">
            Select topics above to filter posts
        </p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

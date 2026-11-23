<?php
require_once __DIR__ . '/includes/db.php';

// Get total users and posts count
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM blogpost")->fetchColumn();

// Get most popular/featured post
$featured = $pdo->query("
    SELECT p.*, u.username, u.profile_image,
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
    FROM blogpost p 
    JOIN user u ON p.user_id = u.id 
    ORDER BY (
        (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) * 2 + 
        (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id)
    ) DESC, p.created_at DESC
    LIMIT 1
")->fetch();

$pageTitle = 'Code & Canvas — Engineering Journal by Dashintha Jayawardana';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-cream via-white to-taupe/30 py-16 sm:py-24 rounded-3xl overflow-hidden border border-taupe/80 shadow-sm mb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-8 text-center relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 mb-6 text-xs font-semibold bg-accent/10 text-accent rounded-full">
                <i data-lucide="feather" class="w-3.5 h-3.5"></i>
                <span>Engineering Journal by Dashintha Jayawardana</span>
            </span>
            
            <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl leading-tight font-bold tracking-tight text-charcoal mb-6 text-balance">
                Code &amp; Canvas
            </h1>
            
            <p class="text-base sm:text-lg text-charcoal/70 mb-8 max-w-2xl mx-auto leading-relaxed">
                Documenting deep-dives into systems architecture, IoT systems, cloud engineering, and technical lessons learned building software.
            </p>
            
            <!-- Stats Row -->
            <div class="flex items-center justify-center gap-8 sm:gap-12 mb-8 py-4 border-y border-taupe/60 max-w-md mx-auto">
                <div class="text-center">
                    <span class="block font-serif text-3xl font-bold text-charcoal">
                        <?= number_format($totalPosts) ?>
                    </span>
                    <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium">Stories</span>
                </div>
                <div class="h-8 w-px bg-taupe"></div>
                <div class="text-center">
                    <a href="https://dashijay.dev" target="_blank" rel="noopener noreferrer" class="group flex flex-col items-center">
                        <span class="block font-serif text-3xl font-bold text-charcoal group-hover:text-accent transition-colors">
                            dashijay.dev
                        </span>
                        <span class="text-xs text-charcoal/60 uppercase tracking-wider font-medium flex items-center gap-1">
                            Portfolio <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                        </span>
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="/Blog/posts.php" 
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-dark text-white hover:bg-charcoal transition-all text-sm font-medium shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>Explore Stories</span>
                </a>
                <a href="https://dashijay.dev" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-charcoal/30 hover:border-charcoal bg-white/70 hover:bg-white text-charcoal transition-all text-sm font-medium">
                    <i data-lucide="external-link" class="w-4 h-4 text-accent"></i>
                    <span>Visit Portfolio</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/Blog/editor.php" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-accent text-white hover:bg-accent/90 transition-all text-sm font-medium shadow-sm">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        <span>Write Story</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Popular Topics -->
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </span>
                <h2 class="font-serif text-2xl font-bold text-charcoal">Popular Topics</h2>
            </div>
            <a href="/Blog/topics.php" class="text-sm font-medium text-accent hover:underline flex items-center gap-1">
                <span>Browse all</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        
        <?php
        // Fetch distinct topics with counts
        $topicCounts = [];
        $topicRows = $pdo->query("SELECT topics FROM blogpost WHERE topics IS NOT NULL AND topics != ''")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($topicRows as $row) {
            $parts = explode(',', $row);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p)) {
                    $topicCounts[$p] = ($topicCounts[$p] ?? 0) + 1;
                }
            }
        }
        arsort($topicCounts);
        $topTopics = array_slice($topicCounts, 0, 4, true);
        if (empty($topTopics)) {
            $topTopics = ['Design' => 0, 'Development' => 0, 'Tutorial' => 0, 'Technology' => 0];
        }
        ?>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach($topTopics as $topicName => $count): ?>
                <a href="/Blog/posts.php?topic=<?= urlencode($topicName) ?>" 
                   class="p-5 bg-white rounded-2xl border border-taupe hover:border-dark hover:shadow-md transition-all group flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-xl bg-cream flex items-center justify-center text-charcoal/70 group-hover:bg-dark group-hover:text-white transition-colors mb-4">
                        <i data-lucide="hash" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-serif font-bold text-lg text-charcoal group-hover:text-accent transition-colors"><?= htmlspecialchars($topicName) ?></h3>
                        <p class="text-xs text-charcoal/60 mt-1"><?= $count ?> <?= $count === 1 ? 'story' : 'stories' ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Post Section -->
    <?php if ($featured): ?>
    <section class="mb-16">
        <div class="flex items-center gap-3 mb-8">
            <span class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
            </span>
            <h2 class="font-serif text-2xl font-bold text-charcoal">Featured Story</h2>
        </div>
        
        <a href="/Blog/post.php?id=<?= $featured['id'] ?>"
           class="group block bg-white rounded-3xl overflow-hidden border border-taupe shadow-sm hover:shadow-xl transition-all duration-300">
            <div class="grid md:grid-cols-2 gap-8 p-6 sm:p-10 items-center">
                <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-taupe relative">
                    <?php if (!empty($featured['image_url'])): ?>
                        <img src="<?= htmlspecialchars($featured['image_url']) ?>" 
                             alt="<?= htmlspecialchars($featured['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-cream flex items-center justify-center text-charcoal/30">
                            <i data-lucide="image" class="w-16 h-16"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col justify-center space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-accent/10 text-accent rounded-full text-xs font-semibold">
                            <i data-lucide="star" class="w-3 h-3"></i>
                            Featured
                        </span>
                        <?php if (!empty($featured['topics'])): ?>
                            <?php foreach(explode(',', $featured['topics']) as $t): if(trim($t)): ?>
                                <span class="px-2.5 py-0.5 bg-cream rounded-full text-xs font-medium text-charcoal/70 border border-taupe">
                                    <?= htmlspecialchars(trim($t)) ?>
                                </span>
                            <?php endif; endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-charcoal group-hover:text-accent transition-colors leading-tight">
                        <?= htmlspecialchars($featured['title']) ?>
                    </h2>

                    <p class="text-charcoal/70 text-sm sm:text-base line-clamp-3 leading-relaxed">
                        <?= htmlspecialchars(substr(strip_tags($featured['content']), 0, 220)) ?>...
                    </p>

                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-taupe text-xs text-charcoal/60">
                        <div class="flex items-center gap-2.5">
                            <img src="<?= $featured['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                 alt="<?= htmlspecialchars($featured['username']) ?>"
                                 class="w-7 h-7 rounded-full object-cover border border-taupe">
                            <span class="font-medium text-charcoal"><?= htmlspecialchars($featured['username']) ?></span>
                            <span>•</span>
                            <time><?= date('M j, Y', strtotime($featured['created_at'])) ?></time>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1">
                                <i data-lucide="heart" class="w-3.5 h-3.5 text-accent"></i>
                                <?= (int)$featured['like_count'] ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 text-charcoal/50"></i>
                                <?= (int)$featured['comment_count'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </section>
    <?php endif; ?>

    <!-- Recent Stories Grid -->
    <section class="pb-16">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </span>
                <h2 class="font-serif text-2xl font-bold text-charcoal">Recent Stories</h2>
            </div>
            <a href="/Blog/posts.php" class="text-sm font-medium text-accent hover:underline flex items-center gap-1">
                <span>View all stories</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $stmt = $pdo->prepare("
                SELECT p.*, u.username, u.profile_image,
                       (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
                       (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
                FROM blogpost p 
                JOIN user u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT 9
            ");
            $stmt->execute();
            $posts = $stmt->fetchAll();
            ?>

            <?php if (empty($posts)): ?>
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-taupe">
                    <div class="w-14 h-14 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/40 mb-4">
                        <i data-lucide="inbox" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-charcoal mb-2">No stories published yet</h3>
                    <p class="text-sm text-charcoal/60 max-w-sm mx-auto mb-6">Be the first to share your insights, tutorials, or perspectives with the community.</p>
                    <a href="/Blog/editor.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark text-white rounded-xl text-sm font-medium hover:bg-charcoal transition-all shadow-sm">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        <span>Write First Story</span>
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $p): ?>
                    <article class="group bg-white rounded-2xl overflow-hidden border border-taupe shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                        <a href="/Blog/post.php?id=<?= $p['id'] ?>" class="block">
                            <div class="aspect-[16/10] bg-taupe relative overflow-hidden">
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($p['title']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full bg-cream flex items-center justify-center text-charcoal/30">
                                        <i data-lucide="feather" class="w-10 h-10"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-6">
                                <?php if (!empty($p['topics'])): ?>
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        <?php 
                                        $topicsList = array_slice(explode(',', $p['topics']), 0, 2);
                                        foreach($topicsList as $t): if(trim($t)): ?>
                                            <span class="px-2 py-0.5 bg-cream rounded-md text-[11px] font-medium text-charcoal/70 border border-taupe">
                                                <?= htmlspecialchars(trim($t)) ?>
                                            </span>
                                        <?php endif; endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="font-serif text-xl font-bold text-charcoal group-hover:text-accent transition-colors line-clamp-2 mb-2 leading-snug">
                                    <?= htmlspecialchars($p['title']) ?>
                                </h3>
                                
                                <p class="text-charcoal/70 text-xs sm:text-sm line-clamp-3 leading-relaxed mb-4">
                                    <?= htmlspecialchars(substr(strip_tags($p['content']), 0, 140)) ?>...
                                </p>
                            </div>
                        </a>

                        <div class="px-6 pb-6 pt-0 border-t border-taupe/40 mt-auto flex items-center justify-between text-xs text-charcoal/60">
                            <div class="flex items-center gap-2">
                                <img src="<?= $p['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                     alt="<?= htmlspecialchars($p['username']) ?>"
                                     class="w-6 h-6 rounded-full object-cover border border-taupe">
                                <span class="font-medium text-charcoal"><?= htmlspecialchars($p['username']) ?></span>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 text-accent"></i>
                                    <?= (int)$p['like_count'] ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-charcoal/50"></i>
                                    <?= (int)$p['comment_count'] ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

// Get total users and posts count
$totalUsers = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$totalPosts = $pdo->query("SELECT COUNT(*) FROM blogPost")->fetchColumn();

// Update the query to include topics
// Update the featured post query to get most popular post
$featured = $pdo->query("
    SELECT p.*, u.username, u.profile_image,
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count,
           (
               SELECT COUNT(*) 
               FROM comment c2 
               JOIN comment_dislike cd ON c2.id = cd.comment_id 
               WHERE c2.post_id = p.id
           ) as dislike_count
    FROM blogPost p 
    JOIN user u ON p.user_id = u.id 
    ORDER BY (
        (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) + 
        (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) - 
        (
            SELECT COALESCE(COUNT(*), 0)
            FROM comment c2 
            JOIN comment_dislike cd ON c2.id = cd.comment_id 
            WHERE c2.post_id = p.id
        )
    ) DESC, p.created_at DESC
    LIMIT 1
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Code & Canvas — Minimalist Blog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">
    <main class="max-w-7xl mx-auto py-8">
        <!-- Enhanced Hero Section -->
        <section class="relative bg-cream py-20 sm:py-32 rounded-3xl overflow-hidden mb-16">
            <!-- Decorative elements -->
            <div class="absolute inset-0 pointer-events-none">
                <svg class="absolute right-0 top-0 h-full w-1/2 text-charcoal/5" viewBox="0 0 150 350">
                    <pattern id="dots" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="2" fill="currentColor"/>
                    </pattern>
                    <rect width="150" height="350" fill="url(#dots)"/>
                </svg>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-block px-4 py-1 mb-6 text-sm font-medium bg-accent/10 text-accent rounded-full scroll-fade-up">
                        Featured Platform for Developers & Designers
                    </span>
                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl leading-tight tracking-tight text-balance mb-6 scroll-fade-up scroll-delay-1">
                        Where Design Meets Digital Craft
                    </h1>
                    <p class="text-lg sm:text-xl text-charcoal/70 mb-8 text-balance max-w-xl scroll-fade-up scroll-delay-2">
                        Join our community of creative developers and designers. Share your stories, 
                        learn from others, and grow together.
                    </p>
                    
                    <!-- Stats Row -->
                    <div class="flex gap-8 mb-8 text-charcoal/80 scroll-fade-up scroll-delay-3">
                        <div>
                            <span class="block font-serif text-3xl">
                                <?= number_format($totalUsers) ?>
                            </span>
                            <span class="text-sm">Community Members</span>
                        </div>
                        <div>
                            <span class="block font-serif text-3xl">
                                <?= number_format($totalPosts) ?>
                            </span>
                            <span class="text-sm">Published Stories</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 scroll-fade-up scroll-delay-3">
                        <a href="/Blog/posts.php" 
                           class="inline-flex items-center px-6 py-3 rounded-lg bg-dark text-white hover:bg-charcoal transition-all">
                            Explore Stories
                        </a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="/Blog/register.php" 
                               class="inline-flex items-center px-6 py-3 rounded-lg border border-charcoal/20 hover:border-charcoal/40 transition-all">
                                Join Community
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Categories -->
        <section class="mb-16 px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-2xl mb-8 scroll-fade-up">Popular Topics</h2>
            <?php
            // Get popular topics based on post count
            $topicsStmt = $pdo->query("
                SELECT topic, COUNT(*) as post_count
                FROM (
                    SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(topics, ',', n.n), ',', -1)) as topic
                    FROM blogPost t CROSS JOIN (
                        SELECT a.N + b.N * 10 + 1 n
                        FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) a,
                             (SELECT 0 AS N UNION ALL SELECT 1) b
                        ORDER BY n
                    ) n
                    WHERE n.n <= 1 + (LENGTH(topics) - LENGTH(REPLACE(topics, ',', '')))
                ) temp
                WHERE topic != ''
                GROUP BY topic
                ORDER BY post_count DESC
                LIMIT 4
            ");
            $popularTopics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div id="popularTopics" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach($popularTopics as $index => $topic): ?>
                    <a href="/Blog/posts.php?topic=<?= urlencode($topic['topic']) ?>" 
                       class="p-6 bg-white rounded-xl hover:shadow-lg transition-all group scroll-fade-up scroll-delay-<?= $index + 1 ?>">
                        <svg class="w-8 h-8 text-accent mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $iconPath ?>"/>
                        </svg>
                        <div>
                            <h3 class="font-medium group-hover:text-accent transition-colors"><?= htmlspecialchars($topic['topic']) ?></h3>
                            <p class="text-sm text-charcoal/60"><?= $topic['post_count'] ?> posts</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Featured Post Section -->
        <section class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <?php if ($featured): ?>
                    <!-- Add Featured Story label -->
                    <div class="flex items-center gap-4 mb-8">
                        <h2 class="font-serif text-2xl">Featured Story</h2>
                        <span class="px-3 py-1 bg-accent/10 text-accent rounded-full text-sm">Editor's Pick</span>
                    </div>
                    
                    <a href="/Blog/post.php?id=<?= $featured['id'] ?>"
                       class="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="grid md:grid-cols-2 gap-8 p-8">
                            <div class="aspect-[4/3] rounded-xl overflow-hidden">
                            <?php if ($featured['image_url']): ?>
                            <img src="<?= htmlspecialchars($featured['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($featured['title']) ?>"
                                 class="w-full h-full object-cover image-fade-in"
                                 loading="lazy"
                                 onload="this.classList.add('loaded')">
                            <?php else: ?>
                            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14">
                                </svg>
                            </div>
                            <?php endif; ?>
                            </div>
                            <div class="flex flex-col justify-center">
                                <!-- Add Featured tag at the top -->
                                <div class="flex gap-3 mb-6">
                                    <span class="px-3 py-1 bg-accent/10 text-accent rounded-full text-sm">Featured Story</span>
                                    <?php if ($featured['topics']): ?>
                                        <?php 
                                        $topicsArray = explode(',', $featured['topics']);
                                        foreach($topicsArray as $topic): 
                                            if(trim($topic)):
                                        ?>
                                            <span class="px-3 py-1 bg-cream rounded-full text-sm text-charcoal/70">
                                                <?= htmlspecialchars(trim($topic)) ?>
                                            </span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    <?php endif; ?>
                                </div>
                                <h2 class="font-serif text-2xl sm:text-3xl mb-4">
                                    <?= htmlspecialchars($featured['title']) ?>
                                </h2>
                                <p class="text-charcoal/70 mb-6 line-clamp-3">
                                    <?= htmlspecialchars(substr($featured['content'], 0, 200)) ?>...
                                </p>
                                <div class="flex items-center gap-4 text-sm text-charcoal/60">
                                    <div class="flex items-center gap-2">
                                        <?php
                                        $authorStmt = $pdo->prepare("SELECT profile_image FROM user WHERE id = ?");
                                        $authorStmt->execute([$featured['user_id']]);
                                        $author = $authorStmt->fetch();
                                        ?>
                                        <img src="<?= $author['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                             alt="<?= htmlspecialchars($featured['username']) ?>"
                                             class="w-8 h-8 rounded-full object-cover">
                                        <span><?= htmlspecialchars($featured['username']) ?></span>
                                    </div>
                                    <time><?= date('M j, Y', strtotime($featured['created_at'])) ?></time>
                                    <span>5 min read</span>
                                    <!-- Add like count -->
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Recent Posts Grid -->
        <section class="pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="font-serif text-2xl mb-12 scroll-fade-up">Recent Stories</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    $stmt = $pdo->prepare("SELECT p.*, u.username, u.profile_image FROM blogPost p JOIN user u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 9");
                    $stmt->execute();
                    while ($post = $stmt->fetch()): ?>
                        <article class="group scroll-fade-up">
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
                                    <?php if ($post['id'] === $featured['id']): ?>
                                        <span class="inline-block px-3 py-1 bg-accent/10 text-accent rounded-full text-sm mb-3">Featured Story</span>
                                    <?php endif; ?>
                                    <h3 class="font-serif text-xl mb-3"><?= htmlspecialchars($post['title']) ?></h3>
                                    <p class="text-charcoal/70 mb-4 line-clamp-2">
                                        <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                                    </p>
                                    <div class="flex items-center gap-3 text-sm text-charcoal/60">
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
                            </a>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

<!-- Add scroll animation script before closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Stop observing once visible
            }
        });
    }, observerOptions);

    // Observe all elements with scroll animation classes
    document.querySelectorAll('.scroll-fade-up, .scroll-fade-in').forEach(el => {
        observer.observe(el);
    });
});
</script>

<!-- Add counter animation script before closing body tag -->
<script>
function animateCounters() {
    const counters = document.querySelectorAll('.animate-counter');
    const speed = 1000; // Increased duration for smoother animation
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        let current = parseInt(counter.getAttribute('data-current'));
        
        const increment = target / speed;
        
        if (current < target) {
            current += increment;
            if (current > target) current = target;
            counter.setAttribute('data-current', current);
            counter.textContent = Math.floor(current).toLocaleString();
            requestAnimationFrame(() => animateCounters());
        }
    });
}

// Start animation when elements are in view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.animate-counter').forEach(counter => {
    observer.observe(counter);
});
</script>

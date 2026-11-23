<?php 
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

$pageTitle = 'Dashboard — Code & Canvas';
require_once __DIR__ . '/includes/header.php';

// Fetch published posts
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
    FROM blogpost p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Fetch drafts
$draftStmt = $pdo->prepare("
    SELECT * FROM draft_post 
    WHERE user_id = ? 
    ORDER BY last_saved DESC
");
$draftStmt->execute([$_SESSION['user_id']]);
$drafts = $draftStmt->fetchAll();

// Get user stats
$userStats = getUserStats($_SESSION['user_id']);

// Top performing post
$topPostStmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count
    FROM blogpost p 
    WHERE p.user_id = ? 
    ORDER BY (
        (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) * 2 + 
        (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id)
    ) DESC 
    LIMIT 1
");
$topPostStmt->execute([$_SESSION['user_id']]);
$topPost = $topPostStmt->fetch();

$unreadNotifications = getUnreadNotificationCount($_SESSION['user_id']);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Header banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-8 border-b border-taupe mb-10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent/10 text-accent">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                    Author Workspace
                </span>
            </div>
            <h1 class="font-serif text-3xl font-bold text-charcoal">Dashboard</h1>
            <p class="text-sm text-charcoal/60 mt-1">Review your content metrics, edit published stories, and continue drafts.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Notifications Popover -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open; if(open) loadNotifications();" 
                        class="relative p-2.5 bg-white border border-taupe text-charcoal/70 hover:text-charcoal rounded-xl shadow-sm hover:bg-cream transition-all">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="notification-count absolute -top-1.5 -right-1.5 px-2 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">
                            <?= $unreadNotifications ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Dropdown -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-taupe py-2 z-50 divide-y divide-gray-100"
                     style="display: none;">
                    <div class="flex items-center justify-between px-4 py-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="bell" class="w-4 h-4 text-accent"></i>
                            <h3 class="font-semibold text-sm text-charcoal">Notifications</h3>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <button onclick="handleNotifications('mark_read', 'all')" class="text-accent hover:underline">Mark all read</button>
                            <button onclick="handleNotifications('delete', 'all')" class="text-red-500 hover:underline">Clear</button>
                        </div>
                    </div>
                    <div id="notifications-list" class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100 text-xs text-charcoal/70">
                        <div class="px-4 py-4 text-center text-charcoal/40">Loading notifications...</div>
                    </div>
                </div>
            </div>

            <!-- Profile View & Copy Link -->
            <a href="/Blog/public-profile.php?id=<?= $_SESSION['user_id'] ?>" 
               class="px-4 py-2.5 bg-white border border-taupe text-charcoal rounded-xl text-xs font-semibold hover:bg-cream transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-4 h-4 text-charcoal/60"></i>
                <span>Public Profile</span>
            </a>

            <!-- Write New Story CTA -->
            <a href="/Blog/editor.php" 
               class="px-5 py-2.5 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="pen-tool" class="w-4 h-4"></i>
                <span>Write New Story</span>
            </a>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl border border-taupe shadow-sm flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-charcoal/50 mb-1">Published Stories</p>
                <p class="text-3xl font-serif font-bold text-charcoal"><?= count($posts) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-taupe shadow-sm flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4">
                <i data-lucide="heart" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-charcoal/50 mb-1">Total Likes</p>
                <p class="text-3xl font-serif font-bold text-charcoal"><?= (int)($userStats['total_likes'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-taupe shadow-sm flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4">
                <i data-lucide="message-square" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-charcoal/50 mb-1">Comments Received</p>
                <p class="text-3xl font-serif font-bold text-charcoal"><?= (int)($userStats['total_comments'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-taupe shadow-sm flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-charcoal/50 mb-1">Followers</p>
                <p class="text-3xl font-serif font-bold text-charcoal"><?= (int)($userStats['followers'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Top Performing Story Spotlight -->
    <?php if ($topPost): ?>
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-taupe shadow-sm mb-12">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-7 h-7 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
                <h2 class="font-serif text-lg font-bold text-charcoal">Top Performing Story</h2>
            </div>

            <div class="flex flex-col sm:flex-row items-start gap-6">
                <?php if (!empty($topPost['image_url'])): ?>
                    <div class="w-full sm:w-48 h-32 rounded-2xl overflow-hidden bg-taupe shrink-0">
                        <img src="<?= htmlspecialchars($topPost['image_url']) ?>" 
                             alt="<?= htmlspecialchars($topPost['title']) ?>" 
                             class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                
                <div class="flex-1 space-y-2">
                    <h3 class="font-serif text-xl font-bold">
                        <a href="/Blog/post.php?id=<?= $topPost['id'] ?>" class="text-charcoal hover:text-accent transition-colors">
                            <?= htmlspecialchars($topPost['title']) ?>
                        </a>
                    </h3>
                    <p class="text-xs sm:text-sm text-charcoal/70 line-clamp-2">
                        <?= htmlspecialchars(substr(strip_tags($topPost['content']), 0, 160)) ?>...
                    </p>
                    <div class="flex items-center gap-6 pt-2 text-xs text-charcoal/60">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="heart" class="w-3.5 h-3.5 text-accent"></i>
                            <?= (int)$topPost['like_count'] ?> likes
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 text-charcoal/50"></i>
                            <?= (int)$topPost['comment_count'] ?> comments
                        </span>
                        <time><?= date('M j, Y', strtotime($topPost['created_at'])) ?></time>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stories & Drafts Management Tabbed Panel -->
    <div class="bg-white rounded-3xl border border-taupe shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 sm:px-8 py-4 border-b border-taupe bg-cream/30">
            <div class="flex items-center gap-6">
                <button onclick="switchTab('posts')" 
                        id="postsTab"
                        class="pb-2 font-serif text-lg font-bold border-b-2 border-dark text-dark transition-all flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Published (<?= count($posts) ?>)</span>
                </button>
                <button onclick="switchTab('drafts')" 
                        id="draftsTab"
                        class="pb-2 font-serif text-lg font-bold text-charcoal/40 hover:text-charcoal border-b-2 border-transparent transition-all flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Drafts (<?= count($drafts) ?>)</span>
                </button>
            </div>
            
            <a href="/Blog/editor.php" class="px-4 py-2 bg-dark text-white rounded-xl text-xs font-semibold hover:bg-charcoal transition-all shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">New Story</span>
            </a>
        </div>

        <!-- Published Posts List -->
        <div id="postsContent" class="divide-y divide-gray-100 p-6 sm:p-8">
            <?php if (empty($posts)): ?>
                <div class="text-center py-12">
                    <div class="w-12 h-12 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-3">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-medium text-charcoal/70 mb-1">No published stories yet</p>
                    <p class="text-xs text-charcoal/50 mb-4">Click below to start writing your first story.</p>
                    <a href="/Blog/editor.php" class="px-4 py-2 bg-dark text-white text-xs font-medium rounded-xl hover:bg-charcoal">Write Now</a>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                        <div class="flex items-center gap-4 min-w-0">
                            <?php if (!empty($post['image_url'])): ?>
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-taupe shrink-0">
                                    <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                         alt=""
                                         class="w-full h-full object-cover">
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-xl bg-cream border border-taupe flex items-center justify-center shrink-0 text-charcoal/30">
                                    <i data-lucide="feather" class="w-6 h-6"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="min-w-0">
                                <h3 class="font-serif font-bold text-base text-charcoal truncate">
                                    <a href="/Blog/post.php?id=<?= $post['id'] ?>" class="hover:text-accent transition-colors">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <div class="flex items-center gap-4 text-xs text-charcoal/60 mt-1">
                                    <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
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
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <a href="/Blog/post.php?id=<?= $post['id'] ?>" 
                               class="p-2 border border-taupe rounded-lg text-charcoal/70 hover:text-charcoal hover:bg-cream text-xs" title="View Story">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                            <a href="/Blog/editor.php?id=<?= $post['id'] ?>" 
                               class="p-2 border border-taupe rounded-lg text-charcoal/70 hover:text-accent hover:bg-cream text-xs" title="Edit Story">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <button onclick="deletePost(<?= $post['id'] ?>)" 
                                    class="p-2 border border-red-200 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 text-xs" title="Delete Story">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Drafts List -->
        <div id="draftsContent" class="divide-y divide-gray-100 p-6 sm:p-8" style="display: none;">
            <?php if (empty($drafts)): ?>
                <div class="text-center py-12">
                    <div class="w-12 h-12 rounded-2xl bg-cream flex items-center justify-center mx-auto text-charcoal/30 mb-3">
                        <i data-lucide="edit-3" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-medium text-charcoal/70 mb-1">No drafts in progress</p>
                    <p class="text-xs text-charcoal/50">Any stories you start writing will automatically save here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($drafts as $draft): ?>
                    <div class="py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="font-serif font-bold text-base text-charcoal truncate">
                                <?= htmlspecialchars($draft['title'] ?: 'Untitled Draft') ?>
                            </h3>
                            <p class="text-xs text-charcoal/50 mt-1">
                                Last saved: <?= date('M j, Y • g:i a', strtotime($draft['last_saved'])) ?>
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="/Blog/editor.php?draft_id=<?= $draft['id'] ?>" 
                               class="px-3.5 py-1.5 bg-dark text-white rounded-lg text-xs font-semibold hover:bg-charcoal flex items-center gap-1.5">
                                <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                                <span>Continue Writing</span>
                            </a>
                            <button onclick="deleteDraft(<?= $draft['id'] ?>)" 
                                    class="p-2 border border-red-200 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg text-xs" title="Delete Draft">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    const postsTab = document.getElementById('postsTab');
    const draftsTab = document.getElementById('draftsTab');
    const postsContent = document.getElementById('postsContent');
    const draftsContent = document.getElementById('draftsContent');
    
    if (tab === 'posts') {
        postsTab.className = 'pb-2 font-serif text-lg font-bold border-b-2 border-dark text-dark transition-all flex items-center gap-2';
        draftsTab.className = 'pb-2 font-serif text-lg font-bold text-charcoal/40 hover:text-charcoal border-b-2 border-transparent transition-all flex items-center gap-2';
        postsContent.style.display = 'block';
        draftsContent.style.display = 'none';
    } else {
        draftsTab.className = 'pb-2 font-serif text-lg font-bold border-b-2 border-dark text-dark transition-all flex items-center gap-2';
        postsTab.className = 'pb-2 font-serif text-lg font-bold text-charcoal/40 hover:text-charcoal border-b-2 border-transparent transition-all flex items-center gap-2';
        draftsContent.style.display = 'block';
        postsContent.style.display = 'none';
    }
}

function deleteDraft(id) {
    if (!confirm('Are you sure you want to delete this draft?')) return;
    
    fetch('/Blog/api/delete_draft.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ draft_id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to delete draft');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to delete draft');
    });
}

function deletePost(id) {
    if (!confirm('Are you sure you want to delete this published story?')) return;
    
    fetch(`/Blog/api/delete_post.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Failed to delete post');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to delete post');
        });
}

function loadNotifications() {
    fetch('/Blog/api/get_notifications.php')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('notifications-list');
            if (!data.success || !data.notifications || data.notifications.length === 0) {
                list.innerHTML = '<div class="px-4 py-6 text-center text-charcoal/40">No notifications yet</div>';
                return;
            }

            let html = '';
            data.notifications.forEach(notif => {
                html += `
                    <div id="notification-${notif.id}" 
                         data-notification="${notif.id}"
                         class="px-4 py-3 flex items-start gap-3 hover:bg-cream/50 transition-colors ${!notif.read ? 'bg-accent/5' : ''}">
                        <img src="${notif.profile_image || '/Blog/assets/images/default-avatar.png'}" 
                             class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5 border border-taupe">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-charcoal leading-snug">
                                <span class="font-bold">${escapeHtml(notif.username)}</span>
                                ${notif.action_text}
                            </p>
                            ${notif.post_title ? `
                                <a href="/Blog/post.php?id=${notif.post_id}" 
                                   onclick="handleNotifications('mark_read', ${notif.id})"
                                   class="block text-[11px] text-accent truncate mt-0.5 hover:underline">
                                    "${escapeHtml(notif.post_title)}"
                                </a>
                            ` : ''}
                            <span class="text-[10px] text-charcoal/40 mt-1 block">${notif.seconds_ago}</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            ${!notif.read ? `
                                <button onclick="handleNotifications('mark_read', ${notif.id})" class="p-1 text-accent hover:text-accent/80" title="Mark read">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </button>
                            ` : ''}
                            <button onclick="handleNotifications('delete', ${notif.id})" class="p-1 text-charcoal/30 hover:text-red-500" title="Delete">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            list.innerHTML = html;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(err => {
            console.error(err);
        });
}

function handleNotifications(action, id) {
    fetch('/Blog/api/notification_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=${action}&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            // Update badge
            const badge = document.querySelector('.notification-count');
            if (badge && (action === 'delete' || id === 'all')) {
                badge.remove();
            }
        }
    });
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

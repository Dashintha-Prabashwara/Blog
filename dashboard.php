<?php 
require_once 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /Blog/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count
    FROM blogpost p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Add drafts query
$draftStmt = $pdo->prepare("
    SELECT * FROM draft_post 
    WHERE user_id = ? 
    ORDER BY last_saved DESC
");
$draftStmt->execute([$_SESSION['user_id']]);
$drafts = $draftStmt->fetchAll();

// Get user data
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user stats
$userStats = getUserStats($_SESSION['user_id']);

// Get top performing post
$topPostStmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) as like_count,
           (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id) as comment_count,
           (SELECT COUNT(*) FROM comment c2 
            JOIN comment_dislike cd ON c2.id = cd.comment_id 
            WHERE c2.post_id = p.id) as dislike_count
    FROM blogpost p 
    WHERE p.user_id = ? 
    ORDER BY (
        (SELECT COUNT(*) FROM post_like pl WHERE pl.post_id = p.id) + 
        (SELECT COUNT(*) FROM comment c WHERE c.post_id = p.id)
    ) DESC 
    LIMIT 1");
$topPostStmt->execute([$_SESSION['user_id']]);
$topPost = $topPostStmt->fetch();
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:py-12">
    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0 mb-8 sm:mb-12">
        <div>
            <h1 class="font-serif text-2xl sm:text-3xl mb-2">Dashboard</h1>
            <p class="text-charcoal/70">Manage your posts and account</p>
            <div class="flex flex-col sm:flex-row gap-2 mt-2">
                <a href="/Blog/public-profile.php?id=<?= $_SESSION['user_id'] ?>" 
                   class="inline-block text-sm text-accent hover:underline">
                    View Public Profile
                </a>
                <div class="flex items-center gap-2 text-sm text-charcoal/70">
                    <span>Share:</span>
                    <div class="relative flex items-center">
                        <input type="text" 
                               value="<?= 'http://' . $_SERVER['HTTP_HOST'] . '/Blog/public-profile.php?id=' . $_SESSION['user_id'] ?>" 
                               class="w-64 pr-10 py-1 px-2 text-xs bg-white border border-gray-200 rounded" 
                               readonly>
                        <button onclick="copyProfileLink(this)" 
                                class="absolute right-2 p-1 text-charcoal/50 hover:text-accent"
                                title="Copy link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2M8 12h12a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                            </svg>
                            <span class="copied-feedback hidden absolute -right-16 -top-8 px-2 py-1 text-xs bg-dark text-white rounded">
                                Copied!
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="relative p-2 text-charcoal/70 hover:text-charcoal rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <?php if ($unreadNotifications = getUnreadNotificationCount($_SESSION['user_id'])): ?>
                        <span class="absolute -top-1 -right-1 px-2 py-1 text-xs bg-red-500 text-white rounded-full">
                            <?= $unreadNotifications ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Notifications Panel -->
                <div x-show="open"
                     @click.away="open = false"
                     x-init="$watch('open', value => value && loadNotifications())"
                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg py-2 z-50">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                        <h3 class="font-medium">Notifications</h3>
                        <div class="flex items-center gap-2 text-sm">
                            <button onclick="handleNotifications('mark_read', 'all')" 
                                    class="text-accent hover:underline">Mark all read</button>
                            <button onclick="handleNotifications('delete', 'all')" 
                                    class="text-red-600 hover:underline">Clear all</button>
                        </div>
                    </div>
                    <div id="notifications-list" class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100">
                        <!-- Notifications will be loaded here -->
                        <div class="px-4 py-3 text-sm text-charcoal/70">Loading notifications...</div>
                    </div>
                </div>
            </div>
            
            <button onclick="showDeleteAccountConfirm()" 
                    class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                Delete Account
            </button>
            <a href="/Blog/editor.php" 
               class="px-4 sm:px-6 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">
                Write New Post
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-12">
        <div class="bg-white p-6 rounded-xl">
            <h3 class="text-sm text-charcoal/70 mb-1">Total Posts</h3>
            <p class="text-3xl font-serif"><?= count($posts) ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl">
            <h3 class="text-sm text-charcoal/70 mb-1">Total Likes</h3>
            <p class="text-3xl font-serif"><?= $userStats['total_likes'] ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl">
            <h3 class="text-sm text-charcoal/70 mb-1">Total Comments</h3>
            <p class="text-3xl font-serif"><?= $userStats['total_comments'] ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl">
            <h3 class="text-sm text-charcoal/70 mb-1">Followers</h3>
            <p class="text-3xl font-serif"><?= $userStats['followers'] ?></p>
        </div>
    </div>

    <!-- Top Performing Post -->
    <?php if ($topPost): ?>
    <div class="bg-white p-6 rounded-xl mb-12">
        <h2 class="font-serif text-xl mb-6">Top Performing Post</h2>
        <div class="flex items-start gap-6">
            <?php if ($topPost['image_url']): ?>
            <div class="w-48 h-32 rounded-lg overflow-hidden shrink-0">
                <img src="<?= htmlspecialchars($topPost['image_url']) ?>" 
                     alt="" 
                     class="w-full h-full object-cover">
            </div>
            <?php endif; ?>
            <div class="flex-1">
                <h3 class="font-medium text-lg mb-2">
                    <a href="/Blog/post.php?id=<?= $topPost['id'] ?>" 
                       class="hover:text-accent">
                        <?= htmlspecialchars($topPost['title']) ?>
                    </a>
                </h3>
                <p class="text-charcoal/70 line-clamp-2 mb-4">
                    <?= htmlspecialchars(substr($topPost['content'], 0, 150)) ?>...
                </p>
                <div class="flex gap-6 text-sm text-charcoal/70">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span><?= $topPost['like_count'] ?> likes</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span><?= $topPost['comment_count'] ?> comments</span>
                    </div>
                    <time><?= date('M j, Y', strtotime($topPost['created_at'])) ?></time>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Existing Tabs Section -->
    <div class="space-y-6">
        <!-- Posts/Drafts Tabs -->
        <div class="bg-white p-6 rounded-xl">
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-4">
                    <button onclick="switchTab('posts')" 
                            id="postsTab"
                            class="font-serif text-xl relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-accent">
                        Published Posts
                    </button>
                    <button onclick="switchTab('drafts')" 
                            id="draftsTab"
                            class="font-serif text-xl text-charcoal/50 hover:text-charcoal/70">
                        Drafts
                    </button>
                </div>
                <a href="/Blog/editor.php" class="px-6 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">
                    Write New Post
                </a>
            </div>

            <!-- Published Posts Section -->
            <div id="postsContent" class="divide-y divide-gray-100">
                <?php if ($posts): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4 w-full sm:w-auto">
                                <?php if ($post['image_url']): ?>
                                <div class="w-20 h-20 sm:w-16 sm:h-16 rounded-lg overflow-hidden shrink-0">
                                    <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                         alt=""
                                         class="w-full h-full object-cover">
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium truncate">
                                        <a href="/Blog/post.php?id=<?= $post['id'] ?>" 
                                           class="hover:text-accent">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h3>
                                    <div class="flex flex-wrap gap-2 sm:gap-4 text-sm text-charcoal/70 mt-1">
                                        <time><?= date('M j, Y', strtotime($post['created_at'])) ?></time>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                            <span><?= $post['like_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto">
                                <a href="/Blog/editor.php?id=<?= $post['id'] ?>" 
                                   class="text-accent hover:underline">Edit</a>
                                <button onclick="deletePost(<?= $post['id'] ?>)" 
                                        class="text-red-600 hover:underline">Delete</button>
                                <button onclick="viewComments(<?= $post['id'] ?>)"
                                        class="text-charcoal/70 hover:text-charcoal">Manage Comments</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center py-8 text-charcoal/70">
                        No published posts yet. Start writing!
                    </p>
                <?php endif; ?>
            </div>

            <!-- Drafts Section -->
            <div id="draftsContent" class="divide-y divide-gray-100" style="display: none;">
                <?php if ($drafts): ?>
                    <?php foreach ($drafts as $draft): ?>
                        <div class="py-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">
                                    <?= htmlspecialchars($draft['title'] ?: 'Untitled Draft') ?>
                                </h3>
                                <div class="flex gap-4 text-sm text-charcoal/70 mt-1">
                                    <time>Last saved: <?= date('M j, Y g:i A', strtotime($draft['last_saved'])) ?></time>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="/Blog/editor.php?draft_id=<?= $draft['id'] ?>" 
                                   class="text-accent hover:underline">Continue Writing</a>
                                <button onclick="deleteDraft(<?= $draft['id'] ?>)" 
                                        class="text-red-600 hover:underline">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center py-8 text-charcoal/70">
                        No drafts yet.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Add this function at the beginning of your script section
function copyProfileLink(button) {
    const input = button.parentElement.querySelector('input');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const feedback = button.querySelector('.copied-feedback');
    feedback.classList.remove('hidden');
    
    // Hide feedback after 2 seconds
    setTimeout(() => {
        feedback.classList.add('hidden');
    }, 2000);
}

function loadNotifications() {
    fetch('/Blog/api/get_notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const list = document.getElementById('notifications-list');
                if (!data.notifications?.length) {
                    list.innerHTML = '<div class="px-4 py-3 text-sm text-charcoal/70">No notifications yet</div>';
                    return;
                }

                let html = '';
                data.notifications.forEach(notif => {
                    html += `
                        <div id="notification-${notif.id}" 
                             data-notification="${notif.id}"
                             class="block px-4 py-3 hover:bg-gray-50 ${!notif.read ? 'bg-blue-50' : ''}">
                            <div class="flex items-start gap-3">
                                <a href="/Blog/public-profile.php?id=${notif.from_user_id}" class="shrink-0">
                                    <img src="${notif.profile_image || '/Blog/assets/images/default-avatar.png'}"
                                         alt=""
                                         class="w-8 h-8 rounded-full object-cover">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1 text-sm">
                                        <a href="/Blog/public-profile.php?id=${notif.from_user_id}" 
                                           class="font-medium hover:text-accent">
                                            ${escapeHtml(notif.username)}
                                        </a>
                                        <span class="text-charcoal/70">${notif.action_text}</span>
                                    </div>
                                    ${notif.post_title ? `
                                        <a href="/Blog/post.php?id=${notif.post_id}" 
                                           onclick="handleNotifications('mark_read', ${notif.id})"
                                           class="block text-xs text-charcoal/60 hover:text-accent truncate">
                                            ${escapeHtml(notif.post_title)}
                                        </a>
                                    ` : ''}
                                    <span class="text-xs text-charcoal/60">${notif.seconds_ago}</span>
                                </div>
                                <div class="flex gap-2">
                                    ${!notif.read ? `
                                        <button onclick="handleNotifications('mark_read', ${notif.id})" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    ` : ''}
                                    <button onclick="handleNotifications('delete', ${notif.id})"
                                            class="text-red-600 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                list.innerHTML = html;
                updateNotificationCount();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('notifications-list').innerHTML = 
                '<div class="px-4 py-3 text-sm text-red-600">Error loading notifications</div>';
        });
}

document.addEventListener('alpine:init', () => {
    Alpine.data('notificationsPanel', () => ({
        open: false,
        init() {
            this.$watch('open', value => {
                if (value) {
                    this.loadNotifications();
                    startNotificationCheck();
                } else {
                    stopNotificationCheck();
                }
            });
        },
        togglePanel() {
            this.open = !this.open;
            if (this.open) {
                this.loadNotifications();
            }
        },
        async loadNotifications() {
            try {
                const response = await fetch('/Blog/api/get_notifications.php');
                const data = await response.json();
                if (data.success) {
                    const list = document.getElementById('notifications-list');
                    if (!data.notifications?.length) {
                        list.innerHTML = '<div class="px-4 py-3 text-sm text-charcoal/70">No notifications yet</div>';
                        return;
                    }

                    let html = '';
                    data.notifications.forEach(notif => {
                        html += `
                            <div id="notification-${notif.id}" 
                                 data-notification="${notif.id}"
                                 class="block px-4 py-3 hover:bg-gray-50 ${!notif.read ? 'bg-blue-50' : ''}">
                                <div class="flex items-start gap-3">
                                    <a href="/Blog/public-profile.php?id=${notif.from_user_id}" class="shrink-0">
                                        <img src="${notif.profile_image || '/Blog/assets/images/default-avatar.png'}"
                                             alt=""
                                             class="w-8 h-8 rounded-full object-cover">
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 text-sm">
                                            <a href="/Blog/public-profile.php?id=${notif.from_user_id}" 
                                               class="font-medium hover:text-accent">
                                                ${escapeHtml(notif.username)}
                                            </a>
                                            <span class="text-charcoal/70">${notif.action_text}</span>
                                        </div>
                                        ${notif.post_title ? `
                                            <a href="/Blog/post.php?id=${notif.post_id}" 
                                               onclick="handleNotifications('mark_read', ${notif.id})"
                                               class="block text-xs text-charcoal/60 hover:text-accent truncate">
                                                ${escapeHtml(notif.post_title)}
                                            </a>
                                        ` : ''}
                                        <span class="text-xs text-charcoal/60">${notif.seconds_ago}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        ${!notif.read ? `
                                            <button onclick="handleNotifications('mark_read', ${notif.id})" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        ` : ''}
                                        <button onclick="handleNotifications('delete', ${notif.id})"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    list.innerHTML = html;
                    updateNotificationCount();
                }
            } catch (err) {
                console.error('Error loading notifications:', err);
                document.getElementById('notifications-list').innerHTML = 
                    '<div class="px-4 py-3 text-sm text-red-600">Error loading notifications</div>';
            }
        }
    }));
});

function switchTab(tab) {
    // Update tab styles
    const postsTab = document.getElementById('postsTab');
    const draftsTab = document.getElementById('draftsTab');
    const postsContent = document.getElementById('postsContent');
    const draftsContent = document.getElementById('draftsContent');
    
    if (tab === 'posts') {
        postsTab.className = 'font-serif text-xl relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-accent';
        draftsTab.className = 'font-serif text-xl text-charcoal/50 hover:text-charcoal/70';
        postsContent.style.display = 'block';
        draftsContent.style.display = 'none';
    } else {
        draftsTab.className = 'font-serif text-xl relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-accent';
        postsTab.className = 'font-serif text-xl text-charcoal/50 hover:text-charcoal/70';
        draftsContent.style.display = 'block';
        postsContent.style.display = 'none';
    }
}

// Fix the script errors
function deleteDraft(id) {
    if (!confirm('Are you sure you want to delete this draft?')) return;
    
    fetch('/Blog/api/delete_draft.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ draft_id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to delete draft');
    });
}

function deletePost(id) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch(`/Blog/api/delete_post.php?id=${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then((data) => {
                if (data.success) {
                    window.location.reload();
                }
            });
    }
}

async function deleteComment(commentId, postId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    try {
        const response = await fetch('/Blog/api/delete_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });
        const data = await response.json();
        if (data.success) {
            // Remove the comment element from DOM
            document.querySelector(`#comments-list div[data-comment-id="${commentId}"]`).remove();
            // If no comments left, show empty message
            if (document.querySelectorAll('#comments-list > div').length === 0) {
                document.getElementById('comments-list').innerHTML = 
                    '<p class="text-center py-4 text-charcoal/70">No comments yet</p>';
            }
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Failed to delete comment');
    }
}

function viewComments(postId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-medium">Manage Comments</h3>
                <button onclick="this.closest('.fixed').remove()" 
                        class="text-charcoal/70 hover:text-charcoal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="comments-list" class="space-y-4">
                <div class="animate-pulse text-center py-4">Loading comments...</div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Load comments
    fetch(`/Blog/api/get_comments.php?post_id=${postId}`)
        .then(res => res.json())
        .then(comments => {
            const list = document.getElementById('comments-list');
            list.innerHTML = ''; // Clear loading state
            
            if (!comments.length) {
                list.innerHTML = '<p class="text-center py-4 text-charcoal/70">No comments yet</p>';
                return;
            }

            comments.forEach(comment => {
                const div = document.createElement('div');
                div.className = 'p-4 border rounded-lg';
                div.dataset.commentId = comment.id;
                div.innerHTML = `
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <p class="font-medium mb-1">${escapeHtml(comment.username)}</p>
                            <p class="text-sm text-charcoal/70">${escapeHtml(comment.content)}</p>
                            <time class="text-xs text-charcoal/60 mt-1 block">
                                ${new Date(comment.created_at).toLocaleDateString()}
                            </time>
                        </div>
                        <button onclick="deleteComment(${comment.id}, ${postId})" 
                                class="text-red-600 hover:underline text-sm">Delete</button>
                    </div>
                `;
                list.appendChild(div);
            });
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('comments-list').innerHTML = 
                '<p class="text-center py-4 text-red-600">Error loading comments</p>';
        });
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Notification handling functions
async function handleNotifications(action, id) {
    try {
        const response = await fetch('/Blog/api/notification_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&id=${id}`
        });
        
        const data = await response.json();
        if (data.success) {
            if (id === 'all') {
                if (action === 'delete') {
                    document.getElementById('notifications-list').innerHTML = 
                        '<div class="px-4 py-3 text-sm text-charcoal/70">No notifications yet</div>';
                } else {
                    document.querySelectorAll('#notifications-list [data-notification]')
                        .forEach(el => el.classList.remove('bg-blue-50'));
                }
            } else {
                const notif = document.querySelector(`[data-notification="${id}"]`);
                if (action === 'delete') {
                    notif?.remove();
                } else {
                    notif?.classList.remove('bg-blue-50');
                }
            }
            updateNotificationCount();
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Failed to process notification action');
    }
}

async function updateNotificationCount() {
    const response = await fetch('/Blog/api/get_notification_count.php');
    const data = await response.json();
    const countBadge = document.querySelector('.notification-count');
    if (data.count > 0) {
        if (countBadge) {
            countBadge.textContent = data.count;
        } else {
            const badge = document.createElement('span');
            badge.className = 'notification-count absolute top-0 right-0 px-2 py-1 text-xs bg-red-500 text-white rounded-full';
            badge.textContent = data.count;
            document.querySelector('button[class*="relative p-2"]').appendChild(badge);
        }
    } else if (countBadge) {
        countBadge.remove();
    }
}

// Add real-time notification checking
let notificationCheckInterval;

function startNotificationCheck() {
    checkNewNotifications(); // Check immediately
    notificationCheckInterval = setInterval(checkNewNotifications, 30000); // Then every 30 seconds
}

function stopNotificationCheck() {
    clearInterval(notificationCheckInterval);
}

async function checkNewNotifications() {
    try {
        const response = await fetch('/Blog/api/get_notifications.php');
        const data = await response.json();
        if (data.success) {
            const list = document.getElementById('notifications-list');
            if (!data.notifications.length) {
                list.innerHTML = '<div class="px-4 py-3 text-sm text-charcoal/70">No notifications yet</div>';
                return;
            }

            let html = '';
            data.notifications.forEach(notif => {
                html += `
                    <div id="notification-${notif.id}" 
                         data-notification="${notif.id}"
                         class="block px-4 py-3 hover:bg-gray-50 ${!notif.read ? 'bg-blue-50' : ''}">
                    <div class="flex items-start gap-3">
                        <a href="/Blog/public-profile.php?id=${notif.from_user_id}" class="shrink-0">
                            <img src="${notif.profile_image || '/Blog/assets/images/default-avatar.png'}"
                                 alt=""
                                 class="w-8 h-8 rounded-full object-cover">
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1 text-sm">
                                <a href="/Blog/public-profile.php?id=${notif.from_user_id}" 
                                   class="font-medium hover:text-accent">
                                    ${escapeHtml(notif.username)}
                                </a>
                                <span class="text-charcoal/70">${notif.action_text}</span>
                            </div>
                            ${notif.post_title ? `
                                <a href="/Blog/post.php?id=${notif.post_id}" 
                                   onclick="handleNotifications('mark_read', ${notif.id})"
                                   class="block text-xs text-charcoal/60 hover:text-accent truncate">
                                    ${escapeHtml(notif.post_title)}
                                </a>
                            ` : ''}
                            <span class="text-xs text-charcoal/60">${notif.seconds_ago}</span>
                        </div>
                        <div class="flex gap-2">
                            ${!notif.read ? `
                                <button onclick="handleNotifications('mark_read', ${notif.id})" 
                                        class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            ` : ''}
                            <button onclick="handleNotifications('delete', ${notif.id})"
                                    class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                `;
            });
            list.innerHTML = html;
            updateNotificationCount();
        }
    } catch (err) {
        console.error('Error checking notifications:', err);
    }
}

// Initialize notification checking when notifications panel opens/closes
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationsPanel', () => ({
        open: false,
        init() {
            this.$watch('open', value => {
                if (value) {
                    startNotificationCheck();
                } else {
                    stopNotificationCheck();
                }
            });
        }
    }));
});

// Add these functions at the end of your script section
function showDeleteAccountConfirm() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h3 class="text-xl font-medium mb-4">Delete Account</h3>
            <p class="text-charcoal/70 mb-6">
                Are you sure you want to delete your account? This action cannot be undone and will:
                <ul class="list-disc ml-6 mt-2 space-y-1">
                    <li>Delete all your posts</li>
                    <li>Remove all your comments</li>
                    <li>Delete your profile information</li>
                    <li>Remove all your likes and follows</li>
                </ul>
            </p>
            <div class="flex justify-end gap-4">
                <button onclick="this.closest('.fixed').remove()" 
                        class="px-4 py-2 text-sm text-charcoal/70 hover:text-charcoal">
                    Cancel
                </button>
                <button onclick="showDeleteAccountPassword()" 
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Yes, Delete Account
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function showDeleteAccountPassword() {
    // Remove first confirmation modal
    document.querySelector('.fixed').remove();
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h3 class="text-xl font-medium mb-4">Confirm with Password</h3>
            <p class="text-charcoal/70 mb-6">
                Please enter your password to confirm account deletion.
            </p>
            <form onsubmit="deleteAccount(event)" class="space-y-4">
                <div>
                    <input type="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                           placeholder="Enter your password">
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button"
                            onclick="this.closest('.fixed').remove()" 
                            class="px-4 py-2 text-sm text-charcoal/70 hover:text-charcoal">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete My Account
                    </button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

async function deleteAccount(event) {
    event.preventDefault();
    const password = event.target.password.value;
    
    try {
        const response = await fetch('/Blog/api/delete_account.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password })
        });
        
        const data = await response.json();
        if (data.success) {
            window.location.href = '/Blog/logout.php';
        } else {
            alert(data.error || 'Incorrect password');
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Failed to delete account. Please try again.');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

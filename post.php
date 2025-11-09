<?php 
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Update query to include description
$stmt = $pdo->prepare("SELECT p.*, u.username, u.profile_image, u.description FROM blogPost p JOIN user u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: /Blog/404.php');
    exit;
}
?>

<article class="max-w-4xl mx-auto px-4 py-8 sm:py-16">
    <!-- Post Header -->
    <header class="mb-8 sm:mb-16">
        <?php if ($post['topics']): ?>
        <div class="flex flex-wrap gap-2 sm:gap-3 mb-4 sm:mb-6">
            <?php foreach(explode(',', $post['topics']) as $topic): ?>
                <?php if(trim($topic)): ?>
                    <span class="px-3 py-1 bg-cream rounded-full text-sm text-charcoal/70">
                        <?= htmlspecialchars(trim($topic)) ?>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl leading-tight mb-6">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-charcoal/70">
            <div class="flex items-center gap-3">
                <a href="<?= is_logged_in() && $post['user_id'] == $_SESSION['user_id'] 
                            ? '/Blog/dashboard.php' 
                            : '/Blog/public-profile.php?id=' . $post['user_id'] ?>" 
                   class="hover:opacity-80 transition-opacity">
                    <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($post['username']) ?>"
                         class="w-10 h-10 rounded-full object-cover">
                </a>
                <div class="flex items-center gap-3">
                    <a href="<?= is_logged_in() && $post['user_id'] == $_SESSION['user_id'] 
                                ? '/Blog/dashboard.php' 
                                : '/Blog/public-profile.php?id=' . $post['user_id'] ?>"
                       class="hover:text-accent transition-colors">
                        <?= htmlspecialchars($post['username']) ?>
                    </a>
                    <?php if ($post['user_id'] != ($_SESSION['user_id'] ?? null)): ?>
                        <?php if (is_logged_in()): ?>
                            <button onclick="toggleFollow(<?= $post['user_id'] ?>, this)" 
                                    class="px-3 py-1 text-sm border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                <?= isFollowing($post['user_id']) ? 'Following' : 'Follow' ?>
                            </button>
                        <?php else: ?>
                            <button onclick="showAuthPrompt()" 
                                    class="px-3 py-1 text-sm border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                Follow
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <time><?= date('F j, Y', strtotime($post['created_at'])) ?></time>
            <span>5 min read</span>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if (isset($post['image_url']) && $post['image_url']): ?>
    <div class="aspect-[16/9] mb-16 rounded-2xl overflow-hidden bg-taupe">
        <img src="<?= htmlspecialchars($post['image_url']) ?>" 
             alt="<?= htmlspecialchars($post['title']) ?>"
             class="w-full h-full object-cover">
    </div>
    <?php endif; ?>

    <!-- Post Content -->
    <div class="prose prose-lg max-w-none markdown-body">
        <?php if (is_logged_in()): ?>
            <div id="content"></div>
        <?php else: ?>
            <div class="mb-16 relative">
                <!-- Preview content with blur effect -->
                <div class="relative max-h-[500px] overflow-hidden">
                    <div id="preview"></div>
                    <div class="absolute inset-0 top-[200px] bg-gradient-to-b from-transparent via-white/70 to-white backdrop-blur-sm"></div>
                </div>
                
                <!-- Login Prompt with glass effect -->
                <div class="relative -mt-32">
                    <div class="relative p-8 bg-white/60 backdrop-blur-md border border-white/40 rounded-2xl text-center shadow-lg">
                        <h3 class="font-serif text-xl mb-4 text-charcoal">Want to read more?</h3>
                        <p class="text-charcoal/70 mb-6">Sign in to read the full story and join our community.</p>
                        <div class="flex justify-center gap-4">
                            <form action="/Blog/login.php" method="GET">
                                <input type="hidden" name="return" value="<?= htmlspecialchars("/Blog/post.php?id=" . $post['id']) ?>">
                                <button type="submit" 
                                        class="px-6 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all hover:shadow-lg">
                                    Sign In
                                </button>
                            </form>
                            <form action="/Blog/register.php" method="GET">
                                <input type="hidden" name="return" value="<?= htmlspecialchars("/Blog/post.php?id=" . $post['id']) ?>">
                                <button type="submit"
                                        class="px-6 py-2 border border-dark rounded-lg hover:bg-dark hover:text-white transition-all hover:shadow-lg">
                                    Create Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Author Bio -->
    <div class="mt-16 pt-16 border-t border-taupe">
        <div class="flex items-start justify-between gap-6">
            <div class="flex items-start gap-6">
                <a href="<?= is_logged_in() && $post['user_id'] == $_SESSION['user_id'] 
                            ? '/Blog/dashboard.php' 
                            : '/Blog/public-profile.php?id=' . $post['user_id'] ?>" 
                   class="hover:opacity-80 transition-opacity">
                    <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($post['username']) ?>"
                         class="w-16 h-16 rounded-full object-cover shrink-0">
                </a>
                <div>
                    <h3 class="font-serif text-xl mb-2">
                        <a href="<?= is_logged_in() && $post['user_id'] == $_SESSION['user_id'] 
                                    ? '/Blog/dashboard.php' 
                                    : '/Blog/public-profile.php?id=' . $post['user_id'] ?>"
                           class="hover:text-accent transition-colors">
                            Written by <?= htmlspecialchars($post['username']) ?>
                        </a>
                    </h3>
                    <p class="text-charcoal/70">
                        <?= htmlspecialchars($post['description'] ?? 'A passionate writer and developer exploring the intersection of design and technology.') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Like & Comment Section -->
    <div class="mt-8 border-t border-taupe pt-8">
        <div class="flex items-center gap-4 mb-8">
            <?php if (is_logged_in()): ?>
                <button onclick="toggleLike(<?= $post['id'] ?>)" 
                        id="likeBtn"
                        class="flex items-center gap-2 text-charcoal/70 hover:text-accent transition-colors">
                    <svg class="w-6 h-6" fill="<?= hasLiked($post['id']) ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span id="likeCount"><?= getLikeCount($post['id']) ?></span>
                </button>
            <?php else: ?>
                <button onclick="showAuthPrompt()" 
                        class="flex items-center gap-2 text-charcoal/70">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span><?= getLikeCount($post['id']) ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <div class="space-y-8">
            <h3 class="font-serif text-2xl">Comments</h3>
            
            <?php if (is_logged_in()): ?>
                <form id="commentForm" class="space-y-4">
                    <textarea name="content" 
                              rows="3" 
                              placeholder="Add a comment..."
                              class="w-full px-4 py-2 border border-taupe rounded-lg resize-none"></textarea>
                    <button type="submit" class="px-6 py-2 bg-dark text-white rounded-lg hover:bg-charcoal transition-all">
                        Post Comment
                    </button>
                </form>
            <?php else: ?>
                <p class="p-4 bg-cream rounded-lg text-center">
                    <a href="/Blog/login.php?return=<?= urlencode("/Blog/post.php?id=" . $post['id']) ?>" 
                       class="text-accent hover:underline">Sign in</a> 
                    to join the discussion.
                </p>
            <?php endif; ?>

            <div id="comments" class="space-y-6">
                <?php foreach (getComments($post['id']) as $comment): ?>
                    <div class="flex gap-4" id="comment-<?= $comment['id'] ?>">
                        <img src="<?= $comment['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                             alt="" class="w-10 h-10 rounded-full shrink-0">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium"><?= htmlspecialchars($comment['username']) ?></span>
                                    <?php if ($comment['user_id'] == $post['user_id']): ?>
                                        <span class="text-xs px-2 py-1 bg-accent/10 text-accent rounded-full">Author</span>
                                    <?php endif; ?>
                                    <?php if ($comment['user_id'] != ($_SESSION['user_id'] ?? null)): ?>
                                        <?php if (is_logged_in()): ?>
                                            <button onclick="toggleFollow(<?= $comment['user_id'] ?>, this)" 
                                                    class="px-3 py-1 text-sm border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                                <?= isFollowing($comment['user_id']) ? 'Following' : 'Follow' ?>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="showAuthPrompt()" 
                                                    class="px-3 py-1 text-sm border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                                Follow
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-4">
                                    <time class="text-sm text-charcoal/60">
                                        <?= date('M j, Y', strtotime($comment['created_at'])) ?>
                                    </time>
                                    <?php if (is_logged_in() && $comment['user_id'] == $_SESSION['user_id']): ?>
                                        <div class="flex gap-2">
                                            <button onclick="editComment(<?= $comment['id'] ?>)" 
                                                    class="text-sm text-accent hover:underline">Edit</button>
                                            <button onclick="deleteComment(<?= $comment['id'] ?>)" 
                                                    class="text-sm text-red-600 hover:underline">Delete</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div id="comment-content-<?= $comment['id'] ?>" class="text-charcoal/80">
                                <p><?= htmlspecialchars($comment['content']) ?></p>
                                <?php if (is_logged_in()): ?>
                                    <div class="mt-2 flex items-center gap-4 text-xs">
                                        <div class="flex items-center gap-2">
                                            <button onclick="toggleCommentLike(<?= $comment['id'] ?>)" 
                                                    id="comment-like-<?= $comment['id'] ?>" 
                                                    class="flex items-center gap-1 hover:text-blue-500 transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="<?= hasLikedComment($comment['id']) ? '#3B82F6' : 'none' ?>" 
                                                     stroke="currentColor" stroke-width="2">
                                                    <path d="M14.017 18L14.017 10.609C14.017 4.905 17.748 1.039 23 0L23.995 2.151C21.563 3.068 20 5.789 20 8H24V18H14.017ZM0 18V10.609C0 4.905 3.748 1.039 9 0L9.996 2.151C7.563 3.068 6 5.789 6 8H10V18H0Z"/>
                                                </svg>
                                                <span id="comment-like-count-<?= $comment['id'] ?>"><?= getCommentLikeCount($comment['id']) ?></span>
                                            </button>
                                            <button onclick="toggleCommentDislike(<?= $comment['id'] ?>)" 
                                                    id="comment-dislike-<?= $comment['id'] ?>" 
                                                    class="flex items-center gap-1 hover:text-red-500 transition-colors">
                                                <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="<?= hasDislikedComment($comment['id']) ? '#EF4444' : 'none' ?>" 
                                                     stroke="currentColor" stroke-width="2">
                                                    <path d="M14.017 18L14.017 10.609C14.017 4.905 17.748 1.039 23 0L23.995 2.151C21.563 3.068 20 5.789 20 8H24V18H14.017ZM0 18V10.609C0 4.905 3.748 1.039 9 0L9.996 2.151C7.563 3.068 6 5.789 6 8H10V18H0Z"/>
                                                </svg>
                                                <span id="comment-dislike-count-<?= $comment['id'] ?>"><?= getCommentDislikeCount($comment['id']) ?></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div id="comment-edit-<?= $comment['id'] ?>" class="hidden">
                                <textarea class="w-full px-4 py-2 border border-taupe rounded-lg resize-none"
                                          rows="3"><?= htmlspecialchars($comment['content']) ?></textarea>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button onclick="cancelEdit(<?= $comment['id'] ?>)"
                                            class="px-3 py-1 text-sm text-charcoal/70 hover:text-charcoal">Cancel</button>
                                    <button onclick="updateComment(<?= $comment['id'] ?>)"
                                            class="px-3 py-1 text-sm bg-dark text-white rounded-lg hover:bg-charcoal">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</article>

<!-- Add marked.js for markdown rendering -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown-light.min.css">
<style>
    .markdown-body {
        background: none !important;
    }
    .prose {
        max-width: none;
    }
    .prose img {
        border-radius: 0.75rem;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
<?php if (is_logged_in()): ?>
    document.getElementById('content').innerHTML = marked.parse(`<?= str_replace('`', '\`', $post['content']) ?>`);
<?php else: ?>
    // Show first 300 characters for preview
    const preview = `<?= str_replace('`', '\`', substr($post['content'], 0, 300)) ?>...`;
    document.getElementById('preview').innerHTML = marked.parse(preview);
<?php endif; ?>
</script>

<!-- Add interaction scripts -->
<script>
async function toggleLike(postId) {
    try {
        const response = await fetch('/Blog/api/toggle_like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId })
        });
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById('likeBtn');
            const svg = btn.querySelector('svg');
            svg.setAttribute('fill', data.liked ? 'currentColor' : 'none');
            document.getElementById('likeCount').textContent = data.count;
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

async function toggleFollow(userId, button) {
    try {
        const response = await fetch('/Blog/api/toggle_follow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        const data = await response.json();
        if (data.success) {
            // Update all follow buttons for this user
            document.querySelectorAll(`button[onclick="toggleFollow(${userId}, this)"]`
                ).forEach(btn => btn.textContent = data.following ? 'Following' : 'Follow');
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const content = e.target.content.value;
    if (!content.trim()) return;

    try {
        const response = await fetch('/Blog/api/add_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                post_id: <?= $post['id'] ?>,
                content: content 
            })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        }
    } catch (err) {
        console.error('Error:', err);
    }
});

function showAuthPrompt() {
    alert('Please sign in to interact with posts');
}

async function toggleCommentLike(commentId) {
    try {
        const response = await fetch('/Blog/api/toggle_comment_like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById(`comment-like-${commentId}`);
            const svg = btn.querySelector('svg');
            svg.setAttribute('fill', data.liked ? '#3B82F6' : 'none');
            // Update like count
            document.getElementById(`comment-like-count-${commentId}`).textContent = data.count || '0';

            // If liked, reset dislike button
            if (data.liked) {
                const dislikeBtn = document.getElementById(`comment-dislike-${commentId}`);
                const dislikeSvg = dislikeBtn.querySelector('svg');
                dislikeSvg.setAttribute('fill', 'none');
                document.getElementById(`comment-dislike-count-${commentId}`).textContent = '0';
            }
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

async function toggleCommentDislike(commentId) {
    try {
        const response = await fetch('/Blog/api/toggle_comment_dislike.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById(`comment-dislike-${commentId}`);
            const svg = btn.querySelector('svg');
            svg.setAttribute('fill', data.disliked ? '#EF4444' : 'none');
            // Update dislike count
            document.getElementById(`comment-dislike-count-${commentId}`).textContent = data.count || '0';

            // If disliked, reset like button
            if (data.disliked) {
                const likeBtn = document.getElementById(`comment-like-${commentId}`);
                const likeSvg = likeBtn.querySelector('svg');
                likeSvg.setAttribute('fill', 'none');
                document.getElementById(`comment-like-count-${commentId}`).textContent = '0';
            }
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

function editComment(commentId) {
    document.getElementById(`comment-content-${commentId}`).classList.add('hidden');
    document.getElementById(`comment-edit-${commentId}`).classList.remove('hidden');
}

function cancelEdit(commentId) {
    document.getElementById(`comment-content-${commentId}`).classList.remove('hidden');
    document.getElementById(`comment-edit-${commentId}`).classList.add('hidden');
}

async function updateComment(commentId) {
    const content = document.querySelector(`#comment-edit-${commentId} textarea`).value;
    if (!content.trim()) return;

    try {
        const response = await fetch('/Blog/api/update_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId, content: content })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
        const response = await fetch('/Blog/api/delete_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById(`comment-${commentId}`).remove();
        }
    } catch (err) {
        console.error('Error:', err);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

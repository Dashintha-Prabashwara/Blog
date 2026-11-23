<?php 
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_image, u.description 
    FROM blogpost p 
    JOIN user u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: /Blog/404.php');
    exit;
}

$pageTitle = htmlspecialchars($post['title']) . ' — Code & Canvas';
$pageDescription = substr(strip_tags($post['content']), 0, 160);
$pageImage = $post['image_url'] ?? '/Blog/assets/images/default-og.jpg';

require_once __DIR__ . '/includes/header.php';

// Calculate read time
$wordCount = str_word_count(strip_tags($post['content']));
$readTime = max(1, ceil($wordCount / 200));
?>

<!-- Marked.js for rendering markdown -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown-light.min.css">
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<article class="max-w-4xl mx-auto px-4 py-8 sm:py-16">
    <!-- Post Header -->
    <header class="mb-10 sm:mb-14">
        <?php if (!empty($post['topics'])): ?>
            <div class="flex flex-wrap gap-2 mb-6">
                <?php foreach(explode(',', $post['topics']) as $topic): if(trim($topic)): ?>
                    <a href="/Blog/posts.php?topic=<?= urlencode(trim($topic)) ?>" 
                       class="inline-flex items-center gap-1 px-3 py-1 bg-cream hover:bg-taupe/60 transition-colors border border-taupe rounded-full text-xs font-medium text-charcoal/80">
                        <i data-lucide="tag" class="w-3 h-3 text-charcoal/50"></i>
                        <span><?= htmlspecialchars(trim($topic)) ?></span>
                    </a>
                <?php endif; endforeach; ?>
            </div>
        <?php endif; ?>

        <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold leading-tight mb-6 text-charcoal">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <div class="flex flex-wrap items-center justify-between gap-4 text-xs sm:text-sm text-charcoal/70 pt-4 border-t border-taupe">
            <div class="flex items-center gap-3">
                <a href="<?= is_logged_in() && (int)$post['user_id'] === (int)$_SESSION['user_id'] ? '/Blog/dashboard.php' : '/Blog/public-profile.php?id=' . $post['user_id'] ?>" 
                   class="hover:opacity-80 transition-opacity">
                    <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($post['username']) ?>"
                         class="w-11 h-11 rounded-full object-cover border border-taupe shadow-sm">
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <a href="<?= is_logged_in() && (int)$post['user_id'] === (int)$_SESSION['user_id'] ? '/Blog/dashboard.php' : '/Blog/public-profile.php?id=' . $post['user_id'] ?>"
                           class="font-semibold text-charcoal hover:text-accent transition-colors">
                            <?= htmlspecialchars($post['username']) ?>
                        </a>
                        <?php if ((int)$post['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                            <?php if (is_logged_in()): ?>
                                <button onclick="toggleFollow(<?= (int)$post['user_id'] ?>, this)" 
                                        class="px-3 py-0.5 text-xs font-medium border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                    <?= isFollowing($post['user_id']) ? 'Following' : 'Follow' ?>
                                </button>
                            <?php else: ?>
                                <button onclick="showAuthPrompt()" 
                                        class="px-3 py-0.5 text-xs font-medium border border-dark rounded-full hover:bg-dark hover:text-white transition-all">
                                    Follow
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 text-charcoal/60 text-xs mt-0.5">
                        <span class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            <time><?= date('F j, Y', strtotime($post['created_at'])) ?></time>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span><?= $readTime ?> min read</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Share & Edit Actions -->
            <div class="flex items-center gap-2">
                <?php if (is_logged_in() && (int)$post['user_id'] === (int)$_SESSION['user_id']): ?>
                    <a href="/Blog/editor.php?id=<?= (int)$post['id'] ?>" 
                       class="px-3.5 py-1.5 rounded-lg border border-taupe hover:bg-white text-xs font-medium text-charcoal flex items-center gap-1.5 shadow-sm transition-all">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit Story</span>
                    </a>
                <?php endif; ?>
                <button onclick="copyPostLink()" 
                        id="shareBtn"
                        class="p-2 rounded-lg border border-taupe hover:bg-white text-charcoal/70 hover:text-charcoal text-xs shadow-sm transition-all flex items-center gap-1"
                        title="Copy story link">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span id="shareText" class="hidden sm:inline">Share</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if (!empty($post['image_url'])): ?>
        <div class="aspect-[16/9] mb-12 rounded-3xl overflow-hidden bg-taupe border border-taupe shadow-sm">
            <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                 alt="<?= htmlspecialchars($post['title']) ?>"
                 class="w-full h-full object-cover">
        </div>
    <?php endif; ?>

    <!-- Post Content Container -->
    <div class="prose prose-lg max-w-none text-charcoal/90 leading-relaxed font-sans mb-14">
        <?php if (is_logged_in()): ?>
            <div id="content" class="space-y-4"></div>
        <?php else: ?>
            <div class="relative">
                <div class="relative max-h-[380px] overflow-hidden">
                    <div id="preview" class="space-y-4"></div>
                    <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-cream via-cream/80 to-transparent pointer-events-none"></div>
                </div>
                
                <!-- Sign in Prompt -->
                <div class="mt-6 p-8 bg-white/90 backdrop-blur-md border border-taupe rounded-3xl text-center shadow-lg max-w-lg mx-auto">
                    <span class="w-12 h-12 rounded-2xl bg-dark text-white flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </span>
                    <h3 class="font-serif text-2xl font-bold mb-2 text-charcoal">Read the full story</h3>
                    <p class="text-sm text-charcoal/70 mb-6">Join our community or sign in to read the complete article, comment, and engage with the author.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="/Blog/login.php?return=<?= urlencode('/Blog/post.php?id=' . $post['id']) ?>" 
                           class="px-5 py-2.5 bg-dark text-white rounded-xl text-sm font-medium hover:bg-charcoal transition-all shadow-sm">
                            Sign In
                        </a>
                        <a href="/Blog/register.php?return=<?= urlencode('/Blog/post.php?id=' . $post['id']) ?>" 
                           class="px-5 py-2.5 border border-dark rounded-xl text-sm font-medium text-dark hover:bg-dark hover:text-white transition-all">
                            Create Account
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Interaction Bar (Like & Stats) -->
    <div class="flex items-center justify-between py-6 border-y border-taupe mb-12">
        <div class="flex items-center gap-6">
            <?php if (is_logged_in()): ?>
                <button onclick="toggleLike(<?= (int)$post['id'] ?>)" 
                        id="likeBtn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-taupe hover:border-accent bg-white shadow-sm transition-all text-sm font-medium <?= hasLiked($post['id']) ? 'text-accent border-accent' : 'text-charcoal/70' ?>">
                    <i data-lucide="heart" class="w-5 h-5 <?= hasLiked($post['id']) ? 'fill-accent text-accent' : '' ?>" id="likeIcon"></i>
                    <span id="likeCount"><?= getLikeCount($post['id']) ?></span>
                    <span class="hidden sm:inline">Likes</span>
                </button>
            <?php else: ?>
                <button onclick="showAuthPrompt()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-taupe bg-white shadow-sm text-sm font-medium text-charcoal/70">
                    <i data-lucide="heart" class="w-5 h-5"></i>
                    <span><?= getLikeCount($post['id']) ?></span>
                    <span class="hidden sm:inline">Likes</span>
                </button>
            <?php endif; ?>

            <div class="flex items-center gap-1.5 text-sm font-medium text-charcoal/70">
                <i data-lucide="message-square" class="w-4 h-4 text-charcoal/50"></i>
                <span><?= count(getComments($post['id'])) ?> Comments</span>
            </div>
        </div>

        <button onclick="copyPostLink()" class="inline-flex items-center gap-2 text-sm text-charcoal/60 hover:text-charcoal transition-colors">
            <i data-lucide="link" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Copy Link</span>
        </button>
    </div>

    <!-- Author Profile Card -->
    <div class="p-8 bg-white rounded-3xl border border-taupe shadow-sm mb-16 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-start gap-5">
            <img src="<?= $post['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                 alt="<?= htmlspecialchars($post['username']) ?>"
                 class="w-16 h-16 rounded-2xl object-cover border border-taupe shadow-sm shrink-0">
            <div>
                <p class="text-xs uppercase tracking-wider text-charcoal/50 font-semibold mb-1">Author</p>
                <h3 class="font-serif text-xl font-bold text-charcoal">
                    <a href="/Blog/public-profile.php?id=<?= $post['user_id'] ?>" class="hover:text-accent transition-colors">
                        <?= htmlspecialchars($post['username']) ?>
                    </a>
                </h3>
                <p class="text-sm text-charcoal/70 mt-1 max-w-xl leading-relaxed">
                    <?= htmlspecialchars($post['description'] ?? 'Exploring craft, design systems, and digital engineering.') ?>
                </p>
            </div>
        </div>
        
        <?php if ((int)$post['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
            <?php if (is_logged_in()): ?>
                <button onclick="toggleFollow(<?= (int)$post['user_id'] ?>, this)" 
                        class="px-5 py-2.5 rounded-xl border border-dark bg-dark text-white hover:bg-charcoal text-xs font-semibold shadow-sm transition-all shrink-0">
                    <?= isFollowing($post['user_id']) ? 'Following' : 'Follow Author' ?>
                </button>
            <?php else: ?>
                <button onclick="showAuthPrompt()" 
                        class="px-5 py-2.5 rounded-xl border border-dark bg-dark text-white hover:bg-charcoal text-xs font-semibold shadow-sm transition-all shrink-0">
                    Follow Author
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Comments Section -->
    <section class="space-y-8" id="commentsSection">
        <div class="flex items-center justify-between">
            <h3 class="font-serif text-2xl font-bold text-charcoal flex items-center gap-2">
                <i data-lucide="message-square" class="w-5 h-5 text-accent"></i>
                <span>Discussion</span>
            </h3>
            <span class="text-xs text-charcoal/50 font-medium"><?= count(getComments($post['id'])) ?> thoughts</span>
        </div>
        
        <?php if (is_logged_in()): ?>
            <form id="commentForm" class="bg-white p-6 rounded-2xl border border-taupe shadow-sm space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-charcoal/60 mb-2">Leave a comment</label>
                    <textarea name="content" 
                              rows="3" 
                              placeholder="Share your thoughts, feedback, or constructive critiques..."
                              class="w-full p-4 border border-taupe rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30 resize-none leading-relaxed"
                              required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm flex items-center gap-1.5">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Post Comment</span>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="p-6 bg-white rounded-2xl border border-taupe text-center">
                <p class="text-sm text-charcoal/70">
                    <a href="/Blog/login.php?return=<?= urlencode('/Blog/post.php?id=' . $post['id']) ?>" class="text-accent font-semibold hover:underline">Sign in</a> 
                    to join the conversation and reply to this story.
                </p>
            </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div id="comments" class="space-y-4">
            <?php 
            $comments = getComments($post['id']);
            if (empty($comments)): 
            ?>
                <div class="p-8 text-center bg-white/50 rounded-2xl border border-taupe/60">
                    <p class="text-sm text-charcoal/50">No comments yet. Be the first to share your perspective!</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="p-6 bg-white rounded-2xl border border-taupe shadow-sm space-y-3" id="comment-<?= $comment['id'] ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="<?= $comment['profile_image'] ?? '/Blog/assets/images/default-avatar.png' ?>" 
                                     alt="" class="w-9 h-9 rounded-full object-cover border border-taupe">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-charcoal"><?= htmlspecialchars($comment['username']) ?></span>
                                        <?php if ((int)$comment['user_id'] === (int)$post['user_id']): ?>
                                            <span class="text-[10px] px-2 py-0.5 bg-accent/10 text-accent font-semibold rounded-full">Author</span>
                                        <?php endif; ?>
                                    </div>
                                    <time class="text-xs text-charcoal/50"><?= date('M j, Y • g:i a', strtotime($comment['created_at'])) ?></time>
                                </div>
                            </div>

                            <?php if (is_logged_in() && ((int)$comment['user_id'] === (int)$_SESSION['user_id'] || (int)$post['user_id'] === (int)$_SESSION['user_id'])): ?>
                                <div class="flex items-center gap-2">
                                    <?php if ((int)$comment['user_id'] === (int)$_SESSION['user_id']): ?>
                                        <button onclick="editComment(<?= $comment['id'] ?>)" 
                                                class="text-xs text-charcoal/60 hover:text-charcoal p-1">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteComment(<?= $comment['id'] ?>)" 
                                            class="text-xs text-red-500 hover:text-red-700 p-1"
                                            title="Delete comment">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div id="comment-content-<?= $comment['id'] ?>" class="text-sm text-charcoal/80 leading-relaxed pl-12">
                            <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            
                            <!-- Upvote / Downvote Reactions with Lucide Icons -->
                            <div class="mt-3 flex items-center gap-4 text-xs text-charcoal/60">
                                <?php if (is_logged_in()): ?>
                                    <button onclick="toggleCommentLike(<?= $comment['id'] ?>)" 
                                            id="comment-like-<?= $comment['id'] ?>" 
                                            class="inline-flex items-center gap-1 hover:text-accent transition-colors <?= hasLikedComment($comment['id']) ? 'text-accent font-semibold' : '' ?>">
                                        <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>
                                        <span id="comment-like-count-<?= $comment['id'] ?>"><?= getCommentLikeCount($comment['id']) ?></span>
                                    </button>
                                    <button onclick="toggleCommentDislike(<?= $comment['id'] ?>)" 
                                            id="comment-dislike-<?= $comment['id'] ?>" 
                                            class="inline-flex items-center gap-1 hover:text-red-600 transition-colors <?= hasDislikedComment($comment['id']) ? 'text-red-600 font-semibold' : '' ?>">
                                        <i data-lucide="thumbs-down" class="w-3.5 h-3.5"></i>
                                        <span id="comment-dislike-count-<?= $comment['id'] ?>"><?= getCommentDislikeCount($comment['id']) ?></span>
                                    </button>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1">
                                        <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>
                                        <?= getCommentLikeCount($comment['id']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Edit form -->
                        <div id="comment-edit-<?= $comment['id'] ?>" class="hidden pl-12 space-y-3">
                            <textarea class="w-full p-3 border border-taupe rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30 resize-none"
                                      rows="2"><?= htmlspecialchars($comment['content']) ?></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="cancelEdit(<?= $comment['id'] ?>)"
                                        class="px-3 py-1.5 text-xs text-charcoal/70 hover:text-charcoal border border-taupe rounded-lg">Cancel</button>
                                <button type="button" onclick="updateComment(<?= $comment['id'] ?>)"
                                        class="px-3.5 py-1.5 text-xs bg-dark text-white rounded-lg hover:bg-charcoal">Save</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

<!-- Markdown Renderer Script -->
<script>
marked.setOptions({
    breaks: true,
    gfm: true
});

const postMarkdown = <?= json_encode($post['content']) ?>;

<?php if (is_logged_in()): ?>
    document.getElementById('content').innerHTML = marked.parse(postMarkdown);
<?php else: ?>
    const previewText = postMarkdown.substring(0, 320) + (postMarkdown.length > 320 ? '...' : '');
    document.getElementById('preview').innerHTML = marked.parse(previewText);
<?php endif; ?>

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
            const icon = document.getElementById('likeIcon');
            const count = document.getElementById('likeCount');
            
            count.textContent = data.count;
            if (data.liked) {
                btn.classList.add('text-accent', 'border-accent');
                btn.classList.remove('text-charcoal/70');
                icon.classList.add('fill-accent', 'text-accent');
            } else {
                btn.classList.remove('text-accent', 'border-accent');
                btn.classList.add('text-charcoal/70');
                icon.classList.remove('fill-accent', 'text-accent');
            }
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
            document.querySelectorAll(`button[onclick*="toggleFollow(${userId}"]`).forEach(btn => {
                btn.textContent = data.following ? 'Following' : 'Follow';
            });
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const content = e.target.content.value.trim();
    if (!content) return;

    try {
        const response = await fetch('/Blog/api/add_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                post_id: <?= (int)$post['id'] ?>,
                content: content 
            })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to post comment');
        }
    } catch (err) {
        console.error('Error:', err);
    }
});

function showAuthPrompt() {
    window.location.href = '/Blog/login.php?return=' + encodeURIComponent(window.location.pathname + window.location.search);
}

function copyPostLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const text = document.getElementById('shareText');
        if (text) {
            const original = text.textContent;
            text.textContent = 'Copied!';
            setTimeout(() => { text.textContent = original; }, 2000);
        } else {
            alert('Story link copied to clipboard!');
        }
    });
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
            const count = document.getElementById(`comment-like-count-${commentId}`);
            count.textContent = data.count || '0';
            if (data.liked) {
                btn.classList.add('text-accent', 'font-semibold');
                const dislikeBtn = document.getElementById(`comment-dislike-${commentId}`);
                if (dislikeBtn) dislikeBtn.classList.remove('text-red-600', 'font-semibold');
            } else {
                btn.classList.remove('text-accent', 'font-semibold');
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
            const count = document.getElementById(`comment-dislike-count-${commentId}`);
            count.textContent = data.count || '0';
            if (data.disliked) {
                btn.classList.add('text-red-600', 'font-semibold');
                const likeBtn = document.getElementById(`comment-like-${commentId}`);
                if (likeBtn) likeBtn.classList.remove('text-accent', 'font-semibold');
            } else {
                btn.classList.remove('text-red-600', 'font-semibold');
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
    const textarea = document.querySelector(`#comment-edit-${commentId} textarea`);
    const content = textarea.value.trim();
    if (!content) return;

    try {
        const response = await fetch('/Blog/api/update_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId, content: content })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to update comment');
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
        } else {
            alert(data.error || 'Failed to delete comment');
        }
    } catch (err) {
        console.error('Error:', err);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

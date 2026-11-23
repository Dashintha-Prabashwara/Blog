<?php
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: /Blog/login.php'); 
    exit; 
}

$editing = false;
$isDraft = false;
$post = ['title'=>'', 'content'=>'', 'topics'=>'', 'image_url'=>''];

if (isset($_GET['draft_id'])) {
    $draftId = (int)$_GET['draft_id'];
    $stmt = $pdo->prepare('SELECT * FROM draft_post WHERE id = ? AND user_id = ?');
    $stmt->execute([$draftId, $_SESSION['user_id']]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /Blog/404.php'); exit; }
    $isDraft = true;
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM blogpost WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /Blog/404.php'); exit; }
    if ((int)$post['user_id'] !== (int)$_SESSION['user_id']) { 
        header('Location: /Blog/404.php'); 
        exit; 
    }
    $editing = true;
}

$pageTitle = ($editing ? 'Edit Story' : ($isDraft ? 'Edit Draft' : 'New Story')) . ' — Code & Canvas';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Marked.js for Markdown Preview -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<div class="max-w-5xl mx-auto px-4 py-8 sm:py-12">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-taupe">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-dark text-white flex items-center justify-center shadow-sm">
                <i data-lucide="pen-tool" class="w-5 h-5"></i>
            </span>
            <div>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-charcoal">
                    <?= $editing ? 'Edit Story' : ($isDraft ? 'Edit Draft' : 'Write a Story') ?>
                </h1>
                <p class="text-xs text-charcoal/60 mt-0.5">Use Markdown for rich text formatting and code blocks.</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="/Blog/dashboard.php" class="px-4 py-2 border border-taupe rounded-xl hover:bg-white text-sm font-medium transition-colors text-charcoal/70 hover:text-charcoal flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <button type="button" onclick="document.getElementById('postForm').requestSubmit()" class="px-5 py-2 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span><?= $editing ? 'Update Story' : 'Publish Story' ?></span>
            </button>
        </div>
    </div>

    <form id="postForm" method="post" class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-taupe space-y-6" enctype="multipart/form-data">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
        <?php endif; ?>
        
        <!-- Title Input -->
        <div>
            <label class="block text-sm font-medium mb-2 text-charcoal/80">Story Title</label>
            <input type="text" 
                   name="title" 
                   placeholder="Enter an inspiring title..." 
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                   class="w-full px-4 py-3 text-lg font-serif border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30"
                   required>
        </div>

        <!-- Topics Input -->
        <div>
            <label class="block text-sm font-medium mb-2 text-charcoal/80">Topics &amp; Tags</label>
            <div class="space-y-3">
                <!-- Selected Topics -->
                <div id="selectedTopics" class="flex flex-wrap gap-2">
                    <?php
                    $commonTopics = ['Design', 'Development', 'Tutorial', 'Technology', 'UI/UX', 'Architecture', 'AI'];
                    $selectedTopics = !empty($post['topics']) ? array_map('trim', explode(',', $post['topics'])) : [];
                    foreach($commonTopics as $topic): 
                        $isSelected = in_array($topic, $selectedTopics);
                    ?>
                        <button type="button" 
                                onclick="toggleTopic(this, '<?= htmlspecialchars($topic) ?>')"
                                class="px-3.5 py-1.5 rounded-full text-xs font-medium border transition-colors flex items-center gap-1 <?= $isSelected ? 'bg-dark text-white border-dark' : 'bg-cream/60 border-taupe text-charcoal/70 hover:border-dark' ?>">
                            <i data-lucide="tag" class="w-3 h-3"></i>
                            <span><?= htmlspecialchars($topic) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Custom Topic Input -->
                <div class="flex gap-2 max-w-md">
                    <div class="relative flex-1">
                        <i data-lucide="plus" class="w-4 h-4 text-charcoal/40 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" 
                               id="newTopic"
                               placeholder="Add custom topic..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-taupe rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/40 bg-cream/30">
                    </div>
                    <button type="button" 
                            onclick="addCustomTopic()"
                            class="px-4 py-2 bg-cream text-charcoal font-medium text-sm border border-taupe rounded-xl hover:bg-taupe/40 transition-colors">
                        Add
                    </button>
                </div>

                <input type="hidden" name="topics" id="topicsInput" value="<?= htmlspecialchars($post['topics'] ?? '') ?>">
            </div>
        </div>

        <!-- Featured Image Upload -->
        <div>
            <label class="block text-sm font-medium mb-2 text-charcoal/80">Cover Image</label>
            <div class="relative">
                <input type="file" 
                       id="image" 
                       name="image" 
                       accept="image/jpeg,image/png,image/webp,image/gif" 
                       class="hidden"
                       onchange="previewImage(this)">
                
                <div id="imagePreview" 
                     class="aspect-[21/9] rounded-2xl border-2 border-dashed border-taupe hover:border-accent/60 bg-cream/30 cursor-pointer overflow-hidden transition-colors flex items-center justify-center p-6 <?= !empty($post['image_url']) ? 'hidden' : '' ?>"
                     onclick="document.getElementById('image').click()">
                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-taupe/60 flex items-center justify-center mx-auto text-charcoal/60">
                            <i data-lucide="image-plus" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-medium text-charcoal/80">Click to upload cover image</p>
                        <p class="text-xs text-charcoal/50">PNG, JPG, or WEBP up to 5MB</p>
                    </div>
                </div>

                <?php if (!empty($post['image_url'])): ?>
                    <div id="currentImageContainer" class="relative group aspect-[21/9] rounded-2xl overflow-hidden bg-taupe border border-taupe">
                        <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                             id="currentImage"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <button type="button" onclick="document.getElementById('image').click()" 
                                    class="px-4 py-2 bg-white text-dark rounded-xl text-xs font-semibold shadow hover:bg-gray-100 flex items-center gap-1.5">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                <span>Change Image</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Split Screen Editor -->
        <div class="grid lg:grid-cols-2 gap-6 pt-2">
            <!-- Markdown Input -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-charcoal/80">Write Content (Markdown)</label>
                    <span class="text-xs text-charcoal/50 font-mono">GFM Enabled</span>
                </div>
                <div class="border border-taupe rounded-2xl bg-white overflow-hidden shadow-sm">
                    <!-- Formatting Toolbar with Lucide Icons -->
                    <div class="bg-cream/60 border-b border-taupe px-3 py-2 flex flex-wrap items-center gap-1.5 text-charcoal/70">
                        <button type="button" onclick="insertMarkdown('**', '**')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Bold">
                            <i data-lucide="bold" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('*', '*')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Italic">
                            <i data-lucide="italic" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('# ', '')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Heading">
                            <i data-lucide="heading" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('- ', '')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Bullet List">
                            <i data-lucide="list" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('1. ', '')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Numbered List">
                            <i data-lucide="list-ordered" class="w-4 h-4"></i>
                        </button>
                        <div class="h-4 w-px bg-taupe mx-1"></div>
                        <button type="button" onclick="insertMarkdown('[', '](https://)')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Insert Link">
                            <i data-lucide="link" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('![alt text](', ')')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Insert Image">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('`', '`')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Inline Code">
                            <i data-lucide="code" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('```\n', '\n```')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Code Block">
                            <i data-lucide="square-code" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="insertMarkdown('> ', '')" class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Quote">
                            <i data-lucide="quote" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <textarea id="markdown" 
                              name="content"
                              class="w-full h-[520px] p-4 font-mono text-sm focus:outline-none resize-none leading-relaxed text-charcoal/90"
                              placeholder="Once upon a time in software..."
                              onkeyup="updatePreview()"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-charcoal/80">Live Reader Preview</label>
                    <span class="text-xs text-charcoal/50">Auto-rendered</span>
                </div>
                <div id="preview" class="border border-taupe rounded-2xl bg-cream/20 h-[565px] p-6 overflow-y-auto prose prose-slate max-w-none">
                    <p class="text-charcoal/40 text-sm">Preview will appear here in real-time as you write...</p>
                </div>
            </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center justify-between pt-6 border-t border-taupe">
            <div id="saveStatusContainer" class="flex items-center gap-2 text-xs text-charcoal/60">
                <span id="saveStatusIcon"></span>
                <span id="saveStatus">All changes saved automatically</span>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="/Blog/dashboard.php" class="px-5 py-2.5 border border-taupe rounded-xl hover:bg-cream transition-colors text-sm font-medium text-charcoal/70">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-dark text-white rounded-xl hover:bg-charcoal transition-all text-sm font-medium shadow-sm flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span><?= $editing ? 'Update Story' : 'Publish Story' ?></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Configure marked options
marked.setOptions({
    breaks: true,
    gfm: true
});

function updatePreview() {
    const markdown = document.getElementById('markdown').value;
    const preview = document.getElementById('preview');
    if (markdown.trim()) {
        preview.innerHTML = marked.parse(markdown);
    } else {
        preview.innerHTML = '<p class="text-charcoal/40 text-sm">Preview will appear here in real-time as you write...</p>';
    }
}

function insertMarkdown(prefix, suffix) {
    const textarea = document.getElementById('markdown');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    const before = text.substring(0, start);
    const selection = text.substring(start, end);
    const after = text.substring(end);
    
    textarea.value = before + prefix + selection + suffix + after;
    updatePreview();
    
    textarea.focus();
    const newCursor = start + prefix.length + selection.length + suffix.length;
    textarea.setSelectionRange(newCursor, newCursor);
    autosaveDraft();
}

// Initialize preview on load
updatePreview();

let draftId = <?= $isDraft ? (int)$post['id'] : 'null' ?>;
let draftTimeout = null;
const AUTOSAVE_DELAY = 2500;

function autosaveDraft() {
    clearTimeout(draftTimeout);
    draftTimeout = setTimeout(async () => {
        const title = document.querySelector('input[name="title"]').value;
        const content = document.getElementById('markdown').value;
        const topics = document.getElementById('topicsInput').value;

        if (!title.trim() && !content.trim()) return;

        updateSaveStatus('Saving draft...', false, true);

        try {
            const response = await fetch('/Blog/api/save_draft.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    draft_id: draftId,
                    title: title,
                    content: content,
                    topics: topics
                })
            });
            
            const data = await response.json();
            if (data.success) {
                draftId = data.draft_id;
                updateSaveStatus(`Draft saved at ${data.timestamp.split(' ')[1]}`, false);
            }
        } catch (err) {
            console.error('Error saving draft:', err);
            updateSaveStatus('Failed to save draft', true);
        }
    }, AUTOSAVE_DELAY);
}

function updateSaveStatus(message, isError = false, isSaving = false) {
    const status = document.getElementById('saveStatus');
    const icon = document.getElementById('saveStatusIcon');
    if (!status || !icon) return;
    
    status.textContent = message;
    if (isError) {
        status.className = 'text-xs text-red-600 font-medium';
        icon.innerHTML = '<i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-600"></i>';
    } else if (isSaving) {
        status.className = 'text-xs text-accent font-medium';
        icon.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 text-accent animate-spin"></i>';
    } else {
        status.className = 'text-xs text-green-600 font-medium';
        icon.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>';
    }
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

document.querySelector('input[name="title"]').addEventListener('input', autosaveDraft);
document.getElementById('markdown').addEventListener('input', autosaveDraft);
document.getElementById('topicsInput').addEventListener('change', autosaveDraft);

document.getElementById('postForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    <?php if ($isDraft): ?>
    formData.append('draft_id', '<?= (int)$post['id'] ?>');
    <?php endif; ?>

    try {
        const response = await fetch('/Blog/api/save_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            <?php if ($isDraft): ?>
            // Delete draft after publishing
            await fetch('/Blog/api/delete_draft.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ draft_id: <?= (int)$post['id'] ?> })
            });
            <?php endif; ?>
            window.location.href = `/Blog/post.php?id=${data.id}`;
        } else {
            alert(data.error || 'Failed to save post');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred. Please try again.');
    }
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const currentContainer = document.getElementById('currentImageContainer');
            
            if (currentContainer) currentContainer.remove();
            
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

let selectedTopics = new Set(<?= json_encode($selectedTopics) ?>);

function toggleTopic(button, topic) {
    if (selectedTopics.has(topic)) {
        selectedTopics.delete(topic);
        button.className = 'px-3.5 py-1.5 rounded-full text-xs font-medium border transition-colors flex items-center gap-1 bg-cream/60 border-taupe text-charcoal/70 hover:border-dark';
    } else {
        selectedTopics.add(topic);
        button.className = 'px-3.5 py-1.5 rounded-full text-xs font-medium border transition-colors flex items-center gap-1 bg-dark text-white border-dark';
    }
    updateTopicsInput();
    autosaveDraft();
}

function addCustomTopic() {
    const input = document.getElementById('newTopic');
    const topic = input.value.trim();
    
    if (topic && !selectedTopics.has(topic)) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'px-3.5 py-1.5 rounded-full text-xs font-medium border transition-colors flex items-center gap-1 bg-dark text-white border-dark';
        button.innerHTML = `<i data-lucide="tag" class="w-3 h-3"></i><span>${topic}</span>`;
        button.onclick = () => toggleTopic(button, topic);
        
        selectedTopics.add(topic);
        document.getElementById('selectedTopics').appendChild(button);
        updateTopicsInput();
        autosaveDraft();
        input.value = '';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

function updateTopicsInput() {
    document.getElementById('topicsInput').value = Array.from(selectedTopics).join(',');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

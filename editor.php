<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) { header('Location: /login.php'); exit; }

$editing = false;
$isDraft = false;
$post = ['title'=>'','content'=>'', 'topics'=>''];

if (isset($_GET['draft_id'])) {
    $draftId = (int)$_GET['draft_id'];
    $stmt = $pdo->prepare('SELECT * FROM draft_post WHERE id = ? AND user_id = ?');
    $stmt->execute([$draftId, $_SESSION['user_id']]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /Blog/404.php'); exit; }
    $isDraft = true;
} elseif (isset($_GET['id'])){
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare('SELECT * FROM blogPost WHERE id = ?');
  $stmt->execute([$id]);
  $post = $stmt->fetch();
  if (!$post) { header('Location: /Blog/404.php'); exit; }
  if ($post['user_id'] != $_SESSION['user_id']){ echo '<div class="container">Unauthorized</div>'; require __DIR__.'/includes/footer.php'; exit; }
  $editing = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $editing ? 'Edit Post' : 'New Post'; ?> — Code & Canvas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">
    <main class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-3xl font-bold mb-6"><?php echo $editing ? 'Edit Post' : 'New Post'; ?></h1>
        <form id="postForm" method="post" class="bg-white p-6 rounded-lg shadow space-y-6" enctype="multipart/form-data">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo $post['id']; ?>"><?php endif; ?>
            
            <!-- Title Input -->
            <div>
                <label class="block text-sm font-medium mb-2">Title</label>
                <input type="text" 
                       name="title" 
                       placeholder="Enter your post title..." 
                       value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <!-- Topics Input -->
            <div>
                <label class="block text-sm font-medium mb-2">Topics</label>
                <div class="space-y-3">
                    <!-- Selected Topics -->
                    <div id="selectedTopics" class="flex flex-wrap gap-2">
                        <?php
                        $commonTopics = ['Design', 'Development', 'Tutorial', 'Technology', 'UI/UX'];
                        $selectedTopics = isset($post['topics']) ? explode(',', $post['topics']) : [];
                        foreach($commonTopics as $topic): ?>
                            <button type="button" 
                                    onclick="toggleTopic(this, '<?= $topic ?>')"
                                    class="px-3 py-1 rounded-full text-sm border <?= in_array($topic, $selectedTopics) ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-gray-300 hover:border-blue-500' ?>">
                                <?= htmlspecialchars($topic) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Custom Topic Input -->
                    <div class="flex gap-2">
                        <input type="text" 
                               id="newTopic"
                               placeholder="Add custom topic..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" 
                                onclick="addCustomTopic()"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Add Topic
                        </button>
                    </div>

                    <!-- Hidden input to store selected topics -->
                    <input type="hidden" name="topics" id="topicsInput" value="<?= htmlspecialchars($post['topics'] ?? '') ?>">
                </div>
            </div>

            <!-- Featured Image Upload -->
            <div>
                <label class="block text-sm font-medium mb-2">Featured Image</label>
                <div class="relative">
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/*" 
                           class="hidden"
                           onchange="previewImage(this)">
                    <div id="imagePreview" 
                         class="aspect-video rounded-lg border-2 border-dashed border-gray-300 hover:border-blue-500 cursor-pointer overflow-hidden <?= isset($post['image_url']) && $post['image_url'] ? 'hidden' : '' ?>"
                         onclick="document.getElementById('image').click()">
                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2">Click to upload featured image</p>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($post['image_url']) && $post['image_url']): ?>
                        <div id="currentImageContainer">
                            <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                 id="currentImage"
                                 class="aspect-video rounded-lg object-cover cursor-pointer"
                                 onclick="document.getElementById('image').click()">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Split Screen Editor -->
            <div class="grid md:grid-cols-2 gap-4">
                <!-- Markdown Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Write (Markdown)</label>
                    <div class="border border-gray-300 rounded-lg bg-white overflow-hidden">
                        <div class="bg-gray-100 border-b border-gray-300 px-4 py-2 flex gap-2">
                            <button type="button" onclick="insertMarkdown('**', '**')" class="px-2 py-1 hover:bg-gray-200 rounded font-bold" title="Bold">B</button>
                            <button type="button" onclick="insertMarkdown('*', '*')" class="px-2 py-1 hover:bg-gray-200 rounded italic" title="Italic">I</button>
                            <button type="button" onclick="insertMarkdown('# ', '')" class="px-2 py-1 hover:bg-gray-200 rounded" title="Heading">#</button>
                            <button type="button" onclick="insertMarkdown('- ', '')" class="px-2 py-1 hover:bg-gray-200 rounded" title="List">•</button>
                            <button type="button" onclick="insertMarkdown('[', '](url)')" class="px-2 py-1 hover:bg-gray-200 rounded" title="Link">🔗</button>
                            <button type="button" onclick="insertMarkdown('![alt](', ')')" class="px-2 py-1 hover:bg-gray-200 rounded" title="Image">🖼️</button>
                            <button type="button" onclick="insertMarkdown('```\n', '\n```')" class="px-2 py-1 hover:bg-gray-200 rounded font-mono text-sm" title="Code Block">&lt;/&gt;</button>
                        </div>
                        <textarea id="markdown" 
                                  name="content"
                                  class="w-full h-[500px] p-4 font-mono text-sm focus:outline-none resize-none"
                                  placeholder="Write your post content in markdown..."
                                  onkeyup="updatePreview()"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Preview -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Preview</label>
                    <div id="preview" class="border border-gray-300 rounded-lg bg-white h-[556px] p-4 overflow-y-auto prose prose-sm max-w-none">
                        <p class="text-gray-400">Preview will appear here...</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <a href="/Blog/dashboard.php" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                    <?= $editing ? 'Update Post' : 'Publish Post' ?>
                </button>
            </div>
        </form>
    </main>

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
            preview.innerHTML = '<p class="text-gray-400">Preview will appear here...</p>';
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
        
        // Reset cursor position
        textarea.focus();
        const newCursor = start + prefix.length + selection.length + suffix.length;
        textarea.setSelectionRange(newCursor, newCursor);
    }

    // Initialize preview on load
    updatePreview();

    let draftId = null;
    let draftTimeout = null;
    const AUTOSAVE_DELAY = 3000; // 3 seconds

    function autosaveDraft() {
        clearTimeout(draftTimeout);
        draftTimeout = setTimeout(async () => {
            const title = document.querySelector('input[name="title"]').value;
            const content = document.getElementById('markdown').value;
            const topics = document.getElementById('topicsInput').value;

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
                    updateSaveStatus(`Draft saved at ${data.timestamp}`);
                }
            } catch (err) {
                console.error('Error saving draft:', err);
                updateSaveStatus('Failed to save draft', true);
            }
        }, AUTOSAVE_DELAY);
    }

    function updateSaveStatus(message, isError = false) {
        const status = document.getElementById('saveStatus');
        status.textContent = message;
        status.className = `text-sm ${isError ? 'text-red-500' : 'text-green-500'}`;
        setTimeout(() => {
            status.textContent = '';
        }, 3000);
    }

    // Add event listeners for autosave
    document.querySelector('input[name="title"]').addEventListener('input', autosaveDraft);
    document.getElementById('markdown').addEventListener('input', autosaveDraft);
    document.getElementById('topicsInput').addEventListener('change', autosaveDraft);

    // Add save status display
    const saveStatusDiv = document.createElement('div');
    saveStatusDiv.innerHTML = `
        <div class="fixed bottom-4 right-4 p-4 bg-white rounded-lg shadow-lg">
            <span id="saveStatus"></span>
        </div>
    `;
    document.body.appendChild(saveStatusDiv);

    // Modify form submission to handle drafts
    document.getElementById('postForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        <?php if ($isDraft): ?>
        formData.append('draft_id', '<?= $post['id'] ?>');
        <?php endif; ?>

        try {
            const response = await fetch('/Blog/api/save_post.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                <?php if ($isDraft): ?>
                // Delete draft after successful publish
                await fetch('/Blog/api/delete_draft.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ draft_id: <?= $post['id'] ?> })
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

    // Image preview function
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

    // Topic management
    let selectedTopics = new Set(<?= json_encode($selectedTopics) ?>);

    function toggleTopic(button, topic) {
        if (selectedTopics.has(topic)) {
            selectedTopics.delete(topic);
            button.classList.remove('bg-blue-50', 'border-blue-500', 'text-blue-700');
            button.classList.add('border-gray-300');
        } else {
            selectedTopics.add(topic);
            button.classList.add('bg-blue-50', 'border-blue-500', 'text-blue-700');
            button.classList.remove('border-gray-300');
        }
        updateTopicsInput();
    }

    function addCustomTopic() {
        const input = document.getElementById('newTopic');
        const topic = input.value.trim();
        
        if (topic && !selectedTopics.has(topic)) {
            // Create new topic button
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'px-3 py-1 rounded-full text-sm border bg-blue-50 border-blue-500 text-blue-700';
            button.textContent = topic;
            button.onclick = () => toggleTopic(button, topic);
            
            // Add to selected topics
            selectedTopics.add(topic);
            document.getElementById('selectedTopics').appendChild(button);
            updateTopicsInput();
            
            // Clear input
            input.value = '';
        }
    }

    function updateTopicsInput() {
        document.getElementById('topicsInput').value = Array.from(selectedTopics).join(',');
    }
    </script>
</body>
</html>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php
// notes.php - DB-backed notes (single "README" note)
require_once 'config.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$message = '';
$error = '';

// Always use the first note row (single-note setup)
$noteId = null;

// Get the current note row (first row)
try {
    $stmt = $pdo->query("SELECT id, title, content, updated_at FROM site_notes ORDER BY id ASC LIMIT 1");
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note) {
        // If somehow empty, create it
        $ins = $pdo->prepare("INSERT INTO site_notes (title, content) VALUES (:title, :content) RETURNING id");
        $ins->execute([':title' => 'Website Notes', ':content' => '']);
        $noteId = (int)$ins->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, title, content, updated_at FROM site_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $noteId = (int)$note['id'];
} catch (Throwable $e) {
    $error = "Database error while loading notes: " . $e->getMessage();
    $note = ['title' => 'Website Notes', 'content' => '', 'updated_at' => null];
}

// Save updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $title = trim($_POST['title'] ?? 'Website Notes');
    $content = $_POST['content'] ?? '';

    if ($title === '') $title = 'Website Notes';

    try {
        // If you did NOT add the trigger, this sets updated_at manually:
        $sql = "UPDATE site_notes
                SET title = :title,
                    content = :content,
                    updated_at = now()
                WHERE id = :id";
        $upd = $pdo->prepare($sql);
        $upd->execute([
            ':title' => $title,
            ':content' => $content,
            ':id' => $noteId
        ]);

        $message = "Saved!";

        // Reload fresh content
        $stmt = $pdo->prepare("SELECT id, title, content, updated_at FROM site_notes WHERE id = :id");
        $stmt->execute([':id' => $noteId]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {
        $error = "Database error while saving notes: " . $e->getMessage();
    }
}

$updatedText = '';
if (!empty($note['updated_at'])) {
    try {
        $dt = new DateTime($note['updated_at']);
        $updatedText = $dt->format('Y-m-d H:i');
    } catch (Throwable $e) {
        $updatedText = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Website Notes</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .wrap { max-width: 980px; margin: 0 auto; padding: 16px; }
    .topbar { display:flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap; }
    textarea { width: 100%; min-height: 65vh; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    input[type="text"]{ width: 100%; max-width: 520px; }
    .msg { margin: 12px 0; padding: 10px; border-radius: 6px; }
    .ok { background: #e8f5e9; border: 1px solid #b7e1b9; }
    .err { background: #ffe6e6; border: 1px solid #ffb3b3; }
    .btn { padding: 8px 12px; cursor: pointer; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div>
        <h1>Website Notes / README</h1>
        <p><a href="index.php">← Back to Inventory</a></p>
      </div>
      <div>
        <?php if ($updatedText): ?>
          <small><strong>Last updated:</strong> <?= h($updatedText) ?></small>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="msg ok"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="msg err"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="title"><strong>Title</strong></label><br>
      <input id="title" type="text" name="title" value="<?= h($note['title'] ?? 'Website Notes') ?>"><br><br>

      <label for="content"><strong>Notes</strong></label><br>
      <textarea id="content" name="content" placeholder="Type your notes here..."><?= h($note['content'] ?? '') ?></textarea><br>

      <button class="btn" type="submit">Save Notes</button>
    </form>
  </div>
</body>
</html>

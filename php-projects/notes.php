<?php
// notes.php - simple "README" notes page stored in a local file

$notesFile = __DIR__ . '/website_notes.md'; // saves next to this file
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    // Save safely
    file_put_contents($notesFile, $content, LOCK_EX);
    $message = 'Saved!';
}

// Load existing content
$current = file_exists($notesFile) ? file_get_contents($notesFile) : '';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Website Notes</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .wrap { max-width: 900px; margin: 0 auto; padding: 16px; }
    textarea { width: 100%; min-height: 65vh; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .bar { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
    .msg { color: #0a7a0a; font-weight: 600; }
    .btn { padding: 8px 12px; cursor: pointer; }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Website Notes / README</h1>
      <p>Write anything you want here (setup notes, to-do list, how the site works).</p>
      <p><a href="index.php">← Back to Inventory</a></p>
    </header>

    <form method="POST">
      <div class="bar">
        <button class="btn" type="submit">Save Notes</button>
        <?php if ($message): ?><span class="msg"><?= h($message) ?></span><?php endif; ?>
      </div>

      <textarea name="content" placeholder="Type your notes here..."><?= h($current) ?></textarea>
    </form>
  </div>
</body>
</html>

<?php
// index.php
require_once 'config.php';

// -------------------------
// Handle row update (POST)
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Required
    $id = (int)($_POST['id'] ?? 0);

    // Editable fields (sanitize/normalize)
    $name            = trim($_POST['name'] ?? '');
    $category_id     = ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'];
    $location_id     = ($_POST['location_id'] ?? '') === '' ? null : (int)$_POST['location_id'];
    $quantity        = (int)($_POST['quantity'] ?? 0);
    $condition       = trim($_POST['condition'] ?? '');
    $estimated_value = trim($_POST['estimated_value'] ?? '');
    $notes           = trim($_POST['notes'] ?? '');

    // Convert empty value to NULL, otherwise numeric
    $estimated_value = ($estimated_value === '') ? null : (float)$estimated_value;

    // Basic validation (optional but helpful)
    if ($id > 0 && $name !== '') {
        $updateSql = "
            UPDATE items
            SET
                name = :name,
                category_id = :category_id,
                location_id = :location_id,
                quantity = :quantity,
                condition = :condition,
                estimated_value = :estimated_value,
                notes = :notes
            WHERE id = :id
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([
            ':name'            => $name,
            ':category_id'     => $category_id,
            ':location_id'     => $location_id,
            ':quantity'        => $quantity,
            ':condition'       => $condition,
            ':estimated_value' => $estimated_value,
            ':notes'           => $notes,
            ':id'              => $id,
        ]);
    }

    // PRG pattern: avoid resubmitting on refresh
    header("Location: index.php");
    exit;
}

// -------------------------
// Load dropdown options
// -------------------------
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$locations  = $pdo->query("SELECT id, name FROM locations  ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// -------------------------
// Query: items + ids + names
// -------------------------
$sql = "
    SELECT
        i.id,
        i.name,
        i.category_id,
        c.name AS category,
        i.location_id,
        l.name AS location,
        i.quantity,
        i.condition,
        i.estimated_value,
        i.notes,
        i.created_at
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN locations  l ON i.location_id  = l.id
    ORDER BY i.created_at DESC, i.name;
";
$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Inventory</title>
    <link rel="stylesheet" href="styles.css">
    <style>
      /* light inline-edit styling (optional) */
      td input[type="text"], td input[type="number"], td select, td textarea {
        width: 100%;
        box-sizing: border-box;
      }
      td textarea { min-height: 40px; }
      .save-btn { padding: 6px 10px; cursor: pointer; }
      .readonly { background: #f3f3f3; }
    </style>
</head>
<body>
<header>
    <h1>Household Inventory</h1>
    <p>Items stored in PostgreSQL (mydb.items).</p>
</header>

<main>
    <section class="controls">
        <label for="searchInput">Search:</label>
        <input type="text" id="searchInput" placeholder="Filter by name, category, or location...">
    </section>

    <section class="table-container">
        <table id="itemsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Qty</th>
                    <th>Condition</th>
                    <th>Value</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Save</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($items) === 0): ?>
                <tr>
                    <td colspan="10" class="no-data">No items found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <!-- Each row is its own form so Save only updates that row -->
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= h($row['id']) ?>">

                            <!-- Read-only: ID -->
                            <td class="readonly"><?= h($row['id']) ?></td>

                            <!-- Editable: name -->
                            <td>
                                <input type="text" name="name" value="<?= h($row['name']) ?>" required>
                            </td>

                            <!-- Editable: category dropdown -->
                            <td>
                                <select name="category_id">
                                    <option value="">— None —</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= h($c['id']) ?>"
                                            <?= ((int)$row['category_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                                            <?= h($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <!-- Editable: location dropdown -->
                            <td>
                                <select name="location_id">
                                    <option value="">— None —</option>
                                    <?php foreach ($locations as $l): ?>
                                        <option value="<?= h($l['id']) ?>"
                                            <?= ((int)$row['location_id'] === (int)$l['id']) ? 'selected' : '' ?>>
                                            <?= h($l['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <!-- Editable: quantity -->
                            <td>
                                <input type="number" name="quantity" min="0" value="<?= h($row['quantity']) ?>">
                            </td>

                            <!-- Editable: condition -->
                            <td>
                                <input type="text" name="condition" value="<?= h($row['condition'] ?? '') ?>" placeholder="e.g., Good">
                            </td>

                            <!-- Editable: estimated_value -->
                            <td>
                                <input type="number" name="estimated_value" step="0.01" min="0"
                                       value="<?= h($row['estimated_value'] ?? '') ?>"
                                       placeholder="e.g., 25.00">
                            </td>

                            <!-- Editable: notes -->
                            <td>
                                <textarea name="notes" placeholder="Notes..."><?= h($row['notes'] ?? '') ?></textarea>
                            </td>

                            <!-- Read-only: created_at -->
                            <td class="readonly">
                                <?php
                                if (!empty($row['created_at'])) {
                                    $dt = new DateTime($row['created_at']);
                                    echo h($dt->format('Y-m-d H:i'));
                                }
                                ?>
                            </td>

                            <td>
                                <button class="save-btn" type="submit">Save</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script src="script.js"></script>
</body>
</html>

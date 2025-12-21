<?php
// index.php
require_once 'config.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// -------------------------
// Load dropdown options
// -------------------------
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$locations  = $pdo->query("SELECT id, name FROM locations  ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// -------------------------
// Handle actions (POST)
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Helper: normalize numeric input to NULL/float
    $numOrNull = function($raw) {
        $raw = trim((string)$raw);
        return ($raw === '') ? null : (float)$raw;
    };

    // -------- ADD --------
    if ($action === 'add') {
        $name            = trim($_POST['name'] ?? '');
        $category_id     = ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'];
        $location_id     = ($_POST['location_id'] ?? '') === '' ? null : (int)$_POST['location_id'];
        $quantity        = (int)($_POST['quantity'] ?? 0);
        $condition       = trim($_POST['condition'] ?? '');
        $estimated_value = $numOrNull($_POST['estimated_value'] ?? '');
        $sold_price      = $numOrNull($_POST['sold_price'] ?? '');
        $notes           = trim($_POST['notes'] ?? '');

        if ($name !== '') {
            $insertSql = "
                INSERT INTO items (name, category_id, location_id, quantity, condition, estimated_value, sold_price, notes)
                VALUES (:name, :category_id, :location_id, :quantity, :condition, :estimated_value, :sold_price, :notes)
            ";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                ':name'            => $name,
                ':category_id'     => $category_id,
                ':location_id'     => $location_id,
                ':quantity'        => $quantity,
                ':condition'       => $condition,
                ':estimated_value' => $estimated_value,
                ':sold_price'      => $sold_price,
                ':notes'           => $notes,
            ]);
        }

        header("Location: index.php");
        exit;
    }

    // ------ UPDATE -------
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);

        $name            = trim($_POST['name'] ?? '');
        $category_id     = ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'];
        $location_id     = ($_POST['location_id'] ?? '') === '' ? null : (int)$_POST['location_id'];
        $quantity        = (int)($_POST['quantity'] ?? 0);
        $condition       = trim($_POST['condition'] ?? '');
        $estimated_value = $numOrNull($_POST['estimated_value'] ?? '');
        $sold_price      = $numOrNull($_POST['sold_price'] ?? '');
        $notes           = trim($_POST['notes'] ?? '');

        $shipping_service = trim($_POST['shipping_service'] ?? '');
        $shipping_cost    = ($_POST['shipping_cost'] ?? '') === '' ? null : (float)$_POST['shipping_cost'];

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
                sold_price = :sold_price,
                shipping_service = :shipping_service,
                shipping_cost = :shipping_cost,
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
                ':sold_price'      => $sold_price,
                ':notes'           => $notes,
                ':id'              => $id,
                ':shipping_service' => $shipping_service,
                ':shipping_cost'    => $shipping_cost,
            ]);
        }

        header("Location: index.php");
        exit;
    }

    // ------ DELETE -------
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $del = $pdo->prepare("DELETE FROM items WHERE id = :id");
            $del->execute([':id' => $id]);
        }

        header("Location: index.php");
        exit;
    }
}

// -------------------------
// Query items
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
    i.sold_price,
    i.shipping_service,
    i.shipping_cost,
    i.notes,
    i.created_at
FROM items i
LEFT JOIN categories c ON i.category_id = c.id
LEFT JOIN locations  l ON i.location_id  = l.id
ORDER BY i.created_at DESC, i.name;
";
$items = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <nav style="margin-top:10px;">
    <a href="notes.php">📌 Website Notes / README</a>
    </nav>

    <meta charset="UTF-8">
    <title>Household Inventory</title>
    <link rel="stylesheet" href="styles.css">
    <style>
      td input[type="text"], td input[type="number"], td select, td textarea { width: 100%; box-sizing: border-box; }
      td textarea { min-height: 40px; }
      .readonly { background: #f3f3f3; }
      .btn { padding: 6px 10px; cursor: pointer; }
      .btn-danger { background: #ffe6e6; border: 1px solid #ffb3b3; }
      .btn-save { background: #e8f5e9; border: 1px solid #b7e1b9; }
      .add-form { margin: 16px 0; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
      .add-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 10px; }
      .add-grid label { display: block; font-size: 0.9rem; margin-bottom: 4px; }
      .add-grid input, .add-grid select, .add-grid textarea { width: 100%; }
      .add-actions { margin-top: 10px; }
    </style>
</head>
<body>
<header>
    <h1>Household Inventory</h1>
    <p>Items stored in PostgreSQL (mydb.items).</p>
</header>

<main>
    <!-- ADD ITEM FORM -->
    <section class="add-form">
        <h2>Add Item</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="add-grid">
                <div>
                    <label>Item Name</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">— None —</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= h($c['id']) ?>"><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Location</label>
                    <select name="location_id">
                        <option value="">— None —</option>
                        <?php foreach ($locations as $l): ?>
                            <option value="<?= h($l['id']) ?>"><?= h($l['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Qty</label>
                    <input type="number" name="quantity" min="0" value="1">
                </div>

                <div>
                    <label>Condition</label>
                    <input type="text" name="condition" placeholder="e.g., Good">
                </div>

                <div>
                    <label>Estimated Value</label>
                    <input type="number" name="estimated_value" step="0.01" min="0" placeholder="e.g., 25.00">
                </div>

                <div>
                    <label>Sold Price</label>
                    <input type="number" name="sold_price" step="0.01" min="0" placeholder="e.g., 20.00">
                </div>

                <div style="grid-column: span 2;">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Optional notes..."></textarea>
                </div>
            </div>

            <div class="add-actions">
                <button class="btn btn-save" type="submit">Add Item</button>
            </div>
        </form>
    </section>

    <!-- SEARCH -->
    <section class="controls">
        <label for="searchInput">Search:</label>
        <input type="text" id="searchInput" placeholder="Filter by name, category, or location...">
    </section>

    <!-- TABLE -->
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
                    <th>Est. Value</th>
                    <th>Sold Price</th>
                    <th>Notes</th>
                    <th>Created At</th>
                    <th>Save</th>
                    <th>Delete</th>
                    <th>Shipping Service</th>
                    <th>Ship Cost</th>

                </tr>
            </thead>
            <tbody>
            <?php if (count($items) === 0): ?>
                <tr>
                    <td colspan="12" class="no-data">No items found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <!-- UPDATE FORM -->
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= h($row['id']) ?>">

                            <td class="readonly"><?= h($row['id']) ?></td>

                            <td><input type="text" name="name" value="<?= h($row['name']) ?>" required></td>

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
                            
                            <td>
                                <input type="text"
                                    name="shipping_service"
                                    value="<?= h($row['shipping_service'] ?? '') ?>"
                                    placeholder="USPS Ground Advantage">
                            </td>

                            <td>
                                <input type="number"
                                    name="shipping_cost"
                                    step="0.01"
                                    min="0"
                                    value="<?= h($row['shipping_cost'] ?? '') ?>"
                                    placeholder="e.g. 6.00">
                            </td>



                            <td><input type="number" name="quantity" min="0" value="<?= h($row['quantity']) ?>"></td>

                            <td><input type="text" name="condition" value="<?= h($row['condition'] ?? '') ?>"></td>

                            <td>
                                <input type="number" name="estimated_value" step="0.01" min="0"
                                       value="<?= h($row['estimated_value'] ?? '') ?>">
                            </td>

                            <td>
                                <input type="number" name="sold_price" step="0.01" min="0"
                                       value="<?= h($row['sold_price'] ?? '') ?>">
                            </td>

                            <td><textarea name="notes"><?= h($row['notes'] ?? '') ?></textarea></td>

                            <td class="readonly">
                                <?php
                                if (!empty($row['created_at'])) {
                                    $dt = new DateTime($row['created_at']);
                                    echo h($dt->format('Y-m-d H:i'));
                                }
                                ?>
                            </td>

                            <td><button class="btn btn-save" type="submit">Save</button></td>
                        </form>

                        <!-- DELETE FORM -->
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this item? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>

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

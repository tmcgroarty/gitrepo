<?php
// index.php
require_once 'config.php';

// Simple query to grab items plus category + location names
$sql = "
    SELECT
        i.id,
        i.name,
        c.name AS category,
        l.name AS location,
        i.quantity,
        i.color,
        i.size,
        i.condition,
        i.estimated_value,
        i.notes
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN locations  l ON i.location_id  = l.id
    ORDER BY i.name;
";

$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Inventory</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Household Inventory</h1>
    <p>Viewing items from your PostgreSQL database.</p>
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
                    <th>Color</th>
                    <th>Size</th>
                    <th>Condition</th>
                    <th>Value</th>
                    <th>Notes</th>
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
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['location'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['color'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['size'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['condition'] ?? '') ?></td>
                        <td>
                            <?php
                            if ($row['estimated_value'] !== null) {
                                echo '$' . number_format((float)$row['estimated_value'], 2);
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['notes'] ?? '') ?></td>
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

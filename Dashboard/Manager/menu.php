<?php
// menu.php
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_item'])) {
    $id = (int) $_POST['id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = (float) $_POST['price'];
    $available = isset($_POST['available']) ? 1 : 0;

    $query = "UPDATE menu_items SET name='$name', description='$description', price=$price, available=$available WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $alertMessage = "Item updated successfully.";
    } else {
        $alertMessage = "Error: " . mysqli_error($conn);
    }
}

// Fetch all items with category names
$items = [];
$result = mysqli_query($conn, "SELECT mi.*, mc.name as category_name 
                               FROM menu_items mi 
                               JOIN menu_categories mc ON mi.category_id = mc.id 
                               ORDER BY mc.name, mi.name");
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
?>
<div class="subpage-header">
    <h2>Menu Management</h2>
    <p>Update existing menu items (prices, descriptions, availability).</p>
</div>

<?php if (!empty($alertMessage)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($alertMessage) ?></div>
<?php endif; ?>

<table border="1" cellpadding="8">
    <thead>
        <tr><th>Category</th><th>Name</th><th>Description</th><th>Price</th><th>Available</th><th>Actions</th> </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <tr>
                <td><?= htmlspecialchars($item['category_name']) ?></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required></td>
                <td><textarea name="description" rows="2"><?= htmlspecialchars($item['description']) ?></textarea></td>
                <td><input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required></td>
                <td><input type="checkbox" name="available" <?= $item['available'] ? 'checked' : '' ?>></td>
                <td><button type="submit" name="edit_item">Update</button></td>
            </tr>
        </form>
    <?php endforeach; ?>
    </tbody>
</table>
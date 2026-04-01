<?php
// tables.php
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_table'])) {
    $id = (int) $_POST['id'];
    $capacity = (int) $_POST['capacity'];
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE tables SET capacity=$capacity, section='$section', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $alertMessage = "Table updated successfully.";
    } else {
        $alertMessage = "Error: " . mysqli_error($conn);
    }
}

$tables = [];
$result = mysqli_query($conn, "SELECT * FROM tables ORDER BY table_number");
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}
?>
<div class="subpage-header">
    <h2>Table Management</h2>
    <p>Update table capacity, section, and status.</p>
</div>

<?php if (!empty($alertMessage)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($alertMessage) ?></div>
<?php endif; ?>

<table border="1" cellpadding="8">
    <thead>
        <tr><th>Number</th><th>Capacity</th><th>Section</th><th>Status</th><th>Actions</th> </thead>
    <tbody>
    <?php foreach ($tables as $t): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <tr>
                <td><?= $t['table_number'] ?></td>
                <td><input type="number" name="capacity" value="<?= $t['capacity'] ?>" required></td>
                <td>
                    <select name="section">
                        <option value="AC" <?= $t['section'] == 'AC' ? 'selected' : '' ?>>AC</option>
                        <option value="Non AC" <?= $t['section'] == 'Non AC' ? 'selected' : '' ?>>Non AC</option>
                        <option value="Outdoor" <?= $t['section'] == 'Outdoor' ? 'selected' : '' ?>>Outdoor</option>
                    </select>
                </td>
                <td>
                    <select name="status">
                        <option value="available" <?= $t['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="occupied" <?= $t['status'] == 'occupied' ? 'selected' : '' ?>>Occupied</option>
                        <option value="reserved" <?= $t['status'] == 'reserved' ? 'selected' : '' ?>>Reserved</option>
                        <option value="cleaning" <?= $t['status'] == 'cleaning' ? 'selected' : '' ?>>Cleaning</option>
                    </select>
                </td>
                <td><button type="submit" name="edit_table">Update</button></td>
            </tr>
        </form>
    <?php endforeach; ?>
    </tbody>
</table>
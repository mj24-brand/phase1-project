<?php  include("auth.php");?>
<?php include("../includes/header.php"); ?>

<?php
include("../config/db.php");

$id = $_GET['id'];
$c = $conn->query("SELECT * FROM customers WHERE id=$id")->fetch_assoc();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Customer Profile</h2>
    <a href="customers.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">

    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card shadow border-0 text-center">
            <div class="card-body">

                <i class="fa fa-user-circle fa-5x text-primary mb-3"></i>

                <h4><?= $c['name'] ?></h4>
                <p class="text-muted"><?= $c['email'] ?></p>

                <span class="badge bg-success">
                    <?= $c['loyalty_points'] ?> Points
                </span>

                <hr>

                <p><strong>Phone:</strong> <?= $c['phone'] ?></p>
                <p><strong>Address:</strong><br><?= $c['address'] ?></p>

            </div>
        </div>
    </div>

    <!-- Order History -->
    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-body">

                <h5 class="mb-3">Order History</h5>

                <div class="table-responsive">
                    <table class="table table-hover">

                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                        $orders = $conn->query("SELECT * FROM orders WHERE customer_id=$id");

                        while($o = $orders->fetch_assoc()){
                        ?>
                            <tr>
                                <td>#<?= $o['id'] ?></td>
                                <td>₹<?= $o['total_amount'] ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $o['status'] ?>
                                    </span>
                                </td>
                                <td><?= $o['created_at'] ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<?php include("../includes/footer.php"); ?>
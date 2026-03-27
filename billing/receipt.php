<?php
include "../config/db.php";

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM invoices WHERE invoice_number='$id'");
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
        }

        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
        }

        .text-primary-accent {
            color: #af101a;
        }

        .bg-primary-accent {
            background: #af101a;
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body class="py-4">

    <div class="invoice-container rounded border">

        <!-- HEADER -->
        <div class="text-center py-5 border-bottom bg-light">
            <img src="../assets/images/image.png">
            <h1 class="h3 fw-bold text-primary-accent">Hotel Mangalore International</h1>
        </div>

        <div class="p-5">

            <!-- TOP INFO -->
            <div class="row mb-4">
                <div class="col-sm-8">
                    <h5 class="fw-bold">Invoice</h5>
                    <p class="small mb-0">
                        Date: <b><?php echo date("d-m-Y"); ?></b><br>
                        Invoice No: <b>INV-#<?php echo $data['invoice_number']; ?></b><br>
                        Customer: <b><?php echo $data['guest_name']; ?></b>
                    </p>
                </div>

                <div class="col-sm-4 text-end">
                    <p class="small text-primary-accent fw-bold">Tax Invoice</p>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $itemRes = mysqli_query($conn, "SELECT * FROM invoice_items WHERE invoice_number='$id'");

                    while ($item = mysqli_fetch_assoc($itemRes)) {
                        ?>
                        <tr>
                            <td><?php echo $item['item_name']; ?></td>
                            <td class="text-center"><?php echo $item['qty']; ?></td>
                            <td class="text-end">₹<?php echo $item['price']; ?></td>
                            <td class="text-end">
                                ₹<?php echo $item['qty'] * $item['price']; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="row justify-content-end">
                <div class="col-md-5">

                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <b>₹<?php echo number_format($data['subtotal'], 2); ?></b>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Tax:</span>
                        <b>₹<?php echo number_format($data['tax'], 2); ?></b>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Discount:</span>
                        <b>₹<?php echo number_format($data['discount'], 2); ?></b>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <h5>Total:</h5>
                        <h5 class="text-primary-accent">
                            ₹<?php echo number_format($data['total'], 2); ?>
                        </h5>
                    </div>

                    <p class="mt-3">
                        Payment Mode: <b><?php echo $data['payment_mode']; ?></b>
                    </p>

                </div>
            </div>

            <!-- PRINT BUTTON -->
            <div class="text-center mt-5 no-print">
                <button class="btn bg-primary-accent px-4" onclick="window.print()">
                    Print Bill
                </button>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="bg-light text-center p-3">
            <small>Thank you for your visit</small>
        </div>

    </div>

</body>

</html>
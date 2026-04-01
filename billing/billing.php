<?php
include "../config/db.php";
$result = mysqli_query($conn, "SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");

if ($row = mysqli_fetch_assoc($result)) {
    $nextInvoice = $row['invoice_number'] + 1;
} else {
    $nextInvoice = 1001;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Billing &amp; Invoices - Hotel Mangalore International</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="billing.css">
</head>

<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar d-none d-md-flex flex-column">
        <div class="px-4 mb-4 d-flex align-items-center gap-2">
            <div>
                <div class="fw-bold brand-color small text-uppercase">The Sovereign</div>
                <div class="text-muted" style="font-size: 10px;">Premium Concierge</div>
            </div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="#"> Dashboard</a>
            <a class="nav-link active" href="#">Billing</a>
            <a class="nav-link" href="#">Inventory</a>
            <a class="nav-link" href="#"> Suppliers</a>
            <a class="nav-link" href="#"> Purchases</a>
            <a class="nav-link" href="#">Reservations</a>
        </nav>
        <div class="mt-auto p-3 border-top">
            <button class="btn bg-brand w-100 mb-3 fw-bold">New Invoice</button>
            <a class="nav-link py-1 px-2 m-0 text-muted" href="#"><span class="material-symbols-outlined">help</span>
                Help Center</a>
            <a class="nav-link py-1 px-2 m-0 text-muted" href="#"><span class="material-symbols-outlined">logout</span>
                Logout</a>
        </div>
    </div>
    <!-- Header -->
    <header class="top-navbar fixed-top py-3 px-4 d-flex justify-content-between align-items-center">
        <h1 class="h5 fw-bold brand-color mb-0">Hotel Mangalore International</h1>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <div class="small fw-bold">Admin Manager</div>
            </div>
        </div>
    </header>
    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Billing Console Formm -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <span class="text-muted text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 1px;">Invoice Engine</span>
                            <h2 class="fw-bold mb-0">Billing Console</h2>
                        </div>
                        <div class="badge bg-white text-dark border p-2 px-3 rounded-pill fw-medium">
                            <p id="date"></p>
                            <script>
                                const today = new Date();
                                let day = today.getDate();
                                let month = today.getMonth() + 1;
                                let year = today.getFullYear();
                                const formattedDate = day + "-" + month + "-" + year;
                                document.getElementById("date").innerHTML = formattedDate;
                            </script>
                        </div>
                        <div class="badge bg-white text-dark border p-2 px-3 rounded-pill fw-medium">
                            <p class="mb-0">INV-#
                                <?php echo $nextInvoice; ?>
                            </p>
                        </div>
                    </div>
                    <!-- Form  -->
                    <form action="processpayment.php" class="card border-0 shadow-sm rounded-4 overflow-hidden"
                        method="POST">
                        <div class="card-body p-4 border-bottom bg-light bg-opacity-50">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">Customer Name</label>
                                    <input class="form-control border-0 border-bottom rounded-0 bg-transparent px-0"
                                        name="guest_name" placeholder="Enter guest name" type="text" />
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="small text-muted text-uppercase fw-bold">
                                            <th style="width: 50%;">Item</th>
                                            <th class="text-end" style="width: 15%;">Qty</th>
                                            <th class="text-end" style="width: 20%;">Price (₹)</th>
                                            <th class="text-center" style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTable">
                                    <tr>
                                        <td><input name="item[]" class="form-control" type="text" placeholder="Item">
                                        </td>
                                        <td><input name="qty[]" class="qty form-control text-end" type="number"
                                                value="1"></td>
                                        <td><input name="price[]" class="price form-control text-end" type="number"
                                                value="0"></td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="addRow" class="btn btn-sm btn-outline-danger mt-2">
                                + Add Row
                            </button>
                        </div>
                        <div class="card-footer p-4 bg-light border-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">Payment Mode</label>
                                    <select class="form-select border-0 shadow-sm" name="payment_mode">
                                        <option value="UPI">UPI Transfer</option>
                                        <option value="Card">Credit/Debit Card</option>
                                        <option selected="" value="Cash">Cash</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">Tax (%)</label>
                                    <div class="input-group shadow-sm rounded">
                                        <input class="form-control border-0" name="tax" type="number" value="12" />
                                        <span class="input-group-text border-0 bg-white">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">Discount Amount</label>
                                    <div class="input-group shadow-sm rounded">
                                        <span class="input-group-text border-0 bg-white">₹</span>
                                        <input class="form-control border-0" name="discount" placeholder="0.00"
                                            type="number" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-dark p-3 d-flex justify-content-between align-items-center">
                            <div class="text-white small opacity-75 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-success">verified_user</span>
                                <span>Digitally signed &amp; with standards compliant.</span>
                            </div>
                            <button class="btn bg-brand btn-lg px-4 fw-bold shadow-lg d-flex align-items-center gap-2"
                                type="submit">
                                <span class="material-symbols-outlined">print</span>
                                GENERATE &amp; PRINT BILL
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Right Sidebar: Summary -->
                <div class="col-lg-4">
                    <div class="card summary-card border-0 shadow rounded-4 mb-4 overflow-hidden position-sticky"
                        style="top: 5rem;">
                        <div class="card-body p-4">
                            <p class="small text-uppercase fw-bold opacity-75 mb-4">Settlement Summary</p>
                            <div class="d-flex justify-content-between mb-2 opacity-75 small">
                                <span id="subtotal">₹ 0.00</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 opacity-75 small">
                                <span id="taxAmount">₹ 0.00</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 opacity-75 small">
                                <span id="discountAmount">₹ 0.00</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 opacity-75 small">
                                <h2 id="finalTotal">₹ 0.00</h2>
                            </div>
                        </div>

                    </div>
                    <div class="bg-white text-dark p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h6 fw-bold text-uppercase text-muted mb-0">Recent Invoices</h3>
                            <a class="small fw-bold text-danger text-decoration-none" href="#">VIEW ALL</a>
                        </div>
                        <div class="vstack gap-3">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM invoices ORDER BY id DESC LIMIT 5");

                            while ($r = mysqli_fetch_assoc($res)) {
                                ?>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light p-2 rounded text-muted">
                                            <span class="material-symbols-outlined">description</span>
                                        </div>
                                        <div>
                                            <div class="small fw-bold">
                                                <?php echo $r['guest_name']; ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 10px;">
                                                #
                                                <?php echo $r['invoice_number']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-bold">₹
                                            <?php echo $r['total']; ?>
                                        </div>
                                        <span class="badge rounded-pill bg-success-subtle text-success small"
                                            style="font-size: 9px;">PAID</span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="mt-4 pt-4 border-top">
                            <p class="small fw-bold text-muted text-uppercase mb-3" style="font-size: 10px;">Daily
                                Revenue Pace</p>
                            <div class="d-flex align-items-end gap-1" style="height: 30px;">
                                <div class="flex-fill bg-danger bg-opacity-10" style="height: 40%;"></div>
                                <div class="flex-fill bg-danger bg-opacity-10" style="height: 60%;"></div>
                                <div class="flex-fill bg-danger bg-opacity-10" style="height: 80%;"></div>
                                <div class="flex-fill bg-danger" style="height: 100%;"></div>
                                <div class="flex-fill bg-danger bg-opacity-40" style="height: 70%;"></div>
                                <div class="flex-fill bg-danger bg-opacity-10" style="height: 30%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calc() {
            let qty = document.querySelectorAll(".qty");
            let price = document.querySelectorAll(".price");

            let subtotal = 0;
            qty.forEach((q, i) => {
                subtotal += q.value * price[i].value;
            });

            let tax = document.querySelector("[name='tax']").value || 0;
            let discount = document.querySelector("[name='discount']").value || 0;

            let taxAmt = subtotal * tax / 100;
            let total = subtotal + taxAmt - discount;

            document.getElementById("subtotal").innerText = "₹ " + subtotal;
            document.getElementById("taxAmount").innerText = "₹ " + taxAmt;
            document.getElementById("discountAmount").innerText = "₹ " + discount;
            document.getElementById("finalTotal").innerText = "₹ " + total;
        }

        document.querySelectorAll(".qty,.price,[name='tax'],[name='discount']")
            .forEach(e => e.addEventListener("input", calc));

        calc();
    </script>
    <script>
        document.getElementById("addRow").addEventListener("click", function () {

            let row = `<tr>
        <td><input name="item[]" class="form-control" type="text"></td>
        <td><input name="qty[]" class="qty form-control text-end" type="number" value="1"></td>
        <td><input name="price[]" class="price form-control text-end" type="number" value="0"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
        </td>
    </tr>`;

            document.getElementById("itemsTable").insertAdjacentHTML("beforeend", row);
        });

        // TO REMOVE ROW
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("removeRow")) {
                e.target.closest("tr").remove();
                calc();
            }
        });

        // FOR AUTOMATIC CALCULATION
        function calc() {
            let qty = document.querySelectorAll(".qty");
            let price = document.querySelectorAll(".price");

            let subtotal = 0;

            qty.forEach((q, i) => {
                subtotal += (q.value || 0) * (price[i].value || 0);
            });

            let tax = document.querySelector("[name='tax']").value || 0;
            let discount = document.querySelector("[name='discount']").value || 0;

            let taxAmt = subtotal * tax / 100;
            let total = subtotal + taxAmt - discount;

            document.getElementById("subtotal").innerText = "₹ " + subtotal.toFixed(2);
            document.getElementById("taxAmount").innerText = "₹ " + taxAmt.toFixed(2);
            document.getElementById("discountAmount").innerText = "₹ " + discount;
            document.getElementById("finalTotal").innerText = "₹ " + total.toFixed(2);
        }

        document.addEventListener("input", function (e) {
            if (e.target.classList.contains("qty") || e.target.classList.contains("price") || e.target.name == "tax" || e.target.name == "discount") {
                calc();
            }
        });

        calc();
    </script>
</body>

</html>
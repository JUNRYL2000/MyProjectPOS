<?php
// Database connection
$host = 'localhost'; // Change if necessary
$dbname = 'myprojectpos';
$username = 'root'; // Change if necessary
$password = 'root'; // Change if necessary

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch sales data
    $sales_query = $pdo->query("SELECT Sales_date, SaleTotal_amount FROM saletransaction");
    $sales_data = $sales_query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch delivery data
    $delivery_query = $pdo->query("SELECT Delivery_Date, Total_Amount FROM deliverytransaction");
    $delivery_data = $delivery_query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total number of customers
    $customer_query = $pdo->query("SELECT COUNT(*) AS total_customers FROM customer");
    $customer_data = $customer_query->fetch(PDO::FETCH_ASSOC)['total_customers'];

    // Fetch total users (admin and cashier)
    $user_query = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $user_data = $user_query->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Fetch total sales amount
    $total_sales_query = $pdo->query("SELECT SUM(SaleTotal_amount) AS total_sales_amount FROM saletransaction");
    $total_sales = $total_sales_query->fetch(PDO::FETCH_ASSOC)['total_sales_amount'];

    // Fetch total deliveries amount
    $total_deliveries_query = $pdo->query("SELECT SUM(Total_Amount) AS total_deliveries_amount FROM deliverytransaction");
    $total_deliveries = $total_deliveries_query->fetch(PDO::FETCH_ASSOC)['total_deliveries_amount'];

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card-icon {
            font-size: 50px;
            color: #4CAF50;
        }
        .dashboard-summary {
            text-align: center;
            padding: 20px;
        }
        .summary-icon {
            font-size: 2em;
            color: #007bff;
        }
        .summary-title {
            margin-top: 10px;
            font-weight: bold;
        }
        canvas {
            max-width: 100%;
            height: 400px;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Dashboard</h1>

        <!-- Summary section with icons -->
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-summary bg-light border rounded p-3">
                    <span class="summary-icon"><i class="bi bi-cart-check-fill"></i></span>
                    <div class="summary-title">Total Sales</div>
                    <div><?php echo count($sales_data); ?> Transactions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-summary bg-light border rounded p-3">
                    <span class="summary-icon"><i class="bi bi-cash-stack"></i></span>
                    <div class="summary-title">Total Sales Amount</div>
                    <div><?php echo number_format($total_sales, 2); ?> PHP</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-summary bg-light border rounded p-3">
                    <span class="summary-icon"><i class="bi bi-truck"></i></span>
                    <div class="summary-title">Total Deliveries</div>
                    <div><?php echo count($delivery_data); ?> Transactions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-summary bg-light border rounded p-3">
                    <span class="summary-icon"><i class="bi bi-box-seam"></i></span>
                    <div class="summary-title">Total Deliveries Amount</div>
                    <div><?php echo number_format($total_deliveries, 2); ?> PHP</div>
                </div>
            </div>
        </div>

        <!-- Sales and Delivery Graphs -->
        <div class="row mt-5">
            <div class="col-md-6">
                <h5>Sales Over Time</h5>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="col-md-6">
                <h5>Deliveries Over Time</h5>
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Chart.js Initialization -->
    <script>
        // Prepare Sales Data
        const salesData = <?php echo json_encode(array_column($sales_data, 'SaleTotal_amount')); ?>;
        const salesLabels = <?php echo json_encode(array_column($sales_data, 'Sales_date')); ?>;
        
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales Amount',
                    data: salesData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Prepare Delivery Data
        const deliveryData = <?php echo json_encode(array_column($delivery_data, 'Total_Amount')); ?>;
        const deliveryLabels = <?php echo json_encode(array_column($delivery_data, 'Delivery_Date')); ?>;

        // Delivery Chart
        const deliveryCtx = document.getElementById('deliveryChart').getContext('2d');
        const deliveryChart = new Chart(deliveryCtx, {
            type: 'line',
            data: {
                labels: deliveryLabels,
                datasets: [{
                    label: 'Delivery Amount',
                    data: deliveryData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>
</html>

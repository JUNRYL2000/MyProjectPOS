<?php
// Database connection and queries (unchanged)
$host = 'localhost';
$dbname = 'myprojectpos';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch sales and delivery data
    $sales_query = $pdo->query("SELECT Sales_date, SaleTotal_amount FROM saletransaction");
    $sales_data = $sales_query->fetchAll(PDO::FETCH_ASSOC);

    $delivery_query = $pdo->query("SELECT Delivery_Date, Total_Amount FROM deliverytransaction");
    $delivery_data = $delivery_query->fetchAll(PDO::FETCH_ASSOC);

    // Total sales and deliveries amounts
    $total_sales_query = $pdo->query("SELECT SUM(SaleTotal_amount) AS total_sales_amount FROM saletransaction");
    $total_sales = $total_sales_query->fetch(PDO::FETCH_ASSOC)['total_sales_amount'];

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
    body{
        background-color: lightgray;
    }
    .container{
        background-color: white;
    }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Dashboard</h1>

      <div class="conatiner">  <!-- Summary Cards for Totals -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Sales</h5>
                        <p class="card-text display-6">₱ <?php echo number_format($total_sales, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Deliveries</h5>
                        <p class="card-text display-6">₱ <?php echo number_format($total_deliveries, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales, Deliveries, and Pie Chart Section -->
        <div class="row mt-5">
            <div class="col-md-4">
                <h5>Sales Over Time</h5>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="col-md-4">
                <h5>Deliveries Over Time</h5>
                <canvas id="deliveryChart"></canvas>
            </div>
            <div class="col-md-4">
                <h5>Sales vs Deliveries</h5>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js Initialization -->
    <script>
        // Sales Data
        const salesData = <?php echo json_encode(array_column($sales_data, 'SaleTotal_amount')); ?>;
        const salesLabels = <?php echo json_encode(array_column($sales_data, 'Sales_date')); ?>;
        
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
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
            options: { scales: { y: { beginAtZero: true } } }
        });

        // Delivery Data
        const deliveryData = <?php echo json_encode(array_column($delivery_data, 'Total_Amount')); ?>;
        const deliveryLabels = <?php echo json_encode(array_column($delivery_data, 'Delivery_Date')); ?>;

        // Delivery Chart
        const deliveryCtx = document.getElementById('deliveryChart').getContext('2d');
        new Chart(deliveryCtx, {
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
            options: { scales: { y: { beginAtZero: true } } }
        });

        // Pie Chart for Sales vs Deliveries
        const pieData = [<?php echo $total_sales; ?>, <?php echo $total_deliveries; ?>];
        const pieLabels = ['Total Sales', 'Total Deliveries'];
        
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#3498db', '#e74c3c'],
                    hoverOffset: 4
                }]
            }
        });
    </script>
    </div>
</body>
</html>

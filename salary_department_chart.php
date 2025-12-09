<?php
include 'koneksi.php';

// QUERY: Ambil data Salary Department
$sql = "SELECT NamaDepartemen, RataRata_GajiTerakhir 
        FROM stg_salary_department
        ORDER BY RataRata_GajiTerakhir DESC";
$result = mysqli_query($conn, $sql);

$labels = [];
$values   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['NamaDepartemen'];
    $values[] = (float)$row['RataRata_GajiTerakhir'];
}

$total = array_sum($values);

$data = [];
foreach ($values as $v) {
    $data[] = round(($v / $total) * 100, 2);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Salary Department Chart</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #layoutSidenav {
            display: flex;
        }

        #layoutSidenav_nav {
            width: 250px;
        }

        #layoutSidenav_content {
            flex: 1;
        }

        .chart-container {
            position: relative;
            height: 420px;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <!-- NAVBAR -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <i class="fas fa-store" style="color:grey"></i>
        </a>
        <a class="navbar-brand ps-3" href="home.php">FINAL PROJECT DATA WAREHOUSE & OLAP</a>

        <!-- Tombol Toggle -->
        <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- SIDEBAR + CONTENT -->
    <div id="layoutSidenav">

        <!-- SIDEBAR -->
        <?php include 'sidebar.php'; ?>

        <!-- CONTENT -->
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">

                <h1 class="mt-4">Rata-Rata Gaji Terakhir per Departemen</h1>

                <div class="card mb-4 mt-3">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salaryDeptChart"></canvas>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <script>
        // DATA dari PHP
        const sdLabels = <?= json_encode($labels); ?>;
        const sdPercent = <?= json_encode($data); ?>; // persen
        const sdOriginal = <?= json_encode($values); ?>; // dollar asli

        const ctx = document.getElementById('salaryDeptChart').getContext('2d');

        let salaryChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: sdLabels,
                datasets: [{
                    label: 'Komposisi Gaji per Departemen',
                    data: sdPercent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: 'right'
                    },

                    // ============================
                    // TOOLTIP PERSENTASE + DOLLAR
                    // ============================
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let idx = ctx.dataIndex;
                                let label = ctx.label;

                                let percent = sdPercent[idx]; // persen
                                let dollar = sdOriginal[idx]; // dollar asli

                                // Format dollar ke USD
                                let formattedDollar = dollar.toLocaleString("en-US", {
                                    style: "currency",
                                    currency: "USD"
                                });

                                return `${label}: ${percent}% (${formattedDollar})`;
                            }
                        }
                    }
                }
            }
        });

        // Agar chart tetap responsif saat sidebar dibuka/tutup
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => salaryChart.resize(), 300);
        });
    </script>

</body>

</html>
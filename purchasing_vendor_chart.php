<?php
include 'koneksi.php';

// ambil data dari staging purchasing vendor
$sql = "SELECT NamaVendor, TotalNilaiPembelian 
        FROM stg_purchasing_vendor
        ORDER BY TotalNilaiPembelian DESC";
$result = mysqli_query($conn, $sql);

$labels = [];
$data   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['NamaVendor'];
    $data[]   = (float)$row['TotalNilaiPembelian'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Purchasing Vendor Chart</title>

    <!-- SB Admin CSS -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome (Wajib untuk ikon sidebar) -->
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
            height: 430px;
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

                <h1 class="mt-4">Total Nilai Pembelian per Vendor</h1>

                <div class="card mb-4 mt-3">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="purchasingVendorChart"></canvas>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>

    <!-- Bootstrap + Script Toggle SB Admin -->
    <script>
        // DATA dari PHP
        const pvLabels = <?= json_encode($labels); ?>;
        const pvData = <?= json_encode($data); ?>;

        const ctxPV = document.getElementById('purchasingVendorChart').getContext('2d');

        let purchasingChart = new Chart(ctxPV, {
            type: 'bar',
            data: {
                labels: pvLabels,
                datasets: [{
                    label: 'Total Nilai Pembelian',
                    data: pvData,

                    // ==== STYLING BAR DI SINI ====
                    backgroundColor: 'rgba(59, 130, 246, 0.35)', // biru soft
                    borderColor: 'rgba(37, 99, 235, 0.9)', // garis tepi
                    borderWidth: 2,
                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.60)',
                    hoverBorderColor: 'rgba(37, 99, 235, 1)',
                    borderRadius: 6, // sudut bar melengkung
                    maxBarThickness: 42 // batasi ketebalan bar
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const label = ctx.dataset.label || '';
                                const value = ctx.parsed.y || 0;
                                // format ribuan (tanpa currency, bisa kamu ubah ke Rp)
                                const formatted = value.toLocaleString('id-ID');
                                return `${label}: ${formatted}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Responsif saat toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => purchasingChart.resize(), 300);
        });
    </script>


</body>

</html>
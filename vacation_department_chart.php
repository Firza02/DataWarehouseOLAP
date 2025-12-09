<?php
include 'koneksi.php';

$sql = "SELECT DepartmentName, Rata2Cuti_Hari 
        FROM stg_vacation_department
        ORDER BY Rata2Cuti_Hari DESC";
$result = mysqli_query($conn, $sql);

$labels = [];
$data   = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['DepartmentName'];
    $data[]   = (float)$row['Rata2Cuti_Hari'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Vacation Department Chart</title>

    <!-- SB Admin CSS -->
    <link href="css/styles-table.css" rel="stylesheet">

    <!-- Font Awesome (WAJIB agar icon sidebar & navbar muncul) -->
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
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-danger">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <i class="fas fa-store" style="color:grey"></i>
        </a>
        <a class="navbar-brand ps-3" href="home.php">FINAL PROJECT DATA WAREHOUSE & OLAP</a>

        <!-- Tombol Toggle Sidebar -->
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

                <h1 class="mt-4">Rata-rata Cuti per Departemen (Hari)</h1>

                <div class="card mb-4 mt-3">
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="vacationDeptChart"></canvas>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Bootstrap + SB Admin Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT SB ADMIN (WAJIB agar toggle bekerja) -->
    <script src="js/scripts-table.js"></script>

    <script>
        // DATA dari PHP
        const vdLabels = <?= json_encode($labels); ?>;
        const vdData = <?= json_encode($data); ?>;

        // Warna modern (Tailwind Blue 500)
        const barColor = "rgba(59, 130, 246, 0.65)"; // Soft Blue
        const barBorderColor = "rgba(37, 99, 235, 1)"; // Strong Blue

        const ctxVD = document.getElementById('vacationDeptChart').getContext('2d');

        let vacationChart = new Chart(ctxVD, {
            type: 'bar',
            data: {
                labels: vdLabels,
                datasets: [{
                    label: 'Rata-rata Cuti (Hari)',
                    data: vdData,

                    // === Styling modern ===
                    backgroundColor: barColor,
                    borderColor: barBorderColor,
                    borderWidth: 2,
                    borderRadius: 8, // rounded bar
                    hoverBackgroundColor: "rgba(59, 130, 246, 0.85)",
                    hoverBorderColor: barBorderColor,
                    maxBarThickness: 38
                }]
            },
            options: {
                indexAxis: 'y', // horizontal bar
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "rgba(0,0,0,0.8)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                        padding: 10,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed.x ?? 0;
                                return " " + val.toLocaleString("id-ID") + " hari";
                            }
                        }
                    }
                },

                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: "#334155", // slate-600
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            borderDash: [4, 4],
                            color: "rgba(148,163,184,0.25)", // soft grid
                        }
                    },
                    y: {
                        ticks: {
                            color: "#1e293b", // slate-800
                            font: {
                                size: 13,
                                weight: "600"
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Agar chart responsif saat toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => vacationChart.resize(), 300);
        });
    </script>


</body>

</html>
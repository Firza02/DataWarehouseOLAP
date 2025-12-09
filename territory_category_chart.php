<?php
// territory_category_chart.php
require 'koneksi.php'; // file koneksi mysqli, sesuaikan

// ============================
//  QUERY LEVEL 1: TERRITORY
// ============================
$sqlTerritory = "
    SELECT Territory,
           SUM(TotalQty) AS total_qty
    FROM stg_territory_category
    GROUP BY Territory
    ORDER BY Territory;
";
$resTerritory = mysqli_query($conn, $sqlTerritory);

$territoryLabels = [];
$territoryValues = [];

while ($row = mysqli_fetch_assoc($resTerritory)) {
    $territoryLabels[] = $row['Territory'];
    $territoryValues[] = (int)$row['total_qty'];
}

// =========================================
//  QUERY LEVEL 2: CATEGORY PER TERRITORY
// =========================================
$sqlCategory = "
    SELECT Territory,
           Category,
           SUM(TotalQty) AS total_qty
    FROM stg_territory_category
    GROUP BY Territory, Category
    ORDER BY Territory, Category;
";
$resCategory = mysqli_query($conn, $sqlCategory);

$categoryDataByTerritory = []; // ['Australia' => ['labels'=>[], 'data'=>[]], ...]

while ($row = mysqli_fetch_assoc($resCategory)) {
    $territory = $row['Territory'];
    $category  = $row['Category'];
    $qty       = (int)$row['total_qty'];

    if (!isset($categoryDataByTerritory[$territory])) {
        $categoryDataByTerritory[$territory] = [
            'labels' => [],
            'data'   => []
        ];
    }
    $categoryDataByTerritory[$territory]['labels'][] = $category;
    $categoryDataByTerritory[$territory]['data'][]   = $qty;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Territory vs Category</title>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles-table.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
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
            height: 400px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-danger">
        <a class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-store" style="color:grey"></i>
            </div>
        </a>
        <a class="navbar-brand ps-3" href="home.php">FINAL PROJECT DATA WAREHOUSE & OLAP</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="layoutSidenav">
        <?php include 'sidebar.php'; ?>

        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">
                <h1 class="mt-4">Territory (Drilldown Category)</h1>

                <div class="card mb-4 mt-3">
                    <div class="card-body">
                        <button id="btnBack" class="btn btn-sm btn-secondary mb-3 d-none">
                            Kembali ke Territory
                        </button>

                        <div class="chart-container">
                            <canvas id="territoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>



    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts-table.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script src="js/datatables-simple-demo.js"></script>

    <script>
        // ====== DATA PHP -> JS ======
        const territoryLabels = <?= json_encode($territoryLabels) ?>;
        const territoryValues = <?= json_encode($territoryValues) ?>;
        const categoryDataByTerritory = <?= json_encode($categoryDataByTerritory) ?>;

        const ctx = document.getElementById('territoryChart').getContext('2d');
        const btnBack = document.getElementById('btnBack');
        let currentLevel = 'territory';

        // Palet warna untuk drilldown kategori
        const categoryPalette = [
            "#3B82F6", // blue
            "#EF4444", // red
            "#22C55E", // green
            "#F59E0B", // amber
            "#8B5CF6", // purple
            "#06B6D4", // cyan
            "#EC4899", // pink
            "#A855F7", // violet
            "#14B8A6", // teal
            "#F97316" // orange
        ];

        function formatNumberID(v) {
            return Number(v || 0).toLocaleString("id-ID");
        }

        // --- fungsi render territory (level atas) ---
        function renderTerritory(chart) {
            chart.data.labels = territoryLabels;
            chart.data.datasets[0].data = territoryValues;

            chart.data.datasets[0].backgroundColor = "rgba(59, 130, 246, 0.6)";
            chart.data.datasets[0].borderColor = "rgba(37, 99, 235, 1)";
            chart.data.datasets[0].borderWidth = 2;
            chart.data.datasets[0].borderRadius = 8;
            chart.data.datasets[0].maxBarThickness = 46;

            chart.options.plugins.title.text = 'Total Qty per Territory';
            currentLevel = 'territory';
            btnBack.classList.add('d-none');

            chart.update();
        }

        // --- fungsi render category (drilldown per territory) ---
        function renderCategory(chart, territoryName) {
            const cfg = categoryDataByTerritory[territoryName];
            if (!cfg) return;

            chart.data.labels = cfg.labels;
            chart.data.datasets[0].data = cfg.data;

            // generate warna beda per kategori
            const colors = cfg.labels.map((_, idx) => categoryPalette[idx % categoryPalette.length]);
            chart.data.datasets[0].backgroundColor = colors.map(c => c + "B3"); // +opacity
            chart.data.datasets[0].borderColor = colors;
            chart.data.datasets[0].borderWidth = 2;
            chart.data.datasets[0].borderRadius = 8;
            chart.data.datasets[0].maxBarThickness = 46;

            chart.options.plugins.title.text = 'Total Qty per Category - ' + territoryName;
            currentLevel = 'category';
            btnBack.classList.remove('d-none');

            chart.update();
        }

        // --- inisialisasi chart ---
        const territoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: territoryLabels,
                datasets: [{
                    label: 'Total Qty',
                    data: territoryValues
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Qty per Territory'
                    },
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
                                const lbl = ctx.label || '';
                                const val = ctx.parsed.y || 0;
                                return ' ' + lbl + ': ' + formatNumberID(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: "#1e293b",
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: "#475569",
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            borderDash: [4, 4],
                            color: "rgba(148,163,184,0.3)"
                        }
                    }
                },
                onClick: (evt, elements) => {
                    if (!elements.length || currentLevel !== 'territory') return;
                    const idx = elements[0].index;
                    const territoryName = territoryLabels[idx];
                    renderCategory(territoryChart, territoryName);
                }
            }
        });

        // Set awal: tampil territory
        renderTerritory(territoryChart);

        // tombol back
        btnBack.addEventListener('click', () => renderTerritory(territoryChart));

        // responsif saat sidebar dibuka
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            setTimeout(() => territoryChart.resize(), 300);
        });
    </script>


</body>

</html>
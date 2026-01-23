<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Print</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        @media print {
            .no-print {
                display: none;
            }
            page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<?php include 'header.php'; ?>

<!-- SECCIÓN LABOR -->
<h2>Labor</h2>
<?php include 'form_labor.php'; ?>

<page-break></page-break>

<!-- SECCIÓN DIRECT COSTS -->
<h2>Direct Costs</h2>
<?php include 'form_direct_costs.php'; ?>

<page-break></page-break>

<!-- PROFIT -->
<h2>Profit</h2>
<?php include 'form_profit.php'; ?>

<script>
    window.onload = function () {
        window.print();
    }
</script>

</body>
</html>

<?php
session_start();
include '../../_conexionMySQL.php';

// Obtener el nombre del colegio de la sesión
$nombre_colegio = $_SESSION["Nombre"];

// Obtener todos los grados disponibles
$sql_grados = "SELECT DISTINCT grado FROM evaluaciones WHERE colegio = '$nombre_colegio'";
$result_grados = $conn->query($sql_grados);

$grados_disponibles = [];

if ($result_grados->num_rows > 0) {
    while ($row = $result_grados->fetch_assoc()) {
        $grados_disponibles[] = $row['grado'];
    }
}

// Obtener el grado seleccionado
$grado_seleccionado = isset($_POST['grado']) ? $_POST['grado'] : $grados_disponibles[0];

// Obtener las divisiones disponibles según el grado seleccionado
$sql_divisiones = "SELECT DISTINCT division FROM evaluaciones WHERE colegio = '$nombre_colegio' AND grado = '$grado_seleccionado'";
$result_divisiones = $conn->query($sql_divisiones);

$divisiones_disponibles = [];

if ($result_divisiones->num_rows > 0) {
    while ($row = $result_divisiones->fetch_assoc()) {
        $divisiones_disponibles[] = $row['division'];
    }
}

// Obtener la división seleccionada
$division_seleccionada = isset($_POST['division']) ? $_POST['division'] : $divisiones_disponibles[0];

// Obtener los datos para el gráfico de promedio de puntos
// $sql = "SELECT grado, AVG(total_puntos) as promedio_puntos FROM evaluaciones WHERE colegio = '$nombre_colegio' GROUP BY grado";
// $sql = "SELECT grado, AVG(total_puntos) as promedio_puntos FROM evaluaciones WHERE colegio = '$nombre_colegio' AND grado = '$grado_seleccionado' GROUP BY grado";
$sql = "SELECT grado, AVG(total_puntos) as promedio_puntos FROM evaluaciones WHERE colegio = '$nombre_colegio' AND grado = '$grado_seleccionado' AND division = '$division_seleccionada' GROUP BY grado";
$result = $conn->query($sql);

$grados = [];
$promedio_puntos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $grados[] = $row['grado'];
        $promedio_puntos[] = $row['promedio_puntos'];
    }
}

// Obtener los datos para el gráfico de promedio de tipografía
// $sql_tipografia = "SELECT grado, AVG(tipografia) as promedio_tipografia FROM evaluaciones WHERE colegio = '$nombre_colegio' GROUP BY grado";
// $sql_tipografia = "SELECT grado, AVG(tipografia) as promedio_tipografia FROM evaluaciones WHERE colegio = '$nombre_colegio' AND grado = '$grado_seleccionado' GROUP BY grado";
$sql_tipografia = "SELECT grado, AVG(tipografia) as promedio_tipografia FROM evaluaciones WHERE colegio = '$nombre_colegio' AND grado = '$grado_seleccionado' AND division = '$division_seleccionada' GROUP BY grado";
$result_tipografia = $conn->query($sql_tipografia);

$promedio_tipografia = [];

if ($result_tipografia->num_rows > 0) {
    while ($row = $result_tipografia->fetch_assoc()) {
        $promedio_tipografia[] = $row['promedio_tipografia'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos de Barras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gráficos de Barras</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="grado" class="form-label">Seleccione el Grado</label>
                <select class="form-select" id="grado" name="grado" onchange="this.form.submit()">
                    <?php foreach ($grados_disponibles as $grado): ?>
                        <option value="<?= $grado ?>" <?= $grado == $grado_seleccionado ? 'selected' : '' ?>><?= $grado ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="division" class="form-label">Seleccione la División</label>
                <select class="form-select" id="division" name="division" onchange="this.form.submit()">
                    <?php foreach ($divisiones_disponibles as $division): ?>
                        <option value="<?= $division ?>" <?= $division == $division_seleccionada ? 'selected' : '' ?>><?= $division ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <h2>Promedio de Puntos por Grado (en %)</h2>
        <canvas id="myChart1" height="8" width="100"></canvas>
        <h2>Promedio de Tipografía por Grado</h2>
        <canvas id="myChart2" height="8" width="100"></canvas>
    </div>
    <script>
        // Obtener los datos de PHP
        const grados = <?php echo json_encode($grados); ?>;
        const promedioPuntos = <?php echo json_encode($promedio_puntos); ?>;
        const promedioTipografia = <?php echo json_encode($promedio_tipografia); ?>;

        // Convertir los puntos a porcentajes
        const maxPuntos = 52;
        const promedioPuntosPorcentaje = promedioPuntos.map(punto => (punto / maxPuntos) * 100);

        // Crear el gráfico de barras horizontal para promedio de puntos en porcentaje
        const ctx1 = document.getElementById('myChart1').getContext('2d');
        const myChart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: grados,
                datasets: [{
                    label: 'Promedio de Puntos por Grado (%)',
                    data: promedioPuntosPorcentaje,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    // barThickness: 10,  Ajustar el grosor de las barras
                    // categoryPercentage: 0.5 // Ajustar el alto de cada barra
                }]
            },
            options: {
                indexAxis: 'y',
                // maintainAspectRatio: false, // No mantener la relación de aspecto
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100 // El máximo en el eje x es 100%
                    }
                }
            }
        });

        // Crear el gráfico de barras horizontal para promedio de tipografía
        const ctx2 = document.getElementById('myChart2').getContext('2d');
        const myChart2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: grados,
                datasets: [{
                    label: 'Promedio de Tipografía por Grado',
                    data: promedioTipografia,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    // barThickness: 10, // Ajustar el grosor de las barras
                    // categoryPercentage: 0.5 // Ajustar el alto de cada barra
                }]
            },
            options: {
                indexAxis: 'y',
                // maintainAspectRatio: false, // No mantener la relación de aspecto
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
session_start(); // Asegúrate de iniciar la sesión
include '../_conexionMySQL.php';

// Determinar los filtros seleccionados para el filtro
$filtro_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$filtro_colegio = isset($_GET['colegio']) ? $_GET['colegio'] : '';
$filtro_pais = isset($_GET['pais']) ? $_GET['pais'] : '';

// Configuración de paginación
$items_per_page_options = [10, 25, 50, 100, 'todos'];
$results_per_page = isset($_GET['items_per_page']) && in_array($_GET['items_per_page'], $items_per_page_options) ? $_GET['items_per_page'] : 10;

if ($results_per_page === 'todos') {
    $results_per_page = PHP_INT_MAX; // Muestra todos los registros si se selecciona 'todos'
}

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Obtener el total de registros
$sql_total = "SELECT COUNT(*) AS total FROM usuarios WHERE 1";
if ($filtro_nombre) {
    $sql_total .= " AND Nombre LIKE '%$filtro_nombre%'";
}
if ($filtro_colegio) {
    $sql_total .= " AND Colegio LIKE '%$filtro_colegio%'";
}
if ($filtro_pais) {
    $sql_total .= " AND Pais LIKE '%$filtro_pais%'";
}
$result_total = $conn->query($sql_total);
$total_rows = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Obtener países únicos de la base de datos para el filtro
$sql_paises = "SELECT DISTINCT Pais FROM usuarios";
$result_paises = $conn->query($sql_paises);
$paises = [];
if ($result_paises->num_rows > 0) {
    while ($row = $result_paises->fetch_assoc()) {
        $paises[] = $row['Pais'];
    }
}

// Determinar el campo y el orden de clasificación
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'Nombre';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Cambiar el orden de clasificación al hacer clic
$order_toggle = $order === 'ASC' ? 'DESC' : 'ASC';

// Consultar la base de datos para obtener los colegios filtrados y ordenados con paginación
$sql = "SELECT * FROM usuarios WHERE 1";
if ($filtro_nombre) {
    $sql .= " AND Nombre LIKE '%$filtro_nombre%'";
}
if ($filtro_colegio) {
    $sql .= " AND Colegio LIKE '%$filtro_colegio%'";
}
if ($filtro_pais) {
    $sql .= " AND Pais LIKE '%$filtro_pais%'";
}
$sql .= " ORDER BY $sort_field $order LIMIT $offset, $results_per_page";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Colegios</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Enlace a Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .filters-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .filter-box {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
            margin: 5px;
        }

        .filter-box label, .filter-box select, .filter-box input {
            margin-right: 10px;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a.active {
            font-weight: bold;
            text-decoration: underline;
            color: #000;
        }

        .total-colegios {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Listado de Colegios</h1>

    <div class="filters-container">
        <!-- Filtro por Nombre -->
        <div class="filter-box">
            <form method="get" action="">
                <label for="nombre">Filtrar por Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($filtro_nombre); ?>">
                <input type="hidden" name="colegio" value="<?php echo $filtro_colegio; ?>">
                <input type="hidden" name="pais" value="<?php echo $filtro_pais; ?>">
                <input type="hidden" name="items_per_page" value="<?php echo isset($_GET['items_per_page']) ? $_GET['items_per_page'] : 10; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Filtro por Colegio -->
        <div class="filter-box">
            <form method="get" action="">
                <label for="colegio">Filtrar por Colegio:</label>
                <input type="text" id="colegio" name="colegio" value="<?php echo htmlspecialchars($filtro_colegio); ?>">
                <input type="hidden" name="nombre" value="<?php echo $filtro_nombre; ?>">
                <input type="hidden" name="pais" value="<?php echo $filtro_pais; ?>">
                <input type="hidden" name="items_per_page" value="<?php echo isset($_GET['items_per_page']) ? $_GET['items_per_page'] : 10; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Filtro por País -->
        <div class="filter-box">
            <form method="get" action="">
                <label for="pais">Filtrar por País:</label>
                <select name="pais" id="pais" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo $pais; ?>" <?php if ($pais === $filtro_pais) echo 'selected'; ?>>
                            <?php echo $pais; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="nombre" value="<?php echo $filtro_nombre; ?>">
                <input type="hidden" name="colegio" value="<?php echo $filtro_colegio; ?>">
                <input type="hidden" name="items_per_page" value="<?php echo isset($_GET['items_per_page']) ? $_GET['items_per_page'] : 10; ?>">
            </form>
        </div>

        <!-- Combo box para seleccionar cantidad de ítems por página -->
        <div class="filter-box">
            <form method="get" action="">
                <label for="items_per_page">Items por página:</label>
                <select name="items_per_page" id="items_per_page" onchange="this.form.submit()">
                    <?php foreach ($items_per_page_options as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($results_per_page == $option || ($results_per_page == PHP_INT_MAX && $option === 'todos')) echo 'selected'; ?>>
                            <?php echo ucfirst($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="nombre" value="<?php echo $filtro_nombre; ?>">
                <input type="hidden" name="colegio" value="<?php echo $filtro_colegio; ?>">
                <input type="hidden" name="pais" value="<?php echo $filtro_pais; ?>">
            </form>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>
                    Nombre
                    <a href="?sort=Nombre&order=<?php echo $sort_field === 'Nombre' ? $order_toggle : 'ASC'; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>&page=<?php echo $current_page; ?>" style="text-decoration: none; margin-left: 5px;">
                        <?php if ($sort_field === 'Nombre' && $order === 'ASC'): ?>
                            &#9650;
                        <?php elseif ($sort_field === 'Nombre' && $order === 'DESC'): ?>
                            &#9660;
                        <?php else: ?>
                            &#9651;
                        <?php endif; ?>
                    </a>
                </th>
                <th>Usuario</th>
                <th>Password</th>
                <th>
                    Colegio
                    <a href="?sort=Colegio&order=<?php echo $sort_field === 'Colegio' ? $order_toggle : 'ASC'; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>&page=<?php echo $current_page; ?>" style="text-decoration: none; margin-left: 5px;">
                        <?php if ($sort_field === 'Colegio' && $order === 'ASC'): ?>
                            &#9650;
                        <?php elseif ($sort_field === 'Colegio' && $order === 'DESC'): ?>
                            &#9660;
                        <?php else: ?>
                            &#9651;
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    País
                    <a href="?sort=Pais&order=<?php echo $sort_field === 'Pais' ? $order_toggle : 'ASC'; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>&page=<?php echo $current_page; ?>" style="text-decoration: none; margin-left: 5px;">
                        <?php if ($sort_field === 'Pais' && $order === 'ASC'): ?>
                            &#9650;
                        <?php elseif ($sort_field === 'Pais' && $order === 'DESC'): ?>
                            &#9660;
                        <?php else: ?>
                            &#9651;
                        <?php endif; ?>
                    </a>
                </th>
                <th>Localidad</th>
                <th>Provincia</th>
                <th>Logo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['Usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td>
                        <td><?php echo htmlspecialchars($row['Colegio']); ?></td>
                        <td><?php echo htmlspecialchars($row['Pais']); ?></td>
                        <td><?php echo htmlspecialchars($row['Localidad']); ?></td>
                        <td><?php echo htmlspecialchars($row['Provincia']); ?></td>
                        <td style="text-align: center;">
                            <?php if (!empty($row['Logo'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($row['Logo']); ?>" alt="Logo" style="max-width: 40px;">
                            <?php else: ?>
                                <i class="fas fa-times-circle icon-no"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Aquí puedes agregar acciones como ver, editar, borrar -->
                            <a href="ver_colegio.php?id=<?php echo $row['Usuario_id']; ?>">Ver</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No hay colegios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Navegación de Paginación y Total de Colegios -->
    <div class="pagination">
        <div class="total-colegios">Total de colegios: <?php echo $total_rows; ?></div>
        <div>
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sort_field; ?>&order=<?php echo $order; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_field; ?>&order=<?php echo $order; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>" <?php if ($i == $current_page) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sort_field; ?>&order=<?php echo $order; ?>&nombre=<?php echo $filtro_nombre; ?>&colegio=<?php echo $filtro_colegio; ?>&pais=<?php echo $filtro_pais; ?>&items_per_page=<?php echo $results_per_page; ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
    </div>

    <button onclick="window.location.href='index.php'">Realizar Nueva Evaluación</button>
</body>
</html>
<?php $conn->close(); ?>
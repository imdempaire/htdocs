<?php
    session_start();
    include '../_conexionMySQL.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación de Escritura</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" type="text/css" href="../css/styles.css?v1">
    <style>
        #screeningSection, #evaluationForm, #fileUpload, #uploadSection {
            display: none;
        }
    </style>
</head>

<body>
<?php
    $nombre = '';
    $apellido = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $legajo = $_POST['legajo'];
        $query = "SELECT nombre, apellido FROM alumnos WHERE id_estudiante = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $legajo);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $nombre = $student['nombre'];
            $apellido = $student['apellido'];
        } else {
            echo "<p>No se encontró ningún estudiante con ese legajo.</p>";
        }
    }
?>

<form method="POST" action="">
    <label for="legajo">Legajo:</label>
    <input type="text" id="legajo" name="legajo" value="<?php echo isset($legajo) ? $legajo : ''; ?>" required><br><br>

    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" readonly><br><br>

    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" value="<?php echo isset($apellido) ? $apellido : ''; ?>" readonly><br><br>

    <input type="submit" value="Buscar">
</form>


    <?php   $GLOBALS['titulo'] = "Plataforma de Screening de IA JEL Aprendizaje";
            include '../_header.php';
    ?>

    <h1>Evaluación de Escritura</h1>
    <!-- Sección de datos básicos -->
    
    <div id="initialForm">

        <?php
        // Si esta logoneado como Admin, que le permita elegir el colegio.
//            if ($_SESSION["Nombre"] == "Admin" ) {
//                // Obtener colegios únicos de la base de datos, tabla usuarios (=colegio) para la seleccion del colegio.
//                $sql_colegios = "SELECT DISTINCT Colegio FROM usuarios";
//                $result_colegios = $conn->query($sql_colegios);
//                $colegios = [];
//                if ($result_colegios->num_rows > 0) {
//                    while ($row = $result_colegios->fetch_assoc()) {
//                        $colegios[] = $row['Colegio'];
//                    }
//                }
//                echo "<label for=\"colegio\">Colegio:</label>";
//                echo "<select id=\"colegio\" name=\"colegio\" required>";
//                        foreach ($colegios as $colegio):
//                            echo "<option value=\"$colegio\">".$colegio."</option>";
//                        endforeach;
//                echo "</select>";
//            }
        ?>

        <label for="id">Legajo del alumno:</label>
        <input type="text" id="id" name="student_id" required>

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>
        
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required>
        
        <label for="id">Legajo del alumno:</label>
        <input type="text" id="id" name="student_id" required>
        
        <label for="grado">Grado:</label>
        <select id="grado" name="grado" required>
            <option value="1er grado">1er grado</option>
            <option value="2do grado">2do grado</option>
            <option value="3er grado">3er grado</option>
            <option value="4to grado">4to grado</option>
            <option value="5to grado">5to grado</option>
            <option value="6to grado">6to grado</option>
        </select>
        
        <label for="division">División:</label>
            <select id="division" name="division" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        
            <?php
            // Si esta logoneado como Admin, que le permita elegir el colegio.
            if ($_SESSION["Nombre"] == "Admin" ) {
                // echo "<button type=\"button\" onclick=\"startScreening2()\">Empezar Screening (Admin)</button>";
                echo "<button type=\"button\" onclick=\"startScreening()\">Empezar Screening (Admin)</button>";
            } else {
                echo "<button type=\"button\" onclick=\"startScreening()\">Empezar Screening</button>\"";
            }
            ?>

    </div>
    
    <!-- Sección de evaluación -->
    <form id="evaluationForm" action="process_evaluation.php" method="post" enctype="multipart/form-data">
        <div id="screeningSection">
            <!-- Campos ocultos para guardar los datos iniciales -->
            <!-- <input type="hidden" id="colegio_hidden" name="colegio"> --> 
            <input type="hidden" id="nombre_hidden" name="nombre">
            <input type="hidden" id="apellido_hidden" name="apellido">
            <input type="hidden" id="id_hidden" name="student_id">
            <input type="hidden" id="grado_hidden" name="grado">
            <input type="hidden" id="division_hidden" name="division">

            <?php include 'indexContenido.php';?>

            <button type="button" onclick="askForFileUpload()">Finalizar Evaluación</button>
        </div>

        <div id="uploadSection">

            <!-- Campo para Observaciones -->
            <label for="observaciones">Observaciones:</label>
            <textarea id="observaciones" name="observaciones" rows="4" cols="50" placeholder="Escribe las observaciones aquí..."></textarea>

            <h2>Subir Archivo</h2>
            <p>¿Deseas subir una foto asociada con la evaluación?</p>
            <button type="button" onclick="showFileUpload()">Sí</button>
            <button type="button" onclick="submitForm()">No</button>
            
            <div id="fileUpload">
                <input type="file" name="evaluation_file" id="evaluation_file" accept="image/*">
                <button type="button" onclick="submitForm()">Subir y Continuar</button>
            </div>
        </div>
    </form>

    <script>
        function startScreening() {
            // Transferir valores a los campos ocultos
            document.getElementById('nombre_hidden').value = document.getElementById('nombre').value;
            document.getElementById('apellido_hidden').value = document.getElementById('apellido').value;
            document.getElementById('id_hidden').value = document.getElementById('id').value;
            document.getElementById('grado_hidden').value = document.getElementById('grado').value;
            document.getElementById('division_hidden').value = document.getElementById('division').value;

            // Limpiar y mostrar la sección de evaluación
            document.getElementById('initialForm').style.display = 'none';
            document.getElementById('screeningSection').style.display = 'block';
            document.getElementById('evaluationForm').style.display = 'block';
        }

        function askForFileUpload() {
            document.getElementById('screeningSection').style.display = 'none';
            document.getElementById('uploadSection').style.display = 'block';
        }

        function startScreening2() {
            // Transferir valores a los campos ocultos
            document.getElementById('colegio_hidden').value = document.getElementById('colegio').value;
            document.getElementById('nombre_hidden').value = document.getElementById('nombre').value;
            document.getElementById('apellido_hidden').value = document.getElementById('apellido').value;
            document.getElementById('id_hidden').value = document.getElementById('id').value;
            document.getElementById('grado_hidden').value = document.getElementById('grado').value;
            document.getElementById('division_hidden').value = document.getElementById('division').value;

            // Limpiar y mostrar la sección de evaluación
            document.getElementById('initialForm').style.display = 'none';
            document.getElementById('screeningSection').style.display = 'block';
            document.getElementById('evaluationForm').style.display = 'block';
        }

        function askForFileUpload() {
            document.getElementById('screeningSection').style.display = 'none';
            document.getElementById('uploadSection').style.display = 'block';
        }


        function showFileUpload() {
            document.getElementById('fileUpload').style.display = 'block';
        }

        function submitForm() {
            document.getElementById('evaluationForm').submit();
        }
    </script>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#legajo').on('blur', function() {
            var legajo = $(this).val();
            if(legajo) {
                $.ajax({
                    url: 'process_evaluation.php',
                    type: 'POST',
                    data: {legajo: legajo, action: 'fetch_student_data'},
                    success: function(response) {
                        var data = JSON.parse(response);
                        if(data.status == 'success') {
                            $('#nombre').val(data.nombre);
                            $('#apellido').val(data.apellido);
                        } else {
                            alert('No se encontró ningún estudiante con ese legajo.');
                        }
                    }
                });
            }
        });
    });
</script>
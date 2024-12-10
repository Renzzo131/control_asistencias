<?php
require 'config.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirigir al login si no está logueado
    exit;
}

// Obtener el monto por asistencia desde la configuración
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'revenue_per_attendance'");
$stmt->execute();
$revenue_per_student = $stmt->fetchColumn(); // Monto dinámico

// Obtener las categorías de los estudiantes
$categories = $pdo->query("SELECT DISTINCT category FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario de asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $present_students = $_POST['attendance'] ?? [];
    $date = $_POST['attendance_date'];

    $existing_attendance = []; // Array para almacenar los estudiantes con asistencia ya registrada

    // Verificar si ya se ha registrado asistencia para los estudiantes en esa fecha
    foreach ($present_students as $student_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = :student_id AND attendance_date = :attendance_date");
        $stmt->execute([
            'student_id' => $student_id,
            'attendance_date' => $date
        ]);
        $existing_record = $stmt->fetchColumn();

        if ($existing_record > 0) {
            $existing_attendance[] = $student_id; // Agregar al array de estudiantes con asistencia ya registrada
            continue; // Saltar a la siguiente iteración sin registrar la asistencia de este estudiante
        }

        // Registrar asistencia "Presente"
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, attendance_date, status, revenue) 
            VALUES (:student_id, :attendance_date, 'Present', :revenue)
        ");
        $stmt->execute([
            'student_id' => $student_id,
            'attendance_date' => $date,
            'revenue' => $revenue_per_student,
        ]);
    }

    // Registrar asistencia "Ausente"
    $stmt = $pdo->prepare("
        SELECT id FROM students 
        WHERE category = :category AND id NOT IN (" . implode(',', $present_students ?: [0]) . ")
    ");
    $stmt->execute(['category' => $category]);
    $absent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($absent_students as $student) {
        // Verificar si ya existe un registro para los ausentes
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = :student_id AND attendance_date = :attendance_date");
        $stmt->execute([
            'student_id' => $student['id'],
            'attendance_date' => $date
        ]);
        $existing_record = $stmt->fetchColumn();

        if ($existing_record > 0) {
            continue; // Si ya existe un registro para este estudiante, no insertamos de nuevo
        }

        // Registrar asistencia "Ausente"
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, attendance_date, status, revenue) 
            VALUES (:student_id, :attendance_date, 'Absent', 0)
        ");
        $stmt->execute([
            'student_id' => $student['id'],
            'attendance_date' => $date,
        ]);
    }

    // Mostrar el mensaje de asistencia registrada para algunos estudiantes
    if (count($existing_attendance) > 0) {
        echo "La asistencia para algunos estudiantes ya ha sido registrada en el día $date.<br>";
    } else {
        echo "Asistencias registradas exitosamente.<br>";
    }
}

// Obtener los estudiantes de la categoría seleccionada
// Obtener los estudiantes de la categoría seleccionada
$selected_category = $_GET['category'] ?? null;
$students = [];
if ($selected_category) {
    // Filtrar solo estudiantes con status 'activo'
    $stmt = $pdo->prepare("SELECT * FROM students WHERE category = :category AND status = 'activo'");
    $stmt->execute(['category' => $selected_category]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a69bd;
            --secondary-color: #718093;
            --accent-color: #f1c40f;
        }

        body {
            background-color: #f4f6f7;
        }

        .navbar {
            background-color: var(--primary-color);
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff;
        }

        .navbar-nav .nav-link {
            color: #fff;
            transition: 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: var(--accent-color);
        }

        .container {
            margin-top: 20px;
        }

        .form-label {
            font-weight: bold;
            color: var(--primary-color);
        }

        .table th {
            background-color: var(--primary-color);
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- Menú de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php"><i class="bi bi-basketball"></i> Asistencias ABC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_students.php"><i class="bi bi-person"></i> Gestionar Alumnos</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_attendance.php"><i class="bi bi-list-check"></i> Ver Asistencia</a></li>
                    <li class="nav-item"><a class="nav-link active" href="form_register.php"><i class="bi bi-person-lines-fill"></i> Registrar Usuario</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <h1 class="text-center text-primary mb-4"><i class="bi bi-check-circle"></i> Registrar Asistencia</h1>

        <a href="update_settings.php" class="btn btn-warning mb-4"><i class="bi bi-cash-coin"></i> Configurar Monto por Asistencia</a>

        <!-- Selección de categoría -->
        <form method="GET" class="mb-4">
            <div class="mb-3">
                <label for="category" class="form-label">Selecciona una categoría:</label>
                <select name="category" id="category" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category'] ?>" <?= $selected_category === $cat['category'] ? 'selected' : '' ?>>
                            <?= $cat['category'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Cargar Alumnos</button>
        </form>

        <!-- Lista de estudiantes -->
        <?php if ($students): ?>
            <form method="POST">
                <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
                <div class="mb-3">
                    <label for="attendance_date" class="form-label">Fecha de asistencia:</label>
                    <input type="date" name="attendance_date" id="attendance_date" class="form-control" required>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><input type="checkbox" name="attendance[]" value="<?= $student['id'] ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-dark w-100 mb-3"><i class="bi bi-check-circle"></i> Registrar Asistencia</button>
            </form>
        <?php elseif ($selected_category): ?>
            <div class="alert alert-warning text-center">No se encontraron alumnos en la categoría seleccionada.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
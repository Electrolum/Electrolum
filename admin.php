<?php
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header("Location: admin_login.php");
  exit;
}

// Configuración de Firebase
require 'firebase.php';

// Función para obtener información de un cliente por cédula
function obtenerInformacionCliente($cedula) {
  global $db;
  $userDoc = $db->collection('Tabla 1')->document($cedula);
  $snapshot = $userDoc->snapshot();

  if ($snapshot->exists()) {
    return $snapshot->data();
  } else {
    return null;
  }
}

// Función para añadir puntos a un cliente
function añadirPuntos($cedula, $puntos) {
  global $db;
  $userDoc = $db->collection('Tabla 1')->document($cedula);
  $userDoc->update([
    ['path' => 'puntos', 'value' => FieldValue::increment($puntos)]
  ]);
}

// Función para quitar puntos a un cliente
function quitarPuntos($cedula, $puntos) {
  global $db;
  $userDoc = $db->collection('Tabla 1')->document($cedula);
  $userDoc->update([
    ['path' => 'puntos', 'value' => FieldValue::increment(-$puntos)]
  ]);
}

// Procesamiento del formulario de búsqueda de cliente
if (isset($_POST['buscarCliente'])) {
  $cedula = $_POST['searchCedula'];
  $cliente = obtenerInformacionCliente($cedula);

  if ($cliente !== null) {
    $clientNombre = $cliente['nombre'];
    $clientApellido = $cliente['apellido'];
    $clientCedula = $cliente['cedula'];
    $clientPuntos = $cliente['puntos'];
  } else {
    $errorCliente = true;
  }
}

// Procesamiento del formulario de añadir puntos
if (isset($_POST['addPoints'])) {
  $cedula = $_POST['clientCedula'];
  $puntos = (int)$_POST['addPointsInput'];
  añadirPuntos($cedula, $puntos);
}

// Procesamiento del formulario de quitar puntos
if (isset($_POST['removePoints'])) {
  $cedula = $_POST['clientCedula'];
  $puntos = (int)$_POST['removePointsInput'];
  quitarPuntos($cedula, $puntos);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administrador - Electrolum</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header>
    <h1>Electrolum</h1>
  </header>
  <main>
    <h2>Panel de Administrador</h2>
    <form id="searchForm" method="POST">
      <input type="text" name="searchCedula" placeholder="Cédula del cliente" required>
      <button type="submit" name="buscarCliente">Buscar Cliente</button>
    </form>
    <div id="client-info" class="client-info">
      <?php if (isset($clientNombre)): ?>
        <h2>Información del Cliente</h2>
        <p>Nombre: <?php echo $clientNombre; ?></p>
        <p>Apellido: <?php echo $clientApellido; ?></p>
        <p>Cédula: <?php echo $clientCedula; ?></p>
        <p>Puntos: <?php echo $clientPuntos; ?></p>
        <div id="modifyPoints" class="modify-points">
          <form method="POST">
            <input type="hidden" name="clientCedula" value="<?php echo $clientCedula; ?>">
            <div>
              <input type="number" name="addPointsInput" placeholder="Cantidad de puntos a añadir" required>
              <button type="submit" name="addPoints">Añadir Puntos</button>
            </div>
          </form>
          <form method="POST">
            <input type="hidden" name="clientCedula" value="<?php echo $clientCedula; ?>">
            <div>
              <input type="number" name="removePointsInput" placeholder="Cantidad de puntos a quitar" required>
              <button type="submit" name="removePoints">Quitar Puntos</button>
            </div>
          </form>
        </div>
      <?php elseif (isset($errorCliente)): ?>
        <p>No se encontró el cliente</p>
      <?php endif; ?>
    </div>
    <button class="logout"><a href="logout.php">Logout</a></button>
  </main>

  <script src="firebase.js"></script>
  <script src="admin.js"></script>
</body>
</html>

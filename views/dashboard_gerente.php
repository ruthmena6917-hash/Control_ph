<?php
require '../auth/session.php';
verificarRol(['gerente']);
echo "Dashboard gerente funcionando";

?>
<a href="../auth/logout.php">Cerrar sesión</a>
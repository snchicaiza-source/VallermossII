<?php
// ConvenioController.php - Redirige al AdministradorController
// Este controlador ya no se usa, se mantiene por compatibilidad
session_start();
header('Location: ../views/administrador/convenios.php');
exit();

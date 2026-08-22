<?php
// config/Validador.php
// Validadores centralizados de backend (regex y reglas de negocio).
// Cada metodo devuelve NULL si el valor es valido, o un mensaje de error.

class Validador {

    // Texto obligatorio con longitud minima/maxima
    public static function texto($valor, $etiqueta, $min = 1, $max = 255) {
        $valor = trim((string)$valor);
        $longitud = mb_strlen($valor);
        if ($longitud < $min) {
            return "{$etiqueta} es obligatorio" . ($min > 1 ? " (mínimo {$min} caracteres)" : "") . ".";
        }
        if ($longitud > $max) {
            return "{$etiqueta} no puede exceder {$max} caracteres.";
        }
        return null;
    }

    // Correo electronico con formato valido
    public static function correo($valor, $etiqueta = 'El correo electrónico', $requerido = true) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return $requerido ? "{$etiqueta} es obligatorio." : null;
        }
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            return "{$etiqueta} no tiene un formato valido (ejemplo: nombre@dominio.com).";
        }
        if (mb_strlen($valor) > 100) {
            return "{$etiqueta} no puede exceder 100 caracteres.";
        }
        return null;
    }

    // Telefono/WhatsApp de Ecuador: 09XXXXXXXX, 5939XXXXXXXX o +593...
    public static function telefono($valor, $etiqueta = 'El teléfono', $requerido = false) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return $requerido ? "{$etiqueta} es obligatorio." : null;
        }
        $limpio = preg_replace('/[\s\-]/', '', $valor);
        if (!preg_match('/^(\+?593|0)9\d{8}$/', $limpio)) {
            return "{$etiqueta} debe ser un número válido de Ecuador (ejemplo: 0987654321 o +593987654321).";
        }
        return null;
    }

    // Cedula ecuatoriana: 10 digitos numericos
    public static function cedula($valor, $etiqueta = 'La cédula') {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return "{$etiqueta} es obligatoria.";
        }
        if (!preg_match('/^\d{10}$/', $valor)) {
            return "{$etiqueta} debe tener exactamente 10 digitos numericos.";
        }
        return null;
    }

    // Monto monetario: decimal positivo con hasta 2 decimales. Rechaza negativos, texto y simbolos.
    public static function dinero($valor, $etiqueta = 'El monto', $mayorQueCero = true) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return "{$etiqueta} es obligatorio.";
        }
        if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $valor)) {
            return "{$etiqueta} debe ser un número positivo con máximo 2 decimales (ejemplo: 150.50). No se permiten valores negativos ni texto.";
        }
        if ($mayorQueCero && (float)$valor <= 0) {
            return "{$etiqueta} debe ser mayor que $0.00.";
        }
        return null;
    }

    // Numero entero positivo
    public static function enteroPositivo($valor, $etiqueta = 'El valor', $minimo = 1) {
        $valor = trim((string)$valor);
        if ($valor === '' || !preg_match('/^\d+$/', $valor) || (int)$valor < $minimo) {
            return "{$etiqueta} debe ser un número entero mayor o igual a {$minimo}.";
        }
        return null;
    }

    // Fecha en formato YYYY-MM-DD valido
    public static function fecha($valor, $etiqueta = 'La fecha', $requerido = true) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return $requerido ? "{$etiqueta} es obligatoria." : null;
        }
        $partes = explode('-', $valor);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) || count($partes) !== 3 || !checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            return "{$etiqueta} no es una fecha valida.";
        }
        return null;
    }

    // Contrasena minimo 6 caracteres
    public static function contrasena($valor, $etiqueta = 'La contraseña') {
        $valor = (string)$valor;
        if (strlen($valor) < 6) {
            return "{$etiqueta} debe tener al menos 6 caracteres.";
        }
        if (strlen($valor) > 72) {
            return "{$etiqueta} no puede exceder 72 caracteres.";
        }
        return null;
    }

    // Recorre una lista de errores [campo => mensaje]; devuelve true si hay alguno.
    public static function hayErrores(array $errores) {
        return !empty(array_filter($errores));
    }

    // Convierte la lista de errores en texto plano separado por espacios.
    public static function primerError(array $errores) {
        foreach ($errores as $mensaje) {
            if ($mensaje !== null && $mensaje !== '') {
                return $mensaje;
            }
        }
        return null;
    }
}

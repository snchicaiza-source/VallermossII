<?php
// services/WhatsAppService.php
// Servicio de WhatsApp - Usa enlaces wa.me para abrir chats con mensaje pre-llenado
// Para envio automatico, configure Ultramsg en los campos de abajo

class WhatsAppService {

    private static $instanceId = '';
    private static $token = '';

    public static function configurar($instanceId, $token) {
        self::$instanceId = $instanceId;
        self::$token = $token;
    }

    public static function enviarMensaje($telefono, $mensaje) {
        if (empty(self::$instanceId) || empty(self::$token)) {
            return ['exito' => false, 'mensaje' => 'API no configurada'];
        }

        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        $apiUrl = "https://api.ultramsg.com/" . self::$instanceId . "/messages/chat";

        $data = [
            'token' => self::$token,
            'to' => $telefonoLimpio,
            'body' => $mensaje
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200)
            ? ['exito' => true, 'mensaje' => 'Enviado']
            : ['exito' => false, 'mensaje' => 'Error HTTP ' . $httpCode];
    }

    public static function formatearTelefono($telefono) {
        $limpio = preg_replace('/[^0-9]/', '', $telefono);

        // Si ya empieza con 593 (cod. Ecuador), esta bien
        if (substr($limpio, 0, 3) === '593') {
            return $limpio;
        }

        // Si empieza con 0, quitar el 0 y agregar 593
        if (substr($limpio, 0, 1) === '0') {
            return '593' . substr($limpio, 1);
        }

        // Si tiene 9 digitos (celular sin 0), agregar 593
        if (strlen($limpio) === 9) {
            return '593' . $limpio;
        }

        // Si tiene 10 digitos, asumir que el primero es el 0 local
        if (strlen($limpio) === 10) {
            return '593' . substr($limpio, 1);
        }

        return $limpio;
    }

    public static function generarEnlaceDirecto($telefono, $titulo, $mensaje) {
        $telefonoLimpio = self::formatearTelefono($telefono);

        $texto = "VALLERMOSSO II\n\n";
        $texto .= $titulo . "\n\n";
        $texto .= $mensaje;

        $textoUrl = urlencode($texto);

        return "https://wa.me/" . $telefonoLimpio . "?text=" . $textoUrl;
    }

    public static function notificarResidente($telefono, $asunto, $mensaje) {
        return self::enviarMensaje($telefono, $asunto . "\n\n" . $mensaje);
    }
}

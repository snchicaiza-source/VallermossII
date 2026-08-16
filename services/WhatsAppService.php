<?php
// services/WhatsAppService.php

class WhatsAppService {

    // Genera la URL de envío directo para WhatsApp Web / App
    public static function generarEnlaceDirecto($telefono, $titulo, $mensaje) {
        // Formatear el texto con Markdown básico de WhatsApp
        $textoFormateado = "📢 *VALLERMOSSO II - COMUNICADO OFICIAL*\n\n";
        $textoFormateado .= "📌 *" . $titulo . "*\n\n";
        $textoFormateado .= $mensaje . "\n\n";
        $textoFormateado .= "_Por favor no responda a este mensaje automático._";

        $textoUrl = urlencode($textoFormateado);
        
        // Limpiar número de teléfono (dejar solo dígitos)
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        return "https://api.whatsapp.com/send?phone=" . $telefonoLimpio . "&text=" . $textoUrl;
    }
}
?>
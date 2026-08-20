<?php
// config/mail_config.php
// Configuracion SMTP para envio de correos
// Instrucciones para Gmail:
// 1. Activa la verificacion en 2 pasos en tu cuenta de Google
// 2. Genera una "Contraseña de aplicacion" en: https://myaccount.google.com/apppasswords
// 3. Reemplaza los valores de abajo

return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'tu_correo@gmail.com',
    'smtp_pass' => 'tu_contraseña_de_aplicacion',
    'from_email' => 'tu_correo@gmail.com',
    'from_name'  => 'Vallermosso II - Administracion',
    'habilitado' => false,
];

/*
Configuracion para Gmail:
1. Activa la verificacion en 2 pasos en tu cuenta de Google
2. Ve a https://myaccount.google.com/apppasswords
3. Genera una "Contraseña de aplicacion"
4. Cambia smtp_user, smtp_pass y from_email con tu correo real
5. Cambia habilitado a true
*/

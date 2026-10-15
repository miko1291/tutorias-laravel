<?php

return [
    // Remitente institucional de los correos (en el original: Yii::$app->params['supportEmail']).
    'support_email' => env('SUPPORT_EMAIL'),

    // Destinatario controlado para el "correo de prueba" de expedientes pendientes.
    'expediente_notification_test_email' => env('EXPEDIENTE_NOTIFICATION_TEST_EMAIL'),
];

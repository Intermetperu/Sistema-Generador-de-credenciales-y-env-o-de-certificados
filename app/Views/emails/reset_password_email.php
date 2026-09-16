<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablece tu contraseña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

        body {
            background-color: #f1f3f6;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: #2e2e2e;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .email-header {
            background: linear-gradient(90deg, #4e73df, #2c7be5);
            padding: 30px;
            color: #fff;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-body {
            padding: 30px;
        }

        .email-body h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .email-body p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            background-color: #4e73df;
            color: #fff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }

        .email-footer {
            padding: 20px 30px;
            font-size: 13px;
            color: #999;
            background-color: #f8f9fc;
            text-align: center;
        }

        .email-logo {
            max-width: 120px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="email-wrapper">

        <div class="email-header">
            <!-- Puedes reemplazar el src por tu logo -->
            <!-- <img src="<?= base_url('assets/logo.png') ?>" alt="Logo" class="email-logo"> -->
            <h1><?= env('TITLE'); ?></h1>
        </div>

        <div class="email-body">
            <h2>Hola,</h2>
            <p>Hemos recibido una solicitud para restablecer tu contraseña. Para continuar, haz clic en el siguiente botón:</p>
            <a href="<?= $resetLink ?>" class="btn">Restablecer contraseña</a>
            <p style="margin-top: 30px;">Si tú no solicitaste este cambio, puedes ignorar este mensaje. Tu contraseña seguirá siendo segura.</p>
        </div>

        <div class="email-footer">
            &copy; <?= date('Y') ?> <?= env('TITLE'); ?>. Todos los derechos reservados.
        </div>

    </div>

</body>
</html>

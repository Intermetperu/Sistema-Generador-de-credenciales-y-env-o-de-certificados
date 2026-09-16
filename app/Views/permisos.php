<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>403 - Acceso Denegado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f2f4f8, #e6ecf0);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
        }

        .error-container {
            background: #fff;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            max-width: 480px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        .error-icon {
            font-size: 4.5rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .error-message {
            font-size: 1rem;
            color: #555;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background-color: #3498db;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .error-title {
                font-size: 1.5rem;
            }

            .error-icon {
                font-size: 3.5rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-/Nfpyj+jp1ZTzSpdqEdlvzjQjNOU+jktHJ2Ugd3nU1ZC7VJ7gAa62KjCAtGv12CM+dbkmVtYCBowJWDjArCdcg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="error-title">Acceso Denegado</div>
        <div class="error-message">No tienes permisos suficientes para ver esta página.</div>
        <a href="<?= base_url('dashboard') ?>" class="btn"><i class="fas fa-arrow-left me-1"></i> Volver al Dashboard</a>
    </div>
</body>
</html>

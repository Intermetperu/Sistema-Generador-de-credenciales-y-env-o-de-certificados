<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado - <?= esc($evento['nombre'] ?? '') ?></title>
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: Arial, sans-serif;
            color: #1e2433;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
        }
        .fondo-certificado {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            filter: blur(8px) brightness(1.05);
            transform: scale(1.1);
            z-index: 0;
        }
        .overlay-oscuro {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(20, 24, 34, 0.12);
            z-index: 1;
        }
        .popup {
            position: relative;
            z-index: 2;
            max-width: 640px;
            width: 100%;
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }
        h1 { font-size: 20px; margin: 0 0 8px; text-align: center; }
        p.subtitle { color: #7b8494; font-size: 14px; margin: 0 0 24px; text-align: center; }
        .lock-icon {
            width: 52px; height: 52px; border-radius: 50%;
            background: #eef3ff; color: #3b82f6;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin: 0 auto 16px;
        }
        iframe {
            width: 100%; height: 480px; border: 1px solid #e5e9f0; border-radius: 12px;
        }
        button, .btn-descargar button {
            margin-top: 22px; width: 100%; padding: 12px; border: none; border-radius: 999px;
            background: linear-gradient(135deg, #34d399, #15803d); color: #fff;
            font-weight: 700; font-size: 15px; cursor: pointer;
        }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-descargar { display: block; text-decoration: none; }
        .completado-icon { font-size: 40px; text-align: center; margin-bottom: 8px; }
        .aviso {
            background: #fff7e6; border: 1px solid #fde3a7; color: #b7791f;
            padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="fondo-certificado" style="background-image: url('<?= site_url("encuesta/{$token}/preview") ?>');"></div>
<div class="overlay-oscuro"></div>

<div class="popup">

<?php if (!$acceso['encuesta_completada']): ?>

    <div class="lock-icon">🔒</div>
    <h1>Certificado de <?= esc($acceso['nombre_completo']) ?></h1>
    <p class="subtitle">Completa la encuesta de <?= esc($evento['nombre'] ?? '') ?> para desbloquear tu certificado.</p>

    <?php if (empty($linkEncuesta)): ?>
        <div class="aviso">La encuesta de este evento todavía no está configurada. Contacta al organizador.</div>
    <?php else: ?>
        <iframe id="iframeEncuesta" src="<?= esc($linkEncuesta, 'attr') ?>"></iframe>

        <button type="button" id="btnConfirmarEncuesta" disabled>
            Completa la encuesta arriba para continuar...
        </button>

        <script>
        (function () {
            const iframe = document.getElementById('iframeEncuesta');
            const boton = document.getElementById('btnConfirmarEncuesta');
            let cargas = 0;
            let confirmado = false;

            iframe.addEventListener('load', function () {
                cargas++;

                // La 1ra carga es el formulario abriéndose.
                // De la 2da carga en adelante es porque el usuario dio "Enviar"
                // dentro del iframe y Google Forms navegó a su página de
                // confirmación (no podemos leer la URL por CORS, pero el
                // evento "load" sí se dispara igual).
                if (cargas > 1 && !confirmado) {
                    confirmado = true;
                    marcarComoCompletada();
                }
            });

            function marcarComoCompletada() {
                boton.disabled = true;
                boton.textContent = 'Verificando...';

                fetch('<?= site_url("encuesta/{$token}/guardar") ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                })
                .then(function (r) { return r.ok ? window.location.reload() : Promise.reject(); })
                .catch(function () {
                    boton.disabled = false;
                    boton.textContent = 'Hubo un problema, intenta de nuevo';
                    boton.onclick = marcarComoCompletada;
                });
            }

            // Fallback manual: por si el navegador bloquea la detección del
            // iframe (pasa a veces con bloqueadores de terceros / Safari
            // estricto). Se habilita recién tras un tiempo razonable, solo
            // como respaldo, no como validación principal.
            setTimeout(function () {
                if (!confirmado) {
                    boton.disabled = false;
                    boton.textContent = 'Ya completé la encuesta, verificar';
                    boton.onclick = marcarComoCompletada;
                }
            }, 45000);
        })();
        </script>
    <?php endif; ?>

<?php else: ?>

    <div class="completado-icon">✅</div>
    <h1>¡Gracias por tu respuesta!</h1>
    <p class="subtitle">Tu certificado de <?= esc($evento['nombre'] ?? '') ?> ya está listo.</p>

    <a href="<?= site_url("encuesta/{$token}/descargar") ?>" target="_blank" class="btn-descargar">
        <button type="button">Ver / descargar mi certificado</button>
    </a>

<?php endif; ?>

</div>
</body>
</html>
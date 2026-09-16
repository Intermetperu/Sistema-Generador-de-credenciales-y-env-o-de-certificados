<?php
function fechaPerzo($fecha)
{
    $datos = explode('-', $fecha);
    $anio = $datos[0];
    $me = ltrim($datos[1], "0");
    $dia = $datos[2];
    $mes = array(
        "",
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    );
    return $dia . " de " . $mes[$me] . " de " . $anio;
}

if (!function_exists('has_permission')) {
    /**
     * Verifica si el usuario tiene un permiso específico.
     *
     * @param string $permission El permiso que deseas verificar (por ejemplo, 'Usuarios:ver').
     * @return bool Retorna true si tiene el permiso, de lo contrario false.
     */
    function has_permission($permission)
    {
        // Obtener los permisos de la sesión
        $permissions = session()->get('permissions');

        // Verificar si el permiso existe en el array de permisos
        return in_array($permission, $permissions);
    }
}

function get_nombre_dia($fecha)
{
    $fechats = strtotime($fecha); //pasamos a timestamp

    //lo devuelve en numero 0 domingo, 1 lunes,....
    switch (date('w', $fechats)) {
        case 0:
            return "Domingo";
            break;
        case 1:
            return "Lunes";
            break;
        case 2:
            return "Martes";
            break;
        case 3:
            return "Miercoles";
            break;
        case 4:
            return "Jueves";
            break;
        case 5:
            return "Viernes";
            break;
        case 6:
            return "Sabado";
            break;
    }
}

function base64_url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Decodificar base64 seguro para URL
function base64_url_decode($data)
{
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
}

function encrypt_id($id, $secret_key)
{
    // Convertir ID a string
    $plaintext = (string)$id;

    // Generar un vector de inicialización (IV)
    $iv_length = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // Encriptar el ID usando AES-256-CBC
    $encrypted_text = openssl_encrypt($plaintext, 'AES-256-CBC', $secret_key, 0, $iv);

    // Concatenar IV y texto encriptado, luego codificar de forma segura para URL
    return base64_url_encode($iv . $encrypted_text);
}

function decrypt_id($encrypted_id, $secret_key)
{
    // Decodificar desde base64 seguro para URL
    $encrypted_data = base64_url_decode($encrypted_id);

    // Obtener el tamaño del IV
    $iv_length = openssl_cipher_iv_length('AES-256-CBC');

    // Separar el IV y el texto encriptado
    $iv = substr($encrypted_data, 0, $iv_length);
    $encrypted_text = substr($encrypted_data, $iv_length);

    // Desencriptar el texto encriptado usando AES-256-CBC
    $decrypted_text = openssl_decrypt($encrypted_text, 'AES-256-CBC', $secret_key, 0, $iv);

    // Convertir de vuelta a entero si es necesario
    return (int)$decrypted_text;
}


if (!function_exists('form_group_input')) {
    function form_group_input($col, $type, $name, $label, $required = false, $step = null, $min = null, $validation = null, $value = null)
    {
        $value = set_value($name, $value);
        $error = $validation ? $validation->getError($name) : null;

        $requiredSpan = $required ? '<span class="text-danger">*</span>' : '';
        $stepAttr = $step ? 'step="' . $step . '"' : '';
        $minAttr = $min ? 'min="' . $min . '"' : '';

        $errorHtml = $error ? '<span class="text-danger">' . $error . '</span>' : '';

        return <<<HTML
        <div class="col-md-$col">
            <div class="form-group mb-2">
                <label for="$name" class="form-label">$label $requiredSpan</label>
                <input type="$type" class="form-control" id="$name" name="$name" placeholder="$label" value="$value" $stepAttr $minAttr>
                $errorHtml
            </div>
        </div>
        HTML;
    }
}

if (!function_exists('form_group_select')) {
    function form_group_select($col, $name, $label, $options = [], $required = false, $selected = null, $validation = null)
    {
        $selected = set_value($name, $selected);
        $error = $validation ? $validation->getError($name) : null;

        $requiredSpan = $required ? '<span class="text-danger">*</span>' : '';
        $errorHtml = $error ? '<span class="text-danger">' . $error . '</span>' : '';

        $optionsHtml = '';
        foreach ($options as $value => $text) {
            $isSelected = ($value == $selected) ? 'selected' : '';
            $optionsHtml .= "<option value=\"$value\" $isSelected>$text</option>";
        }

        return <<<HTML
        <div class="col-md-$col">
            <div class="form-group mb-2">
                <label for="$name" class="form-label">$label $requiredSpan</label>
                <select class="form-select" id="$name" name="$name">
                    <option value="">Seleccione...</option>
                    $optionsHtml
                </select>
                $errorHtml
            </div>
        </div>
        HTML;
    }
}

function indexById($array) {
    $indexed = [];
    foreach ($array as $item) {
        $indexed[$item['id']] = $item;
    }
    return $indexed;
}


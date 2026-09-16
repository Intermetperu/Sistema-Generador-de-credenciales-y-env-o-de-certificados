# Nombre del Proyecto

Breve descripción de tu proyecto CodeIgniter: qué hace, para quién es y por qué existe.

##  Requisitos

- PHP >= 8.2
- Composer
- MySQL / MariaDB (o el motor de BD que uses)
- Extensiones PHP: `intl`, `mbstring`, `json`, `curl`, `mysqlnd`

2. Instala las dependencias:
   ```bash
   composer install
   ```

3. Copia el archivo de entorno y configúralo:
   ```bash
   cp env .env
   ```

4. Edita `.env` con tus datos:
   ```env
   CI_ENVIRONMENT = development

   app.baseURL = 'http://localhost:8080/'

   database.default.hostname = localhost
   database.default.database = nombre_bd
   database.default.username = tu_usuario
   database.default.password = tu_contraseña
   database.default.DBDriver = MySQLi
   ```

5. Da permisos a la carpeta `writable`:
   ```bash
   chmod -R 777 writable/
   ```

6. Ejecuta las migraciones (si aplica):
   ```bash
   php spark migrate
   ```

7. Levanta el servidor local:
   ```bash
   php spark serve
   ```

La aplicación quedará disponible en `http://localhost:8080`.

##  Comandos útiles

| Comando | Descripción |
|---|---|
| `php spark list` | Lista todos los comandos disponibles |
| `php spark make:controller Nombre` | Crea un nuevo controlador |
| `php spark make:model Nombre` | Crea un nuevo modelo |
| `php spark migrate` | Ejecuta las migraciones pendientes |
| `php spark db:seed NombreSeeder` | Ejecuta un seeder |

## Tests

```bash
composer test
```

## Contribuir

1. Haz un fork del proyecto
2. Crea una rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Haz commit de tus cambios (`git commit -m 'Agrega nueva funcionalidad'`)
4. Sube la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).

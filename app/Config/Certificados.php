<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Certificados extends BaseConfig
{
    public string $dirPlantillasCertificados = WRITEPATH . 'uploads/plantillas_certificados';
    public string $dirCertificadosGenerados = WRITEPATH . 'uploads/certificados_generados';
    public string $dirTmp = WRITEPATH . 'uploads/tmp';
}
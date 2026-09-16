<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\GoogleSheetsService;
use App\Models\CategoriaModel;
use App\Models\EventoModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $eventos = new EventoModel();
        $categoriasModel = new CategoriaModel();

        $evento = $eventos->obtenerActivo();

        $data = [
            'evento' => $evento,
            'errorSheets' => null,
            'totalParticipantes' => 0,
            'totalConfirmados' => 0,
            'totalPendientes' => 0,
            'porCategoria' => [],   // [{categoria, total, confirmados, pendientes}]
            'porDia' => [],         // [{fecha, total}]
        ];

        if (!$evento) {
            return view('admin/dashboard', $data);
        }

        $categorias = $categoriasModel->porEvento($evento['id']);

        try {
            $sheets = new GoogleSheetsService($evento['spreadsheet_id'], $evento['hoja_limite']);
        } catch (\Throwable $e) {
            $data['errorSheets'] = $e->getMessage();
            return view('admin/dashboard', $data);
        }

        $conteosPorDia = [];

        foreach ($categorias as $cat) {
            try {
                $resultado = $sheets->obtenerFilas($cat['nombre_hoja']);
            } catch (\Throwable $e) {
                // Si una hoja puntual falla (ej. nombre no coincide), la saltamos sin tumbar todo el dashboard
                continue;
            }

            $headers = $resultado['headers'];
            $filas = $resultado['rows'];

            $total = count($filas);
            $confirmados = 0;

            $colConfirmacion = $headers[0] ?? null;
            $colFecha = $headers[1] ?? null; // "Marca temporal" es la 2da columna visible, igual que en el script original

            foreach ($filas as $fila) {
                if ($colConfirmacion && ($fila[$colConfirmacion] ?? false) === true) {
                    $confirmados++;
                }

                if ($colFecha && !empty($fila[$colFecha])) {
                    $fechaTexto = (string) $fila[$colFecha];
                    // Se queda solo con la parte de fecha (DD/MM/YYYY), ignorando la hora si la trae
                    $soloFecha = trim(explode(' ', $fechaTexto)[0]);
                    if ($soloFecha !== '') {
                        $conteosPorDia[$soloFecha] = ($conteosPorDia[$soloFecha] ?? 0) + 1;
                    }
                }
            }

            $pendientes = $total - $confirmados;

            $data['porCategoria'][] = [
                'categoria' => $cat['nombre_hoja'],
                'total' => $total,
                'confirmados' => $confirmados,
                'pendientes' => $pendientes,
            ];

            $data['totalParticipantes'] += $total;
            $data['totalConfirmados'] += $confirmados;
            $data['totalPendientes'] += $pendientes;
        }

        // Ordenar por fecha (asumiendo formato DD/MM/YYYY) y quedarnos con los últimos 14 días con datos
        uksort($conteosPorDia, function ($a, $b) {
            return strtotime(str_replace('/', '-', $a)) <=> strtotime(str_replace('/', '-', $b));
        });
        $conteosPorDia = array_slice($conteosPorDia, -14, null, true);

        foreach ($conteosPorDia as $fecha => $total) {
            $data['porDia'][] = ['fecha' => $fecha, 'total' => $total];
        }

        return view('admin/dashboard', $data);
    }
}

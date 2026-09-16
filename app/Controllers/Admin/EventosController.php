<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;

class EventosController extends BaseController
{
    protected EventoModel $eventos;

    public function __construct()
    {
        $this->eventos = new EventoModel();
    }

    public function index()
    {
        return view('admin/eventos/index', [
            'title' => 'Eventos',
            'eventos' => $this->eventos->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('admin/eventos/form', [
            'title' => 'Nuevo evento',
            'evento' => null,
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost(['nombre', 'spreadsheet_id', 'hoja_limite', 'mailgun_domain', 'mailgun_api_key', 'mailgun_from']);

        if (!$this->eventos->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->eventos->errors());
        }

        $id = $this->eventos->getInsertID();

        // Si es el primer evento, lo activamos automáticamente
        if ($this->eventos->countAll() === 1) {
            $this->eventos->activar($id);
        }

        return redirect()->to(base_url('eventos'))->with('success', 'Evento creado correctamente.');
    }

    public function edit(int $id)
    {
        $evento = $this->eventos->find($id);
        if (!$evento) {
            return redirect()->to(base_url('eventos'))->with('error', 'Evento no encontrado.');
        }

        return view('admin/eventos/form', [
            'title' => 'Editar evento',
            'evento' => $evento,
        ]);
    }

    public function update(int $id)
    {
        $data = $this->request->getPost(['nombre', 'spreadsheet_id', 'hoja_limite', 'mailgun_domain', 'mailgun_api_key', 'mailgun_from']);

        $this->eventos->update($id, $data);

        if ($this->eventos->errors()) {
            return redirect()->back()->withInput()->with('errors', $this->eventos->errors());
        }

        return redirect()->to(base_url('eventos'))->with('success', 'Evento actualizado correctamente.');
    }

    public function activar(int $id)
    {
        $this->eventos->activar($id);

        return redirect()->to(base_url('eventos'))->with('success', 'Evento activado. Ya es el evento en uso en "Credenciales".');
    }

    public function delete(int $id)
    {
        $this->eventos->delete($id); // las categorías se borran en cascada (FK)

        return redirect()->to(base_url('eventos'))->with('success', 'Evento eliminado.');
    }
}

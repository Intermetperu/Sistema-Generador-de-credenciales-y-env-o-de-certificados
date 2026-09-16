<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    public function edit()
    {
        $userModel = new UserModel();
        $user = $userModel->find(session('user_id'));

        return view('admin/users/profile', ['user' => $user]);
    }

    public function update()
    {
        helper(['form']);

        $validationRules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'avatar' => 'uploaded[avatar]|is_image[avatar]|max_size[avatar,2048]|mime_in[avatar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        // Solo validar avatar si se sube
        if (!$this->request->getFile('avatar')->isValid()) {
            unset($validationRules['avatar']);
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $userModel = new \App\Models\UserModel();
        $id = session('user_id');

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
        ];

        // Procesar avatar si se subió
        $avatar = $this->request->getFile('avatar');
        if ($avatar->isValid()) {
            $avatarName = $avatar->getRandomName();
            $avatar->move('assets/images/avatars', $avatarName);
            $data['avatar'] = $avatarName;
        }

        $userModel->update($id, $data);
        return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
    }
    public function changePassword()
    {
        helper(['form']);

        $validationRules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $userModel = new \App\Models\UserModel();
        $id = session('user_id');
        $user = $userModel->find($id);

        $current = $this->request->getPost('current_password');
        $new = $this->request->getPost('new_password');

        if (!password_verify($current, $user['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual no es válida.');
        }

        $userModel->update($id, [
            'password' => password_hash($new, PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }
}

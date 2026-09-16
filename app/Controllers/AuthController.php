<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use App\Models\UserModel;
use Config\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return view('auth/login', [
                'validation' => $this->validator
            ]);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->select('users.*, r.name AS role, r.permissions')
            ->join('roles r', 'users.role_id = r.id')
            ->where('users.email', $email)
            ->where('users.is_active', 1)
            ->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $permissions = [];
                if (!empty($user['permissions'])) {
                    $decoded = json_decode($user['permissions'], true);
                    if (is_array($decoded)) {
                        $permissions = $decoded;
                    }
                }

                session()->set([
                    'user_id'   => $user['id'],
                    'name'     => $user['name'],
                    'email'     => $user['email'],
                    'role'     => $user['role'],
                    'permissions' => $permissions,
                    'logged_in' => true,
                ]);
                return redirect()->to('/dashboard');
            } else {
                $validation = Services::validation();
                $validation->setError('password', 'Contraseña incorrecta');
                return view('auth/login', ['validation' => $validation]);
            }
        } else {
            $validation = Services::validation();
            $validation->setError('email', 'El correo no está registrado');
            return view('auth/login', ['validation' => $validation]);
        }
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $email = $this->request->getPost('email');
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Correo no encontrado.');
        }

        $token = bin2hex(random_bytes(32));
        $userModel->update($user['id'], [
            'verification_token' => $token,
            'token_expire' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]);

        $this->sendResetEmail($email, $token);

        return redirect()->back()->with('success', 'Revisa tu correo electrónico para restablecer tu contraseña.');
    }

    private function getSettings()
    {
        $empresaModel = new \App\Models\SettingModel();
        $settings = $empresaModel->findAll();

        $config = [];
        foreach ($settings as $setting) {
            $config[$setting['key']] = $setting['value'];
        }

        return $config;
    }


    private function sendResetEmail($email, $token)
    {
        $mail = new PHPMailer(true);

        $settings = $this->getSettings();

        $host = $settings['smtp_host'] ?? '';
        $user = $settings['smtp_user'] ?? '';
        $pass = $settings['smtp_pass'] ?? '';
        $port = $settings['smtp_port'] ?? 465;
        $secure = $settings['smtp_secure'] ?? 'ssl'; // ejemplo: ssl, tls
        $fromEmail = $settings['smtp_user'] ?? ''; // o 'smtp_fromEmail'
        $fromName = $settings['smtp_fromName'] ?? 'Soporte';

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            $mail->SMTPSecure = $secure;
            $mail->Port = $port;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Restablece tu contraseña';

            $resetLink = base_url("reset-password/{$token}");
            $data = ['resetLink' => $resetLink];
            $body = view('emails/reset_password_email', $data);

            $mail->Body = $body;

            $mail->send();
        } catch (Exception $e) {
            log_message('error', 'Mailer Error: ' . $mail->ErrorInfo);
        }
    }


    public function resetPassword($token)
    {
        $userModel = new UserModel();
        $user = $userModel->where('verification_token', $token)
            ->where('token_expire >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('forgot-password')->with('error', 'Token inválido o expirado.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword($token)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'password' => 'required|min_length[6]|max_length[255]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->where('verification_token', $token)
            ->where('token_expire >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('forgot-password')->with('error', 'Token inválido o expirado.');
        }

        $userModel->update($user['id'], [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'verification_token' => null,
            'token_expire' => null,
        ]);

        return redirect()->to('/')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}

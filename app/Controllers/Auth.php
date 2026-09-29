<?php

namespace App\Controllers;

class Auth extends BaseController
{
    /**
     * Show passcode login form.
     */
    public function login()
    {
        $session = session();
        if ($session->get('is_authorized')) {
            return redirect()->to(site_url('admiralty'));
        }

        $data = [
            'title'       => 'Akses Sistem R-PASOET | PT. Eser Geosurvey Indonesia',
            'authError'   => $session->getFlashdata('auth_error'),
            'authSuccess' => $session->getFlashdata('auth_success'),
            'authInfo'    => $session->getFlashdata('auth_info'),
        ];

        return view('auth/login', $data);
    }

    /**
     * Process passcode verification.
     */
    public function processLogin()
    {
        $session = session();
        $submittedPasscode = trim((string) $this->request->getPost('passcode'));
        $submittedUserName = trim((string) $this->request->getPost('user_name'));

        // Retrieve valid passcode from .env (fallback default: 'esergeo2026')
        $validPasscode = (string) (env('APP_PASSCODE') ?: 'esergeo2026');

        if ($submittedPasscode === '' || !hash_equals($validPasscode, $submittedPasscode)) {
            return redirect()->to(site_url('login'))
                ->withInput()
                ->with('auth_error', 'Passcode akses kantor tidak sesuai. Silakan hubungi koordinator tim untuk mendapatkan passcode resmi.');
        }

        // Authorize session
        $displayName = $submittedUserName !== '' ? $submittedUserName : 'Surveyor Hidrografi (PT. Eser)';
        $session->set([
            'is_authorized'  => true,
            'auth_user_name' => $displayName,
            'auth_logged_at' => date('Y-m-d H:i:s'),
        ]);

        $redirectUrl = $session->get('auth_redirect_url') ?: site_url('admiralty');
        $session->remove('auth_redirect_url');

        return redirect()->to($redirectUrl)
            ->with('auth_success', 'Autentikasi berhasil. Selamat bekerja, ' . esc($displayName) . '!');
    }

    /**
     * Logout and destroy authorization session.
     */
    public function logout()
    {
        $session = session();
        $session->remove(['is_authorized', 'auth_user_name', 'auth_logged_at', 'auth_redirect_url']);
        
        return redirect()->to(site_url('login'))
            ->with('auth_info', 'Anda telah berhasil keluar dari sistem R-PASOET.');
    }
}

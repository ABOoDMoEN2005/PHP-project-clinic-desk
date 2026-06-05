<?php
/**
 * ============================================================
 * Project  : ClinicDesk - Clinic Management Dashboard
 * Name     : Abd alrahman Moen Hemaid | ID: 120240672
 * الجامعة  : الجامعة الإسلامية - غزة
 * Course   : SDEV 2106 / WDMM 2010 / MOBC 2102
 * Instructor: Eng. Mohammed Zuqlam | Semester 2, 2025-2026
 * ============================================================
 */

// AuthController.php - تسجيل الدخول والخروج
// Auth و CSRF موجودين مسبقاً من index.php

require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // showLogin() - عرض صفحة تسجيل الدخول
    public function showLogin(): void
    {
        // إذا داخل أصلاً روح للداشبورد
        if (Auth::check()) {
            redirect('?page=dashboard');
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    // handleLogin() - معالجة الـ POST
    public function handleLogin(): void
    {
        // تحقق من CSRF أولاً
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CSRF::validateToken($csrfToken)) {
            setFlash('error', 'Invalid request. Please try again.');
            redirect('?page=login');
        }

        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            setFlash('error', 'Please enter your email and password.');
            redirect('?page=login');
        }

        // نبحث عن المستخدم
        $user = $this->userModel->findByEmail($email);

        // رسالة خطأ عامة - ما نفصّل ليش غلط
        if (!$user) {
            setFlash('error', 'Invalid email or password.');
            redirect('?page=login');
        }

        // نتحقق من أن الحساب نشط
        if ((int)$user['is_active'] !== 1) {
            setFlash('error', 'Account suspended. Contact administrator.');
            redirect('?page=login');
        }

        // نتحقق من الباسورد
        if (!password_verify($password, $user['password'])) {
            setFlash('error', 'Invalid email or password.');
            redirect('?page=login');
        }

        // كل شي صح - نسجّل الدخول
        CSRF::refreshToken(); // نجدد الـ token بعد الدخول
        Auth::login($user);

        setFlash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect('?page=dashboard');
    }

    // logout() - تسجيل الخروج (POST فقط)
    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (CSRF::validateToken($csrfToken)) {
                Auth::logout();
            }
        }
        redirect('?page=login');
    }
}

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

// Auth.php - إدارة تسجيل الدخول والصلاحيات

require_once __DIR__ . '/helpers.php';

class Auth
{
    // login() - بتحفظ بيانات المستخدم بالـ session
    // session_regenerate_id بتحمي من Session Fixation attack
    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
        session_regenerate_id(true);
    }

    // logout() - تسجيل خروج كامل
    public static function logout(): void
    {
        session_unset();
        session_destroy();
        // نبدأ session جديدة عشان نقدر نستخدم redirect
        session_start();
        redirect('?page=login');
    }

    // check() - هل المستخدم داخل؟
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    // currentUser() - بيانات المستخدم الحالي
    public static function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // role() - دور المستخدم
    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? '';
    }

    // requireRole() - أول سطر بكل controller action
    // بيتحقق من الدور قبل أي شي تاني
    public static function requireRole(string ...$roles): void
    {
        if (!self::check()) {
            redirect('?page=login');
        }

        if (!in_array(self::role(), $roles)) {
            redirect('?page=error&code=403');
        }
    }
}

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

// CSRF.php - حماية النماذج من هجوم CSRF
// كل form لازم تحمل token سري - الـ server بيتحقق منه قبل أي إجراء

class CSRF
{
    // generateToken() - ينشئ token ويحفظه بالـ session
    public static function generateToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // validateToken() - يتحقق من صحة الـ token
    // hash_equals أآمن من === لأنها تحمي من Timing Attack
    public static function validateToken(string $token): bool
    {
        if (empty($token) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // refreshToken() - نجدد الـ token بعد كل POST ناجح
    public static function refreshToken(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

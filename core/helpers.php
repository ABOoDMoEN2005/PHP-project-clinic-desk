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

// helpers.php - وظائف مساعدة بنستخدمها في كل المشروع

// redirect() - إعادة توجيه المستخدم
function redirect(string $url): void
{
    // نبني الـ URL الكامل
    $fullUrl = BASE_URL . ltrim($url, '/');
    header('Location: ' . $fullUrl);
    exit();
}

// sanitize() - تنظيف المدخلات من XSS
// htmlspecialchars بتحول < > " ' & لـ HTML entities آمنة
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// setFlash() - رسالة مؤقتة تظهر مرة واحدة
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

// formatDate() - تنسيق التاريخ
function formatDate(string $date): string
{
    if (empty($date) || $date === '0000-00-00') return '-';
    $d = date_create($date);
    return $d ? date_format($d, 'M d, Y') : $date;
}

// formatTime() - تنسيق الوقت
function formatTime(string $time): string
{
    if (empty($time)) return '-';
    $t = date_create($time);
    return $t ? date_format($t, 'g:i A') : $time;
}

// getStatusBadge() - badge ملون لحالة الموعد
function getStatusBadge(string $status): string
{
    $classes = [
        'pending'   => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $class = $classes[$status] ?? 'secondary';
    return '<span class="badge badge-' . $class . '">' . sanitize($status) . '</span>';
}

// generateTimeSlots() - أوقات المواعيد من 9 صباحاً لـ 4 مساءً
function generateTimeSlots(): array
{
    $slots = [];
    $start = strtotime('09:00');
    $end   = strtotime('16:00');
    for ($t = $start; $t <= $end; $t += 1800) {
        $slots[] = date('H:i', $t);
    }
    return $slots;
}

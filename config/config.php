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

// اسم التطبيق
define('APP_NAME', 'ClinicDesk');

// الـ URL الأساسي - حسب اسم مجلدك في htdocs
define('BASE_URL', '/clinicdesk/');

// كم عنصر بكل صفحة pagination
define('ITEMS_PER_PAGE', 10);

// حجم أقصى للصور - 1MB
define('MAX_IMAGE_SIZE', 1 * 1024 * 1024);

// حجم أقصى للـ PDF - 3MB
define('MAX_PDF_SIZE', 3 * 1024 * 1024);

// مجلد الرفع
define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/');

// إظهار الأخطاء للتطوير
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

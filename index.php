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

// =============================================================
// index.php - الـ Front Controller
// كل طلب يمر من هون - الترتيب مهم جداً!
// =============================================================

// 1 - config أول شي قبل أي شي
require_once __DIR__ . '/config/config.php';

// 2 - session_start بعد config مباشرة
session_start();

// 3 - helpers و core classes
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CSRF.php';

// 4 - نقرأ الصفحة والـ action من الـ URL
$page   = $_GET['page']   ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// 5 - التوجيه حسب الصفحة
switch ($page) {

    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handleLogin();
        } else {
            $controller->showLogin();
        }
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'dashboard':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'users':
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        switch ($action) {
            case 'create': $controller->create(); break;
            case 'store':  $controller->store();  break;
            case 'edit':   $controller->edit();   break;
            case 'update': $controller->update(); break;
            case 'toggle': $controller->toggle(); break;
            default:       $controller->index();  break;
        }
        break;

    case 'doctors':
        require_once __DIR__ . '/controllers/DoctorController.php';
        $controller = new DoctorController();
        switch ($action) {
            case 'edit':   $controller->edit();   break;
            case 'update': $controller->update(); break;
            default:       $controller->index();  break;
        }
        break;

    case 'appointments':
        require_once __DIR__ . '/controllers/AppointmentController.php';
        $controller = new AppointmentController();
        switch ($action) {
            case 'book':          $controller->book();         break;
            case 'store':         $controller->store();        break;
            case 'detail':        $controller->detail();       break;
            case 'update_status': $controller->updateStatus(); break;
            case 'cancel':        $controller->cancel();       break;
            default:              $controller->index();        break;
        }
        break;

    case 'prescriptions':
        require_once __DIR__ . '/controllers/PrescriptionController.php';
        $controller = new PrescriptionController();
        switch ($action) {
            case 'add':      $controller->add();      break;
            case 'store':    $controller->store();    break;
            case 'download': $controller->download(); break;
            default:         $controller->index();    break;
        }
        break;

    case 'specializations':
        require_once __DIR__ . '/controllers/SpecializationController.php';
        $controller = new SpecializationController();
        switch ($action) {
            case 'store':  $controller->store();  break;
            case 'delete': $controller->delete(); break;
            default:       $controller->index();  break;
        }
        break;

    case 'reports':
        require_once __DIR__ . '/controllers/ReportController.php';
        $controller = new ReportController();
        $controller->index();
        break;

    case 'error':
        $code = $_GET['code'] ?? '404';
        if ($code === '403') {
            require_once __DIR__ . '/views/errors/403.php';
        } else {
            require_once __DIR__ . '/views/errors/404.php';
        }
        break;

    default:
        require_once __DIR__ . '/views/errors/404.php';
        break;
}

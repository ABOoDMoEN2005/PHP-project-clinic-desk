<?php
/**
 * ============================================================
 * Project  : ClinicDesk - Clinic Management Dashboard
 * ------------------------------------------------------------
 * الاسم    : عبد الرحمن معين حميد
 * Name     : Abd alrahman Moen Hemaid
 * ID       : 120240672
 * ------------------------------------------------------------
 * الجامعة  : الجامعة الإسلامية - غزة
 * Course   : SDEV 2106 / WDMM 2010 / MOBC 2102
 * Semester : Semester 2, 2025-2026
 * Instructor: Eng. Mohammed Zuqlam
 * ============================================================
 */

// =============================================================
// ملف DashboardController.php - إدارة صفحات الـ Dashboard
//
// كل دور (admin/doctor/patient) عنده dashboard خاص فيه
// هون بنشوف مين داخل ونعرضله الـ dashboard المناسب
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class DashboardController
{
    private AppointmentModel  $appointmentModel;
    private UserModel         $userModel;
    private DoctorModel       $doctorModel;
    private PrescriptionModel $prescriptionModel;

    public function __construct()
    {
        $this->appointmentModel  = new AppointmentModel();
        $this->userModel         = new UserModel();
        $this->doctorModel       = new DoctorModel();
        $this->prescriptionModel = new PrescriptionModel();
    }

    // =============================================================
    // index() - بيعرض الـ dashboard المناسب حسب الدور
    // =============================================================
    public function index(): void
    {
        // لازم المستخدم يكون داخل
        Auth::requireRole('admin', 'doctor', 'patient');

        $role = Auth::role();

        // نوجه حسب الدور
        if ($role === 'admin') {
            $this->adminDashboard();
        } elseif ($role === 'doctor') {
            $this->doctorDashboard();
        } else {
            $this->patientDashboard();
        }
    }

    // =============================================================
    // adminDashboard() - إحصاءات للأدمن
    //
    // بنجيب: عدد المستخدمين حسب الدور، مواعيد اليوم،
    //         إحصاء الأسبوع، آخر 5 مواعيد
    // =============================================================
    private function adminDashboard(): void
    {
        // عدد المستخدمين حسب الدور (admin, doctor, patient)
        // استعلام واحد بيجيب الكل
        $userCounts  = $this->userModel->countByRole();

        // عدد مواعيد اليوم
        $todayCount  = $this->appointmentModel->countTodayAppointments();

        // إحصاء المواعيد حسب الحالة لهذا الأسبوع
        $weeklyStats = $this->appointmentModel->getWeeklyStatusCounts();

        // آخر 5 مواعيد للجدول السريع
        $recentAppts = $this->appointmentModel->getRecentForAdmin();

        // نعرض صفحة الـ dashboard مع البيانات
        require_once __DIR__ . '/../views/dashboard/admin.php';
    }

    // =============================================================
    // doctorDashboard() - الدكتور بيشوف مواعيده وإحصاءاته
    // =============================================================
    private function doctorDashboard(): void
    {
        $currentUser = Auth::currentUser();
        $userId      = $currentUser['id'];

        // نجيب بيانات الدكتور
        $doctor = $this->doctorModel->findByUserId($userId);

        if (!$doctor) {
            // إذا الحساب doctor بس ما عنده record بجدول doctors
            setFlash('error', 'Doctor profile not found. Contact admin.');
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $doctorId = $doctor['id'];

        // مواعيد اليوم - أهم شي للدكتور يشوفه
        $todayAppointments = $this->appointmentModel->getTodayByDoctor($doctorId);

        // إحصاءات الشهر الحالي
        $monthlyStats = $this->getMonthlyStatsForDoctor($doctorId);

        require_once __DIR__ . '/../views/dashboard/doctor.php';
    }

    // =============================================================
    // patientDashboard() - المريض بشوف مواعيده القادمة وإحصاءاته
    // =============================================================
    private function patientDashboard(): void
    {
        $currentUser = Auth::currentUser();
        $patientId   = $currentUser['id'];

        // الموعد القادم - نعرضه بشكل بارز فوق الصفحة
        $nextAppointment = $this->appointmentModel->getUpcomingByPatient($patientId);

        // عدد الوصفات المتاحة
        $prescriptionCount = $this->prescriptionModel->countByPatient($patientId);

        // عدد المواعيد النشطة (pending + confirmed)
        $activeAppointmentsCount = $this->getActiveAppointmentsCount($patientId);

        // عدد المواعيد المكتملة
        $completedCount = $this->getCompletedAppointmentsCount($patientId);

        require_once __DIR__ . '/../views/dashboard/patient.php';
    }

    // =============================================================
    // وظائف مساعدة خاصة لحساب الإحصاءات
    // =============================================================

    private function getMonthlyStatsForDoctor(int $doctorId): array
    {
        // نستخدم AppointmentModel لحساب إحصاءات الشهر
        // بنعمل countFiltered مع فلاتر الشهر الحالي
        $thisMonth = date('Y-m-01');  // أول يوم بالشهر الحالي

        return [
            'total'     => $this->appointmentModel->countFiltered('doctor', $doctorId, ['date_from' => $thisMonth]),
            'pending'   => $this->appointmentModel->countFiltered('doctor', $doctorId, ['date_from' => $thisMonth, 'status' => 'pending']),
            'completed' => $this->appointmentModel->countFiltered('doctor', $doctorId, ['date_from' => $thisMonth, 'status' => 'completed']),
        ];
    }

    private function getActiveAppointmentsCount(int $patientId): int
    {
        // عدد مواعيد pending + confirmed
        $pending   = $this->appointmentModel->countFiltered('patient', $patientId, ['status' => 'pending']);
        $confirmed = $this->appointmentModel->countFiltered('patient', $patientId, ['status' => 'confirmed']);
        return $pending + $confirmed;
    }

    private function getCompletedAppointmentsCount(int $patientId): int
    {
        return $this->appointmentModel->countFiltered('patient', $patientId, ['status' => 'completed']);
    }
}

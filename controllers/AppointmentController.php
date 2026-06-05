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
// ملف AppointmentController.php - إدارة المواعيد
//
// الـ patient بحجز، الـ doctor بتأكيد/إتمام، الـ admin بشوف الكل
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class AppointmentController
{
    private AppointmentModel  $appointmentModel;
    private DoctorModel       $doctorModel;
    private PrescriptionModel $prescriptionModel;

    public function __construct()
    {
        $this->appointmentModel  = new AppointmentModel();
        $this->doctorModel       = new DoctorModel();
        $this->prescriptionModel = new PrescriptionModel();
    }

    // =============================================================
    // index() - قائمة المواعيد حسب الدور
    // =============================================================
    public function index(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $currentUser = Auth::currentUser();
        $role        = Auth::role();
        $currentPage = (int) ($_GET['page_num'] ?? 1);

        // نجمع الفلاتر من الـ URL
        $filters = [
            'status'      => $_GET['status']      ?? '',
            'date_from'   => $_GET['date_from']   ?? '',
            'date_to'     => $_GET['date_to']     ?? '',
            'doctor_id'   => $_GET['doctor_id']   ?? '',
            'patient_name'=> $_GET['patient_name']?? '',
        ];

        // نجيب المواعيد حسب الدور
        if ($role === 'patient') {
            $appointments = $this->appointmentModel->getByPatient(
                $currentUser['id'], $currentPage, $filters
            );
            $totalCount = $this->appointmentModel->countFiltered(
                'patient', $currentUser['id'], $filters
            );

        } elseif ($role === 'doctor') {
            // نجيب الـ doctor ID من جدول doctors
            $doctor = $this->doctorModel->findByUserId($currentUser['id']);
            if (!$doctor) {
                setFlash('error', 'Doctor profile not found.');
                redirect('?page=dashboard');
            }
            $doctorId     = $doctor['id'];
            $appointments = $this->appointmentModel->getByDoctor(
                $doctorId, $currentPage, $filters
            );
            $totalCount   = $this->appointmentModel->countFiltered(
                'doctor', $doctorId, $filters
            );

        } else {
            // admin - يشوف الكل
            $appointments = $this->appointmentModel->getAll($currentPage, $filters);
            $totalCount   = $this->appointmentModel->countFiltered('all', 0, $filters);
        }

        $paginator = new Paginator($totalCount, ITEMS_PER_PAGE, $currentPage);

        // للفلتر بنجيب قائمة الأطباء
        $doctors = $this->doctorModel->getAll();

        require_once __DIR__ . '/../views/appointments/list.php';
    }

    // =============================================================
    // book() - عرض نموذج حجز موعد (المريض فقط)
    // =============================================================
    public function book(): void
    {
        Auth::requireRole('patient');

        // نجيب قائمة الأطباء للقائمة المنسدلة
        $doctors    = $this->doctorModel->getAll();
        $timeSlots  = generateTimeSlots();

        require_once __DIR__ . '/../views/appointments/book.php';
    }

    // =============================================================
    // store() - حفظ الموعد الجديد (POST من المريض)
    // =============================================================
    public function store(): void
    {
        Auth::requireRole('patient');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=appointments&action=book');
        }

        $currentUser = Auth::currentUser();
        $doctorId    = (int) ($_POST['doctor_id']  ?? 0);
        $apptDate    = $_POST['appt_date'] ?? '';
        $apptTime    = $_POST['appt_time'] ?? '';
        $reason      = sanitize($_POST['reason'] ?? '');

        // تحقق من البيانات
        if (!$doctorId || empty($apptDate) || empty($apptTime)) {
            setFlash('error', 'Please fill all required fields.');
            redirect('?page=appointments&action=book');
        }

        // التحقق من إن التاريخ مش بالماضي
        if ($apptDate < date('Y-m-d')) {
            setFlash('error', 'Cannot book appointments in the past.');
            redirect('?page=appointments&action=book');
        }

        // التحقق من إن اليوم من أيام عمل الدكتور
        $availableDays = $this->doctorModel->getAvailableDays($doctorId);
        $dayOfWeek     = date('D', strtotime($apptDate));  // مثلاً "Mon"

        if (!in_array($dayOfWeek, $availableDays)) {
            setFlash('error', 'Doctor is not available on this day.');
            redirect('?page=appointments&action=book');
        }

        // التحقق من التعارض مع مواعيد موجودة
        if ($this->appointmentModel->hasConflict($doctorId, $apptDate, $apptTime)) {
            setFlash('error', 'This time slot is already booked. Please choose another time.');
            redirect('?page=appointments&action=book');
        }

        // حجز الموعد
        $booked = $this->appointmentModel->book([
            'patient_id' => $currentUser['id'],
            'doctor_id'  => $doctorId,
            'appt_date'  => $apptDate,
            'appt_time'  => $apptTime,
            'reason'     => $reason,
        ]);

        if ($booked) {
            setFlash('success', 'Appointment booked successfully!');
            redirect('?page=appointments');
        } else {
            setFlash('error', 'This slot is already taken. Please choose another time.');
            redirect('?page=appointments&action=book');
        }
    }

    // =============================================================
    // detail() - تفاصيل موعد واحد
    // =============================================================
    public function detail(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $id          = (int) ($_GET['id'] ?? 0);
        $appointment = $this->appointmentModel->findById($id);

        if (!$appointment) {
            setFlash('error', 'Appointment not found.');
            redirect('?page=appointments');
        }

        $currentUser = Auth::currentUser();
        $role        = Auth::role();

        // التحقق من الملكية - المريض ما يشوف مواعيد الغير!
        if ($role === 'patient' && (int)$appointment['patient_id'] !== $currentUser['id']) {
            redirect('?page=error&code=403');
        }

        // الدكتور ما يشوف مواعيد دكاترة ثانيين
        if ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId($currentUser['id']);
            if (!$doctor || (int)$appointment['doctor_id'] !== $doctor['id']) {
                redirect('?page=error&code=403');
            }
        }

        // نجيب الوصفة إذا كانت موجودة
        $prescription = $this->prescriptionModel->findByAppointmentId($id);

        require_once __DIR__ . '/../views/appointments/detail.php';
    }

    // =============================================================
    // updateStatus() - تغيير حالة الموعد (الدكتور والأدمن)
    // =============================================================
    public function updateStatus(): void
    {
        Auth::requireRole('admin', 'doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=appointments');
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = sanitize($_POST['doctor_notes'] ?? '');

        // نتأكد إن الحالة صحيحة
        $validStatuses = ['confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            setFlash('error', 'Invalid status.');
            redirect('?page=appointments');
        }

        // الدكتور بس يقدر يغير مواعيده هو
        if (Auth::role() === 'doctor') {
            $appointment = $this->appointmentModel->findById($id);
            $currentUser = Auth::currentUser();
            $doctor      = $this->doctorModel->findByUserId($currentUser['id']);

            if (!$doctor || (int)$appointment['doctor_id'] !== $doctor['id']) {
                redirect('?page=error&code=403');
            }
        }

        $this->appointmentModel->updateStatus($id, $status, $notes);

        setFlash('success', 'Appointment status updated to ' . $status . '.');
        redirect('?page=appointments&action=detail&id=' . $id);
    }

    // =============================================================
    // cancel() - إلغاء موعد (المريض بس يلغي مواعيد pending)
    // =============================================================
    public function cancel(): void
    {
        Auth::requireRole('patient');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=appointments');
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $appointment = $this->appointmentModel->findById($id);
        $currentUser = Auth::currentUser();

        // التحقق من الملكية
        if (!$appointment || (int)$appointment['patient_id'] !== $currentUser['id']) {
            redirect('?page=error&code=403');
        }

        // المريض بس يقدر يلغي المواعيد اللي status=pending
        if ($appointment['status'] !== 'pending') {
            setFlash('error', 'Only pending appointments can be cancelled.');
            redirect('?page=appointments');
        }

        $this->appointmentModel->updateStatus($id, 'cancelled');
        setFlash('success', 'Appointment cancelled successfully.');
        redirect('?page=appointments');
    }
}

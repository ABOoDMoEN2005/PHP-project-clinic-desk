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
// ملف PrescriptionController.php - إدارة الوصفات الطبية
//
// الدكتور بضيف وصفات، المريض بشوف ويحمّل الـ PDF
// الملفات محمية - ما نقدر نوصلها مباشرة!
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class PrescriptionController
{
    private PrescriptionModel $prescriptionModel;
    private AppointmentModel  $appointmentModel;
    private DoctorModel       $doctorModel;

    public function __construct()
    {
        $this->prescriptionModel = new PrescriptionModel();
        $this->appointmentModel  = new AppointmentModel();
        $this->doctorModel       = new DoctorModel();
    }

    // =============================================================
    // index() - قائمة الوصفات للمريض
    // =============================================================
    public function index(): void
    {
        Auth::requireRole('patient');

        $currentUser   = Auth::currentUser();
        $prescriptions = $this->prescriptionModel->getByPatient($currentUser['id']);

        require_once __DIR__ . '/../views/prescriptions/list.php';
    }

    // =============================================================
    // add() - عرض نموذج إضافة وصفة (الدكتور فقط)
    // =============================================================
    public function add(): void
    {
        Auth::requireRole('doctor');

        $apptId      = (int) ($_GET['appt_id'] ?? 0);
        $appointment = $this->appointmentModel->findById($apptId);
        $currentUser = Auth::currentUser();

        // نتحقق إن الموعد موجود
        if (!$appointment) {
            setFlash('error', 'Appointment not found.');
            redirect('?page=appointments');
        }

        // نتحقق إن الموعد تبع هاد الدكتور
        $doctor = $this->doctorModel->findByUserId($currentUser['id']);
        if (!$doctor || (int)$appointment['doctor_id'] !== $doctor['id']) {
            redirect('?page=error&code=403');
        }

        // نتحقق إن الموعد مكتمل
        if ($appointment['status'] !== 'completed') {
            setFlash('error', 'Prescription can only be added to completed appointments.');
            redirect('?page=appointments&action=detail&id=' . $apptId);
        }

        // نتحقق إن ما في وصفة مسبقاً
        $existing = $this->prescriptionModel->findByAppointmentId($apptId);
        if ($existing) {
            setFlash('error', 'A prescription already exists for this appointment.');
            redirect('?page=appointments&action=detail&id=' . $apptId);
        }

        require_once __DIR__ . '/../views/prescriptions/add.php';
    }

    // =============================================================
    // store() - حفظ الوصفة الجديدة مع رفع الملف (POST)
    // =============================================================
    public function store(): void
    {
        Auth::requireRole('doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=appointments');
        }

        $apptId      = (int) ($_POST['appointment_id'] ?? 0);
        $diagnosis   = sanitize($_POST['diagnosis']   ?? '');
        $medications = sanitize($_POST['medications'] ?? '');
        $notes       = sanitize($_POST['notes']       ?? '');
        $currentUser = Auth::currentUser();

        // تحقق من البيانات الأساسية
        if (empty($diagnosis) || empty($medications)) {
            setFlash('error', 'Diagnosis and medications are required.');
            redirect('?page=prescriptions&action=add&appt_id=' . $apptId);
        }

        // التحقق من الملكية مجدداً
        $appointment = $this->appointmentModel->findById($apptId);
        $doctor      = $this->doctorModel->findByUserId($currentUser['id']);

        if (!$doctor || (int)$appointment['doctor_id'] !== $doctor['id']) {
            redirect('?page=error&code=403');
        }

        // ==========================================================
        // معالجة رفع الملف PDF (اختياري)
        //
        // الخطوات:
        // 1. نتحقق إن في ملف مرفوع
        // 2. نتحقق من الحجم
        // 3. نتحقق من النوع بـ finfo_file() (مش بالامتداد!)
        // 4. نحفظ بمجلد محمي
        // ==========================================================
        $filePath = null;

        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['prescription_file'];

            // تحقق من الحجم - أقصى 3MB
            if ($file['size'] > MAX_PDF_SIZE) {
                setFlash('error', 'PDF file is too large. Maximum is 3MB.');
                redirect('?page=prescriptions&action=add&appt_id=' . $apptId);
            }

            // تحقق من نوع الملف بـ finfo_file()
            // ليش مش نستخدم $_FILES['type']؟
            // لأن المهاجم ممكن يغير الـ MIME type بسهولة!
            // finfo_file بتقرأ محتوى الملف الفعلي وتحدد نوعه
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($mimeType !== 'application/pdf') {
                setFlash('error', 'Only PDF files are allowed.');
                redirect('?page=prescriptions&action=add&appt_id=' . $apptId);
            }

            // اسم الملف الجديد - بنستخدم appointment ID والوقت
            // عشان ما في تعارض بين الأسماء
            $fileName = 'prescription_' . $apptId . '_' . time() . '.pdf';
            $destPath = UPLOAD_DIR . 'prescriptions/' . $fileName;

            // ننقل الملف للمجلد المطلوب
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                setFlash('error', 'Failed to upload file. Try again.');
                redirect('?page=prescriptions&action=add&appt_id=' . $apptId);
            }

            // نحفظ اسم الملف بس مش المسار الكامل
            $filePath = $fileName;
        }

        // حفظ الوصفة بقاعدة البيانات
        $this->prescriptionModel->create([
            'appointment_id' => $apptId,
            'diagnosis'      => $diagnosis,
            'medications'    => $medications,
            'notes'          => $notes,
            'file_path'      => $filePath,
        ]);

        setFlash('success', 'Prescription added successfully!');
        redirect('?page=appointments&action=detail&id=' . $apptId);
    }

    // =============================================================
    // download() - تحميل ملف الوصفة PDF بطريقة آمنة
    //
    // ليش ما نتيح الملف مباشرة؟
    // لأن لو عطينا رابط مباشر، أي شخص يعرف الرابط يقدر يحمّل!
    // هون بنتحقق من الهوية والملكية قبل نسمح بالتحميل
    // =============================================================
    public function download(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $prescriptionId = (int) ($_GET['id'] ?? 0);
        $prescription   = $this->prescriptionModel->findById($prescriptionId);

        if (!$prescription) {
            setFlash('error', 'Prescription not found.');
            redirect('?page=prescriptions');
        }

        $currentUser = Auth::currentUser();
        $role        = Auth::role();

        // التحقق من الملكية حسب الدور
        if ($role === 'patient') {
            // المريض بس يحمّل وصفاته هو
            if ((int)$prescription['patient_id'] !== $currentUser['id']) {
                redirect('?page=error&code=403');
            }
        } elseif ($role === 'doctor') {
            // الدكتور بس يوصل لوصفات مواعيده هو
            $doctor = $this->doctorModel->findByUserId($currentUser['id']);
            if (!$doctor || (int)$prescription['doctor_id'] !== $doctor['id']) {
                redirect('?page=error&code=403');
            }
        }
        // الأدمن يقدر يحمل أي شي

        // نتحقق إن في ملف مرفوع أصلاً
        if (empty($prescription['file_path'])) {
            setFlash('error', 'No PDF file attached to this prescription.');
            redirect('?page=prescriptions');
        }

        // نجيب المسار الكامل للملف
        $fullPath = UPLOAD_DIR . 'prescriptions/' . $prescription['file_path'];

        // نتحقق إن الملف موجود على السيرفر
        if (!file_exists($fullPath)) {
            setFlash('error', 'File not found on server.');
            redirect('?page=prescriptions');
        }

        // نرسل الملف للمتصفح
        // هاي الـ headers بتقول للمتصفح: هاد ملف PDF، حمّله
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription.pdf"');
        header('Content-Length: ' . filesize($fullPath));

        // readfile بتقرأ الملف وترسله مباشرة للمتصفح
        readfile($fullPath);
        exit();
    }
}

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
// ملف DoctorController.php - إدارة بيانات الأطباء
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class DoctorController
{
    private DoctorModel         $doctorModel;
    private SpecializationModel $specModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->specModel   = new SpecializationModel();
    }

    // =============================================================
    // index() - قائمة الأطباء (أدمن فقط)
    // =============================================================
    public function index(): void
    {
        Auth::requireRole('admin');

        $currentPage = (int) ($_GET['page_num'] ?? 1);
        $doctors     = $this->doctorModel->getAllPaginated($currentPage);
        $totalCount  = $this->doctorModel->countAll();
        $paginator   = new Paginator($totalCount, ITEMS_PER_PAGE, $currentPage);

        require_once __DIR__ . '/../views/doctors/list.php';
    }

    // =============================================================
    // edit() - تعديل بيانات دكتور (أدمن + الدكتور نفسه)
    // =============================================================
    public function edit(): void
    {
        Auth::requireRole('admin', 'doctor');

        $currentUser = Auth::currentUser();
        $role        = Auth::role();

        // إذا دكتور، يقدر يعدل بياناته هو بس
        if ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId($currentUser['id']);
        } else {
            // الأدمن يقدر يعدل أي دكتور
            $id     = (int) ($_GET['id'] ?? 0);
            $doctor = $this->doctorModel->findById($id);
        }

        if (!$doctor) {
            setFlash('error', 'Doctor not found.');
            redirect('?page=doctors');
        }

        $specializations = $this->specModel->getAll();
        $availableDays   = explode(',', $doctor['available_days']);

        require_once __DIR__ . '/../views/doctors/edit.php';
    }

    // =============================================================
    // update() - حفظ التعديلات (POST)
    // =============================================================
    public function update(): void
    {
        Auth::requireRole('admin', 'doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=doctors');
        }

        $doctorId   = (int) ($_POST['doctor_id'] ?? 0);
        $specId     = (int) ($_POST['specialization_id'] ?? 0);
        $bio        = sanitize($_POST['bio'] ?? '');
        $fee        = (float) ($_POST['consultation_fee'] ?? 0);
        $availDays  = $_POST['available_days'] ?? [];

        // نحول مصفوفة الأيام لـ string
        $availDaysStr = implode(',', $availDays);

        // معالجة صورة الدكتور (اختياري)
        $photoName = null;
        if (isset($_FILES['doctor_photo']) && $_FILES['doctor_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['doctor_photo'];

            // تحقق من الحجم - أقصى 1MB
            if ($file['size'] > MAX_IMAGE_SIZE) {
                setFlash('error', 'Photo is too large. Maximum is 1MB.');
                redirect('?page=doctors&action=edit&id=' . $doctorId);
            }

            // تحقق من إن الملف صورة حقيقية بـ getimagesize()
            // getimagesize بتقرأ الصورة فعلياً وترجع false إذا مش صورة
            // هاد أآمن من تحقق الامتداد أو الـ MIME type من الـ header
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                setFlash('error', 'Invalid image file.');
                redirect('?page=doctors&action=edit&id=' . $doctorId);
            }

            // نتأكد إن النوع JPEG أو PNG فقط
            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
            if (!in_array($imageInfo[2], $allowedTypes)) {
                setFlash('error', 'Only JPEG and PNG images are allowed.');
                redirect('?page=doctors&action=edit&id=' . $doctorId);
            }

            $ext       = ($imageInfo[2] === IMAGETYPE_JPEG) ? '.jpg' : '.png';
            $photoName = 'doctor_' . $doctorId . '_' . time() . $ext;
            $destPath  = UPLOAD_DIR . 'doctor_photos/' . $photoName;

            move_uploaded_file($file['tmp_name'], $destPath);
        }

        $this->doctorModel->update($doctorId, [
            'specialization_id' => $specId,
            'bio'               => $bio,
            'consultation_fee'  => $fee,
            'available_days'    => $availDaysStr,
        ]);

        setFlash('success', 'Doctor profile updated.');
        redirect('?page=doctors');
    }
}

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
// ملف SpecializationController.php - إدارة التخصصات (أدمن فقط)
//
// تدفق البيانات:
// index()  -> عرض القائمة
// store()  -> إضافة تخصص جديد (POST)
// delete() -> حذف تخصص (POST) مع تحقق من السلامة
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class SpecializationController
{
    private SpecializationModel $specModel;

    public function __construct()
    {
        $this->specModel = new SpecializationModel();
    }

    // =============================================================
    // index() - عرض قائمة التخصصات مع نموذج الإضافة
    // =============================================================
    public function index(): void
    {
        Auth::requireRole('admin');

        // نجيب كل التخصصات
        $specializations = $this->specModel->getAll();

        require_once __DIR__ . '/../views/specializations/index.php';
    }

    // =============================================================
    // store() - إضافة تخصص جديد (POST)
    // =============================================================
    public function store(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=specializations');
        }

        $name = sanitize($_POST['name'] ?? '');

        if (empty($name)) {
            setFlash('error', 'Specialization name is required.');
            redirect('?page=specializations');
        }

        $this->specModel->create($name);
        setFlash('success', 'Specialization "' . $name . '" added successfully!');
        redirect('?page=specializations');
    }

    // =============================================================
    // delete() - حذف تخصص (POST)
    //
    // قبل الحذف بنتحقق من isSafeToDelete()
    // إذا في دكاترة بهاد التخصص، ما نحذف وبنعطي رسالة خطأ
    // =============================================================
    public function delete(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=specializations');
        }

        $id = (int) ($_POST['id'] ?? 0);

        // نتحقق من السلامة قبل الحذف
        if (!$this->specModel->isSafeToDelete($id)) {
            setFlash('error', 'Cannot delete: doctors are assigned to this specialization.');
            redirect('?page=specializations');
        }

        $this->specModel->delete($id);
        setFlash('success', 'Specialization deleted successfully.');
        redirect('?page=specializations');
    }
}

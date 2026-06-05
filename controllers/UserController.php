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
// ملف UserController.php - إدارة المستخدمين (الأدمن فقط)
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class UserController
{
    private UserModel            $userModel;
    private DoctorModel          $doctorModel;
    private SpecializationModel  $specModel;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->doctorModel = new DoctorModel();
        $this->specModel   = new SpecializationModel();
    }

    // =============================================================
    // index() - عرض قائمة المستخدمين مع Pagination وتصفية
    // =============================================================
    public function index(): void
    {
        // الأدمن فقط!
        Auth::requireRole('admin');

        $currentPage = (int) ($_GET['page_num'] ?? 1);
        $roleFilter  = $_GET['role'] ?? '';

        // نجيب المستخدمين
        $users      = $this->userModel->getAllPaginated($currentPage, $roleFilter);
        $totalCount = $this->userModel->countAll($roleFilter);

        // نجهز الـ Paginator
        $paginator = new Paginator($totalCount, ITEMS_PER_PAGE, $currentPage);

        require_once __DIR__ . '/../views/users/list.php';
    }

    // =============================================================
    // create() - عرض نموذج إنشاء مستخدم جديد
    // =============================================================
    public function create(): void
    {
        Auth::requireRole('admin');

        // نجيب التخصصات للقائمة المنسدلة (لو الدور doctor)
        $specializations = $this->specModel->getAll();

        require_once __DIR__ . '/../views/users/create.php';
    }

    // =============================================================
    // store() - حفظ المستخدم الجديد (POST)
    // =============================================================
    public function store(): void
    {
        Auth::requireRole('admin');

        // التحقق من الـ CSRF
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=users&action=create');
        }

        // جمع البيانات وتنظيفها
        $name  = sanitize($_POST['name']  ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $role  = $_POST['role'] ?? 'patient';
        $phone = sanitize($_POST['phone'] ?? '');
        $pass  = $_POST['password'] ?? '';

        // التحقق من البيانات الأساسية
        if (empty($name) || empty($email) || empty($pass)) {
            setFlash('error', 'Name, email, and password are required.');
            redirect('?page=users&action=create');
        }

        // التحقق من صحة الدور
        if (!in_array($role, ['admin', 'doctor', 'patient'])) {
            setFlash('error', 'Invalid role selected.');
            redirect('?page=users&action=create');
        }

        // تشفير الباسورد - دايماً قبل الحفظ!
        // PASSWORD_BCRYPT أآمن خوارزمية للباسورد
        $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);

        // نحفظ المستخدم
        $newUserId = $this->userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'role'     => $role,
            'phone'    => $phone,
        ]);

        // إذا الدور doctor، نضيف بيانات الدكتور كمان
        if ($role === 'doctor' && $newUserId) {
            $specId      = (int) ($_POST['specialization_id'] ?? 0);
            $fee         = (float) ($_POST['consultation_fee'] ?? 0);
            $bio         = sanitize($_POST['bio'] ?? '');
            $availDays   = $_POST['available_days'] ?? [];

            // نتأكد إن اختار تخصص
            if ($specId > 0) {
                // نحول مصفوفة الأيام لـ string مفصولة بفاصلة
                $availDaysStr = implode(',', $availDays);

                $this->doctorModel->create([
                    'user_id'           => $newUserId,
                    'specialization_id' => $specId,
                    'bio'               => $bio,
                    'consultation_fee'  => $fee,
                    'available_days'    => $availDaysStr ?: 'Sun,Mon,Tue,Wed,Thu',
                ]);
            }
        }

        setFlash('success', 'User created successfully!');
        redirect('?page=users');
    }

    // =============================================================
    // edit() - عرض نموذج تعديل مستخدم
    // =============================================================
    public function edit(): void
    {
        Auth::requireRole('admin');

        $id   = (int) ($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);

        if (!$user) {
            setFlash('error', 'User not found.');
            redirect('?page=users');
        }

        $specializations = $this->specModel->getAll();

        // إذا الدور doctor، نجيب بياناته الإضافية
        $doctorData = null;
        if ($user['role'] === 'doctor') {
            $doctorData = $this->doctorModel->findByUserId($id);
        }

        require_once __DIR__ . '/../views/users/edit.php';
    }

    // =============================================================
    // update() - حفظ تعديلات المستخدم (POST)
    // =============================================================
    public function update(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=users');
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $name  = sanitize($_POST['name']  ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($id) || empty($name)) {
            setFlash('error', 'Name is required.');
            redirect('?page=users&action=edit&id=' . $id);
        }

        $this->userModel->update($id, [
            'name'  => $name,
            'phone' => $phone,
        ]);

        // إذا في باسورد جديد، نغيره
        $newPass = $_POST['new_password'] ?? '';
        if (!empty($newPass)) {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $this->userModel->updatePassword($id, $hash);
        }

        setFlash('success', 'User updated successfully!');
        redirect('?page=users');
    }

    // =============================================================
    // toggle() - تفعيل/تعطيل حساب مستخدم (POST)
    // =============================================================
    public function toggle(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid request.');
            redirect('?page=users');
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $currentUser = Auth::currentUser();

        // الأدمن ما يقدر يعطل حسابه هو!
        if ($id === (int) $currentUser['id']) {
            setFlash('error', 'You cannot deactivate your own account!');
            redirect('?page=users');
        }

        $this->userModel->toggleActive($id);
        setFlash('success', 'Account status updated.');
        redirect('?page=users');
    }
}

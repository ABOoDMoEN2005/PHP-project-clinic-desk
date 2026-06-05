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
// ملف UserModel.php - كل العمليات المتعلقة بالمستخدمين
//
// هاد الكلاس مسؤول عن:
// - جلب بيانات المستخدمين
// - إنشاء مستخدمين جدد
// - تعديل بيانات المستخدمين
// - تغيير الباسورد
// - تفعيل/تعطيل الحسابات
// =============================================================

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    // =============================================================
    // findById() - جيب مستخدم واحد بالـ ID
    // بترجع array أو null إذا ما لقيت
    // =============================================================
    public function findById(int $id): ?array
    {
        // الـ ? في الـ SQL مكان المتغير الحقيقي
        // هاد بيحمي من SQL Injection
        $result = $this->execute(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            'i',       // i = integer
            [$id]
        );

        // fetch_assoc بترجع الصف كـ array أو false إذا ما في
        $row = $result->fetch_assoc();

        // نرجع الـ array أو null
        return $row ?: null;
    }

    // =============================================================
    // findByEmail() - جيب مستخدم بالإيميل (للاستخدام بتسجيل الدخول)
    // =============================================================
    public function findByEmail(string $email): ?array
    {
        $result = $this->execute(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            's',       // s = string
            [$email]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // create() - إنشاء مستخدم جديد
    //
    // مهم: الباسورد لازم يكون مهاش قبل ما يجي هون!
    // الـ hashing يصير بالـ Controller مش هون
    //
    // بترجع الـ ID الجديد للمستخدم
    // =============================================================
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)',
            'sssss',   // خمس strings
            [
                $data['name'],
                $data['email'],
                $data['password'],  // هاشد مسبقاً
                $data['role'],
                $data['phone'] ?? null,
            ]
        );

        // lastInsertId بترجع الـ ID اللي اتحط للسجل الجديد
        return $this->db->lastInsertId();
    }

    // =============================================================
    // update() - تعديل بيانات مستخدم
    // =============================================================
    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?',
            'sssi',
            [
                $data['name'],
                $data['phone'] ?? null,
                $data['avatar'] ?? null,
                $id,
            ]
        );

        return $result !== false;
    }

    // =============================================================
    // updatePassword() - تغيير باسورد المستخدم
    // بناخد الهاش الجديد مباشرة
    // =============================================================
    public function updatePassword(int $id, string $newHash): bool
    {
        $result = $this->execute(
            'UPDATE users SET password = ? WHERE id = ?',
            'si',
            [$newHash, $id]
        );

        return $result !== false;
    }

    // =============================================================
    // getAllPaginated() - جيب كل المستخدمين مع Pagination
    //
    // $page = رقم الصفحة الحالية
    // $role = تصفية حسب الدور (اختياري)
    // =============================================================
    public function getAllPaginated(int $page, string $role = ''): array
    {
        // نحسب من أين نبدأ
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        // إذا في تصفية بالدور نضيف WHERE
        if (!empty($role)) {
            $result = $this->execute(
                'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
                'sii',
                [$role, ITEMS_PER_PAGE, $offset]
            );
        } else {
            // بدون تصفية نجيب الكل
            $result = $this->execute(
                'SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?',
                'ii',
                [ITEMS_PER_PAGE, $offset]
            );
        }

        // fetch_all(MYSQLI_ASSOC) بترجع كل الصفوف كمصفوفة
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // countAll() - عدد المستخدمين الكلي (للـ Pagination)
    // =============================================================
    public function countAll(string $role = ''): int
    {
        if (!empty($role)) {
            $result = $this->execute(
                'SELECT COUNT(*) as total FROM users WHERE role = ?',
                's',
                [$role]
            );
        } else {
            $result = $this->execute('SELECT COUNT(*) as total FROM users');
        }

        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    // =============================================================
    // toggleActive() - تفعيل/تعطيل حساب المستخدم
    //
    // إذا كان 1 يصير 0، وإذا 0 يصير 1
    // بنستخدم SQL الـ NOT operator بدل ما نجيب القيمة أولاً
    // =============================================================
    public function toggleActive(int $id): bool
    {
        // ABS(is_active - 1) تعمل نفس عملية NOT:
        // 1 -> 1-1=0, 0 -> 0-1=-1 -> abs=1
        // أو ممكن نستخدم IF(is_active, 0, 1)
        $result = $this->execute(
            'UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?',
            'i',
            [$id]
        );

        return $result !== false;
    }

    // =============================================================
    // countByRole() - عد المستخدمين حسب الدور للـ Dashboard
    // بترجع مصفوفة: ['admin' => 1, 'doctor' => 5, 'patient' => 20]
    // =============================================================
    public function countByRole(): array
    {
        // استعلام واحد بيجيب عدد كل دور
        $result = $this->execute(
            'SELECT role, COUNT(*) as total FROM users GROUP BY role'
        );

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['role']] = (int) $row['total'];
        }

        return $counts;
    }
}

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
// ملف DoctorModel.php - بيانات الأطباء
//
// كل دكتور عنده record بجدول users وrecord بجدول doctors
// بنستخدم JOIN عشان نجيب بياناتهم مع بعض
// =============================================================

require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel
{
    // =============================================================
    // findByUserId() - جيب بيانات الدكتور باستخدام الـ user_id
    //
    // بنعمل JOIN مع users و specializations
    // عشان نجيب اسم الدكتور واسم التخصص بنفس الوقت
    // =============================================================
    public function findByUserId(int $userId): ?array
    {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, u.is_active,
                    s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE d.user_id = ?
             LIMIT 1',
            'i',
            [$userId]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // findById() - جيب بيانات الدكتور بالـ doctor ID
    // =============================================================
    public function findById(int $id): ?array
    {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, u.is_active,
                    s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE d.id = ?
             LIMIT 1',
            'i',
            [$id]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // getAll() - جيب كل الأطباء (للقائمة المنسدلة بالـ booking)
    // =============================================================
    public function getAll(): array
    {
        $result = $this->execute(
            'SELECT d.id, d.consultation_fee, d.available_days,
                    u.name, s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE u.is_active = 1
             ORDER BY u.name ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // getAllPaginated() - قائمة الأطباء مع Pagination للأدمن
    // =============================================================
    public function getAllPaginated(int $page): array
    {
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.is_active,
                    s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             ORDER BY u.name ASC
             LIMIT ? OFFSET ?',
            'ii',
            [ITEMS_PER_PAGE, $offset]
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // countAll() - عدد الأطباء الكلي (للـ Pagination)
    // =============================================================
    public function countAll(): int
    {
        $result = $this->execute('SELECT COUNT(*) as total FROM doctors');
        $row    = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    // =============================================================
    // create() - إضافة دكتور جديد
    // =============================================================
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days)
             VALUES (?, ?, ?, ?, ?)',
            'iisds',
            [
                $data['user_id'],
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu',
            ]
        );

        return $this->db->lastInsertId();
    }

    // =============================================================
    // update() - تعديل بيانات الدكتور
    // =============================================================
    public function update(int $doctorId, array $data): bool
    {
        $result = $this->execute(
            'UPDATE doctors
             SET specialization_id = ?, bio = ?, consultation_fee = ?, available_days = ?
             WHERE id = ?',
            'isdsi',
            [
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'],
                $data['available_days'],
                $doctorId,
            ]
        );

        return $result !== false;
    }

    // =============================================================
    // getAvailableDays() - إرجاع أيام العمل كمصفوفة
    //
    // بياناتنا محفوظة كـ string: "Sun,Mon,Tue"
    // explode بيفككها لمصفوفة: ['Sun', 'Mon', 'Tue']
    // =============================================================
    public function getAvailableDays(int $doctorId): array
    {
        $result = $this->execute(
            'SELECT available_days FROM doctors WHERE id = ? LIMIT 1',
            'i',
            [$doctorId]
        );

        $row = $result->fetch_assoc();

        if (!$row) {
            return [];
        }

        // explode بتقسم الـ string حسب الفاصلة
        return explode(',', $row['available_days']);
    }
}

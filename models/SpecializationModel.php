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
// ملف SpecializationModel.php - إدارة التخصصات الطبية
// =============================================================

require_once __DIR__ . '/BaseModel.php';

class SpecializationModel extends BaseModel
{
    // =============================================================
    // getAll() - جيب كل التخصصات (للقوائم المنسدلة)
    // =============================================================
    public function getAll(): array
    {
        $result = $this->execute('SELECT * FROM specializations ORDER BY name ASC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // findById() - جيب تخصص واحد بالـ ID
    // =============================================================
    public function findById(int $id): ?array
    {
        $result = $this->execute(
            'SELECT * FROM specializations WHERE id = ? LIMIT 1',
            'i',
            [$id]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // create() - إضافة تخصص جديد
    // =============================================================
    public function create(string $name): int
    {
        $this->execute(
            'INSERT INTO specializations (name) VALUES (?)',
            's',
            [$name]
        );

        return $this->db->lastInsertId();
    }

    // =============================================================
    // delete() - حذف تخصص
    // مهم: لازم نتحقق قبلها إن ما في أطباء بهاد التخصص!
    // =============================================================
    public function delete(int $id): bool
    {
        $result = $this->execute(
            'DELETE FROM specializations WHERE id = ?',
            'i',
            [$id]
        );

        return $result !== false;
    }

    // =============================================================
    // isSafeToDelete() - هل آمن نحذف هاد التخصص؟
    //
    // قبل الحذف نتأكد إن ما في دكاترة بهاد التخصص
    // لأن قاعدة البيانات عندها ON DELETE RESTRICT
    // وبتمنع الحذف أصلاً إذا في دكاترة
    // بس بنعمل هاد الفحص عشان نعطي رسالة خطأ واضحة
    // =============================================================
    public function isSafeToDelete(int $id): bool
    {
        // بنعد كم دكتور عنده هاد التخصص
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM doctors WHERE specialization_id = ?',
            'i',
            [$id]
        );

        $row = $result->fetch_assoc();
        // إذا العدد 0 يعني آمن نحذف
        return (int) $row['total'] === 0;
    }
}

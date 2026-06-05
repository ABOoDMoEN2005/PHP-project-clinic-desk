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
// ملف PrescriptionModel.php - إدارة الوصفات الطبية
// =============================================================

require_once __DIR__ . '/BaseModel.php';

class PrescriptionModel extends BaseModel
{
    // =============================================================
    // findByAppointmentId() - جيب وصفة موعد معين
    // =============================================================
    public function findByAppointmentId(int $apptId): ?array
    {
        $result = $this->execute(
            'SELECT * FROM prescriptions WHERE appointment_id = ? LIMIT 1',
            'i',
            [$apptId]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // create() - إضافة وصفة طبية جديدة
    // =============================================================
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            [
                $data['appointment_id'],
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,  // مسار الـ PDF اختياري
            ]
        );

        return $this->db->lastInsertId();
    }

    // =============================================================
    // update() - تعديل وصفة موجودة
    // =============================================================
    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE prescriptions
             SET diagnosis = ?, medications = ?, notes = ?, file_path = ?
             WHERE id = ?',
            'ssssi',
            [
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,
                $id,
            ]
        );

        return $result !== false;
    }

    // =============================================================
    // getByPatient() - كل وصفات المريض
    //
    // بنعمل JOIN مع appointments عشان نتحقق من الملكية
    // يعني المريض ما يقدر يشوف وصفات مريض ثاني
    // =============================================================
    public function getByPatient(int $patientId): array
    {
        $result = $this->execute(
            'SELECT p.*, a.appt_date, a.appt_time,
                    doc_user.name as doctor_name,
                    s.name as specialization_name
             FROM prescriptions p
             JOIN appointments a ON p.appointment_id = a.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id = ?
             ORDER BY p.created_at DESC',
            'i',
            [$patientId]
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // findById() - جيب وصفة بالـ ID مع تفاصيل الموعد
    // =============================================================
    public function findById(int $id): ?array
    {
        $result = $this->execute(
            'SELECT p.*, a.patient_id, a.doctor_id,
                    a.appt_date, a.appt_time
             FROM prescriptions p
             JOIN appointments a ON p.appointment_id = a.id
             WHERE p.id = ? LIMIT 1',
            'i',
            [$id]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // countByPatient() - عدد وصفات المريض (للـ dashboard)
    // =============================================================
    public function countByPatient(int $patientId): int
    {
        $result = $this->execute(
            'SELECT COUNT(*) as total
             FROM prescriptions p
             JOIN appointments a ON p.appointment_id = a.id
             WHERE a.patient_id = ?',
            'i',
            [$patientId]
        );

        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}

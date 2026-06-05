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
// ملف AppointmentModel.php - إدارة المواعيد
//
// هاد أكبر Model بالمشروع لأن الكل بيتعامل مع المواعيد
// الـ patient بحجز، الـ doctor بتأكيد، الـ admin بشوف الكل
// =============================================================

require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel
{
    // =============================================================
    // hasConflict() - هل الموعد محجوز مسبقاً؟
    //
    // بنتحقق قبل نحجز عشان نعطي رسالة واضحة للمريض
    // وإن كانت قاعدة البيانات عندها UNIQUE KEY بيمنع الحجز المزدوج
    // بس أحسن نتحقق هون ونعطي رسالة مفهومة
    // =============================================================
    public function hasConflict(int $doctorId, string $date, string $time): bool
    {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM appointments
             WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
             AND status != "cancelled"',
            'iss',
            [$doctorId, $date, $time]
        );

        $row = $result->fetch_assoc();
        // إذا العدد أكبر من 0 يعني في تعارض
        return (int) $row['total'] > 0;
    }

    // =============================================================
    // book() - حجز موعد جديد
    //
    // بترجع false إذا في تعارض (UNIQUE constraint)
    // =============================================================
    public function book(array $data): bool
    {
        try {
            $this->execute(
                'INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason)
                 VALUES (?, ?, ?, ?, ?)',
                'iisss',
                [
                    $data['patient_id'],
                    $data['doctor_id'],
                    $data['appt_date'],
                    $data['appt_time'],
                    $data['reason'] ?? null,
                ]
            );
            return true;
        } catch (RuntimeException $e) {
            // إذا في UNIQUE constraint violation يعني الموعد محجوز
            error_log('Booking conflict: ' . $e->getMessage());
            return false;
        }
    }

    // =============================================================
    // findById() - جيب تفاصيل موعد واحد
    //
    // بنعمل JOIN عشان نجيب اسم المريض والدكتور
    // =============================================================
    public function findById(int $id): ?array
    {
        $result = $this->execute(
            'SELECT a.*,
                    patient.name as patient_name, patient.email as patient_email,
                    doc_user.name as doctor_name,
                    d.consultation_fee,
                    s.name as specialization_name
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE a.id = ?
             LIMIT 1',
            'i',
            [$id]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    // =============================================================
    // getByPatient() - مواعيد المريض مع فلاتر وصفحات
    //
    // هنا بنبني الـ WHERE clause ديناميكياً
    // يعني إذا المستخدم اختار فلتر نضيفه، وإذا لا نتجاهله
    // =============================================================
    public function getByPatient(int $patientId, int $page, array $filters = []): array
    {
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        // نبني الـ WHERE conditions كمصفوفة
        $conditions = ['a.patient_id = ?'];
        $params      = [$patientId];
        $types        = 'i';

        // إضافة فلتر الحالة إذا اختار المريض
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $params[]      = $filters['status'];
            $types         .= 's';
        }

        // إضافة فلتر تاريخ البداية
        if (!empty($filters['date_from'])) {
            $conditions[] = 'a.appt_date >= ?';
            $params[]      = $filters['date_from'];
            $types         .= 's';
        }

        // إضافة فلتر تاريخ النهاية
        if (!empty($filters['date_to'])) {
            $conditions[] = 'a.appt_date <= ?';
            $params[]      = $filters['date_to'];
            $types         .= 's';
        }

        // نجمع الـ conditions بـ AND
        $whereClause = implode(' AND ', $conditions);

        // نضيف الـ LIMIT و OFFSET للنهاية
        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $types    .= 'ii';

        $result = $this->execute(
            "SELECT a.*,
                    doc_user.name as doctor_name,
                    s.name as specialization_name
             FROM appointments a
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE {$whereClause}
             ORDER BY a.appt_date DESC, a.appt_time DESC
             LIMIT ? OFFSET ?",
            $types,
            $params
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // getByDoctor() - مواعيد الدكتور مع فلاتر
    // =============================================================
    public function getByDoctor(int $doctorId, int $page, array $filters = []): array
    {
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        $conditions = ['a.doctor_id = ?'];
        $params      = [$doctorId];
        $types        = 'i';

        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $params[]      = $filters['status'];
            $types         .= 's';
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'a.appt_date >= ?';
            $params[]      = $filters['date_from'];
            $types         .= 's';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'a.appt_date <= ?';
            $params[]      = $filters['date_to'];
            $types         .= 's';
        }

        $whereClause = implode(' AND ', $conditions);
        $params[]    = ITEMS_PER_PAGE;
        $params[]    = $offset;
        $types       .= 'ii';

        $result = $this->execute(
            "SELECT a.*,
                    patient.name as patient_name,
                    patient.email as patient_email
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             WHERE {$whereClause}
             ORDER BY a.appt_date ASC, a.appt_time ASC
             LIMIT ? OFFSET ?",
            $types,
            $params
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // getAll() - كل المواعيد للأدمن مع فلاتر
    // =============================================================
    public function getAll(int $page, array $filters = []): array
    {
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        $conditions = [];
        $params      = [];
        $types        = '';

        // فلتر الدكتور
        if (!empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $params[]      = (int) $filters['doctor_id'];
            $types         .= 'i';
        }

        // فلتر اسم المريض (بحث نصي)
        if (!empty($filters['patient_name'])) {
            $conditions[] = 'patient.name LIKE ?';
            $params[]      = '%' . $filters['patient_name'] . '%';
            $types         .= 's';
        }

        // فلتر الحالة
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $params[]      = $filters['status'];
            $types         .= 's';
        }

        // فلتر تاريخ البداية
        if (!empty($filters['date_from'])) {
            $conditions[] = 'a.appt_date >= ?';
            $params[]      = $filters['date_from'];
            $types         .= 's';
        }

        // فلتر تاريخ النهاية
        if (!empty($filters['date_to'])) {
            $conditions[] = 'a.appt_date <= ?';
            $params[]      = $filters['date_to'];
            $types         .= 's';
        }

        // نبني جملة الـ WHERE - إذا ما في شروط ما نضيف WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $types    .= 'ii';

        $result = $this->execute(
            "SELECT a.*,
                    patient.name as patient_name,
                    doc_user.name as doctor_name,
                    s.name as specialization_name
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             {$whereClause}
             ORDER BY a.appt_date DESC, a.appt_time DESC
             LIMIT ? OFFSET ?",
            $types,
            $params
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // countFiltered() - عدد المواعيد مع نفس الفلاتر (للـ Pagination)
    // =============================================================
    public function countFiltered(string $scope, int $scopeId, array $filters = []): int
    {
        $conditions = [];
        $params      = [];
        $types        = '';

        // نحدد نطاق البحث
        if ($scope === 'patient') {
            $conditions[] = 'a.patient_id = ?';
            $params[]      = $scopeId;
            $types         .= 'i';
        } elseif ($scope === 'doctor') {
            $conditions[] = 'a.doctor_id = ?';
            $params[]      = $scopeId;
            $types         .= 'i';
        }
        // إذا 'all' ما نضيف شرط

        // نضيف باقي الفلاتر
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $params[]      = $filters['status'];
            $types         .= 's';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'a.appt_date >= ?';
            $params[]      = $filters['date_from'];
            $types         .= 's';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'a.appt_date <= ?';
            $params[]      = $filters['date_to'];
            $types         .= 's';
        }
        if ($scope === 'all' && !empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $params[]      = (int) $filters['doctor_id'];
            $types         .= 'i';
        }
        if ($scope === 'all' && !empty($filters['patient_name'])) {
            $conditions[] = 'patient.name LIKE ?';
            $params[]      = '%' . $filters['patient_name'] . '%';
            $types         .= 's';
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $result = $this->execute(
            "SELECT COUNT(*) as total
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             {$whereClause}",
            $types,
            $params
        );

        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    // =============================================================
    // updateStatus() - تغيير حالة الموعد
    //
    // الدكتور بيستخدمها لتأكيد/إتمام/إلغاء
    // الأدمن بيستخدمها للإدارة
    // =============================================================
    public function updateStatus(int $id, string $status, string $notes = ''): bool
    {
        $result = $this->execute(
            'UPDATE appointments SET status = ?, doctor_notes = ? WHERE id = ?',
            'ssi',
            [$status, $notes ?: null, $id]
        );

        return $result !== false;
    }

    // =============================================================
    // getTodayByDoctor() - مواعيد اليوم لدكتور معين
    // CURDATE() دالة MySQL بترجع تاريخ اليوم
    // =============================================================
    public function getTodayByDoctor(int $doctorId): array
    {
        $result = $this->execute(
            'SELECT a.*, patient.name as patient_name
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             WHERE a.doctor_id = ? AND a.appt_date = CURDATE()
             AND a.status != "cancelled"
             ORDER BY a.appt_time ASC',
            'i',
            [$doctorId]
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // getRecentForAdmin() - آخر 5 مواعيد للـ admin dashboard
    // =============================================================
    public function getRecentForAdmin(): array
    {
        $result = $this->execute(
            'SELECT a.*,
                    patient.name as patient_name,
                    doc_user.name as doctor_name
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             ORDER BY a.created_at DESC
             LIMIT 5'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =============================================================
    // countTodayAppointments() - عدد مواعيد اليوم (للأدمن)
    // =============================================================
    public function countTodayAppointments(): int
    {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM appointments WHERE appt_date = CURDATE()'
        );
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    // =============================================================
    // getWeeklyStatusCounts() - إحصاء المواعيد حسب الحالة لهذا الأسبوع
    // =============================================================
    public function getWeeklyStatusCounts(): array
    {
        $result = $this->execute(
            'SELECT status, COUNT(*) as total
             FROM appointments
             WHERE WEEK(appt_date) = WEEK(NOW()) AND YEAR(appt_date) = YEAR(NOW())
             GROUP BY status'
        );

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    // =============================================================
    // getUpcomingByPatient() - المواعيد القادمة للمريض (للـ dashboard)
    // =============================================================
    public function getUpcomingByPatient(int $patientId): ?array
    {
        $result = $this->execute(
            'SELECT a.*, doc_user.name as doctor_name, s.name as specialization_name
             FROM appointments a
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id = ?
             AND a.appt_date >= CURDATE()
             AND a.status IN ("pending", "confirmed")
             ORDER BY a.appt_date ASC, a.appt_time ASC
             LIMIT 1',
            'i',
            [$patientId]
        );

        $row = $result->fetch_assoc();
        return $row ?: null;
    }
}

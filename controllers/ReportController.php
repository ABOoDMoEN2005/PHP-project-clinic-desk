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
// ملف ReportController.php - تقارير المواعيد (الأدمن فقط)
//
// الأدمن بيقدر يعمل تقرير حسب:
// - نطاق تاريخ
// - دكتور معين
// - حالة الموعد
//
// وممكن يصدّر النتيجة كملف CSV
// =============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../core/Database.php';

class ReportController
{
    private DoctorModel $doctorModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
    }

    // =============================================================
    // index() - عرض صفحة التقارير مع نتائج الفلترة
    // =============================================================
    public function index(): void
    {
        Auth::requireRole('admin');

        // نجيب قائمة الأطباء للفلتر
        $doctors     = $this->doctorModel->getAll();
        $reportData  = [];
        $totalCount  = 0;
        $statusCounts = [];

        // إذا الفورم اتبعت، نجيب النتائج
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $startDate = $_GET['start_date'];
            $endDate   = $_GET['end_date'];
            $doctorId  = (int) ($_GET['doctor_id'] ?? 0);
            $status    = $_GET['status'] ?? '';

            // نتحقق إن تاريخ البداية قبل النهاية
            if ($startDate > $endDate) {
                setFlash('error', 'Start date must be before end date.');
            } else {
                // نجيب التقرير
                $result = $this->getReportData($startDate, $endDate, $doctorId, $status);
                $reportData   = $result['data'];
                $totalCount   = $result['total'];
                $statusCounts = $result['status_counts'];

                // إذا طلب تصدير CSV
                if (isset($_GET['export']) && $_GET['export'] === 'csv') {
                    $this->exportCSV($reportData, $startDate, $endDate);
                }
            }
        }

        require_once __DIR__ . '/../views/reports/index.php';
    }

    // =============================================================
    // getReportData() - جيب بيانات التقرير من قاعدة البيانات
    // =============================================================
    private function getReportData(string $startDate, string $endDate, int $doctorId, string $status): array
    {
        $db = Database::getInstance();

        // نبني الـ WHERE clause ديناميكياً
        $conditions = ['a.appt_date >= ?', 'a.appt_date <= ?'];
        $params      = [$startDate, $endDate];
        $types        = 'ss';

        if ($doctorId > 0) {
            $conditions[] = 'a.doctor_id = ?';
            $params[]      = $doctorId;
            $types         .= 'i';
        }

        if (!empty($status)) {
            $conditions[] = 'a.status = ?';
            $params[]      = $status;
            $types         .= 's';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        // الاستعلام الرئيسي
        $result = $db->query(
            "SELECT a.id, a.appt_date, a.appt_time, a.status, a.reason,
                    patient.name as patient_name,
                    doc_user.name as doctor_name,
                    s.name as specialization_name
             FROM appointments a
             JOIN users patient ON a.patient_id = patient.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users doc_user ON d.user_id = doc_user.id
             JOIN specializations s ON d.specialization_id = s.id
             {$whereClause}
             ORDER BY a.appt_date DESC, a.appt_time DESC",
            $types,
            $params
        );

        $data = $result->fetch_all(MYSQLI_ASSOC);

        // نعد حسب الحالة للسطر الملخص
        $statusCounts = [];
        foreach ($data as $row) {
            $statusCounts[$row['status']] = ($statusCounts[$row['status']] ?? 0) + 1;
        }

        return [
            'data'          => $data,
            'total'         => count($data),
            'status_counts' => $statusCounts,
        ];
    }

    // =============================================================
    // exportCSV() - تصدير التقرير كملف CSV
    //
    // fputcsv بتكتب صف في ملف CSV بشكل صحيح
    // php://output يعني نكتب مباشرة للمتصفح مش لملف
    // =============================================================
    private function exportCSV(array $data, string $startDate, string $endDate): void
    {
        // نقول للمتصفح إن هاد ملف CSV
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="report_' . $startDate . '_to_' . $endDate . '.csv"');

        // نفتح الـ output مباشرة
        $output = fopen('php://output', 'w');

        // نضيف BOM للـ UTF-8 عشان يظهر العربي صح في Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // نكتب سطر العناوين
        fputcsv($output, [
            '#',
            'Patient Name',
            'Doctor Name',
            'Specialization',
            'Date',
            'Time',
            'Status',
            'Reason',
        ]);

        // نكتب البيانات
        $rowNum = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $rowNum++,
                $row['patient_name'],
                $row['doctor_name'],
                $row['specialization_name'],
                $row['appt_date'],
                $row['appt_time'],
                $row['status'],
                $row['reason'] ?? '',
            ]);
        }

        fclose($output);

        // نوقف التنفيذ هون عشان ما يكمل الكود بعد إرسال الـ CSV
        exit();
    }
}

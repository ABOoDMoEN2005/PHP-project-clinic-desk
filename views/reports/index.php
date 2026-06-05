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

require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');

$pageTitle = 'Reports';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Appointment Reports</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- ==========================================================
           فورم الفلاتر
           ========================================================== -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-filter mr-2"></i> Report Filters
          </h3>
        </div>
        <div class="card-body">
          <!-- GET method عشان يبقى الفلتر بالـ URL ويقدر يعمل export -->
          <form method="GET" action="<?php echo BASE_URL; ?>">
            <input type="hidden" name="page" value="reports">
            <div class="row">

              <div class="col-md-3">
                <label>Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control"
                       value="<?php echo sanitize($_GET['start_date'] ?? date('Y-m-01')); ?>"
                       required>
              </div>

              <div class="col-md-3">
                <label>End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control"
                       value="<?php echo sanitize($_GET['end_date'] ?? date('Y-m-d')); ?>"
                       required>
              </div>

              <div class="col-md-3">
                <label>Doctor (Optional)</label>
                <select name="doctor_id" class="form-control">
                  <option value="">All Doctors</option>
                  <?php foreach ($doctors as $doc): ?>
                  <option value="<?php echo $doc['id']; ?>"
                    <?php echo (($_GET['doctor_id'] ?? '') == $doc['id']) ? 'selected' : ''; ?>>
                    Dr. <?php echo sanitize($doc['name']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label>Status (Optional)</label>
                <select name="status" class="form-control">
                  <option value="">All Statuses</option>
                  <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                  <option value="<?php echo $s; ?>"
                    <?php echo (($_GET['status'] ?? '') === $s) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($s); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i> Generate Report
              </button>

              <!-- زر تصدير CSV - بيضيف export=csv للـ URL الحالي -->
              <?php if (!empty($reportData)): ?>
              <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>"
                 class="btn btn-success ml-2">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
              </a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <!-- ==========================================================
           نتائج التقرير
           ========================================================== -->
      <?php if (!empty($_GET['start_date'])): ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Report Results:
            <?php echo formatDate($_GET['start_date']); ?> —
            <?php echo formatDate($_GET['end_date']); ?>
          </h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($reportData)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    No appointments found for the selected filters.
                  </td>
                </tr>
              <?php else: ?>
                <?php $n = 1; foreach ($reportData as $row): ?>
                <tr>
                  <td><?php echo $n++; ?></td>
                  <td><?php echo sanitize($row['patient_name']); ?></td>
                  <td>Dr. <?php echo sanitize($row['doctor_name']); ?></td>
                  <td><?php echo sanitize($row['specialization_name']); ?></td>
                  <td><?php echo formatDate($row['appt_date']); ?></td>
                  <td><?php echo formatTime($row['appt_time']); ?></td>
                  <td><?php echo getStatusBadge($row['status']); ?></td>
                  <td><?php echo sanitize($row['reason'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- سطر الملخص -->
        <?php if (!empty($reportData)): ?>
        <div class="card-footer">
          <div class="row">
            <div class="col-md-6">
              <strong>Total: <?php echo $totalCount; ?> appointments</strong>
            </div>
            <div class="col-md-6 text-right">
              <!-- ملخص الحالات -->
              <?php foreach ($statusCounts as $st => $cnt): ?>
                <?php echo getStatusBadge($st); ?>
                <span class="mr-2"><?php echo $cnt; ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

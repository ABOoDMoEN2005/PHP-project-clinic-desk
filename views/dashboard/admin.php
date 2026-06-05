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
// صفحة الـ Dashboard للأدمن
// بتعرض إحصاءات الكل: المستخدمين، المواعيد، وآخر النشاطات
// =============================================================

// لازم نعمل role check أول شي
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');

$pageTitle = 'Admin Dashboard';

// نجيب الـ partials المشتركة
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<!-- =============================================================
     محتوى الصفحة الرئيسي
     ============================================================= -->
<div class="content-wrapper">

  <!-- عنوان الصفحة -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- عرض رسائل Flash -->
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- ==========================================================
           بطاقات الإحصاءات - small-box من AdminLTE
           ========================================================== -->
      <div class="row">

        <!-- عدد الأطباء -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <!-- نجيب العدد من مصفوفة userCounts، أو 0 إذا ما في -->
              <h3><?php echo $userCounts['doctor'] ?? 0; ?></h3>
              <p>Total Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-md"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>?page=doctors" class="small-box-footer">
              View Doctors <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- عدد المرضى -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?php echo $userCounts['patient'] ?? 0; ?></h3>
              <p>Total Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>?page=users&role=patient" class="small-box-footer">
              View Patients <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- مواعيد اليوم -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $todayCount; ?></h3>
              <p>Today's Appointments</p>
            </div>
            <div class="icon">
              <i class="fas fa-calendar-day"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>?page=appointments&date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>"
               class="small-box-footer">
              View Today <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- مواعيد هذا الأسبوع -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <!-- نحسب مجموع كل الحالات -->
              <h3><?php echo array_sum($weeklyStats); ?></h3>
              <p>This Week's Appointments</p>
            </div>
            <div class="icon">
              <i class="fas fa-calendar-week"></i>
            </div>
            <a href="<?php echo BASE_URL; ?>?page=appointments" class="small-box-footer">
              View All <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

      </div><!-- /.row -->

      <!-- ==========================================================
           إحصاء المواعيد هذا الأسبوع حسب الحالة
           ========================================================== -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-pie mr-2"></i>
                This Week - Appointments by Status
              </h3>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">

                <!-- Pending -->
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-clock text-warning mr-2"></i> Pending</span>
                  <span class="badge badge-warning badge-pill">
                    <?php echo $weeklyStats['pending'] ?? 0; ?>
                  </span>
                </li>

                <!-- Confirmed -->
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-check text-info mr-2"></i> Confirmed</span>
                  <span class="badge badge-info badge-pill">
                    <?php echo $weeklyStats['confirmed'] ?? 0; ?>
                  </span>
                </li>

                <!-- Completed -->
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-check-double text-success mr-2"></i> Completed</span>
                  <span class="badge badge-success badge-pill">
                    <?php echo $weeklyStats['completed'] ?? 0; ?>
                  </span>
                </li>

                <!-- Cancelled -->
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><i class="fas fa-times text-danger mr-2"></i> Cancelled</span>
                  <span class="badge badge-danger badge-pill">
                    <?php echo $weeklyStats['cancelled'] ?? 0; ?>
                  </span>
                </li>

              </ul>
            </div>
          </div>
        </div>

        <!-- روابط سريعة للأدمن -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-bolt mr-2"></i>
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <a href="<?php echo BASE_URL; ?>?page=users&action=create"
                 class="btn btn-primary btn-block mb-2">
                <i class="fas fa-user-plus mr-2"></i> Add New User
              </a>
              <a href="<?php echo BASE_URL; ?>?page=reports"
                 class="btn btn-info btn-block mb-2">
                <i class="fas fa-file-csv mr-2"></i> Generate Report
              </a>
              <a href="<?php echo BASE_URL; ?>?page=appointments"
                 class="btn btn-success btn-block">
                <i class="fas fa-list mr-2"></i> View All Appointments
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ==========================================================
           جدول آخر 5 مواعيد
           ========================================================== -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-history mr-2"></i>
                Recent Appointments
              </h3>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentAppts)): ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">No appointments yet.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentAppts as $appt): ?>
                    <tr>
                      <td><?php echo $appt['id']; ?></td>
                      <!-- sanitize على كل قيمة بتجي من قاعدة البيانات -->
                      <td><?php echo sanitize($appt['patient_name']); ?></td>
                      <td><?php echo sanitize($appt['doctor_name']); ?></td>
                      <td>
                        <?php echo formatDate($appt['appt_date']); ?>
                        <small class="text-muted"><?php echo formatTime($appt['appt_time']); ?></small>
                      </td>
                      <td><?php echo getStatusBadge($appt['status']); ?></td>
                      <td>
                        <a href="<?php echo BASE_URL; ?>?page=appointments&action=detail&id=<?php echo $appt['id']; ?>"
                           class="btn btn-xs btn-default">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <a href="<?php echo BASE_URL; ?>?page=appointments" class="btn btn-sm btn-default">
                View All Appointments
              </a>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.container-fluid -->
  </section>
</div><!-- /.content-wrapper -->

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

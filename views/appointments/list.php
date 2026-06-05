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
Auth::requireRole('admin', 'doctor', 'patient');

$role      = Auth::role();
$pageTitle = ($role === 'doctor') ? 'My Schedule' : (($role === 'admin') ? 'All Appointments' : 'My Appointments');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><?php echo $pageTitle; ?></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="<?php echo BASE_URL; ?>?page=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Appointments</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- زر حجز موعد للمريض -->
      <?php if ($role === 'patient'): ?>
      <div class="mb-3">
        <a href="<?php echo BASE_URL; ?>?page=appointments&action=book"
           class="btn btn-primary">
          <i class="fas fa-plus mr-1"></i> Book New Appointment
        </a>
      </div>
      <?php endif; ?>

      <!-- ==========================================================
           فلاتر البحث
           ========================================================== -->
      <div class="card card-default collapsed-card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-filter mr-2"></i> Filters
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <!-- الفلاتر عبر GET method عشان تبقى بالـ URL -->
          <form method="GET" action="<?php echo BASE_URL; ?>">
            <input type="hidden" name="page" value="appointments">
            <div class="row">

              <!-- فلتر الحالة -->
              <div class="col-md-3">
                <label>Status</label>
                <select name="status" class="form-control form-control-sm">
                  <option value="">All Statuses</option>
                  <?php
                  $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                  foreach ($statuses as $s):
                  ?>
                  <option value="<?php echo $s; ?>"
                    <?php echo (($_GET['status'] ?? '') === $s) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($s); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- فلتر تاريخ البداية -->
              <div class="col-md-3">
                <label>From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?php echo sanitize($_GET['date_from'] ?? ''); ?>">
              </div>

              <!-- فلتر تاريخ النهاية -->
              <div class="col-md-3">
                <label>To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?php echo sanitize($_GET['date_to'] ?? ''); ?>">
              </div>

              <!-- فلتر الدكتور - للأدمن فقط -->
              <?php if ($role === 'admin'): ?>
              <div class="col-md-3">
                <label>Doctor</label>
                <select name="doctor_id" class="form-control form-control-sm">
                  <option value="">All Doctors</option>
                  <?php foreach ($doctors as $doc): ?>
                  <option value="<?php echo $doc['id']; ?>"
                    <?php echo (($_GET['doctor_id'] ?? '') == $doc['id']) ? 'selected' : ''; ?>>
                    Dr. <?php echo sanitize($doc['name']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php endif; ?>

              <!-- أزرار الفلتر -->
              <div class="col-12 mt-2">
                <button type="submit" class="btn btn-sm btn-primary">
                  <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
                <a href="<?php echo BASE_URL; ?>?page=appointments"
                   class="btn btn-sm btn-default ml-2">
                  <i class="fas fa-times mr-1"></i> Clear
                </a>
              </div>

            </div>
          </form>
        </div>
      </div>

      <!-- ==========================================================
           جدول المواعيد
           ========================================================== -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-list mr-2"></i>
            Appointments
            <span class="badge badge-secondary ml-2"><?php echo $paginator->getTotalItems(); ?></span>
          </h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <?php if ($role !== 'patient'): ?>
                <th>Patient</th>
                <?php endif; ?>
                <?php if ($role !== 'doctor'): ?>
                <th>Doctor</th>
                <?php endif; ?>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($appointments)): ?>
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">
                    No appointments found.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                <tr>
                  <td><?php echo $appt['id']; ?></td>

                  <?php if ($role !== 'patient'): ?>
                  <td><?php echo sanitize($appt['patient_name']); ?></td>
                  <?php endif; ?>

                  <?php if ($role !== 'doctor'): ?>
                  <td>Dr. <?php echo sanitize($appt['doctor_name']); ?></td>
                  <?php endif; ?>

                  <td><?php echo sanitize($appt['specialization_name']); ?></td>
                  <td><?php echo formatDate($appt['appt_date']); ?></td>
                  <td><?php echo formatTime($appt['appt_time']); ?></td>
                  <td><?php echo getStatusBadge($appt['status']); ?></td>
                  <td>
                    <?php echo sanitize($appt['reason'] ?? '-'); ?>
                  </td>
                  <td>
                    <!-- زر عرض التفاصيل -->
                    <a href="<?php echo BASE_URL; ?>?page=appointments&action=detail&id=<?php echo $appt['id']; ?>"
                       class="btn btn-xs btn-default">
                      <i class="fas fa-eye"></i>
                    </a>

                    <!-- زر إلغاء للمريض - بس للـ pending -->
                    <?php if ($role === 'patient' && $appt['status'] === 'pending'): ?>
                    <form method="POST"
                          action="<?php echo BASE_URL; ?>?page=appointments&action=cancel"
                          style="display:inline;"
                          onsubmit="return confirm('Cancel this appointment?');">
                      <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                      <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                      <button type="submit" class="btn btn-xs btn-danger">
                        <i class="fas fa-times"></i>
                      </button>
                    </form>
                    <?php endif; ?>

                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- ==========================================================
             Pagination - زرار التنقل بين الصفحات
             ========================================================== -->
        <?php if ($paginator->totalPages() > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination pagination-sm m-0 float-right">

              <!-- زر السابق -->
              <li class="page-item <?php echo !$paginator->hasPrev() ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="?page=appointments&page_num=<?php echo $paginator->getCurrentPage() - 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => '', 'page_num' => ''])); ?>">
                  &laquo;
                </a>
              </li>

              <!-- أرقام الصفحات -->
              <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
              <li class="page-item <?php echo ($i === $paginator->getCurrentPage()) ? 'active' : ''; ?>">
                <a class="page-link"
                   href="?page=appointments&page_num=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => '', 'page_num' => ''])); ?>">
                  <?php echo $i; ?>
                </a>
              </li>
              <?php endfor; ?>

              <!-- زر التالي -->
              <li class="page-item <?php echo !$paginator->hasNext() ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="?page=appointments&page_num=<?php echo $paginator->getCurrentPage() + 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => '', 'page_num' => ''])); ?>">
                  &raquo;
                </a>
              </li>

            </ul>
          </nav>
          <small class="text-muted">
            Showing <?php echo count($appointments); ?> of <?php echo $paginator->getTotalItems(); ?> appointments
          </small>
        </div>
        <?php endif; ?>

      </div><!-- /.card -->

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

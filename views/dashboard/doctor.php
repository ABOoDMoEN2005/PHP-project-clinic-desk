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
Auth::requireRole('doctor');

$pageTitle = 'My Schedule';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            Welcome, Dr. <?php echo sanitize($doctor['name']); ?>
          </h1>
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

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- ==========================================================
           إحصاءات الشهر الحالي
           ========================================================== -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?php echo $monthlyStats['total']; ?></h3>
              <p>Total This Month</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=appointments" class="small-box-footer">
              View All <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $monthlyStats['pending']; ?></h3>
              <p>Pending Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=appointments&status=pending" class="small-box-footer">
              View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?php echo $monthlyStats['completed']; ?></h3>
              <p>Completed This Month</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=appointments&status=completed" class="small-box-footer">
              View Completed <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- ==========================================================
           مواعيد اليوم - الأهم للدكتور
           ========================================================== -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-calendar-day mr-2"></i>
            Today's Appointments —
            <span class="badge badge-light"><?php echo date('l, F j, Y'); ?></span>
          </h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Time</th>
                <th>Patient</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($todayAppointments)): ?>
                <tr>
                  <td colspan="5" class="text-center py-4">
                    <i class="fas fa-coffee fa-2x text-muted mb-2 d-block"></i>
                    No appointments today. Enjoy your day!
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($todayAppointments as $appt): ?>
                <tr>
                  <td>
                    <strong><?php echo formatTime($appt['appt_time']); ?></strong>
                  </td>
                  <td><?php echo sanitize($appt['patient_name']); ?></td>
                  <td>
                    <?php echo sanitize($appt['reason'] ?? 'Not specified'); ?>
                  </td>
                  <td><?php echo getStatusBadge($appt['status']); ?></td>
                  <td>
                    <a href="<?php echo BASE_URL; ?>?page=appointments&action=detail&id=<?php echo $appt['id']; ?>"
                       class="btn btn-xs btn-primary">
                      <i class="fas fa-eye"></i> View
                    </a>

                    <!-- زر تأكيد - إذا الحالة pending -->
                    <?php if ($appt['status'] === 'pending'): ?>
                    <form method="POST"
                          action="<?php echo BASE_URL; ?>?page=appointments&action=update_status"
                          style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                      <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                      <input type="hidden" name="status" value="confirmed">
                      <button type="submit" class="btn btn-xs btn-info">
                        <i class="fas fa-check"></i> Confirm
                      </button>
                    </form>
                    <?php endif; ?>

                    <!-- زر إتمام - إذا الحالة confirmed -->
                    <?php if ($appt['status'] === 'confirmed'): ?>
                    <form method="POST"
                          action="<?php echo BASE_URL; ?>?page=appointments&action=update_status"
                          style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                      <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                      <input type="hidden" name="status" value="completed">
                      <button type="submit" class="btn btn-xs btn-success">
                        <i class="fas fa-check-double"></i> Complete
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
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

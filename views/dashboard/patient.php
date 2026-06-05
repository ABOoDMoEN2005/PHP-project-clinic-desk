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
Auth::requireRole('patient');

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">
        Welcome, <?php echo sanitize(Auth::currentUser()['name']); ?>!
      </h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- ==========================================================
           الموعد القادم - بطاقة بارزة في الأعلى
           ========================================================== -->
      <?php if ($nextAppointment): ?>
      <div class="callout callout-info">
        <h5>
          <i class="fas fa-calendar-check mr-2"></i>
          Your Next Appointment
        </h5>
        <p>
          <!-- sanitize على كل بيانات جاية من قاعدة البيانات -->
          <strong>Dr. <?php echo sanitize($nextAppointment['doctor_name']); ?></strong>
          — <?php echo sanitize($nextAppointment['specialization_name']); ?>
          <br>
          <i class="fas fa-calendar mr-1"></i>
          <?php echo formatDate($nextAppointment['appt_date']); ?>
          &nbsp;
          <i class="fas fa-clock mr-1"></i>
          <?php echo formatTime($nextAppointment['appt_time']); ?>
          &nbsp;
          <?php echo getStatusBadge($nextAppointment['status']); ?>
        </p>
        <a href="<?php echo BASE_URL; ?>?page=appointments&action=detail&id=<?php echo $nextAppointment['id']; ?>"
           class="btn btn-sm btn-info">
          View Details
        </a>
      </div>
      <?php else: ?>
      <div class="callout callout-warning">
        <h5><i class="fas fa-calendar-plus mr-2"></i>No Upcoming Appointments</h5>
        <p>You have no upcoming appointments.</p>
        <a href="<?php echo BASE_URL; ?>?page=appointments&action=book"
           class="btn btn-sm btn-warning">
          Book an Appointment
        </a>
      </div>
      <?php endif; ?>

      <!-- ==========================================================
           الإحصاءات السريعة
           ========================================================== -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $activeAppointmentsCount; ?></h3>
              <p>Active Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=appointments" class="small-box-footer">
              View <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?php echo $completedCount; ?></h3>
              <p>Completed Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=appointments&status=completed" class="small-box-footer">
              View <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?php echo $prescriptionCount; ?></h3>
              <p>My Prescriptions</p>
            </div>
            <div class="icon"><i class="fas fa-prescription-bottle"></i></div>
            <a href="<?php echo BASE_URL; ?>?page=prescriptions" class="small-box-footer">
              View <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- أزرار سريعة -->
      <div class="card">
        <div class="card-body">
          <a href="<?php echo BASE_URL; ?>?page=appointments&action=book"
             class="btn btn-primary mr-2">
            <i class="fas fa-plus mr-1"></i> Book New Appointment
          </a>
          <a href="<?php echo BASE_URL; ?>?page=appointments"
             class="btn btn-default mr-2">
            <i class="fas fa-list mr-1"></i> My Appointments
          </a>
          <a href="<?php echo BASE_URL; ?>?page=prescriptions"
             class="btn btn-default">
            <i class="fas fa-file-medical mr-1"></i> My Prescriptions
          </a>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

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
$pageTitle = 'Appointment Details';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Appointment #<?php echo $appointment['id']; ?></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="<?php echo BASE_URL; ?>?page=appointments">Appointments</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <!-- معلومات الموعد -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Appointment Information</h3>
              <div class="card-tools">
                <?php echo getStatusBadge($appointment['status']); ?>
              </div>
            </div>
            <div class="card-body">
              <dl class="row">
                <dt class="col-sm-4">Patient:</dt>
                <dd class="col-sm-8"><?php echo sanitize($appointment['patient_name']); ?></dd>

                <dt class="col-sm-4">Doctor:</dt>
                <dd class="col-sm-8">Dr. <?php echo sanitize($appointment['doctor_name']); ?></dd>

                <dt class="col-sm-4">Specialization:</dt>
                <dd class="col-sm-8"><?php echo sanitize($appointment['specialization_name']); ?></dd>

                <dt class="col-sm-4">Date:</dt>
                <dd class="col-sm-8"><?php echo formatDate($appointment['appt_date']); ?></dd>

                <dt class="col-sm-4">Time:</dt>
                <dd class="col-sm-8"><?php echo formatTime($appointment['appt_time']); ?></dd>

                <dt class="col-sm-4">Reason:</dt>
                <dd class="col-sm-8">
                  <?php echo sanitize($appointment['reason'] ?? 'Not specified'); ?>
                </dd>

                <?php if (!empty($appointment['doctor_notes'])): ?>
                <dt class="col-sm-4">Doctor's Notes:</dt>
                <dd class="col-sm-8">
                  <div class="callout callout-info mb-0">
                    <?php echo sanitize($appointment['doctor_notes']); ?>
                  </div>
                </dd>
                <?php endif; ?>
              </dl>
            </div>
          </div>

          <!-- الوصفة الطبية -->
          <?php if ($prescription): ?>
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-prescription mr-2"></i>
                Prescription
              </h3>
            </div>
            <div class="card-body">
              <dl class="row">
                <dt class="col-sm-3">Diagnosis:</dt>
                <dd class="col-sm-9"><?php echo sanitize($prescription['diagnosis']); ?></dd>

                <dt class="col-sm-3">Medications:</dt>
                <dd class="col-sm-9"><?php echo sanitize($prescription['medications']); ?></dd>

                <?php if (!empty($prescription['notes'])): ?>
                <dt class="col-sm-3">Notes:</dt>
                <dd class="col-sm-9"><?php echo sanitize($prescription['notes']); ?></dd>
                <?php endif; ?>
              </dl>

              <!-- زر تحميل الـ PDF -->
              <?php if (!empty($prescription['file_path'])): ?>
              <a href="<?php echo BASE_URL; ?>?page=prescriptions&action=download&id=<?php echo $prescription['id']; ?>"
                 class="btn btn-success">
                <i class="fas fa-file-pdf mr-2"></i> Download Prescription PDF
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Actions Panel -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">

              <!-- أزرار الدكتور والأدمن لتغيير الحالة -->
              <?php if (in_array($role, ['doctor', 'admin'])): ?>

                <!-- تأكيد - إذا pending -->
                <?php if ($appointment['status'] === 'pending'): ?>
                <form method="POST"
                      action="<?php echo BASE_URL; ?>?page=appointments&action=update_status"
                      class="mb-2">
                  <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                  <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                  <input type="hidden" name="status" value="confirmed">
                  <button type="submit" class="btn btn-info btn-block">
                    <i class="fas fa-check mr-2"></i> Confirm Appointment
                  </button>
                </form>
                <?php endif; ?>

                <!-- إتمام + إضافة وصفة - إذا confirmed -->
                <?php if ($appointment['status'] === 'confirmed'): ?>
                <form method="POST"
                      action="<?php echo BASE_URL; ?>?page=appointments&action=update_status"
                      class="mb-2">
                  <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                  <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                  <input type="hidden" name="status" value="completed">
                  <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-check-double mr-2"></i> Mark as Completed
                  </button>
                </form>
                <?php endif; ?>

                <!-- إضافة وصفة - بعد الإتمام -->
                <?php if ($appointment['status'] === 'completed' && !$prescription && $role === 'doctor'): ?>
                <a href="<?php echo BASE_URL; ?>?page=prescriptions&action=add&appt_id=<?php echo $appointment['id']; ?>"
                   class="btn btn-warning btn-block mb-2">
                  <i class="fas fa-prescription mr-2"></i> Add Prescription
                </a>
                <?php endif; ?>

                <!-- إلغاء - إذا pending أو confirmed -->
                <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                <form method="POST"
                      action="<?php echo BASE_URL; ?>?page=appointments&action=update_status"
                      onsubmit="return confirm('Are you sure you want to cancel?');">
                  <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                  <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
                  <input type="hidden" name="status" value="cancelled">
                  <button type="submit" class="btn btn-danger btn-block">
                    <i class="fas fa-times mr-2"></i> Cancel Appointment
                  </button>
                </form>
                <?php endif; ?>

              <?php endif; ?>

              <!-- زر العودة -->
              <a href="<?php echo BASE_URL; ?>?page=appointments"
                 class="btn btn-default btn-block mt-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
              </a>

            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

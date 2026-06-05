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

$pageTitle = 'Book Appointment';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Book Appointment</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="<?php echo BASE_URL; ?>?page=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
              <a href="<?php echo BASE_URL; ?>?page=appointments">Appointments</a>
            </li>
            <li class="breadcrumb-item active">Book</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-calendar-plus mr-2"></i>
                Book a New Appointment
              </h3>
            </div>

            <!-- نموذج الحجز -->
            <form method="POST" action="<?php echo BASE_URL; ?>?page=appointments&action=store">

              <!-- CSRF Token - حماية من هجوم CSRF -->
              <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">

              <div class="card-body">

                <!-- اختيار الدكتور -->
                <div class="form-group">
                  <label for="doctor_id">
                    <i class="fas fa-user-md mr-1"></i>
                    Select Doctor <span class="text-danger">*</span>
                  </label>
                  <select name="doctor_id" id="doctor_id" class="form-control" required>
                    <option value="">-- Choose a Doctor --</option>
                    <?php foreach ($doctors as $doc): ?>
                    <option value="<?php echo $doc['id']; ?>"
                            data-days="<?php echo sanitize($doc['available_days']); ?>"
                            data-fee="<?php echo $doc['consultation_fee']; ?>"
                            <?php echo (($_POST['doctor_id'] ?? '') == $doc['id']) ? 'selected' : ''; ?>>
                      Dr. <?php echo sanitize($doc['name']); ?>
                      — <?php echo sanitize($doc['specialization_name']); ?>
                      (<?php echo number_format($doc['consultation_fee'], 2); ?> ILS)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- عرض أيام الدكتور المتاحة -->
                <div id="doctor-info" class="callout callout-info" style="display:none;">
                  <h6><i class="fas fa-calendar-week mr-1"></i> Available Days:</h6>
                  <p id="available-days-text" class="mb-1"></p>
                  <small class="text-muted">
                    <i class="fas fa-money-bill mr-1"></i>
                    Consultation Fee: <strong id="fee-text"></strong> ILS
                  </small>
                </div>

                <!-- اختيار التاريخ -->
                <div class="form-group">
                  <label for="appt_date">
                    <i class="fas fa-calendar mr-1"></i>
                    Preferred Date <span class="text-danger">*</span>
                  </label>
                  <input type="date"
                         name="appt_date"
                         id="appt_date"
                         class="form-control"
                         min="<?php echo date('Y-m-d'); ?>"
                         value="<?php echo sanitize($_POST['appt_date'] ?? ''); ?>"
                         required>
                  <small class="text-muted">Cannot book past dates.</small>
                </div>

                <!-- اختيار وقت الموعد -->
                <div class="form-group">
                  <label for="appt_time">
                    <i class="fas fa-clock mr-1"></i>
                    Preferred Time <span class="text-danger">*</span>
                  </label>
                  <select name="appt_time" id="appt_time" class="form-control" required>
                    <option value="">-- Select Time Slot --</option>
                    <?php
                    // generateTimeSlots() بترجع أوقات من 09:00 لـ 16:00 كل 30 دقيقة
                    foreach ($timeSlots as $slot):
                    ?>
                    <option value="<?php echo $slot; ?>"
                      <?php echo (($_POST['appt_time'] ?? '') === $slot) ? 'selected' : ''; ?>>
                      <?php echo date('g:i A', strtotime($slot)); ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- سبب الزيارة -->
                <div class="form-group">
                  <label for="reason">
                    <i class="fas fa-notes-medical mr-1"></i>
                    Reason for Visit
                  </label>
                  <input type="text"
                         name="reason"
                         id="reason"
                         class="form-control"
                         maxlength="255"
                         placeholder="Brief description of your condition..."
                         value="<?php echo sanitize($_POST['reason'] ?? ''); ?>">
                </div>

              </div><!-- /.card-body -->

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-calendar-check mr-2"></i> Book Appointment
                </button>
                <a href="<?php echo BASE_URL; ?>?page=appointments"
                   class="btn btn-default ml-2">
                  Cancel
                </a>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- =============================================================
     JavaScript لعرض أيام الدكتور لما المستخدم يختار دكتور
     ============================================================= -->
<script>
document.getElementById('doctor_id').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var infoBox       = document.getElementById('doctor-info');
    var daysText      = document.getElementById('available-days-text');
    var feeText       = document.getElementById('fee-text');

    if (this.value) {
        // نجيب الأيام من الـ data attribute
        var days = selectedOption.getAttribute('data-days');
        var fee  = selectedOption.getAttribute('data-fee');

        daysText.textContent = days;
        feeText.textContent  = parseFloat(fee).toFixed(2);

        // نعرض الـ info box
        infoBox.style.display = 'block';
    } else {
        infoBox.style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

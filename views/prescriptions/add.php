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

$pageTitle = 'Add Prescription';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Add Prescription</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- معلومات الموعد -->
      <div class="callout callout-info mb-3">
        <h6>Appointment Info:</h6>
        <strong>Patient:</strong> <?php echo sanitize($appointment['patient_name']); ?> &nbsp;|&nbsp;
        <strong>Date:</strong> <?php echo formatDate($appointment['appt_date']); ?> &nbsp;|&nbsp;
        <strong>Time:</strong> <?php echo formatTime($appointment['appt_time']); ?>
      </div>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-prescription mr-2"></i>
                Prescription Details
              </h3>
            </div>

            <!--
              enctype="multipart/form-data" ضروري لرفع الملفات
              بدونه ما بيوصل الـ $_FILES للسيرفر
            -->
            <form method="POST"
                  action="<?php echo BASE_URL; ?>?page=prescriptions&action=store"
                  enctype="multipart/form-data">

              <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
              <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">

              <div class="card-body">

                <div class="form-group">
                  <label>Diagnosis <span class="text-danger">*</span></label>
                  <textarea name="diagnosis" class="form-control" rows="3"
                            placeholder="Enter diagnosis..." required></textarea>
                </div>

                <div class="form-group">
                  <label>Medications <span class="text-danger">*</span></label>
                  <textarea name="medications" class="form-control" rows="4"
                            placeholder="List all medications with dosage and instructions..."
                            required></textarea>
                </div>

                <div class="form-group">
                  <label>Additional Notes</label>
                  <textarea name="notes" class="form-control" rows="2"
                            placeholder="Any additional notes or instructions..."></textarea>
                </div>

                <div class="form-group">
                  <label>
                    <i class="fas fa-file-pdf mr-1"></i>
                    Attach Prescription PDF (Optional)
                  </label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="prescription_file"
                           name="prescription_file" accept=".pdf">
                    <label class="custom-file-label" for="prescription_file">
                      Choose PDF file...
                    </label>
                  </div>
                  <small class="text-muted">
                    PDF files only. Maximum size: 3MB.
                    The file will be securely stored and only accessible to the patient.
                  </small>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save mr-2"></i> Save Prescription
                </button>
                <a href="<?php echo BASE_URL; ?>?page=appointments&action=detail&id=<?php echo $appointment['id']; ?>"
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

<!-- عرض اسم الملف المختار -->
<script>
document.getElementById('prescription_file').addEventListener('change', function() {
    var label = this.nextElementSibling;
    if (this.files && this.files[0]) {
        label.textContent = this.files[0].name;
    } else {
        label.textContent = 'Choose PDF file...';
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

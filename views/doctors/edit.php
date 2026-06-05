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
Auth::requireRole('admin', 'doctor');

$pageTitle = 'Edit Doctor Profile';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Dr. <?php echo sanitize($doctor['name']); ?> — Edit Profile</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user-md mr-2"></i> Doctor Information
              </h3>
            </div>

            <!-- enctype لرفع صورة الدكتور -->
            <form method="POST"
                  action="<?php echo BASE_URL; ?>?page=doctors&action=update"
                  enctype="multipart/form-data">

              <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
              <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">

              <div class="card-body">

                <!-- الاسم - للعرض فقط -->
                <div class="form-group">
                  <label>Doctor Name</label>
                  <input type="text" class="form-control"
                         value="Dr. <?php echo sanitize($doctor['name']); ?>" disabled>
                </div>

                <!-- التخصص -->
                <div class="form-group">
                  <label>Specialization <span class="text-danger">*</span></label>
                  <select name="specialization_id" class="form-control" required>
                    <?php foreach ($specializations as $spec): ?>
                    <option value="<?php echo $spec['id']; ?>"
                      <?php echo ($spec['id'] == $doctor['specialization_id']) ? 'selected' : ''; ?>>
                      <?php echo sanitize($spec['name']); ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- السيرة الذاتية -->
                <div class="form-group">
                  <label>Bio</label>
                  <textarea name="bio" class="form-control" rows="4"><?php echo sanitize($doctor['bio'] ?? ''); ?></textarea>
                </div>

                <!-- رسوم الاستشارة -->
                <div class="form-group">
                  <label>Consultation Fee (ILS)</label>
                  <input type="number" name="consultation_fee" class="form-control"
                         step="0.01" min="0"
                         value="<?php echo $doctor['consultation_fee']; ?>">
                </div>

                <!-- أيام العمل -->
                <div class="form-group">
                  <label>Available Days</label>
                  <div class="row">
                    <?php
                    $allDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    foreach ($allDays as $day):
                    ?>
                    <div class="col-md-3 col-6">
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="day_<?php echo $day; ?>"
                               name="available_days[]"
                               value="<?php echo $day; ?>"
                               <?php echo in_array($day, $availableDays) ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="day_<?php echo $day; ?>">
                          <?php echo $day; ?>
                        </label>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <!-- صورة الدكتور -->
                <div class="form-group">
                  <label>Profile Photo (JPEG/PNG, max 1MB)</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input"
                           id="doctor_photo"
                           name="doctor_photo"
                           accept=".jpg,.jpeg,.png">
                    <label class="custom-file-label" for="doctor_photo">
                      Choose photo...
                    </label>
                  </div>
                  <small class="text-muted">Leave empty to keep current photo.</small>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-info">
                  <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>?page=doctors" class="btn btn-default ml-2">
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

<script>
document.getElementById('doctor_photo').addEventListener('change', function() {
    var label = this.nextElementSibling;
    label.textContent = this.files[0] ? this.files[0].name : 'Choose photo...';
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

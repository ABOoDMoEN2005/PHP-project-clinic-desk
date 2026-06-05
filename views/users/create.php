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

$pageTitle = 'Add New User';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Add New User</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">User Information</h3>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>?page=users&action=store">
              <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">

              <div class="card-body">

                <div class="form-group">
                  <label>Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control"
                         value="<?php echo sanitize($_POST['name'] ?? ''); ?>"
                         placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                  <label>Email Address <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control"
                         value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                         placeholder="example@email.com" required>
                </div>

                <div class="form-group">
                  <label>Temporary Password <span class="text-danger">*</span></label>
                  <input type="password" name="password" class="form-control"
                         placeholder="Minimum 8 characters" required>
                  <small class="text-muted">User should change this on first login.</small>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Role <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-control" required>
                      <option value="patient" <?php echo (($_POST['role'] ?? '') === 'patient') ? 'selected' : ''; ?>>Patient</option>
                      <option value="doctor"  <?php echo (($_POST['role'] ?? '') === 'doctor')  ? 'selected' : ''; ?>>Doctor</option>
                      <option value="admin"   <?php echo (($_POST['role'] ?? '') === 'admin')   ? 'selected' : ''; ?>>Admin</option>
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Phone</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?php echo sanitize($_POST['phone'] ?? ''); ?>"
                           placeholder="+970 5x xxx xxxx">
                  </div>
                </div>

                <!-- ==============================================
                     حقول الدكتور - تظهر فقط إذا اختار "Doctor"
                     ============================================== -->
                <div id="doctor-fields" style="display:none;">
                  <hr>
                  <h5 class="text-primary">
                    <i class="fas fa-user-md mr-2"></i>
                    Doctor Information
                  </h5>

                  <div class="form-group">
                    <label>Specialization <span class="text-danger">*</span></label>
                    <select name="specialization_id" class="form-control">
                      <option value="">-- Select Specialization --</option>
                      <?php foreach ($specializations as $spec): ?>
                      <option value="<?php echo $spec['id']; ?>"
                        <?php echo (($_POST['specialization_id'] ?? '') == $spec['id']) ? 'selected' : ''; ?>>
                        <?php echo sanitize($spec['name']); ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="Doctor's biography..."><?php echo sanitize($_POST['bio'] ?? ''); ?></textarea>
                  </div>

                  <div class="form-group">
                    <label>Consultation Fee (ILS)</label>
                    <input type="number" name="consultation_fee" class="form-control"
                           step="0.01" min="0"
                           value="<?php echo sanitize($_POST['consultation_fee'] ?? '0'); ?>">
                  </div>

                  <div class="form-group">
                    <label>Available Days</label>
                    <div class="row">
                      <?php
                      $allDays     = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                      $defaultDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
                      $selectedDays = $_POST['available_days'] ?? $defaultDays;
                      foreach ($allDays as $day):
                      ?>
                      <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input"
                                 id="day_<?php echo $day; ?>"
                                 name="available_days[]"
                                 value="<?php echo $day; ?>"
                                 <?php echo in_array($day, $selectedDays) ? 'checked' : ''; ?>>
                          <label class="custom-control-label" for="day_<?php echo $day; ?>">
                            <?php echo $day; ?>
                          </label>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

              </div><!-- /.card-body -->

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-2"></i> Create User
                </button>
                <a href="<?php echo BASE_URL; ?>?page=users" class="btn btn-default ml-2">
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

<!-- نعرض/نخفي حقول الدكتور حسب الدور المختار -->
<script>
var roleSelect   = document.getElementById('role');
var doctorFields = document.getElementById('doctor-fields');

function toggleDoctorFields() {
    doctorFields.style.display = (roleSelect.value === 'doctor') ? 'block' : 'none';
}

// نشغلها عند تحميل الصفحة
toggleDoctorFields();
// ونشغلها لما يتغير الاختيار
roleSelect.addEventListener('change', toggleDoctorFields);
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

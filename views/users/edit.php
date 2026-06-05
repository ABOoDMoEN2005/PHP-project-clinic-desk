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

$pageTitle = 'Edit User';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Edit User: <?php echo sanitize($user['name']); ?></h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Edit User Information</h3>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>?page=users&action=update">
              <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
              <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

              <div class="card-body">

                <div class="form-group">
                  <label>Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control"
                         value="<?php echo sanitize($user['name']); ?>" required>
                </div>

                <!-- الإيميل للعرض فقط - ما نغيره هون -->
                <div class="form-group">
                  <label>Email (read-only)</label>
                  <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled>
                </div>

                <div class="form-group">
                  <label>Phone</label>
                  <input type="tel" name="phone" class="form-control"
                         value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                  <label>Role</label>
                  <input type="text" class="form-control"
                         value="<?php echo sanitize($user['role']); ?>" disabled>
                  <small class="text-muted">Role cannot be changed after creation.</small>
                </div>

                <hr>
                <h6>Change Password (leave blank to keep current)</h6>

                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" class="form-control"
                         placeholder="Enter new password (optional)">
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-2"></i> Save Changes
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

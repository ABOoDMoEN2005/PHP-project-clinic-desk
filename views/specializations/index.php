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

$pageTitle = 'Specializations';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Manage Specializations</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="<?php echo BASE_URL; ?>?page=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Specializations</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">

        <!-- ==========================================================
             قائمة التخصصات الموجودة
             ========================================================== -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                All Specializations
                <span class="badge badge-secondary ml-2">
                  <?php echo count($specializations); ?>
                </span>
              </h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Specialization Name</th>
                    <th style="width: 100px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($specializations)): ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted py-4">
                        No specializations found.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($specializations as $spec): ?>
                    <tr>
                      <td><?php echo $spec['id']; ?></td>
                      <td>
                        <i class="fas fa-stethoscope mr-2 text-primary"></i>
                        <?php echo sanitize($spec['name']); ?>
                      </td>
                      <td>
                        <!--
                          زر الحذف - POST form مع CSRF
                          ليش مش رابط GET؟
                          لأن حذف بيانات لازم يكون POST دايماً
                          عشان ما يتشغل بالغلط لو حدا فتح الرابط
                        -->
                        <form method="POST"
                              action="<?php echo BASE_URL; ?>?page=specializations&action=delete"
                              style="display:inline;"
                              onsubmit="return confirm('Delete this specialization? Make sure no doctors are assigned to it.');">
                          <input type="hidden" name="csrf_token"
                                 value="<?php echo CSRF::generateToken(); ?>">
                          <input type="hidden" name="id" value="<?php echo $spec['id']; ?>">
                          <button type="submit" class="btn btn-xs btn-danger">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ==========================================================
             نموذج إضافة تخصص جديد (في الجانب الأيمن)
             ========================================================== -->
        <div class="col-md-4">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-plus mr-2"></i>
                Add New Specialization
              </h3>
            </div>

            <form method="POST"
                  action="<?php echo BASE_URL; ?>?page=specializations&action=store">
              <input type="hidden" name="csrf_token"
                     value="<?php echo CSRF::generateToken(); ?>">

              <div class="card-body">
                <div class="form-group">
                  <label>Specialization Name <span class="text-danger">*</span></label>
                  <input type="text"
                         name="name"
                         class="form-control"
                         placeholder="e.g. Cardiology"
                         maxlength="100"
                         required>
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-block">
                  <i class="fas fa-save mr-2"></i> Add Specialization
                </button>
              </div>
            </form>
          </div>

          <!-- ملاحظة عن الحذف -->
          <div class="callout callout-warning">
            <h6><i class="fas fa-exclamation-triangle mr-1"></i> Note</h6>
            <small>
              You cannot delete a specialization if doctors are currently assigned to it.
              Reassign the doctors first.
            </small>
          </div>
        </div>

      </div><!-- /.row -->

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

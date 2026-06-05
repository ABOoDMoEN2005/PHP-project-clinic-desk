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

$pageTitle = 'Manage Doctors';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Manage Doctors</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="mb-3">
        <!-- إضافة دكتور = إضافة مستخدم بدور doctor -->
        <a href="<?php echo BASE_URL; ?>?page=users&action=create" class="btn btn-primary">
          <i class="fas fa-user-plus mr-1"></i> Add New Doctor
        </a>
      </div>

      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Doctor Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Fee (ILS)</th>
                <th>Available Days</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($doctors)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No doctors found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                <tr>
                  <td><?php echo $doc['id']; ?></td>
                  <td>
                    <strong>Dr. <?php echo sanitize($doc['name']); ?></strong>
                  </td>
                  <td><?php echo sanitize($doc['email']); ?></td>
                  <td>
                    <span class="badge badge-info">
                      <?php echo sanitize($doc['specialization_name']); ?>
                    </span>
                  </td>
                  <td><?php echo number_format($doc['consultation_fee'], 2); ?></td>
                  <td>
                    <small><?php echo sanitize($doc['available_days']); ?></small>
                  </td>
                  <td>
                    <?php if ($doc['is_active']): ?>
                      <span class="badge badge-success">Active</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?php echo BASE_URL; ?>?page=doctors&action=edit&id=<?php echo $doc['id']; ?>"
                       class="btn btn-xs btn-info">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($paginator->totalPages() > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination pagination-sm m-0 float-right">
              <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
              <li class="page-item <?php echo ($i === $paginator->getCurrentPage()) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=doctors&page_num=<?php echo $i; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
              <?php endfor; ?>
            </ul>
          </nav>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

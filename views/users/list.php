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

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Manage Users</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- فلتر الدور + زر إضافة مستخدم -->
      <div class="d-flex justify-content-between mb-3">
        <div>
          <a href="?page=users" class="btn btn-sm <?php echo empty($_GET['role']) ? 'btn-primary' : 'btn-default'; ?>">All</a>
          <a href="?page=users&role=admin" class="btn btn-sm <?php echo ($_GET['role'] ?? '') === 'admin' ? 'btn-primary' : 'btn-default'; ?>">Admins</a>
          <a href="?page=users&role=doctor" class="btn btn-sm <?php echo ($_GET['role'] ?? '') === 'doctor' ? 'btn-primary' : 'btn-default'; ?>">Doctors</a>
          <a href="?page=users&role=patient" class="btn btn-sm <?php echo ($_GET['role'] ?? '') === 'patient' ? 'btn-primary' : 'btn-default'; ?>">Patients</a>
        </div>
        <a href="<?php echo BASE_URL; ?>?page=users&action=create" class="btn btn-primary">
          <i class="fas fa-user-plus mr-1"></i> Add User
        </a>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($users)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No users found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                  <td><?php echo $user['id']; ?></td>
                  <td><?php echo sanitize($user['name']); ?></td>
                  <td><?php echo sanitize($user['email']); ?></td>
                  <td>
                    <?php
                    $roleColors = ['admin' => 'danger', 'doctor' => 'info', 'patient' => 'success'];
                    $rc = $roleColors[$user['role']] ?? 'secondary';
                    ?>
                    <span class="badge badge-<?php echo $rc; ?>">
                      <?php echo sanitize($user['role']); ?>
                    </span>
                  </td>
                  <td><?php echo sanitize($user['phone'] ?? '-'); ?></td>
                  <td>
                    <?php if ($user['is_active']): ?>
                      <span class="badge badge-success">Active</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo formatDate($user['created_at']); ?></td>
                  <td>
                    <!-- زر التعديل -->
                    <a href="<?php echo BASE_URL; ?>?page=users&action=edit&id=<?php echo $user['id']; ?>"
                       class="btn btn-xs btn-info">
                      <i class="fas fa-edit"></i>
                    </a>

                    <!-- زر التفعيل/التعطيل -->
                    <?php
                    $currentUser = Auth::currentUser();
                    // الأدمن ما يقدر يعطل حسابه هو
                    if ($user['id'] != $currentUser['id']):
                    ?>
                    <form method="POST"
                          action="<?php echo BASE_URL; ?>?page=users&action=toggle"
                          style="display:inline;"
                          onsubmit="return confirm('Toggle account status?');">
                      <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                      <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                      <button type="submit"
                              class="btn btn-xs <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $user['is_active'] ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>'; ?>
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

        <!-- Pagination -->
        <?php if ($paginator->totalPages() > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination pagination-sm m-0 float-right">
              <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
              <li class="page-item <?php echo ($i === $paginator->getCurrentPage()) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=users&page_num=<?php echo $i; ?>&role=<?php echo urlencode($_GET['role'] ?? ''); ?>">
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

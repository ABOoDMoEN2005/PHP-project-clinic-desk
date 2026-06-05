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

$pageTitle = 'My Prescriptions';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">My Prescriptions</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-body table-responsive p-0">
          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Appointment Date</th>
                <th>Diagnosis</th>
                <th>PDF</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($prescriptions)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-prescription-bottle fa-2x mb-2 d-block"></i>
                    No prescriptions yet.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($prescriptions as $presc): ?>
                <tr>
                  <td><?php echo $presc['id']; ?></td>
                  <td>Dr. <?php echo sanitize($presc['doctor_name']); ?></td>
                  <td><?php echo sanitize($presc['specialization_name']); ?></td>
                  <td><?php echo formatDate($presc['appt_date']); ?></td>
                  <td>
                    <!-- نعرض أول 80 حرف من التشخيص بس -->
                    <?php echo sanitize(mb_substr($presc['diagnosis'], 0, 80)) . (mb_strlen($presc['diagnosis']) > 80 ? '...' : ''); ?>
                  </td>
                  <td>
                    <?php if (!empty($presc['file_path'])): ?>
                      <!-- زر التحميل - يروح للـ controller مش الملف مباشرة -->
                      <a href="<?php echo BASE_URL; ?>?page=prescriptions&action=download&id=<?php echo $presc['id']; ?>"
                         class="btn btn-xs btn-success">
                        <i class="fas fa-file-pdf mr-1"></i> Download
                      </a>
                    <?php else: ?>
                      <span class="text-muted text-sm">No file</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

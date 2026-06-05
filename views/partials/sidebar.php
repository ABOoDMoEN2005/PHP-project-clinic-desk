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

// نجيب الدور عشان نعرض الروابط المناسبة
$role        = Auth::role();
$currentUser = Auth::currentUser();

// نعرف إيش الصفحة الحالية عشان نضيف active للرابط الصح
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- =============================================================
     الشريط الجانبي - بيتغير حسب دور المستخدم
     ============================================================= -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- اسم المشروع -->
  <a href="<?php echo BASE_URL; ?>?page=dashboard" class="brand-link">
    <img src="<?php echo BASE_URL; ?>public/assets/adminlte/dist/img/AdminLTELogo.png"
         alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
    <span class="brand-text font-weight-light"><?php echo APP_NAME; ?></span>
  </a>

  <div class="sidebar">
    <!-- بيانات المستخدم بالـ sidebar -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?php echo BASE_URL; ?>public/assets/adminlte/dist/img/user2-160x160.jpg"
             class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block">
          <?php echo sanitize($currentUser['name']); ?>
        </a>
        <span class="badge badge-success"><?php echo sanitize($role); ?></span>
      </div>
    </div>

    <!-- قائمة التنقل -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview"
          role="menu" data-accordion="false">

        <!-- =================================================
             رابط الـ Dashboard - يظهر للكل
             ================================================= -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=dashboard"
             class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- =================================================
             المواعيد - يظهر للكل لكن بمحتوى مختلف
             ================================================= -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=appointments"
             class="nav-link <?php echo ($currentPage === 'appointments') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>
              <?php if ($role === 'patient'): ?>
                My Appointments
              <?php elseif ($role === 'doctor'): ?>
                My Schedule
              <?php else: ?>
                All Appointments
              <?php endif; ?>
            </p>
          </a>
        </li>

        <!-- =================================================
             حجز موعد - للمريض فقط
             ================================================= -->
        <?php if ($role === 'patient'): ?>
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=appointments&action=book"
             class="nav-link <?php echo ($currentPage === 'appointments' && ($_GET['action'] ?? '') === 'book') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-plus-circle"></i>
            <p>Book Appointment</p>
          </a>
        </li>

        <!-- وصفاتي -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=prescriptions"
             class="nav-link <?php echo ($currentPage === 'prescriptions') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-prescription"></i>
            <p>My Prescriptions</p>
          </a>
        </li>
        <?php endif; ?>

        <!-- =================================================
             قسم الإدارة - للأدمن فقط
             ================================================= -->
        <?php if ($role === 'admin'): ?>

        <li class="nav-header">ADMINISTRATION</li>

        <!-- إدارة المستخدمين -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=users"
             class="nav-link <?php echo ($currentPage === 'users') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Manage Users</p>
          </a>
        </li>

        <!-- إدارة الأطباء -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=doctors"
             class="nav-link <?php echo ($currentPage === 'doctors') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>Manage Doctors</p>
          </a>
        </li>

        <!-- التقارير -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=reports"
             class="nav-link <?php echo ($currentPage === 'reports') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
          </a>
        </li>

        <!-- التخصصات -->
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=specializations"
             class="nav-link <?php echo ($currentPage === 'specializations') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-stethoscope"></i>
            <p>Specializations</p>
          </a>
        </li>

        <?php endif; ?>

        <!-- =================================================
             إعدادات الدكتور - للدكتور فقط
             ================================================= -->
        <?php if ($role === 'doctor'): ?>
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>?page=doctors&action=edit"
             class="nav-link <?php echo ($currentPage === 'doctors') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-edit"></i>
            <p>My Profile</p>
          </a>
        </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>

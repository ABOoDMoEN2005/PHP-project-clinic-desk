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

// نجيب بيانات المستخدم الحالي عشان نعرض اسمه بالـ navbar
$currentUser = Auth::currentUser();
?>

<!-- =============================================================
     شريط التنقل العلوي
     ============================================================= -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">

  <!-- زر فتح/إغلاق الـ sidebar -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <!-- اسم المشروع بجانب زر القائمة -->
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?php echo BASE_URL; ?>?page=dashboard" class="nav-link">
        <strong><?php echo APP_NAME; ?></strong>
      </a>
    </li>
  </ul>

  <!-- الجهة اليمنى - بيانات المستخدم وزر الخروج -->
  <ul class="navbar-nav ml-auto">

    <!-- اسم المستخدم ودوره -->
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
        <!-- صورة المستخدم - إذا ما في نستخدم صورة افتراضية -->
        <img src="<?php echo BASE_URL; ?>public/assets/adminlte/dist/img/user2-160x160.jpg"
             class="img-circle elevation-2"
             alt="User" width="30" height="30">
        <!-- اسم المستخدم - نستخدم sanitize لحماية من XSS -->
        <span class="ml-1"><?php echo sanitize($currentUser['name']); ?></span>
        <!-- badge الدور -->
        <span class="badge badge-secondary ml-1">
          <?php echo sanitize($currentUser['role']); ?>
        </span>
      </a>

      <div class="dropdown-menu dropdown-menu-right">
        <!-- رابط الملف الشخصي -->
        <a href="<?php echo BASE_URL; ?>?page=doctors&action=edit" class="dropdown-item">
          <i class="fas fa-user mr-2"></i> My Profile
        </a>
        <div class="dropdown-divider"></div>

        <!-- زر تسجيل الخروج - POST form مع CSRF حماية -->
        <!-- ليش POST وما استخدمنا رابط GET؟
             لأن رابط GET ممكن يتشغل تلقائياً من صورة في إيميل!
             POST أآمن لأنه يحتاج ضغطة فعلية من المستخدم -->
        <form method="POST" action="<?php echo BASE_URL; ?>?page=logout" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
          <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </li>

  </ul>
</nav>

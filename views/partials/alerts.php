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

// =============================================================
// ملف alerts.php - عرض رسائل Flash
//
// الـ Flash messages هي رسائل مؤقتة بتظهر مرة واحدة بس
// بعد الحفظ أو الحذف أو أي إجراء
//
// كيف بتشتغل؟
// 1. الـ Controller بيحفظ رسالة بالـ session
// 2. بعد الـ redirect، هون بنشوف الرسالة ونعرضها
// 3. بعد العرض نحذفها من الـ session
// =============================================================

// نتحقق إذا في رسالة محفوظة بالـ session
if (!empty($_SESSION['flash'])):
    $flash = $_SESSION['flash'];

    // نحدد نوع الـ alert حسب النوع
    // success = أخضر، error = أحمر، warning = أصفر، info = أزرق
    $alertClass = match($flash['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };

    $icon = match($flash['type']) {
        'success' => 'fas fa-check-circle',
        'error'   => 'fas fa-times-circle',
        'warning' => 'fas fa-exclamation-triangle',
        default   => 'fas fa-info-circle',
    };
?>

<!-- رسالة الـ Flash -->
<div class="alert <?php echo $alertClass; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
  <i class="<?php echo $icon; ?> mr-2"></i>
  <!-- sanitize مهم هون لحماية من XSS -->
  <?php echo sanitize($flash['message']); ?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<?php
    // نمسح الرسالة من الـ session بعد ما عرضناها
    // عشان ما تظهر مرة ثانية لو الصفحة اتحدثت
    unset($_SESSION['flash']);
endif;
?>

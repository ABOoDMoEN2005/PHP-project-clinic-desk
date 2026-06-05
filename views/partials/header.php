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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' | ' . APP_NAME : APP_NAME; ?></title>

  <!--
    كل الـ CSS من الـ AdminLTE المحلي - بدون CDN
    لأن المشروع لازم يشتغل offline أثناء العرض
    الـ BASE_URL بتجيب المسار الصحيح للمشروع
  -->

  <!-- AdminLTE CSS الرئيسي -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/dist/css/adminlte.min.css">

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/bootstrap/css/bootstrap.min.css">

  <!-- Font Awesome للأيقونات -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">

  <!-- DateRangePicker للفلاتر -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/daterangepicker/daterangepicker.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

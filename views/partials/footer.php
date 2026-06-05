  </div><!-- /.content-wrapper -->

  <!-- Footer -->
  <footer class="main-footer">
    <strong>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></strong>
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>

</div><!-- ./wrapper -->

<!--
  كل الـ JavaScript في آخر الصفحة - هاد أفضل للأداء
  لأن المتصفح بيحمّل الـ HTML أول، بعدين يحمّل الـ JS
  ما نحط JS في نص الصفحة!
-->

<!-- jQuery - لازم يجي قبل كل شي تاني -->
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap 4 -->
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE JS الرئيسي -->
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/dist/js/adminlte.min.js"></script>

<!-- DataTables للجداول مع بحث وترتيب -->
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- تشغيل DataTables على كل الجداول اللي عندها class=datatable -->
<script>
$(document).ready(function() {
  // كل جدول عنده class "datatable" بيصير تلقائياً قابل للبحث والترتيب
  $('.datatable').DataTable({
    responsive: true,
    pageLength: 10,         // 10 صفوف بكل صفحة
    order: [],              // ما نرتب تلقائياً
    language: {
      emptyTable: "No data available"
    }
  });
});
</script>

</body>
</html>

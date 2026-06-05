<?php
/**
 * Project: ClinicDesk | Name: Abd alrahman Moen Hemaid | ID: 120240672
 */
// إذا داخل أصلاً روح للداشبورد
if (Auth::check()) {
    redirect('?page=dashboard');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | <?php echo APP_NAME; ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition login-page">

<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>Clinic</b>Desk</a>
    <p class="text-muted text-sm">Clinic Management Dashboard</p>
  </div>

  <div class="card">
    <div class="card-body login-card-body">

      <p class="login-box-msg">Sign in to your account</p>

      <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['flash']['type'] === 'error') ? 'danger' : 'success'; ?> alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?php echo sanitize($_SESSION['flash']['message']); ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php endif; ?>

      <form method="POST" action="<?php echo BASE_URL; ?>?page=login">

        <!-- CSRF Token - حماية من هجوم CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">

        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control"
                 placeholder="Email Address" required autocomplete="email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control"
                 placeholder="Password" required autocomplete="current-password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-sign-in-alt mr-2"></i> Sign In
            </button>
          </div>
        </div>

      </form>

      <p class="mt-3 text-muted text-sm text-center">
        <i class="fas fa-info-circle"></i>
        Contact your administrator to create an account.
      </p>

    </div>
  </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/adminlte/dist/js/adminlte.min.js"></script>

</body>
</html>

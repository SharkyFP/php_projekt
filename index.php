<?php
include "config/db.php";
include "includes/functions.php";

$menu = $_GET['menu'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="SY Development">
  <meta name="keywords" content="HTML5,CSS,PHP,MySQL,CMS,FIVEM,QB">
  <meta name="author" content="Filip Pribanić">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SY Development</title>

  <?php if (file_exists('assets/css/bootstrap.min.css')): ?>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <?php endif; ?>

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>
<?php include "includes/nav.php"; ?>

<main>
<?php
switch ($menu) {
  case 'home': include "pages/home.php"; break;
  case 'news': include "pages/news.php"; break;
  case 'news-single': include "pages/news-single.php"; break;
  case 'contact': include "pages/contact.php"; break;
  case 'gallery': include "pages/gallery.php"; break;
  case 'about': include "pages/about.php"; break;

  case 'login': include "auth/login.php"; break;
  case 'register': include "auth/register.php"; break;

  case 'admin': include "admin/dashboard.php"; break;
  case 'admin-users': include "admin/users.php"; break;
  case 'admin-news': include "admin/news.php"; break;

  default:
    http_response_code(404);
    echo "<h2>Stranica ne postoji</h2>";
}
?>
</main>

<?php include "includes/footer.php"; ?>

<?php if (file_exists('assets/js/bootstrap.min.js')): ?>
  <script src="assets/js/bootstrap.min.js"></script>
<?php endif; ?>
</body>
</html>

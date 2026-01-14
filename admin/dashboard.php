<?php
require_login();
$u = current_user();
if ((int)$u['approved'] !== 1) {
  echo "<h2>Vaš račun čeka odobrenje administratora.</h2>";
  echo "<p>Ne možete pristupiti CMS administraciji dok vas admin ne odobri.</p>";
  return;
}
?>
<h1>Administracija (CMS)</h1>

<div class="admin-nav">
  <a href="index.php?menu=admin-news">Vijesti</a>
  <?php if (has_role(['admin'])): ?>
    <a href="index.php?menu=admin-users">Korisnici</a>
  <?php endif; ?>
</div>

<p>Prijavljeni korisnik: <b><?php echo e($u['username']); ?></b> (rola: <?php echo e($u['role']); ?>)</p>

<ul style="margin-top:12px;line-height:1.8;">
  <li><b>Administrator</b>: uređuje korisnike, dodaje/mijenja/briše vijesti, odobrava vijesti.</li>
  <li><b>Editor</b>: dodaje/mijenja vijesti, arhivira vijesti, ne briše i ne vidi korisnike.</li>
  <li><b>User</b>: može dodati vijest, ali ona nije vidljiva na frontend dok administrator ne odobri.</li>
</ul>

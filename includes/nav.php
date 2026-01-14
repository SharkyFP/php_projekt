<?php $u = current_user(); ?>
<nav>
  <ul>
    <li><a href="index.php?menu=home">Početna</a></li>
    <li><a href="index.php?menu=news">Novosti</a></li>
    <li><a href="index.php?menu=gallery">Galerija</a></li>
    <li><a href="index.php?menu=about">O nama</a></li>
    <li><a href="index.php?menu=contact">Kontakt</a></li>

    <?php if (!$u): ?>
      <li><a href="index.php?menu=register">Registracija</a></li>
      <li><a href="index.php?menu=login">Prijava</a></li>
    <?php else: ?>
      <?php if ((int)$u['approved'] === 1): ?>
        <li><a href="index.php?menu=admin">Administracija</a></li>
      <?php endif; ?>
      <li><a href="auth/logout.php">Odjava (<?php echo e($u['username']); ?>)</a></li>
    <?php endif; ?>
  </ul>
</nav>

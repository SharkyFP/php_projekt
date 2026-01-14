<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT id, username, password_hash, role, approved FROM users WHERE username = ? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $error = '';
  if (!$user || !password_verify($password, $user['password_hash'])) {
    $error = "Neispravno korisničko ime ili lozinka.";
  } elseif ((int)$user['approved'] !== 1) {
    // po uputama: nema prijave dok admin ne omogući
    $error = "Račun nije odobren od administratora. Pokušaj kasnije.";
  } else {
    $_SESSION['user'] = [
      'id' => (int)$user['id'],
      'username' => $user['username'],
      'role' => $user['role'],
      'approved' => (int)$user['approved']
    ];
    echo "<h2>Uspješno prijavljen!</h2>";
    echo "<p><a href='index.php?menu=admin'>Ulazak u administraciju</a></p>";
    return;
  }
}
?>

<h1>Prijava</h1>

<?php if (!empty($error)): ?>
  <div style="background:#f8d7da;padding:10px;border-radius:8px;margin-bottom:12px;">
    <?php echo e($error); ?>
  </div>
<?php endif; ?>

<form method="post" class="kontakt-forma">
  <label>Korisničko ime</label>
  <input type="text" name="username" required>

  <label>Lozinka</label>
  <input type="password" name="password" required>

  <button type="submit">Prijavi se</button>

  <p style="margin-top:10px;font-size:14px;color:#555;">
    Admin test: username <b>admin</b> / password <b>Admin123</b>
  </p>
</form>

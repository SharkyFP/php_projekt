<?php
// Registracija
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstname = trim($_POST['firstname'] ?? '');
  $lastname  = trim($_POST['lastname'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $country_id= (int)($_POST['country_id'] ?? 0);
  $city      = trim($_POST['city'] ?? '');
  $street    = trim($_POST['street'] ?? '');
  $dob       = $_POST['dob'] ?? '';
  $password  = $_POST['password'] ?? '';

  $errors = [];
  if ($firstname === '' || $lastname === '' || $email === '' || $country_id<=0 || $city==='' || $street==='' || $dob==='') {
    $errors[] = "Molimo ispunite sva obavezna polja.";
  }
  if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "E-mail adresa nije ispravna.";
  }

  // Ako korisnik nije upisao lozinku, generiraj
  $generatedPassword = '';
  if ($password === '') {
    $generatedPassword = generate_password(10);
    $password = $generatedPassword;
  }

  if (!$errors) {
    $username = generate_username($conn, $firstname, $lastname);
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, country_id, city, street, dob, password_hash, role, approved)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 0)");
    $stmt->bind_param("ssssissss", $firstname, $lastname, $username, $email, $country_id, $city, $street, $dob, $hash);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
      // auto-login (traženo u zadatku) - ali račun je pending za admin/role
      $_SESSION['user'] = [
        'id' => mysqli_insert_id($conn),
        'username' => $username,
        'role' => 'user',
        'approved' => 0
      ];

      echo "<h2>Uspješna registracija!</h2>";
      echo "<p>Korisničko ime: <b>".e($username)."</b></p>";
      if ($generatedPassword !== '') {
        echo "<p>Generirana lozinka: <b>".e($generatedPassword)."</b> (zapiši je!)</p>";
      }
      echo "<p>Prijavljeni ste, ali vaš račun čeka odobrenje administratora za pristup CMS administraciji.</p>";
      echo "<p><a href='index.php?menu=home'>Natrag na početnu</a></p>";
      return;
    } else {
      $errors[] = "Greška pri spremanju korisnika.";
    }
  }
}

// Countries list
$countries = [];
$res = $conn->query("SELECT id, name FROM countries ORDER BY name");
while ($row = $res->fetch_assoc()) $countries[] = $row;
?>

<h1>Registracija</h1>

<?php if (!empty($errors)): ?>
  <div style="background:#fff3cd;padding:10px;border-radius:8px;margin-bottom:12px;">
    <?php foreach ($errors as $e): ?>
      <p><?php echo e($e); ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" class="kontakt-forma">
  <div class="form-row">
    <div>
      <label>Ime *</label>
      <input type="text" name="firstname" required value="<?php echo e($_POST['firstname'] ?? ''); ?>">
    </div>
    <div>
      <label>Prezime *</label>
      <input type="text" name="lastname" required value="<?php echo e($_POST['lastname'] ?? ''); ?>">
    </div>
  </div>

  <label>E-mail *</label>
  <input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">

  <label>Država *</label>
  <select name="country_id" required>
    <option value="">-- odaberi --</option>
    <?php foreach ($countries as $c): ?>
      <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($_POST['country_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
        <?php echo e($c['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <div class="form-row">
    <div>
      <label>Grad *</label>
      <input type="text" name="city" required value="<?php echo e($_POST['city'] ?? ''); ?>">
    </div>
    <div>
      <label>Ulica *</label>
      <input type="text" name="street" required value="<?php echo e($_POST['street'] ?? ''); ?>">
    </div>
  </div>

  <label>Datum rođenja *</label>
  <input type="date" name="dob" required value="<?php echo e($_POST['dob'] ?? ''); ?>">

  <label>Lozinka (ako ostaviš prazno, generira se automatski)</label>
  <input type="password" name="password">

  <button type="submit">Registriraj se</button>
</form>

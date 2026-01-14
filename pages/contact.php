<?php
require 'C:\xampp\htdocs\cms_project\config\db.php';

// Fetch countries (mysqli)
$sql = "SELECT name FROM countries ORDER BY name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Greška pri dohvaćanju država: " . mysqli_error($conn));
}
?>

<h1>Kontakt</h1>

<section style="margin-bottom:20px;">
  <div>
    <iframe
      title="Google karta"
      width="100%" height="320" style="border:0; border-radius:10px;"
      loading="lazy" allowfullscreen
      src="https://www.google.com/maps?q=Zagreb&output=embed"></iframe>
  </div>
</section>

<section>
  <h2>Kontakt forma</h2>

  <form action="" method="post" class="kontakt-forma">
    <label>Ime *</label>
    <input type="text" name="ime" required>

    <label>Prezime *</label>
    <input type="text" name="prezime" required>

    <label>E-mail *</label>
    <input type="email" name="email" required>

    <label>Država</label>
    <select name="drzava">
      <option value="">-- Odaberite državu --</option>

      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <option value="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endwhile; ?>
    </select>

    <label>Opis</label>
    <textarea name="opis" rows="5"></textarea>

    <button type="submit">Pošalji</button>
  </form>

  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <p><b>Podaci poslani:</b><br>
      <?= htmlspecialchars($_POST['ime'] ?? '', ENT_QUOTES, 'UTF-8') ?>
      <?= htmlspecialchars($_POST['prezime'] ?? '', ENT_QUOTES, 'UTF-8') ?>
      (<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>)<br>
      Država: <?= htmlspecialchars($_POST['drzava'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>
</section>

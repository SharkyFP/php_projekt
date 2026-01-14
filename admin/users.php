<?php
require_role(['admin']);

// update role / approve
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = (int)($_POST['user_id'] ?? 0);
  $role = $_POST['role'] ?? 'user';
  $approved = isset($_POST['approved']) ? 1 : 0;

  if ($user_id > 0 && in_array($role, ['user','editor','admin'], true)) {
    $stmt = $conn->prepare("UPDATE users SET role = ?, approved = ? WHERE id = ? AND username <> 'admin'");
    $stmt->bind_param("sii", $role, $approved, $user_id);
    $stmt->execute();
    $stmt->close();
  }
  redirect("index.php?menu=admin-users");
}

$res = $conn->query("SELECT u.id, u.firstname, u.lastname, u.username, u.email, u.role, u.approved, c.name AS country
                     FROM users u JOIN countries c ON c.id = u.country_id
                     ORDER BY u.created_at DESC");
?>

<h1>Korisnici</h1>
<p><a href="index.php?menu=admin">← Natrag</a></p>

<table class="table">
  <thead>
    <tr>
      <th>Ime</th>
      <th>Korisničko ime</th>
      <th>E-mail</th>
      <th>Država</th>
      <th>Rola</th>
      <th>Odobren</th>
      <th>Akcija</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($u = $res->fetch_assoc()): ?>
    <tr>
      <td><?php echo e($u['firstname'] . ' ' . $u['lastname']); ?></td>
      <td><?php echo e($u['username']); ?></td>
      <td><?php echo e($u['email']); ?></td>
      <td><?php echo e($u['country']); ?></td>
      <td>
        <?php if ($u['username'] === 'admin'): ?>
          admin
        <?php else: ?>
          <form method="post" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
            <select name="role">
              <?php foreach (['user','editor','admin'] as $r): ?>
                <option value="<?php echo $r; ?>" <?php echo ($u['role']===$r)?'selected':''; ?>><?php echo $r; ?></option>
              <?php endforeach; ?>
            </select>
      </td>
      <td>
            <label style="display:flex;gap:6px;align-items:center;">
              <input type="checkbox" name="approved" <?php echo ((int)$u['approved']===1)?'checked':''; ?>>
              Da
            </label>
      </td>
      <td>
            <button type="submit">Spremi</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

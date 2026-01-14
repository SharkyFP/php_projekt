<?php
require_login();
$me = current_user();
if ((int)$me['approved'] !== 1) {
  echo "<h2>Vaš račun čeka odobrenje administratora.</h2>";
  return;
}

$canApprove = has_role(['admin']);
$canDelete  = has_role(['admin']);
$canArchive = has_role(['admin','editor']);
$canEditAll = has_role(['admin','editor']); // user uređuje samo svoje

function allowed_upload(string $name): bool {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  return in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ========= ACTIONS ========= */

// Approve
if ($action === 'approve' && $canApprove && $id>0) {
  $stmt = $conn->prepare("UPDATE news SET is_approved = 1 WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  redirect("index.php?menu=admin-news");
}

// Archive toggle
if ($action === 'archive' && $canArchive && $id>0) {
  $stmt = $conn->prepare("UPDATE news SET is_archived = 1 - is_archived WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  redirect("index.php?menu=admin-news");
}

// Delete
if ($action === 'delete' && $canDelete && $id>0) {
  $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  redirect("index.php?menu=admin-news");
}

// Remove gallery image (admin/editor/owner)
if ($action === 'img-del' && $id>0) {
  $img_id = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
  if ($img_id>0) {
    // check ownership
    $stmt = $conn->prepare("SELECT n.author_id FROM news_images ni JOIN news n ON n.id=ni.news_id WHERE ni.id=?");
    $stmt->bind_param("i",$img_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && ($canEditAll || (int)$row['author_id']===(int)$me['id'])) {
      $stmt = $conn->prepare("DELETE FROM news_images WHERE id=?");
      $stmt->bind_param("i",$img_id);
      $stmt->execute();
      $stmt->close();
    }
  }
  redirect("index.php?menu=admin-news&action=edit&id=".$id);
}

/* ========= ADD / EDIT FORM ========= */
$errors = [];
$edit = null;

if ($action === 'edit' && $id>0) {
  $stmt = $conn->prepare("SELECT * FROM news WHERE id=? LIMIT 1");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$edit) { $action = 'list'; }
  else {
    if (!($canEditAll || (int)$edit['author_id']===(int)$me['id'])) {
      http_response_code(403);
      echo "<h2>Nemate prava uređivati ovu vijest.</h2>";
      return;
    }
  }
}

if (in_array($action, ['add','edit'], true) && $_SERVER['REQUEST_METHOD']==='POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');

  if ($title==='' || $content==='') $errors[] = "Naslov i tekst su obavezni.";

  // main image upload (optional on edit)
  $mainImageName = $edit['image'] ?? '';
  if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
      $errors[] = "Greška kod uploada glavne slike.";
    } elseif (!allowed_upload($_FILES['image']['name'])) {
      $errors[] = "Dozvoljeni formati slike: jpg, jpeg, png, gif, webp.";
    } else {
      $safe = time() . "_" . preg_replace('/[^a-zA-Z0-9_.-]/','_', $_FILES['image']['name']);
      $dest = "uploads/news/" . $safe;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $mainImageName = $safe;
      } else {
        $errors[] = "Ne mogu spremiti glavnu sliku.";
      }
    }
  }

  if (!$errors) {
    if ($action === 'add') {
      $approved = has_role(['admin']) ? 1 : 0; // user/editor ide na odobravanje
      $stmt = $conn->prepare("INSERT INTO news (title, image, content, is_archived, is_approved, author_id)
                              VALUES (?, ?, ?, 0, ?, ?)");
      $stmt->bind_param("sssii", $title, $mainImageName, $content, $approved, $me['id']);
      $stmt->execute();
      $newId = $stmt->insert_id;
      $stmt->close();
      $id = $newId;
      $action = 'edit';
      // refresh edit record
      $stmt = $conn->prepare("SELECT * FROM news WHERE id=?");
      $stmt->bind_param("i",$id);
      $stmt->execute();
      $edit = $stmt->get_result()->fetch_assoc();
      $stmt->close();
    } else {
      $stmt = $conn->prepare("UPDATE news SET title=?, image=?, content=? WHERE id=?");
      $stmt->bind_param("sssi", $title, $mainImageName, $content, $id);
      $stmt->execute();
      $stmt->close();
    }

    // gallery images
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
      for ($i=0; $i<count($_FILES['gallery']['name']); $i++) {
        if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $orig = $_FILES['gallery']['name'][$i];
        if (!allowed_upload($orig)) continue;

        $safe = time() . "_" . $i . "_" . preg_replace('/[^a-zA-Z0-9_.-]/','_', $orig);
        $dest = "uploads/news/gallery/" . $safe;
        if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $dest)) {
          $cap = trim($_POST['gallery_caption'][$i] ?? '');
          $stmt = $conn->prepare("INSERT INTO news_images (news_id, image, caption) VALUES (?, ?, ?)");
          $stmt->bind_param("iss", $id, $safe, $cap);
          $stmt->execute();
          $stmt->close();
        }
      }
    }

    redirect("index.php?menu=admin-news&action=edit&id=".$id);
  }
}
?>

<h1>Vijesti (CMS)</h1>
<p><a href="index.php?menu=admin">← Natrag</a></p>

<div class="admin-nav">
  <a href="index.php?menu=admin-news&action=add">+ Nova vijest</a>
</div>

<?php if (in_array($action, ['add','edit'], true)): ?>
  <h2><?php echo ($action==='add') ? 'Nova vijest' : 'Uredi vijest'; ?></h2>

  <?php if ($errors): ?>
    <div style="background:#fff3cd;padding:10px;border-radius:8px;margin-bottom:12px;">
      <?php foreach ($errors as $e): ?><p><?php echo e($e); ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="kontakt-forma" style="max-width:800px;">
    <label>Naslov *</label>
    <input type="text" name="title" required value="<?php echo e($_POST['title'] ?? ($edit['title'] ?? '')); ?>">

    <label>Tekst *</label>
    <textarea name="content" rows="8" required><?php echo e($_POST['content'] ?? ($edit['content'] ?? '')); ?></textarea>

    <label>Glavna slika (thumbnail) <?php echo ($action==='add') ? '*' : ''; ?></label>
    <input type="file" name="image" <?php echo ($action==='add') ? 'required' : ''; ?>>

    <?php if (!empty($edit['image'])): ?>
      <p>Trenutna slika:</p>
      <img src="uploads/news/<?php echo e($edit['image']); ?>" alt="" style="max-width:200px;">
      <p>
        Status:
        <?php if ((int)$edit['is_approved']===1): ?><span class="badge approved">odobreno</span>
        <?php else: ?><span class="badge pending">čeka odobrenje</span><?php endif; ?>
        <?php if ((int)$edit['is_archived']===1): ?><span class="badge archived">arhivirano</span><?php endif; ?>
      </p>
    <?php endif; ?>

    <label>Galerija slika (više slika)</label>
    <input type="file" name="gallery[]" multiple>

    <p style="font-size:14px;color:#555;">
      Caption (opcionalno): upiši opise po redu uploadanih slika (ako ništa ne upišeš, ostaje prazno).
    </p>

    <!-- 5 caption polja -->
    <?php for ($i=0; $i<5; $i++): ?>
      <input type="text" name="gallery_caption[]" placeholder="Opis slike <?php echo $i+1; ?>">
    <?php endfor; ?>

    <button type="submit">Spremi</button>
  </form>

  <?php if ($action==='edit' && $id>0): ?>
    <h3 style="margin-top:20px;">Postojeća galerija</h3>
    <section class="gallery">
      <?php
        $stmt = $conn->prepare("SELECT id, image, caption FROM news_images WHERE news_id=? ORDER BY id DESC");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $imgs = $stmt->get_result();
        while ($img = $imgs->fetch_assoc()):
      ?>
        <figure class="gallery-item">
          <a href="uploads/news/gallery/<?php echo e($img['image']); ?>" target="_blank">
            <img src="uploads/news/gallery/<?php echo e($img['image']); ?>" alt="">
          </a>
          <figcaption><?php echo e($img['caption'] ?? ''); ?></figcaption>
          <div style="margin-top:8px;">
            <a href="index.php?menu=admin-news&action=img-del&id=<?php echo $id; ?>&img_id=<?php echo (int)$img['id']; ?>">Obriši</a>
          </div>
        </figure>
      <?php endwhile; $stmt->close(); ?>
    </section>

    <p style="margin-top:12px;">
      <?php if ($canApprove && (int)$edit['is_approved']!==1): ?>
        <a href="index.php?menu=admin-news&action=approve&id=<?php echo $id; ?>">Odobri vijest</a> |
      <?php endif; ?>

      <?php if ($canArchive): ?>
        <a href="index.php?menu=admin-news&action=archive&id=<?php echo $id; ?>">
          <?php echo ((int)$edit['is_archived']===1) ? 'Vrati iz arhive' : 'Arhiviraj'; ?>
        </a>
      <?php endif; ?>

      <?php if ($canDelete): ?>
        | <a href="index.php?menu=admin-news&action=delete&id=<?php echo $id; ?>" onclick="return confirm('Obrisati vijest?')">Obriši</a>
      <?php endif; ?>
    </p>
  <?php endif; ?>

<?php else: ?>
  <?php
    // list: admin sees all, editor/user sees their own + approved
    if ($canEditAll) {
      $stmt = $conn->prepare("SELECT n.*, u.username FROM news n JOIN users u ON u.id=n.author_id ORDER BY n.created_at DESC");
    } else {
      $stmt = $conn->prepare("SELECT n.*, u.username FROM news n JOIN users u ON u.id=n.author_id WHERE n.author_id=? ORDER BY n.created_at DESC");
      $stmt->bind_param("i", $me['id']);
    }
    $stmt->execute();
    $res = $stmt->get_result();
  ?>

  <table class="table">
    <thead>
      <tr>
        <th>Naslov</th>
        <th>Autor</th>
        <th>Datum</th>
        <th>Status</th>
        <th>Akcije</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($n = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo e($n['title']); ?></td>
        <td><?php echo e($n['username']); ?></td>
        <td><?php echo date('d.m.Y. H:i', strtotime($n['created_at'])); ?></td>
        <td>
          <?php if ((int)$n['is_approved']===1): ?><span class="badge approved">odobreno</span>
          <?php else: ?><span class="badge pending">čeka odobrenje</span><?php endif; ?>
          <?php if ((int)$n['is_archived']===1): ?><span class="badge archived">arhivirano</span><?php endif; ?>
        </td>
        <td>
          <a href="index.php?menu=admin-news&action=edit&id=<?php echo (int)$n['id']; ?>">Uredi</a>
          <?php if ($canArchive): ?>
            | <a href="index.php?menu=admin-news&action=archive&id=<?php echo (int)$n['id']; ?>">
              <?php echo ((int)$n['is_archived']===1) ? 'Vrati' : 'Arhiviraj'; ?>
            </a>
          <?php endif; ?>
          <?php if ($canApprove && (int)$n['is_approved']!==1): ?>
            | <a href="index.php?menu=admin-news&action=approve&id=<?php echo (int)$n['id']; ?>">Odobri</a>
          <?php endif; ?>
          <?php if ($canDelete): ?>
            | <a href="index.php?menu=admin-news&action=delete&id=<?php echo (int)$n['id']; ?>" onclick="return confirm('Obrisati vijest?')">Obriši</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <?php $stmt->close(); ?>
<?php endif; ?>

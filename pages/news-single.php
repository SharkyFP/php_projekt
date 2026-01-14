<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT n.*, u.username
                        FROM news n
                        JOIN users u ON u.id = n.author_id
                        WHERE n.id = ? AND n.is_approved = 1 AND n.is_archived = 0
                        LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$news) {
  echo "<p>Članak ne postoji ili nije odobren.</p>";
  return;
}
?>

<article>
  <h1><?php echo e($news['title']); ?></h1>
  <h3>Detaljan prikaz članka</h3>
  <p><small>Objavljeno: <?php echo date('d.m.Y.', strtotime($news['created_at'])); ?> | Autor: <?php echo e($news['username']); ?></small></p>

  <figure>
    <img src="uploads/news/<?php echo e($news['image']); ?>" alt="<?php echo e($news['title']); ?>" style="max-width:500px;">
    <figcaption>Glavna slika članka</figcaption>
  </figure>

  <?php
    // sadržaj može biti više odlomaka (u seed-u je s \n\n)
    $parts = preg_split("/\n\s*\n/", trim($news['content']));
    if (count($parts) < 5) {
      while (count($parts) < 5) $parts[] = "Dodatni odlomak (primjer).";
    }
    foreach ($parts as $p) {
      echo "<p>" . nl2br(e($p)) . "</p>";
    }
  ?>

  <h3>Galerija slika</h3>
  <section class="gallery">
    <?php
      $stmt = $conn->prepare("SELECT image, caption FROM news_images WHERE news_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $imgs = $stmt->get_result();
      while ($img = $imgs->fetch_assoc()):
        $file = $img['image'];
        $cap = $img['caption'] ?? '';
    ?>
      <figure class="gallery-item">
        <a href="uploads/news/gallery/<?php echo e($file); ?>" target="_blank">
          <img src="uploads/news/gallery/<?php echo e($file); ?>" alt="<?php echo e($cap ?: 'Slika'); ?>">
        </a>
        <figcaption><?php echo e($cap ?: 'Slika'); ?></figcaption>
      </figure>
    <?php endwhile; $stmt->close(); ?>
  </section>

  <p style="margin-top:16px;">
    <a href="index.php?menu=news">← Povratak na novosti</a>
  </p>
</article>

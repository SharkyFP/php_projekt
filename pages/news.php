<h1>Novosti</h1>

<section class="news-list">
<?php
$stmt = $conn->prepare("SELECT n.id, n.title, n.image, n.content, n.created_at
                        FROM news n
                        WHERE n.is_approved = 1 AND n.is_archived = 0
                        ORDER BY n.created_at DESC
                        LIMIT 5");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()):
  $excerpt = mb_substr(strip_tags($row['content']), 0, 160);
?>
  <article>
    <div class="news-thumb">
      <figure>
        <a href="index.php?menu=news-single&id=<?php echo (int)$row['id']; ?>">
          <img src="uploads/news/<?php echo e($row['image']); ?>" alt="<?php echo e($row['title']); ?>">
        </a>
        <figcaption><?php echo e($row['title']); ?></figcaption>
      </figure>

      <div>
        <h2>
          <a href="index.php?menu=news-single&id=<?php echo (int)$row['id']; ?>">
            <?php echo e($row['title']); ?>
          </a>
        </h2>

        <p><?php echo e($excerpt); ?>...</p>
        <p><small>Objavljeno: <?php echo date('d.m.Y.', strtotime($row['created_at'])); ?></small></p>

        <a href="index.php?menu=news-single&id=<?php echo (int)$row['id']; ?>">Više o ovom</a>
      </div>
    </div>
  </article>
<?php endwhile; $stmt->close(); ?>
</section>

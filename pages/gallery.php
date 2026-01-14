<h1>Galerija</h1>

<section class="gallery">
<?php
$images = [];
for ($i=1; $i<=2; $i++) {
  $images[] = ["g{$i}.png", "Opis slike {$i}"];
}
foreach ($images as $img):
  $file = $img[0];
  $desc = $img[1];
?>
  <figure class="gallery-item">
    <a href="assets/images/gallery/<?php echo e($file); ?>" target="_blank">
      <img src="assets/images/gallery/<?php echo e($file); ?>" alt="<?php echo e($desc); ?>">
    </a>
    <figcaption><?php echo e($desc); ?></figcaption>
  </figure>
<?php endforeach; ?>
</section>

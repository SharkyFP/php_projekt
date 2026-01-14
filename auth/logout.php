<?php
session_start();
session_destroy();
header("Location: ../index.php?menu=home");
exit;

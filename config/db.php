<?php
// XAMPP default: user=root, pass=''.
// Ako ti je lozinka drugačija, promijeni ovdje.
$conn = mysqli_connect("localhost", "root", "", "cms_project");

if (!$conn) {
    die("Greška pri spajanju na bazu: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
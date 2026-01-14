<?php
// Helper functions
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function has_role(array $roles): bool {
    $u = current_user();
    if (!$u) return false;
    return in_array($u['role'], $roles, true);
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect("index.php?menu=login");
    }
}

function require_role(array $roles): void {
    require_login();
    if (!has_role($roles)) {
        http_response_code(403);
        echo "<h2>Nemate prava pristupa.</h2>";
        exit;
    }
}

// Generates username: first letter of firstname + lastname, unique by adding number suffix
function generate_username(mysqli $conn, string $firstname, string $lastname): string {
    $base = strtolower(substr(preg_replace('/\s+/', '', $firstname), 0, 1) . preg_replace('/\s+/', '', $lastname));
    $base = preg_replace('/[^a-z0-9_]/', '', $base);
    if ($base === '') $base = 'user';

    $username = $base;
    $i = 1;

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    while (true) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) break;
        $i++;
        $username = $base . $i;
    }
    $stmt->close();
    return $username;
}

// Simple random password generator (shown only once on registration success)
function generate_password(int $len = 10): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $out = '';
    for ($i=0; $i<$len; $i++) {
        $out .= $chars[random_int(0, strlen($chars)-1)];
    }
    return $out;
}

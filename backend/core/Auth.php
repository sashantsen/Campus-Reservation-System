<?php
/**
 * ------------------------------------------------------------
 * Auth.php
 * ------------------------------------------------------------
 * Shared authentication + session handler.
 * Works seamlessly across /frontend and /backend.
 * ------------------------------------------------------------
 */

class Auth
{
    /** Boot session system using the shared bootstrap */
    private static function boot(): void
    {
        require_once __DIR__ . '/../../shared/session_boot.php';
    }

    /** Start session manually (optional alias) */
    public static function start(): void
    {
        self::boot();
    }

    /** Log in and store user data in session */
    public static function login(array $user): void
    {
        self::boot();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role']    = $user['role'] ?? 'student';

        $_SESSION['user'] = [
            'id'         => (int)$user['id'],
            'name'       => $user['name'] ?? null,
            'email'      => $user['email'] ?? null,
            'student_id' => $user['student_id'] ?? null,
            'role'       => $user['role'] ?? 'student',
        ];
    }

    /** Destroy login session */
    public static function logout(): void
    {
        self::boot();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    /** Get user ID */
    public static function id(): ?int
    {
        self::boot();
        return $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);
    }

    /** Get user role */
    public static function role(): ?string
    {
        self::boot();
        return $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);
    }

    /** Get entire user array */
    public static function user(): ?array
    {
        self::boot();
        return $_SESSION['user'] ?? null;
    }

    /** Check if logged in */
    public static function check(): bool
    {
        return self::id() !== null;
    }

    /** Require login for API endpoints */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthenticated']);
            exit;
        }
    }

    /** Require admin role */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (self::role() !== 'admin') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
}

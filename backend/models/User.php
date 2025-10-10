<?php
class User extends Model {
  protected string $table = 'users';

  public function findByEmail(string $email) {
    $st = Database::conn()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    return $st->fetch();
  }

  public function create(array $data): int {
    $sql = "INSERT INTO users(name,email,student_id,password_hash,role,created_at)
            VALUES(?,?,?,?,?,?)";
    $st = Database::conn()->prepare($sql);
    $st->execute([
      $data['name'],
      $data['email'],
      $data['student_id'],
      password_hash($data['password'], PASSWORD_BCRYPT),
      $data['role'] ?? 'student',
      Helpers::now()
    ]);
    return (int)Database::conn()->lastInsertId();
  }

  /** Admin: quick search */
  public function search(?string $q, ?string $role): array {
    $pdo = Database::conn();
    $where = []; $p = [];
    if ($q)   { $where[]="(name LIKE ? OR email LIKE ? OR student_id LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%"; }
    if ($role){ $where[]="role=?"; $p[]=$role; }

    $sql = "SELECT id,name,email,student_id,role,created_at FROM users ".
           (count($where) ? "WHERE ".implode(' AND ', $where) : "").
           " ORDER BY created_at DESC LIMIT 500";
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function setRole(int $userId, string $role): void {
    $st = Database::conn()->prepare("UPDATE users SET role=? WHERE id=?");
    $st->execute([$role, $userId]);
  }
}

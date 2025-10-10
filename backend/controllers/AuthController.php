<?php
class AuthController extends Controller {

  public function register() {
    $data = $this->input(['name','email','student_id','password'], true);
    $userModel = new User();
    if ($userModel->findByEmail($data['email'])) {
      return $this->json(['error' => 'Email already registered'], 422);
    }
    $id = $userModel->create($data);
    $user = $userModel->find($id);
    Auth::login($user);
    return $this->json([
      'message' => 'Registered',
      'user'    => Helpers::only($user, ['id','name','email','student_id','role'])
    ], 201);
  }

  public function login() {
    $data = $this->input(['email','password'], true);
    $user = (new User())->findByEmail($data['email']);
    if (!$user || !password_verify($data['password'], $user['password_hash'])) {
      return $this->json(['error' => 'Invalid credentials'], 401);
    }
    Auth::login($user);
    return $this->json([
      'message' => 'Logged in',
      'user'    => Helpers::only($user, ['id','name','email','student_id','role'])
    ]);
  }

  public function me() {
    Auth::requireAuth();
    $id = Auth::id();
    $user = (new User())->find($id);
    return $this->json(
      Helpers::only($user, ['id','name','email','student_id','role','avatar_url','created_at'])
    );
  }

  public function logout() {
    Auth::logout();
    return $this->json(['message' => 'Logged out']);
  }

  // ===== NEW: update profile =====
  public function updateProfile() {
    if (empty($_SESSION['user'])) {
      return $this->json(['error' => 'Unauthorized'], 401);
    }
    $userId = (int)$_SESSION['user']['id'];

    // accept name, email (optional to change), avatar_url
    $in = $this->input(['name','email','avatar_url'], false);
    $name  = trim((string)($in['name']  ?? ''));
    $email = trim((string)($in['email'] ?? $_SESSION['user']['email'] ?? ''));
    $avatar= trim((string)($in['avatar_url'] ?? ''));

    if ($name === '' || $email === '') {
      return $this->json(['error' => 'Name and email are required'], 422);
    }

    $userModel = new User();
    $existing  = $userModel->findByEmail($email);
    if ($existing && (int)$existing['id'] !== $userId) {
      return $this->json(['error' => 'Email already in use'], 422);
    }

    $userModel->update($userId, [
      'name'       => $name,
      'email'      => $email,
      'avatar_url' => ($avatar !== '') ? $avatar : null,
    ]);

    $user = $userModel->find($userId);
    Auth::login($user); // refresh session

    return $this->json([
      'message' => 'Profile updated',
      'user'    => Helpers::only($user, ['id','name','email','student_id','role','avatar_url'])
    ]);
  }

  // ===== NEW: change password =====
  public function changePassword() {
    if (empty($_SESSION['user'])) {
      return $this->json(['error' => 'Unauthorized'], 401);
    }
    $userId = (int)$_SESSION['user']['id'];
    $in = $this->input(['current_password','new_password'], true);

    $current = (string)$in['current_password'];
    $new     = (string)$in['new_password'];

    if (strlen($new) < 6) {
      return $this->json(['error' => 'New password must be at least 6 characters'], 422);
    }

    $userModel = new User();
    $user = $userModel->find($userId);
    if (!$user || !password_verify($current, $user['password_hash'])) {
      return $this->json(['error' => 'Current password is incorrect'], 422);
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $userModel->update($userId, ['password_hash' => $hash]);

    return $this->json(['message' => 'Password updated']);
  }
}

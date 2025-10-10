<?php
// Public probe
Route::get('/', [HomeController::class, 'index']);

// ---- Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::get('/auth/me',        [AuthController::class, 'me']);
Route::post('/auth/logout',   [AuthController::class, 'logout']);

Route::post('/auth/update-profile',  [AuthController::class, 'updateProfile']);
Route::post('/auth/change-password', [AuthController::class, 'changePassword']);



// ---- Rooms
Route::get('/rooms',  [RoomController::class, 'list']);    // list active rooms
Route::post('/rooms', [RoomController::class, 'create']);  // admin create

// ---- Reservations (user)
Route::get('/reservations/me',  [ReservationController::class, 'mine']);   // my reservations
Route::post('/reservations',    [ReservationController::class, 'create']); // create

// New: generic list with filters (q/status/range) for UI table
Route::get('/reservations',     [ReservationController::class, 'index']);  

// New: details (GET) and actions (POST) using simple query/body param "id"
Route::get('/reservations/get',      [ReservationController::class, 'show']);
Route::post('/reservations/checkin', [ReservationController::class, 'checkin']);
Route::post('/reservations/cancel',  [ReservationController::class, 'cancel']);

// ---- Reservations (admin)
Route::post('/reservations/status', [ReservationController::class, 'updateStatus']); // admin: set status
Route::get('/reservations/all',     [ReservationController::class, 'all']);          // admin: list all

// ---- Dashboard data
Route::get('/metrics/kpi',           [HomeController::class, 'kpi']);
Route::get('/reservations/upcoming', [ReservationController::class, 'upcoming']);

// ---------- API ALIASES (so /backend/api/... also works) ----------
Route::post('/api/auth/register', [AuthController::class, 'register']);
Route::post('/api/auth/login',    [AuthController::class, 'login']);
Route::get('/api/auth/me',        [AuthController::class, 'me']);
Route::post('/api/auth/logout',   [AuthController::class, 'logout']);
Route::post('/api/auth/update-profile',  [AuthController::class, 'updateProfile']);   
Route::post('/api/auth/change-password', [AuthController::class, 'changePassword']);

Route::get('/api/rooms',  [RoomController::class, 'list']);
Route::post('/api/rooms', [RoomController::class, 'create']);

Route::get('/api/reservations/me',      [ReservationController::class, 'mine']);
Route::post('/api/reservations',        [ReservationController::class, 'create']);
Route::get('/api/reservations',         [ReservationController::class, 'index']);
Route::get('/api/reservations/get',     [ReservationController::class, 'show']);
Route::post('/api/reservations/checkin',[ReservationController::class, 'checkin']);
Route::post('/api/reservations/cancel', [ReservationController::class, 'cancel']);
Route::post('/api/reservations/status', [ReservationController::class, 'updateStatus']);
Route::get('/api/reservations/all',     [ReservationController::class, 'all']);


Route::get('/api/metrics/kpi',           [HomeController::class, 'kpi']);
Route::get('/api/reservations/upcoming', [ReservationController::class, 'upcoming']);
// ---- Admin (added)
Route::get('/admin/dashboard',             [AdminController::class, 'dashboard']);
Route::get('/admin/users',                 [AdminController::class, 'users']);
Route::post('/admin/users/set-role',       [AdminController::class, 'setUserRole']);

Route::get('/admin/rooms',                 [AdminController::class, 'rooms']);
Route::post('/admin/rooms/update',         [AdminController::class, 'updateRoom']);
Route::post('/admin/rooms/toggle',         [AdminController::class, 'toggleRoom']);

// ---- API aliases for admin (added)
Route::get('/api/admin/dashboard',         [AdminController::class, 'dashboard']);
Route::get('/api/admin/users',             [AdminController::class, 'users']);
Route::post('/api/admin/users/set-role',   [AdminController::class, 'setUserRole']);

Route::get('/api/admin/rooms',             [AdminController::class, 'rooms']);
Route::post('/api/admin/rooms/update',     [AdminController::class, 'updateRoom']);
Route::post('/api/admin/rooms/toggle',     [AdminController::class, 'toggleRoom']);

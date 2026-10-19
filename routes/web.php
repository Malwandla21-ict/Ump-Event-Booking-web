<?php

use App\Controllers\AccountController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EventAdminController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Core\Router;

$router = new Router();

// Public
$router->get('/', [HomeController::class, 'index']);
$router->get('/events', [EventController::class, 'index']);
$router->get('/events/{slug}', [EventController::class, 'show']);

// Guests only
$router->group('', ['guest'], function (Router $r) {
    $r->get('/login', [AuthController::class, 'showLogin']);
    $r->post('/login', [AuthController::class, 'login']);
    $r->get('/register', [AuthController::class, 'showRegister']);
    $r->post('/register', [AuthController::class, 'register']);
});

// Signed-in users
$router->group('', ['auth'], function (Router $r) {
    $r->post('/logout', [AuthController::class, 'logout']);
    $r->post('/events/{slug}/book', [BookingController::class, 'store']);

    $r->get('/account/bookings', [BookingController::class, 'index']);
    $r->get('/account/bookings/{reference}', [BookingController::class, 'show']);
    $r->post('/account/bookings/{reference}/cancel', [BookingController::class, 'cancel']);

    $r->get('/account/profile', [AccountController::class, 'edit']);
    $r->post('/account/profile', [AccountController::class, 'updateProfile']);
    $r->post('/account/password', [AccountController::class, 'updatePassword']);
});

// Administrators
$router->group('/admin', ['auth', 'admin'], function (Router $r) {
    $r->get('', [DashboardController::class, 'index']);
    $r->get('/events', [EventAdminController::class, 'index']);
    $r->get('/events/create', [EventAdminController::class, 'create']);
    $r->post('/events', [EventAdminController::class, 'store']);
    $r->get('/events/{id:\d+}/edit', [EventAdminController::class, 'edit']);
    $r->post('/events/{id:\d+}', [EventAdminController::class, 'update']);
    $r->post('/events/{id:\d+}/delete', [EventAdminController::class, 'destroy']);
    $r->get('/events/{id:\d+}/attendees', [EventAdminController::class, 'attendees']);
    $r->get('/events/{id:\d+}/attendees.csv', [EventAdminController::class, 'exportAttendees']);
});

return $router;

<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

// Pages publiques
Router::get('/', [AuthController::class, 'showLogin']);
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);

Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'register']);

Router::get('/logout', [AuthController::class, 'logout']);

// Zone privée minimale
Router::get('/dashboard', [HomeController::class, 'dashboard']);
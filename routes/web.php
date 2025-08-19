<?php

use App\Routes\Route;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AuctionController;
use App\Controllers\StampController;
use App\Controllers\BidController;
use App\Controllers\DashboardController;

// Pages publiques (authentification)
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// Pages publiques
Route::get('/', [PublicController::class, 'home']);
Route::get('/home', [PublicController::class, 'home']); // Alias for navigation consistency

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index']); // User dashboard
Route::post('/dashboard/add-stamp', [DashboardController::class, 'addStamp']);

Route::get('/dashboard/password', [DashboardController::class, 'passwordForm']);
Route::post('/dashboard/password', [DashboardController::class, 'updatePassword']);

Route::get('/dashboard/email', [DashboardController::class, 'emailForm']);
Route::post('/dashboard/email', [DashboardController::class, 'updateEmail']);

Route::get('/dashboard/delete', [DashboardController::class, 'deleteForm']);
Route::post('/dashboard/delete', [DashboardController::class, 'deleteAccount']);

// Enchères publiques et gestion des enchères
Route::get('/auctions', [AuctionController::class, 'publicIndex']); // Public auctions list
Route::get('/auctions/show', [AuctionController::class, 'show']);
Route::get('/auctions/create', [AuctionController::class, 'create']);
Route::post('/auctions/store', [AuctionController::class, 'store']);

// Timbres publiques (consultation)
Route::get('/stamps/show', [StampController::class, 'publicShow']);

// Gestion des timbres (pour administrateurs/utilisateurs connectés)
Route::get('/stamps', [StampController::class, 'index']);

Route::get('/stamps/create', [StampController::class, 'create']);
Route::post('/stamps/store', [StampController::class, 'store']);

// Gestion des offres
Route::post('/bid/store', [BidController::class, 'store']);
Route::post('/bid/delete', [BidController::class, 'delete']);

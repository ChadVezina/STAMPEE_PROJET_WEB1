<?php

use App\Routes\Route;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AuctionController;
use App\Controllers\StampController;
use App\Controllers\BidController;

// Pages publiques (authentification)
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// Pages publiques
Route::get('/', [PublicController::class, 'home']);
Route::get('/home', [PublicController::class, 'home']); // Alias for navigation consistency
Route::get('/dashboard', [PublicController::class, 'dashboard']); // User dashboard

// Enchères publiques et gestion des enchères
Route::get('/auctions', [AuctionController::class, 'publicIndex']); // Public auctions list
Route::get('/auction/show', [AuctionController::class, 'show']);
Route::get('/auction/create', [AuctionController::class, 'create']);
Route::post('/auction/store', [AuctionController::class, 'store']);
Route::get('/auction/edit', [AuctionController::class, 'edit']);
Route::post('/auction/update', [AuctionController::class, 'update']);
Route::post('/auction/delete', [AuctionController::class, 'delete']);

// Timbres publiques (consultation)
Route::get('/stamps/show', [StampController::class, 'publicShow']);

// Gestion des timbres (pour administrateurs/utilisateurs connectés)
Route::get('/stamps', [StampController::class, 'index']); // Changed from /stamp to /stamps for consistency
Route::get('/stamp/show', [StampController::class, 'show']);
Route::get('/stamp/create', [StampController::class, 'create']);
Route::post('/stamp/store', [StampController::class, 'store']);
Route::get('/stamp/edit', [StampController::class, 'edit']);
Route::post('/stamp/update', [StampController::class, 'update']);
Route::post('/stamp/delete', [StampController::class, 'delete']);

// Gestion des offres
Route::post('/bid/store', [BidController::class, 'store']);
Route::post('/bid/delete', [BidController::class, 'delete']);

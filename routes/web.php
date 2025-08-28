<?php

use App\Routes\Route;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AuctionController;
use App\Controllers\StampController;
use App\Controllers\BidController;
use App\Controllers\DashboardController;
use App\Controllers\FavoriteController;

// Pages publiques (authentification)
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// Pages publiques
Route::get('/', [PublicController::class, 'home']);
Route::get('/home', [PublicController::class, 'home']); // Alias for navigation consistency

// Lord Interface - Gestion des Coups de Cœur
Route::get('/lord/login', [FavoriteController::class, 'showLogin']);
Route::post('/lord/login', [FavoriteController::class, 'login']);
Route::get('/lord/favorites/manage', [FavoriteController::class, 'manage']);
Route::post('/lord/favorites/toggle', [FavoriteController::class, 'toggleFavorite']);
Route::get('/lord/logout', [FavoriteController::class, 'logout']);
Route::get('/api/lord/favorites', [FavoriteController::class, 'getFavoritesApi']);

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index']); // User dashboard

Route::get('/dashboard/password', [DashboardController::class, 'passwordForm']);
Route::post('/dashboard/password', [DashboardController::class, 'updatePassword']);

Route::get('/dashboard/email', [DashboardController::class, 'emailForm']);
Route::post('/dashboard/email', [DashboardController::class, 'updateEmail']);

Route::get('/dashboard/delete', [DashboardController::class, 'deleteForm']);
Route::post('/dashboard/delete', [DashboardController::class, 'deleteAccount']);

// Enchères publiques et gestion des enchères
Route::get('/auctions', [AuctionController::class, 'publicIndex']); // Public auctions list
Route::get('/auctions/api/all', [AuctionController::class, 'getAllAuctionsJson']); // Get all auctions as JSON
Route::get('/auctions/show', [AuctionController::class, 'show']);
Route::get('/auctions/create', [AuctionController::class, 'create']);
Route::post('/auctions/store', [AuctionController::class, 'store']);

// Gestion des timbres (pour administrateurs/utilisateurs connectés)
Route::get('/stamps', [StampController::class, 'index']);
Route::get('/stamps/show', [StampController::class, 'show']); // Authenticated stamp view
Route::get('/stamps/create', [StampController::class, 'create']);
Route::post('/stamps/store', [StampController::class, 'store']);
Route::get('/stamps/edit', [StampController::class, 'edit']);
Route::post('/stamps/update', [StampController::class, 'update']);
Route::post('/stamps/delete', [StampController::class, 'delete']);
Route::post('/stamps/image/set-main', [StampController::class, 'setMainImage']);
Route::post('/stamps/image/delete', [StampController::class, 'deleteImage']);

// Timbres publiques (consultation)
Route::get('/stamps/public', [StampController::class, 'publicShow']); // Public stamp view

// Gestion des offres
Route::post('/bid/store', [BidController::class, 'store']);
Route::post('/bid/delete', [BidController::class, 'delete']);
Route::get('/bid/history', [BidController::class, 'history']);

// API pour les enchères (AJAX)
Route::get('/bid/can-bid', [BidController::class, 'canBid']);
Route::get('/bid/auction-stats', [BidController::class, 'auctionStats']);
Route::post('/bid/validate', [BidController::class, 'validateBidAmount']);
Route::post('/bid/ajax-store', [BidController::class, 'ajaxStore']);

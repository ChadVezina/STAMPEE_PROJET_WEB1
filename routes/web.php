<?php

use App\Routes\Route;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\AuctionController;
use App\Controllers\StampController;
use App\Controllers\BidController;

// Pages publiques (authentification)
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

//Pages publiques (Home)
Route::get('/home', [HomeController::class, 'homePage']);

// Zone privée minimale (tableau de bord)
Route::get('/dashboard', [HomeController::class, 'dashboard']);

// Gestion des enchères
Route::get('/auctions', [AuctionController::class, 'index']);
Route::get('/auction/show', [AuctionController::class, 'show']);
Route::get('/auction/create', [AuctionController::class, 'create']);
Route::post('/auction/store', [AuctionController::class, 'store']);
Route::get('/auction/edit', [AuctionController::class, 'edit']);
Route::post('/auction/update', [AuctionController::class, 'update']);
Route::post('/auction/delete', [AuctionController::class, 'delete']);

// Gestion des timbres (catégories)
Route::get('/stamps', [StampController::class, 'index']);
Route::get('/stamp/show', [StampController::class, 'show']);
Route::get('/stamp/create', [StampController::class, 'create']);
Route::post('/stamp/store', [StampController::class, 'store']);
Route::get('/stamp/edit', [StampController::class, 'edit']);
Route::post('/stamp/update', [StampController::class, 'update']);
Route::post('/stamp/delete', [StampController::class, 'delete']);

// Gestion des offres
Route::post('/bid/store', [BidController::class, 'store']);
Route::post('/bid/delete', [BidController::class, 'delete']);

Route::dispatch();

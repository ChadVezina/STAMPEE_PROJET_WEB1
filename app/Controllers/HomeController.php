<?php
namespace App\Controllers;

use App\Core\View;

final class HomeController {
    public function dashboard(): void {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        View::render('pages/accueil/dashboard', ['user' => $_SESSION['user']]);
    }
}
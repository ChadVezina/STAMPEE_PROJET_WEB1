<?php

namespace App\Controllers;

use App\Core\View;

final class HomeController
{
    public function homePage(): void
    {
        // Accessible to everyone (guest or authenticated)
        View::render('pages/accueil/home-page', []);
    }
    public function dashboard(): void
    {
        if (empty($_SESSION['user'])) {
            View::redirect('/login');
        }
        View::render('pages/accueil/dashboard', ['user' => $_SESSION['user']]);
    }
}

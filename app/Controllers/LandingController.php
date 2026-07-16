<?php

namespace App\Controllers;

class LandingController
{
    /**
     * Page d'accueil publique (landing marketing ProsDevis).
     * Pour l'instant on redirige simplement vers la page de login,
     * ce qui évite l'erreur "Controller not found" et reste cohérent
     * avec la structure actuelle (Views auth/dashboard/blog...).
     */
    public function index(): void
    {
        header('Location: /login');
        exit;
    }

    /**
     * Page /pricing (mentionnée dans le router public/index.php).
     * Tu pourras plus tard brancher une vraie vue marketing
     * depuis app/Views/layouts ou un dossier dédié.
     */
    public function pricing(): void
    {
        // TODO: inclure une vue pricing dédiée quand elle sera prête
        header('Location: /login');
        exit;
    }
}

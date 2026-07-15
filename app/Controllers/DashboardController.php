<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Dashboard;

class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $companyId = Auth::companyId();
        $user      = Auth::user();

        $kpis       = Dashboard::kpis($companyId);
        $caMensuel  = Dashboard::caMensuel($companyId, 6);
        $statutsDevis  = Dashboard::statutsDevis($companyId);
        $activite   = Dashboard::activiteRecente($companyId, 12);
        $topClients = Dashboard::topClients($companyId, 5);

        $title = $pageTitle = 'Dashboard';
        $activeNav = 'dashboard';

        ob_start();
        require __DIR__ . '/../Views/dashboard/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Services\HomeDashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeDashboardService $homeDashboardService): View
    {
        $user = Auth::guard('web')->user();

        return view('home', $homeDashboardService->getDashboardData(
            $user instanceof NguoiDung ? $user : null,
        ));
    }
}

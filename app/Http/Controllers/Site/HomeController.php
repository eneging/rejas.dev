<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('site.home', [
            'services' => config('rejas.services'),
            'sectors' => config('rejas.sectors'),
            'plans' => config('rejas.plans'),
            'portfolio' => PortfolioProject::where('is_published', true)->orderBy('position')->get(),
        ]);
    }

    public function caseStudy(PortfolioProject $portfolioProject): View
    {
        abort_unless($portfolioProject->is_published, 404);

        return view('site.case-study', ['project' => $portfolioProject]);
    }
}

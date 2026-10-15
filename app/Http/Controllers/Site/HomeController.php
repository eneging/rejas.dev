<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Support\Pricing;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('site.home', [
            'services' => config('rejas.services'),
            'sectors' => config('rejas.sectors'),
            'workshopPlans' => Pricing::workshopPlans(),
            'portfolio' => PortfolioProject::where('is_published', true)->orderBy('position')->get(),
        ]);
    }

    public function workshop(): View
    {
        return view('site.workshop', [
            'plans' => Pricing::workshopPlans(),
            'workshop' => config('rejas.workshop'),
        ]);
    }

    public function caseStudy(PortfolioProject $portfolioProject): View
    {
        abort_unless($portfolioProject->is_published, 404);

        return view('site.case-study', ['project' => $portfolioProject]);
    }
}

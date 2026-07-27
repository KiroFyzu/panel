<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Auth;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\ServerRepositoryInterface;
use Pterodactyl\Models\PricePackage;

class IndexController extends Controller
{
    /**
     * IndexController constructor.
     */
    public function __construct(
        protected ServerRepositoryInterface $repository,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Returns listing of user's servers.
     */
    public function index(Request $request): View
    {
        if (Auth::check()) {
            return view('templates/base.core');
        }

        $packages = PricePackage::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        return view('billing.landing', [
            'packages' => $packages,
            'appName' => config('app.name', 'Pterodactyl'),
        ]);
    }
}

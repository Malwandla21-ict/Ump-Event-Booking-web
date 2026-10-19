<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\EventRepository;

final class HomeController extends Controller
{
    public function index(Request $request): string
    {
        $events = new EventRepository();

        return $this->view('home/index', [
            'pageTitle' => 'Discover campus events',
            'featured' => $events->upcoming(3),
            'stats' => $events->stats(),
            'categories' => $events->activeCategories(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Repositories\BookingRepository;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;

final class DashboardController extends Controller
{
    public function index(Request $request): string
    {
        $events = new EventRepository();
        $bookings = new BookingRepository();

        return $this->view('admin/dashboard', [
            'pageTitle' => 'Admin dashboard',
            'stats' => $events->stats(),
            'students' => (new UserRepository())->count(),
            'ticketsSold' => $bookings->countConfirmed(),
            'upcoming' => $events->upcoming(5),
            'recent' => $bookings->recent(8),
        ], 'layouts/admin');
    }
}

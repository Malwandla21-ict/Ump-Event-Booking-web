<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\BookingException;
use App\Exceptions\HttpException;
use App\Repositories\BookingRepository;
use App\Repositories\EventRepository;
use App\Services\BookingService;

final class BookingController extends Controller
{
    private const TABS = ['upcoming', 'past', 'cancelled'];

    public function index(Request $request): string
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'upcoming';
        $bookings = new BookingRepository();

        return $this->view('bookings/index', [
            'pageTitle' => 'My bookings',
            'tab' => $tab,
            'bookings' => $bookings->forUser(Auth::id(), $tab),
            'counts' => $bookings->countsForUser(Auth::id()),
        ]);
    }

    public function show(Request $request, string $reference): string
    {
        $booking = (new BookingRepository())->findForUser(Auth::id(), strtoupper($reference))
            ?? throw HttpException::notFound('Booking not found.');

        return $this->view('bookings/show', [
            'pageTitle' => 'Ticket ' . $booking['reference'],
            'booking' => $booking,
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request, string $slug): Response
    {
        $event = (new EventRepository())->findPublishedBySlug($slug) ?? throw HttpException::notFound();

        try {
            $reference = (new BookingService())->book(Auth::id(), (int) $event['id'], (int) $request->input('quantity', 1));
        } catch (BookingException $e) {
            return $this->redirect("/events/$slug", $e->getMessage(), 'error');
        }

        return $this->redirect("/account/bookings/$reference", "You're going to {$event['title']}! Your ticket is below.");
    }

    public function cancel(Request $request, string $reference): Response
    {
        try {
            $booking = (new BookingService())->cancel(Auth::id(), strtoupper($reference));
        } catch (BookingException $e) {
            return $this->redirect('/account/bookings', $e->getMessage(), 'error');
        }

        return $this->redirect('/account/bookings', "Your booking for {$booking['title']} was cancelled.", 'info');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Exceptions\HttpException;
use App\Repositories\BookingRepository;
use App\Repositories\EventRepository;

final class EventController extends Controller
{
    public function index(Request $request): string
    {
        $search = mb_substr((string) $request->query('q', ''), 0, 100);
        $category = $request->query('category');
        $category = array_key_exists((string) $category, Config::get('app.categories')) ? $category : null;
        $past = $request->query('when') === 'past';
        $perPage = (int) Config::get('app.events_per_page');
        $page = max(1, (int) $request->query('page', '1'));

        $events = new EventRepository();
        $result = $events->paginatePublished($search, $category, $past, $page, $perPage);
        $pages = max(1, (int) ceil($result['total'] / $perPage));

        if ($page > $pages && $result['total'] > 0) {
            throw HttpException::notFound();
        }

        return $this->view('events/index', [
            'pageTitle' => $past ? 'Past events' : 'Upcoming events',
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => $pages,
            'filters' => ['q' => $search, 'category' => $category, 'when' => $past ? 'past' : null],
            'categories' => Config::get('app.categories'),
        ]);
    }

    public function show(Request $request, string $slug): string
    {
        $event = (new EventRepository())->findPublishedBySlug($slug)
            ?? throw HttpException::notFound('We couldn\'t find that event. It may have been removed.');

        $booking = Auth::check()
            ? (new BookingRepository())->findByUserAndEvent(Auth::id(), (int) $event['id'])
            : null;

        return $this->view('events/show', [
            'pageTitle' => $event['title'],
            'event' => $event,
            'booking' => $booking && $booking['status'] === 'confirmed' ? $booking : null,
            'maxTickets' => min((int) Config::get('app.booking.max_tickets_per_booking'), (int) $event['seats_left']),
        ]);
    }
}

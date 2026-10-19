<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Repositories\BookingRepository;
use App\Repositories\EventRepository;
use App\Services\EventService;

final class EventAdminController extends Controller
{
    private const FIELDS = ['title', 'description', 'category', 'venue', 'starts_at', 'ends_at', 'capacity', 'status', 'remove_image'];

    public function index(Request $request): string
    {
        $search = mb_substr((string) $request->query('q', ''), 0, 100);

        return $this->view('admin/events/index', [
            'pageTitle' => 'Manage events',
            'events' => (new EventRepository())->allForAdmin($search),
            'search' => $search,
        ], 'layouts/admin');
    }

    public function create(Request $request): string
    {
        return $this->form(null);
    }

    public function store(Request $request): Response
    {
        $id = (new EventService())->create($request->only(self::FIELDS), $request->file('image'), Auth::id());

        return $this->redirect("/admin/events/$id/edit", 'Event created.');
    }

    public function edit(Request $request, string $id): string
    {
        return $this->form($this->findOrFail($id));
    }

    public function update(Request $request, string $id): Response
    {
        $event = $this->findOrFail($id);
        (new EventService())->update($event, $request->only(self::FIELDS), $request->file('image'));

        return $this->redirect("/admin/events/$id/edit", 'Changes saved.');
    }

    public function destroy(Request $request, string $id): Response
    {
        $event = $this->findOrFail($id);
        (new EventService())->delete($event);

        return $this->redirect('/admin/events', "“{$event['title']}” was deleted.");
    }

    public function attendees(Request $request, string $id): string
    {
        $event = $this->findOrFail($id);

        return $this->view('admin/events/attendees', [
            'pageTitle' => 'Attendees · ' . $event['title'],
            'event' => $event,
            'attendees' => (new BookingRepository())->attendees((int) $event['id']),
        ], 'layouts/admin');
    }

    public function exportAttendees(Request $request, string $id): Response
    {
        $event = $this->findOrFail($id);
        $rows = array_map(static fn (array $a) => [
            $a['reference'], $a['full_name'], $a['email'], $a['quantity'], $a['status'], $a['created_at'],
        ], (new BookingRepository())->attendees((int) $event['id']));

        return Response::csv(
            "attendees-{$event['slug']}.csv",
            ['Reference', 'Name', 'Email', 'Tickets', 'Status', 'Booked at'],
            $rows,
        );
    }

    private function form(?array $event): string
    {
        return $this->view('admin/events/form', [
            'pageTitle' => $event ? 'Edit event' : 'New event',
            'event' => $event,
            'categories' => Config::get('app.categories'),
        ], 'layouts/admin');
    }

    private function findOrFail(string $id): array
    {
        return (new EventRepository())->find((int) $id) ?? throw HttpException::notFound('Event not found.');
    }
}

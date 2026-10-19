<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Validator;
use App\Exceptions\ValidationException;
use App\Repositories\EventRepository;

/** Create / update / delete events (admin). */
final class EventService
{
    public function __construct(
        private readonly EventRepository $events = new EventRepository(),
        private readonly ImageUploader $uploader = new ImageUploader(),
    ) {
    }

    /** @throws ValidationException */
    public function create(array $input, ?array $image, int $adminId): int
    {
        $data = $this->validate($input, isNew: true);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by'] = $adminId;
        $data['image_path'] = $image ? $this->uploadOrFail($image, $input) : null;

        return $this->events->create($data);
    }

    /** @throws ValidationException */
    public function update(array $event, array $input, ?array $image): void
    {
        $data = $this->validate($input, isNew: false);

        $taken = (int) $event['seats_taken'];
        if ($data['capacity'] < $taken) {
            throw new ValidationException(
                ['capacity' => "Capacity can't be lower than the $taken seats already booked."],
                $input,
            );
        }

        if ($data['title'] !== $event['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], (int) $event['id']);
        }

        if ($image) {
            $data['image_path'] = $this->uploadOrFail($image, $input);
            $this->uploader->delete($event['image_path']);
        } elseif (($input['remove_image'] ?? '') === '1') {
            $data['image_path'] = null;
            $this->uploader->delete($event['image_path']);
        }

        $this->events->update((int) $event['id'], $data);
    }

    public function delete(array $event): void
    {
        // Bookings are removed by the ON DELETE CASCADE foreign key.
        $this->events->delete((int) $event['id']);
        $this->uploader->delete($event['image_path']);
    }

    private function validate(array $input, bool $isNew): array
    {
        $categories = implode(',', array_keys(Config::get('app.categories')));

        $data = Validator::validate($input, [
            'title' => 'required|min:3|max:150',
            'description' => 'required|min:10|max:5000',
            'category' => "required|in:$categories",
            'venue' => 'required|max:120',
            'starts_at' => 'required|datetime' . ($isNew ? '|future' : ''),
            'ends_at' => 'nullable|datetime|after:starts_at',
            'capacity' => 'required|integer|between:1,100000',
            'status' => 'required|in:published,draft,cancelled',
        ], ['starts_at' => 'start time', 'ends_at' => 'end time']);

        $data['starts_at'] = Validator::parseDate($data['starts_at'])->format('Y-m-d H:i:s');
        $data['ends_at'] = $data['ends_at'] ? Validator::parseDate($data['ends_at'])->format('Y-m-d H:i:s') : null;
        $data['capacity'] = (int) $data['capacity'];

        return $data;
    }

    private function uploadOrFail(array $image, array $input): string
    {
        try {
            return $this->uploader->store($image);
        } catch (ValidationException $e) {
            throw new ValidationException($e->errors, $input);
        }
    }

    private function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title) ?: $title), '-'));
        $base = substr($base ?: 'event', 0, 150);

        $slug = $base;
        for ($i = 2; $this->events->slugExists($slug, $exceptId); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }
}

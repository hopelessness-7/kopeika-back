<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Calendar\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CalendarController extends BaseApiController
{
    public function __construct(
        private readonly CalendarService $calendar,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        return $this->success(
            $this->calendar->forRange($validated['from'], $validated['to'], $this->resolveUserId()),
        );
    }
}

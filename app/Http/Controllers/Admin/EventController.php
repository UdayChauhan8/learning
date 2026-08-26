<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use Inertia\Response;

class EventController extends Controller
{
    
    public function index(): Response
    {
        $events = Event::latest()->paginate(10);

        return Inertia::render('admin/events/index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/events/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:2048'],        // max 2MB image
            'document'    => ['nullable', 'mimes:pdf', 'max:10240'],   // max 10MB PDF
            'custom_url'  => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events/images', 'public');
        }

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('events/docs', 'public');
        }

        Event::create($validated);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event created.');
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('admin/events/edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'document'    => ['nullable', 'mimes:pdf', 'max:10240'],
            'custom_url'  => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events/images', 'public');
        }

        if ($request->hasFile('document')) {
            if ($event->document) {
                Storage::disk('public')->delete($event->document);
            }
            $validated['document'] = $request->file('document')->store('events/docs', 'public');
        }

        $event->update($validated);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event updated.');
    }


    public function destroy(Event $event): RedirectResponse
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        if ($event->document) {
            Storage::disk('public')->delete($event->document);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }
}
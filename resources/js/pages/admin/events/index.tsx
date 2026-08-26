// resources/js/pages/admin/events/index.tsx
import { Head, Link, router } from '@inertiajs/react';

interface Event {
    id: number;
    title: string;
    image: string | null;
    custom_url: string | null;
    created_at: string;
}

interface Paginator {
    data: Event[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    events: Paginator;
}

export default function AdminEvents({ events }: Props) {
    function deleteEvent(id: number) {
        if (!window.confirm('Delete this event? This cannot be undone.')) return;

        // Inertia DELETE request — controller handles file cleanup
        router.delete(`/admin/events/${id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Events" />

            <div className="mx-auto max-w-6xl px-4 py-8">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Events</h1>
                        <p className="mt-1 text-sm text-slate-500">
                            {events.total} total events
                        </p>
                    </div>
                    <Link
                        href="/admin/events/create"
                        className="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        + New Event
                    </Link>
                </div>

                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3 text-left">Image</th>
                                <th className="px-5 py-3 text-left">Title</th>
                                <th className="px-5 py-3 text-left">URL</th>
                                <th className="px-5 py-3 text-left">Created</th>
                                <th className="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {events.data.map(event => (
                                <tr key={event.id} className="hover:bg-slate-50">
                                    <td className="px-5 py-3.5">
                                        {event.image ? (
                                            <img
                                                src={`/storage/${event.image}`}
                                                alt={event.title}
                                                className="h-10 w-16 rounded object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-16 items-center justify-center rounded bg-slate-100 text-xs text-slate-400">
                                                No image
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-5 py-3.5 font-medium">{event.title}</td>
                                    <td className="px-5 py-3.5">
                                        {event.custom_url ? (
                                            <a
                                                href={event.custom_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-blue-600 underline"
                                            >
                                                Link
                                            </a>
                                        ) : (
                                            <span className="text-slate-400">—</span>
                                        )}
                                    </td>
                                    <td className="px-5 py-3.5 text-slate-500">
                                        {new Date(event.created_at).toLocaleDateString()}
                                    </td>
                                    <td className="px-5 py-3.5">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={`/admin/events/${event.id}/edit`}
                                                className="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-200"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                onClick={() => deleteEvent(event.id)}
                                                className="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
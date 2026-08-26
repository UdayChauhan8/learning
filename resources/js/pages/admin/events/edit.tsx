// resources/js/pages/admin/events/edit.tsx
import { Head, useForm } from '@inertiajs/react';
import RichTextEditor from '@/components/rich-text-editor';
import { FormEvent } from 'react';

interface Event {
    id: number;
    title: string;
    image: string | null;
    document: string | null;
    custom_url: string | null;
    description: string | null;
}

interface Props {
    event: Event;  // passed from controller: Inertia::render('admin/events/edit', ['event' => $event])
}

export default function EditEvent({ event }: Props) {
    const form = useForm({
        // Pre-populate with existing values
        title: event.title,
        image: null as File | null,          // null = keep existing image
        document: null as File | null,       // null = keep existing document
        custom_url: event.custom_url ?? '',
        description: event.description ?? '',

        // Laravel needs _method: PUT when using FormData
        // (browsers only support GET/POST in forms natively)
        _method: 'PUT' as const,
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();

        // POST with _method: PUT spoofing — required for file uploads with PUT
        form.post(`/admin/events/${event.id}`, {
            forceFormData: true,
        });
    }

    return (
        <>
            <Head title={`Edit — ${event.title}`} />

            <div className="mx-auto max-w-3xl px-4 py-8">
                <h1 className="mb-6 text-2xl font-semibold">Edit Event</h1>

                <form onSubmit={handleSubmit} className="space-y-6">

                    {/* Title */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Title <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={form.data.title}
                            onChange={e => form.setData('title', e.target.value)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        />
                        {form.errors.title && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.title}</p>
                        )}
                    </div>

                    {/* Image — show current image preview if exists */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Banner Image
                        </label>
                        {event.image && (
                            <div className="mb-2">
                                <p className="mb-1 text-xs text-slate-500">Current image:</p>
                                {/* storage_url = /storage/ + path stored in DB */}
                                <img
                                    src={`/storage/${event.image}`}
                                    alt="Current banner"
                                    className="h-32 rounded-lg object-cover"
                                />
                            </div>
                        )}
                        <input
                            type="file"
                            accept="image/*"
                            onChange={e => form.setData('image', e.target.files?.[0] ?? null)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm"
                        />
                        <p className="mt-1 text-xs text-slate-400">Leave empty to keep current image</p>
                        {form.errors.image && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.image}</p>
                        )}
                    </div>

                    {/* Document — show current file link if exists */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Document / Brochure
                        </label>
                        {event.document && (
                            <div className="mb-2">
                                <a
                                    href={`/storage/${event.document}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-xs text-blue-600 underline"
                                >
                                    View current document
                                </a>
                            </div>
                        )}
                        <input
                            type="file"
                            accept=".pdf"
                            onChange={e => form.setData('document', e.target.files?.[0] ?? null)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm"
                        />
                        <p className="mt-1 text-xs text-slate-400">Leave empty to keep current document</p>
                        {form.errors.document && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.document}</p>
                        )}
                    </div>

                    {/* Custom URL */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            External URL
                        </label>
                        <input
                            type="url"
                            value={form.data.custom_url}
                            onChange={e => form.setData('custom_url', e.target.value)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                            placeholder="https://..."
                        />
                        {form.errors.custom_url && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.custom_url}</p>
                        )}
                    </div>

                    {/* Description */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Description
                        </label>
                        <RichTextEditor
                            value={form.data.description}
                            onChange={html => form.setData('description', html)}
                        />
                        {form.errors.description && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.description}</p>
                        )}
                    </div>

                    {/* Submit */}
                    <div className="flex gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Saving…' : 'Save Changes'}
                        </button>
                        <a
                            href="/admin/events"
                            className="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </>
    );
}
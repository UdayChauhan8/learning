import { Head, useForm } from '@inertiajs/react';
import RichTextEditor from '@/components/rich-text-editor';
import { FormEvent } from 'react';

export default function CreateEvent() {
    // useForm manages field values, errors, and loading state
    // For file uploads, we must use FormData — pass forceFormData: true
    const form = useForm({
        title: '',
        image: null as File | null,
        document: null as File | null,
        custom_url: '',
        description: '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();

        form.post('/admin/events', {
            // Required for file uploads — tells Inertia to use multipart/form-data
            forceFormData: true,
        });
    }

    return (
        <>
            <Head title="Create Event" />

            <div className="mx-auto max-w-3xl px-4 py-8">
                <h1 className="mb-6 text-2xl font-semibold">Create Event</h1>

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
                            placeholder="Event title"
                        />
                        {/* Inertia populates form.errors from Laravel validation */}
                        {form.errors.title && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.title}</p>
                        )}
                    </div>

                    {/* Image upload */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Banner Image
                            <span className="ml-1 text-xs font-normal text-slate-400">(JPEG, PNG — max 2MB)</span>
                        </label>
                        <input
                            type="file"
                            accept="image/*"
                            // File inputs are uncontrolled — read the File object from event
                            onChange={e => form.setData('image', e.target.files?.[0] ?? null)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm"
                        />
                        {form.errors.image && (
                            <p className="mt-1 text-xs text-red-500">{form.errors.image}</p>
                        )}
                    </div>

                    {/* Document upload */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Document / Brochure
                            <span className="ml-1 text-xs font-normal text-slate-400">(PDF — max 10MB)</span>
                        </label>
                        <input
                            type="file"
                            accept=".pdf"
                            onChange={e => form.setData('document', e.target.files?.[0] ?? null)}
                            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm"
                        />
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

                    {/* Description — Tiptap rich text editor */}
                    <div>
                        <label className="mb-1.5 block text-sm font-medium text-slate-700">
                            Description
                        </label>
                        {/*
                            RichTextEditor calls onChange with HTML string on every update.
                            We store that HTML string in form.data.description.
                            Laravel saves it as longText in the DB.
                        */}
                        <RichTextEditor
                            value={form.data.description}
                            onChange={html => form.setData('description', html)}
                            placeholder="Describe the event..."
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
                            {form.processing ? 'Creating…' : 'Create Event'}
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
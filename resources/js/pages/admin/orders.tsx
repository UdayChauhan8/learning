import { Head, Link, router, useForm } from '@inertiajs/react';

interface Item {
    id: number;
    name: string;
    order: number;
}

interface Paginator {
    data: Item[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    items: Paginator;
    editing?: Item;
}

export default function AdminOrders({ items, editing }: Props) {
    const createForm = useForm({ name: '', order: '' });
    const editForm = useForm({
        name: editing?.name ?? '',
        order: editing?.order ? String(editing.order) : '',
    });

    function submitCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post('/admin/orders', {
            preserveScroll: true,
            onSuccess: () => createForm.reset(),
        });
    }

    function submitEdit(e: React.FormEvent) {
        e.preventDefault();
        if (!editing) return;
        editForm.put(`/admin/orders/${editing.id}`, { preserveScroll: true });
    }

    function deleteItem(id: number) {
        if (!window.confirm('Delete this order?')) return;
        createForm.delete(`/admin/orders/${id}`, { preserveScroll: true });
    }

    function goToPage(page: number) {
        const base = editing ? `/admin/orders/${editing.id}` : '/admin/orders';
        router.get(base, { page }, { preserveScroll: true });
    }

    function visiblePages(): (number | '...')[] {
        const cur = items.current_page;
        const last = items.last_page;
        if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);
        const result: (number | '...')[] = [1];
        if (cur > 3) result.push('...');
        for (let p = Math.max(2, cur - 1); p <= Math.min(last - 1, cur + 1); p++) result.push(p);
        if (cur < last - 2) result.push('...');
        if (last > 1) result.push(last);
        return result;
    }

    const inputClass =
        'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/15 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-cyan-400';
    const errorClass = 'mt-1.5 text-xs text-red-500';

    return (
        <>
            <Head title="Admin — Orders" />

            <div className="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                <div className="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

                    {/* Top bar */}
                    <div className="mb-8 flex flex-col gap-4 rounded-2xl bg-slate-900 px-6 py-5 text-white md:flex-row md:items-center md:justify-between">
                        <div>
                            <p className="text-xs uppercase tracking-[0.3em] text-cyan-300/70 mb-1">
                                Admin workspace
                            </p>
                            <h1 className="text-xl font-semibold">Orders manager</h1>
                            <p className="mt-1 text-sm text-slate-400">
                                {items.total} total items · page {items.current_page} of {items.last_page}
                            </p>
                        </div>
                        <Link
                            href="/orders"
                            className="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium transition hover:bg-white/20"
                        >
                            View public page →
                        </Link>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-[320px_1fr]">

                        {/* Form panel */}
                        <section className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <h2 className="text-base font-semibold">
                                {editing ? 'Edit order' : 'Add order'}
                            </h2>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {editing
                                    ? 'Update the selected record.'
                                    : 'Insert a new item at the chosen position.'}
                            </p>

                            {editing ? (
                                <form onSubmit={submitEdit} className="mt-5 space-y-4">
                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
                                            Name
                                        </label>
                                        <input
                                            type="text"
                                            value={editForm.data.name}
                                            onChange={(e) => editForm.setData('name', e.target.value)}
                                            className={inputClass}
                                            placeholder="Item name"
                                        />
                                        {editForm.errors.name && <p className={errorClass}>{editForm.errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
                                            Position
                                        </label>
                                        <input
                                            type="number"
                                            min="1"
                                            value={editForm.data.order}
                                            onChange={(e) => editForm.setData('order', e.target.value)}
                                            className={inputClass}
                                            placeholder="e.g. 3"
                                        />
                                        {editForm.errors.order && <p className={errorClass}>{editForm.errors.order}</p>}
                                    </div>

                                    <div className="flex gap-2 pt-1">
                                        <button
                                            type="submit"
                                            disabled={editForm.processing}
                                            className="flex-1 rounded-xl bg-slate-900 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                                        >
                                            {editForm.processing ? 'Saving…' : 'Save changes'}
                                        </button>
                                        <Link
                                            href="/admin/orders"
                                            className="flex items-center justify-center rounded-xl border border-slate-200 px-4 text-sm text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700"
                                        >
                                            Cancel
                                        </Link>
                                    </div>
                                </form>
                            ) : (
                                <form onSubmit={submitCreate} className="mt-5 space-y-4">
                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
                                            Name
                                        </label>
                                        <input
                                            type="text"
                                            value={createForm.data.name}
                                            onChange={(e) => createForm.setData('name', e.target.value)}
                                            className={inputClass}
                                            placeholder="Item name"
                                        />
                                        {createForm.errors.name && <p className={errorClass}>{createForm.errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
                                            Position
                                        </label>
                                        <input
                                            type="number"
                                            min="1"
                                            value={createForm.data.order}
                                            onChange={(e) => createForm.setData('order', e.target.value)}
                                            className={inputClass}
                                            placeholder="e.g. 5"
                                        />
                                        {createForm.errors.order && <p className={errorClass}>{createForm.errors.order}</p>}
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={createForm.processing}
                                        className="w-full rounded-xl bg-cyan-600 py-2.5 text-sm font-medium text-white transition hover:bg-cyan-700 disabled:opacity-50"
                                    >
                                        {createForm.processing ? 'Adding…' : 'Add order'}
                                    </button>
                                </form>
                            )}
                        </section>

                        {/* Table panel */}
                        <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <div className="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                                <h2 className="text-base font-semibold">All orders</h2>
                                <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Showing {items.from}–{items.to} of {items.total}
                                </p>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-700/50 dark:text-slate-400">
                                        <tr>
                                            <th className="px-5 py-3 text-left w-20">Pos</th>
                                            <th className="px-5 py-3 text-left">Name</th>
                                            <th className="px-5 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                                        {items.data.length === 0 ? (
                                            <tr>
                                                <td colSpan={3} className="px-5 py-12 text-center text-slate-400">
                                                    No orders yet. Add one using the form.
                                                </td>
                                            </tr>
                                        ) : (
                                            items.data.map((item) => (
                                                <tr
                                                    key={item.id}
                                                    className={
                                                        editing?.id === item.id
                                                            ? 'bg-cyan-50 dark:bg-cyan-900/20'
                                                            : 'hover:bg-slate-50 dark:hover:bg-slate-700/30'
                                                    }
                                                >
                                                    <td className="px-5 py-3.5 font-mono text-xs font-semibold text-slate-400">
                                                        #{item.order}
                                                    </td>
                                                    <td className="px-5 py-3.5 font-medium text-slate-900 dark:text-white">
                                                        {item.name}
                                                    </td>
                                                    <td className="px-5 py-3.5">
                                                        <div className="flex justify-end gap-2">
                                                            <Link
                                                                href={`/admin/orders/${item.id}`}
                                                                className="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-800 transition hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60"
                                                            >
                                                                Edit
                                                            </Link>
                                                            <button
                                                                type="button"
                                                                onClick={() => deleteItem(item.id)}
                                                                className="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                                                            >
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {items.last_page > 1 && (
                                <div className="flex items-center justify-between border-t border-slate-200 px-5 py-3.5 dark:border-slate-700">
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        Page {items.current_page} of {items.last_page}
                                    </p>

                                    <div className="flex items-center gap-1">
                                        <button
                                            onClick={() => goToPage(items.current_page - 1)}
                                            disabled={!items.prev_page_url}
                                            className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-sm text-slate-500 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700"
                                        >
                                            ‹
                                        </button>

                                        {visiblePages().map((p, i) =>
                                            p === '...' ? (
                                                <span key={`e${i}`} className="flex h-8 w-8 items-center justify-center text-xs text-slate-400">
                                                    …
                                                </span>
                                            ) : (
                                                <button
                                                    key={p}
                                                    onClick={() => goToPage(p as number)}
                                                    className={`flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition ${
                                                        p === items.current_page
                                                            ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                                                            : 'border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700'
                                                    }`}
                                                >
                                                    {p}
                                                </button>
                                            )
                                        )}

                                        <button
                                            onClick={() => goToPage(items.current_page + 1)}
                                            disabled={!items.next_page_url}
                                            className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-sm text-slate-500 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700"
                                        >
                                            ›
                                        </button>
                                    </div>
                                </div>
                            )}
                        </section>
                    </div>
                </div>
            </div>
        </>
    );
}
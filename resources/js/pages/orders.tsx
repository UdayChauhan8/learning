import { Head, Link, router } from '@inertiajs/react';

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
}

export default function Orders({ items }: Props) {
    function goToPage(page: number) {
        router.get('/orders', { page }, { preserveScroll: true });
    }

    const pages = Array.from({ length: items.last_page }, (_, i) => i + 1);
    const showEllipsis = items.last_page > 7;

    function visiblePages(): (number | '...')[] {
        if (!showEllipsis) return pages;
        const cur = items.current_page;
        const last = items.last_page;
        const result: (number | '...')[] = [1];
        if (cur > 3) result.push('...');
        for (let p = Math.max(2, cur - 1); p <= Math.min(last - 1, cur + 1); p++) {
            result.push(p);
        }
        if (cur < last - 2) result.push('...');
        if (last > 1) result.push(last);
        return result;
    }

    return (
        <>
            <Head title="Orders" />

            <div className="min-h-screen bg-slate-950 text-slate-100">
                <div className="mx-auto flex min-h-screen w-full max-w-4xl flex-col px-4 py-10 sm:px-6 lg:px-8">

                    {/* Header */}
                    <div className="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <p className="text-xs uppercase tracking-[0.3em] text-cyan-400/70 mb-2">
                                Public catalog
                            </p>
                            <h1 className="text-2xl font-semibold sm:text-3xl">
                                Orders list
                            </h1>
                            <p className="mt-1.5 text-sm text-slate-400">
                                Showing {items.from}–{items.to} of {items.total} items
                            </p>
                        </div>

                        <Link
                            href="/admin/orders"
                            className="mt-1 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-4 py-2 text-sm font-medium text-cyan-300 transition hover:bg-cyan-400/20 whitespace-nowrap"
                        >
                            Admin panel →
                        </Link>
                    </div>

                    {/* Items list */}
                    <div className="flex-1">
                        {items.data.length === 0 ? (
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-12 text-center text-slate-400">
                                <p className="text-lg">No orders yet.</p>
                                <p className="mt-1 text-sm">Add items from the admin panel to see them here.</p>
                            </div>
                        ) : (
                            <div className="grid gap-2">
                                {items.data.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center gap-4 rounded-xl border border-white/8 bg-white/4 px-5 py-4 transition hover:bg-white/7"
                                    >
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-400/15 text-sm font-semibold text-cyan-300">
                                            {item.order}
                                        </div>
                                        <p className="text-base font-medium text-white">
                                            {item.name}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Pagination */}
                    {items.last_page > 1 && (
                        <div className="mt-8 flex flex-col items-center gap-4">
                            <div className="flex items-center gap-1.5">
                                <button
                                    onClick={() => goToPage(items.current_page - 1)}
                                    disabled={!items.prev_page_url}
                                    className="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-sm text-slate-300 transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-30"
                                >
                                    ‹
                                </button>

                                {visiblePages().map((p, i) =>
                                    p === '...' ? (
                                        <span key={`ellipsis-${i}`} className="flex h-9 w-9 items-center justify-center text-slate-500 text-sm">
                                            …
                                        </span>
                                    ) : (
                                        <button
                                            key={p}
                                            onClick={() => goToPage(p as number)}
                                            className={`flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition ${
                                                p === items.current_page
                                                    ? 'bg-cyan-500 text-white'
                                                    : 'border border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'
                                            }`}
                                        >
                                            {p}
                                        </button>
                                    )
                                )}

                                <button
                                    onClick={() => goToPage(items.current_page + 1)}
                                    disabled={!items.next_page_url}
                                    className="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-sm text-slate-300 transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-30"
                                >
                                    ›
                                </button>
                            </div>

                            <p className="text-xs text-slate-500">
                                Page {items.current_page} of {items.last_page}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
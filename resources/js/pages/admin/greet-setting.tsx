import { Head, useForm } from '@inertiajs/react';

interface Props {
    message: string;
}

export default function GreetSetting({ message }: Props) {
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        message: message,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        put('/admin/greet');
    }

    return (
        <>
            <Head title="Edit Greeting" />
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="bg-white shadow rounded-xl p-8 w-full max-w-lg">
                    <h1 className="text-2xl font-bold text-gray-800 mb-6">Edit Greeting Message</h1>

                    <form onSubmit={submit}>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Greeting Message
                        </label>
                        <textarea
                            className="w-full border border-gray-300 rounded-lg p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={data.message}
                            onChange={e => setData('message', e.target.value)}
                            rows={4}
                        />
                        {errors.message && (
                            <p className="mt-1 text-sm text-red-500">{errors.message}</p>
                        )}

                        <div className="mt-4 flex items-center gap-4">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition disabled:opacity-60"
                            >
                                {processing ? 'Saving...' : 'Save'}
                            </button>

                            {recentlySuccessful && (
                                <span className="text-green-600 text-sm">Saved!</span>
                            )}
                        </div>
                    </form>

                    <div className="mt-6 border-t pt-4">
                        <p className="text-xs text-gray-400">
                            This message will appear on the public{' '}
                            <a href="/greet" className="text-blue-500 underline">/greet</a> page.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}

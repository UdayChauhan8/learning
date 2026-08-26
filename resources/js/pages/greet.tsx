import { Head } from '@inertiajs/react';

interface Props {
    message: string;
}

export default function Greet({ message }: Props) {
    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen items-center justify-center bg-gray-50">
                <div className="text-center">
                    <h1 className="text-5xl font-bold text-gray-800">{message}</h1>
                    <p className="mt-4 text-gray-500 text-sm">
                        This message is managed by the admin.
                    </p>
                </div>
            </div>
        </>
    );
}

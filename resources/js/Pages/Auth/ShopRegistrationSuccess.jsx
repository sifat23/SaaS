import { useEffect, useState } from 'react';
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, router } from "@inertiajs/react";

const ShopRegistrationSuccess = ({ session_id }) => {
    const [status, setStatus] = useState('processing'); // 'processing' | 'success' | 'timeout' | 'error'
    const [message, setMessage] = useState('Creating your shop and user account...');

    useEffect(() => {
        if (!session_id) {
            setStatus('error');
            setMessage('Missing session information.');
            return;
        }

        let intervalId = null;
        let timeoutId = null;

        const checkStatus = () => {
            // Using Inertia's router.get → preserves Inertia navigation + flash messages if needed
            router.get(
                route('setup.payment.check'), // ← change to your actual named route
                { session_id },
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (page) => {
                        const data = page.props; // ← adjust key name as you send from backend

                        if (data?.completed) {
                            clearInterval(intervalId);
                            clearTimeout(timeoutId);
                            setStatus('success');
                            setMessage('Shop and user account created successfully!');
                        } else if (data?.error) {
                            clearInterval(intervalId);
                            setStatus('error');
                            setMessage(data.error);
                        }
                        //else → still processing, do nothing → keep polling
                    },
                    onError: () => {
                        // Optional: you can decide to keep polling or show error
                        setStatus('error');
                        setMessage('Connection issue. Please refresh the page.');
                    },
                }
            );
        };

        // Start polling after a small delay (give webhook a head start)
        intervalId = setInterval(checkStatus, 2500); // every 2.5 seconds

        // Safety timeout – after 90 seconds give up polling
        timeoutId = setTimeout(() => {
            clearInterval(intervalId);
            setStatus('timeout');
            setMessage(
                'Processing is taking longer than expected. Your shop is being created in the background. ' +
                'Check your email or dashboard in a few minutes.'
            );
        }, 90000);

        // Initial check (faster first look)
        setTimeout(checkStatus, 800);

        return () => {
            clearInterval(intervalId);
            clearTimeout(timeoutId);
        };
    }, [session_id]);

    const isLoading = status === 'processing';
    const isSuccess = status === 'success';
    const isTimeout = status === 'timeout';

    return (
        <GuestLayout>
            <Head title="Shop Registration Success" />

            <div className="max-w-2xl mx-auto mt-10 px-4 text-center">
                {isLoading && (
                    <div className="py-12">
                        <div className="animate-spin rounded-full h-14 w-14 border-b-4 border-blue-600 mx-auto"></div>
                        <p className="mt-6 text-xl font-medium">Payment successful!</p>
                        <p className="mt-3 text-gray-600">{message}</p>
                        <p className="mt-2 text-sm text-gray-500">
                            Session: <span className="font-mono">{session_id}</span>
                        </p>
                    </div>
                )}

                {isSuccess && (
                    <div className="py-12 bg-green-50 rounded-xl border border-green-200">
                        <div className="text-6xl mb-4">🎉</div>
                        <h1 className="text-3xl font-bold text-green-700 mb-4">
                            Welcome! Your shop is ready
                        </h1>
                        <p className="text-lg text-gray-700 mb-8">{message}</p>
                        <button
                            onClick={() => router.visit('/dashboard')}
                            className="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-lg font-medium text-lg transition"
                        >
                            Go to Dashboard
                        </button>

                        <button
                            onClick={() => router.visit('/shop-registration')}
                            className="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-lg font-medium text-lg transition"
                        >
                            Registration
                        </button>
                    </div>
                )}

                {(status === 'error' || isTimeout) && (
                    <div className="py-12 bg-amber-50 rounded-xl border border-amber-200">
                        <div className="text-5xl mb-4">⚠️</div>
                        <h2 className="text-2xl font-bold text-amber-800 mb-4">
                            Almost there...
                        </h2>
                        <p className="text-lg text-gray-700 mb-6">{message}</p>
                        <button
                            onClick={() => router.visit('/shop-registration')}
                            className="bg-amber-600 hover:bg-amber-700 text-white px-8 py-4 rounded-lg font-medium transition"
                        >
                            Go to Registration
                        </button>
                        <p className="mt-4 text-sm text-gray-600">
                            We'll notify you by email once everything is ready.
                        </p>
                    </div>
                )}
            </div>
        </GuestLayout>
    );
};

export default ShopRegistrationSuccess;
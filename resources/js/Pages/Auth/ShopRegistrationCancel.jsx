import { useEffect, useState } from 'react';
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, router } from "@inertiajs/react";

const ShopRegistrationCancel = ({ session_id }) => {
    const [status, setStatus] = useState('cancelled'); // 'cancelled' | 'error'
    const [message, setMessage] = useState('');

    useEffect(() => {
        if (!session_id) {
            setStatus('error');
            setMessage('No session information found.');
            return;
        }

        // Optional: You can verify the session status from your backend if needed
        // For most cancel pages, it's usually static since the user just clicked "Cancel" on Stripe Checkout.

        setMessage('You have cancelled the payment process.');
    }, [session_id]);

    const handleRetry = () => {
        router.visit(route('shop-registration')); // or your registration/checkout start route
    };

    const handleGoHome = () => {
        router.visit('/');
    };

    return (
        <GuestLayout>
            <Head title="Payment Cancelled" />

            <div className="max-w-2xl mx-auto mt-10 px-4 text-center">
                <div className="py-12 bg-amber-50 rounded-xl border border-amber-200">
                    <div className="text-6xl mb-6">🙁</div>
                    
                    <h1 className="text-3xl font-bold text-amber-800 mb-4">
                        Payment Cancelled
                    </h1>
                    
                    <p className="text-lg text-gray-700 mb-8 max-w-md mx-auto">
                        {message || "The payment was not completed. No charges were made."}
                    </p>

                    {session_id && (
                        <p className="text-sm text-gray-500 mb-8 font-mono">
                            Session ID: {session_id}
                        </p>
                    )}

                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <button
                            onClick={handleRetry}
                            className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg font-medium text-lg transition"
                        >
                            Try Payment Again
                        </button>

                        <button
                            onClick={handleGoHome}
                            className="bg-gray-600 hover:bg-gray-700 text-white px-8 py-4 rounded-lg font-medium text-lg transition"
                        >
                            Go to Homepage
                        </button>
                    </div>

                    <p className="mt-10 text-sm text-gray-600">
                        If you encountered any issues, feel free to contact support.
                    </p>
                </div>
            </div>
        </GuestLayout>
    );
};

export default ShopRegistrationCancel;
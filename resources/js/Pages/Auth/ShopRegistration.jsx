import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, useForm, Link } from "@inertiajs/react";
import { useState } from "react";


const ShopRegistration = () => {

    const { data, setData, setError, post, processing, clearErrors, errors, reset } = useForm({
        shop_name: '',
    });

    const [isProcession, setIsProcession] = useState(true);

    const submit = (e) => {
        setIsProcession(!isProcession);
        clearErrors();
        e.preventDefault();

        console.log('processing');
        post(route('shop.registration'), {
            onFinished: () => reset('password', 'password_confirmation')
        })
    }

    return (
        <>
            <GuestLayout>
                <Head title="Shop Registration" />
                <form onSubmit={submit}>
                    <div className="mt-2">
                        <InputLabel htmlFor="shop_name" value="Shop Name" />
                        <TextInput
                            id="shop_name"
                            name="shop_name"
                            value={data.shop_name}
                            className="mt-1 block w-full"
                            autoComplete="shop-name"
                            onChange={(e) => setData('shop_name', e.target.value)}
                        />
                        {errors.shop_name && <InputError message={errors.shop_name} className="mt-2" />}
                    </div>


                    <div className="mt-6 flex items-center justify-end">
                        <Link
                            href={route('login')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Already registered?
                        </Link>
                        
                        <PrimaryButton className="ms-4" type="button" onClick={() => setStep(step - 1)}>
                            Back
                        </PrimaryButton>

                        <PrimaryButton className="ms-4" type="submit" disabled={isProcession}>
                            Register
                        </PrimaryButton>

                        {/* <PrimaryButton className="ms-4" disabled={processing}>
                            Register
                        </PrimaryButton> */}
                    </div>
                </form>
            </GuestLayout>
        </>
    )
}

export default ShopRegistration;

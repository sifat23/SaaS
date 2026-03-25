import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, useForm, Link } from "@inertiajs/react";
import { useState } from "react";
import * as z from 'zod';


// Define schema for Step 1
const step1Schema = z.object({
    shop_name: z.string().min(3, "The shop name is required"),
    // shop_email: z.string()
    //     .min(2, "The shop email is required")
    //     .refine((value) => {
    //         // Basic email validation
    //         if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return false;

    //         // Ensure only one @ exists
    //         if ((value.match(/@/g) || []).length !== 1) return false;

    //         const [local, domain] = value.split("@");

    //         // Local part must exist
    //         if (!local) return false;

    //         // Domain must exist
    //         if (!domain) return false;

    //         // Domain must contain a dot and not end with one
    //         if (!domain.includes(".") || domain.endsWith(".")) return false;

    //         return true;
    //     }, {
    //         message: "The shop email must be a valid email address."
    //     }),
});



const ShopRegistration = () => {

    const { data, setData, setError, post, processing, clearErrors, errors, reset } = useForm({
        owner_name: '',
        shop_name: '',
        owner_email: '',
        password: '',
        password_confirmation: '',
    });

    const [step, setStep] = useState(1);

    const submit = (e) => {
        console.log('sss');

        clearErrors();
        e.preventDefault();

        console.log('step: ', step);


        if (step === 1) {
            const result = step1Schema.safeParse(data);
            if (!result.success) {
                console.log('sss', result);

                const zodErrors = z.treeifyError(result.error);

                Object.entries(zodErrors.properties).forEach(([key, value]) => {
                    setError(key, value.errors[0]);
                });
            } else {
                setStep(step + 1);
            }
        } else {
            // post(route('shop.registration'), {
            //     onFinished: () => reset('password', 'password_confirmation')
            // })

            console.log('processing');
            post(route('shop.registration'), {
                onFinished: () => reset('password', 'password_confirmation')
            })
        }

    }

    return (
        <>
            <GuestLayout>
                <Head title="Shop Registration" />
                <form onSubmit={submit}>

                    {step === 2 && (
                        <>
                            <div>
                                <InputLabel htmlFor="owner_name" value="Owner's Name" />
                                <TextInput
                                    id="owner_name"
                                    name="owner_name"
                                    value={data.owner_name}
                                    className="mt-1 block w-full"
                                    autoComplete="name"
                                    isFocused={true}
                                    onChange={(e) => setData('owner_name', e.target.value)}
                                />
                                {errors.owner_name && <InputError message={errors.owner_name} className="mt-2" />}
                            </div>

                            <div className="mt-2">
                                <InputLabel htmlFor="owner_email" value="Shop Owner Email" />
                                <TextInput
                                    id="owner_email"
                                    name="owner_email"
                                    value={data.owner_email}
                                    className="mt-1 block w-full"
                                    autoComplete="email"
                                    onChange={(e) => setData('owner_email', e.target.value)}
                                />
                                {errors.owner_email && <InputError message={errors.owner_email} className="mt-2" />}
                            </div>

                            <div className="mt-2">
                                <InputLabel htmlFor="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    className="mt-1 block w-full"
                                    autoComplete="new-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && <InputError message={errors.password} className="mt-2" />}
                            </div>

                            <div className="mt-2">
                                <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    className="mt-1 block w-full"
                                    autoComplete="new-password"
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                />
                                {errors.password_confirmation && <InputError message={errors.password_confirmation} className="mt-2" />}
                            </div>
                        </>
                    )}


                    {step === 1 && (
                        <>
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

                            {/* <div className="mt-2">
                                <InputLabel htmlFor="shop_email" value="Shop Email" />
                                <TextInput
                                    id="shop_email"
                                    name="shop_email"
                                    value={data.shop_email}
                                    className="mt-1 block w-full"
                                    autoComplete="email"
                                    onChange={(e) => setData('shop_email', e.target.value)}
                                />
                                {errors.shop_email && <InputError message={errors.shop_email} className="mt-2" />}
                            </div> */}
                        </>
                    )}

                    <div className="mt-6 flex items-center justify-end">
                        <Link
                            href={route('login')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Already registered?
                        </Link>

                        {step > 1 && (
                            <PrimaryButton className="ms-4" type="button" onClick={() => setStep(step - 1)}>
                                Back
                            </PrimaryButton>
                        )}

                        {step < 2 ? (
                            <PrimaryButton className="ms-4" type="submit">
                                Next
                            </PrimaryButton>
                        ) : (
                            <PrimaryButton className="ms-4" type="submit" disabled={processing}>
                                Register
                            </PrimaryButton>
                        )}

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
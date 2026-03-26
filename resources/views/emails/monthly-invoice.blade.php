@component('mail::message')
# Monthly Invoice

Hi {{ $user->name }},

Your monthly subscription invoice is ready.

**Amount:** ${{ $invoice->amount_due / 100 }}<br>
**Invoice PDF:** [Download Invoice]({{ $invoice->invoice_pdf }})

You can also view it in your Stripe Customer Portal.

Thanks for being a customer!
@endcomponent
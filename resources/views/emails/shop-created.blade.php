@component('mail::message')
# Welcome to {{ config('app.name') }}!

Your shop **{{ $shop->name ?? 'Your Shop' }}** has been created successfully.<br>
You now have **1 month free trial**. After that your monthly subscription will start automatically.

View your dashboard: {{ route('dashboard') }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
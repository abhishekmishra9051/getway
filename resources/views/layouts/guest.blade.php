@extends('layouts.base')

@section('body')
<div class="min-h-full grid lg:grid-cols-2">
    {{-- Brand panel --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-slate-950 p-12 text-white">
        <div class="absolute inset-0 opacity-40" style="background: radial-gradient(600px circle at 20% 20%, rgb(79 70 229 / .55), transparent 60%), radial-gradient(500px circle at 90% 80%, rgb(14 165 233 / .35), transparent 60%);"></div>
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px); background-size: 40px 40px;"></div>

        <a href="{{ route('home') }}" class="relative"><x-logo dark /></a>

        <div class="relative max-w-md">
            <h2 class="text-3xl font-bold tracking-tight leading-tight">Accept UPI payments straight into your bank account.</h2>
            <p class="mt-4 text-slate-300">Dynamic QR for every order, instant confirmation and signed webhooks. Zero transaction fees.</p>
            <ul class="mt-8 space-y-3 text-sm text-slate-200">
                @foreach (['Money goes directly to your own UPI ID', 'Works with GPay, PhonePe, Paytm, BHIM and every UPI app', 'Simple REST API and webhooks for developers'] as $point)
                    <li class="flex items-center gap-3"><span class="grid place-items-center w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400"><x-icon name="check" class="w-3.5 h-3.5" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="relative text-xs text-slate-400">© {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    {{-- Form --}}
    <div class="flex flex-col justify-center px-4 py-12 sm:px-12 bg-white">
        <div class="w-full max-w-sm mx-auto">
            <a href="{{ route('home') }}" class="lg:hidden inline-block mb-10"><x-logo /></a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">@yield('title')</h1>
            @hasSection('subtitle')<p class="mt-2 text-sm text-slate-500">@yield('subtitle')</p>@endif

            <div class="mt-8">
                {{-- Register shows errors next to each field; login has only one general error. --}}
                @if ($errors->any() && ! request()->routeIs('register'))
                    <div class="mb-5 flex items-start gap-2 rounded-lg bg-red-50 ring-1 ring-red-200 px-3 py-2.5 text-sm text-red-700">
                        <x-icon name="alert" class="w-5 h-5" /> {{ $errors->first() }}
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.guest')
@section('title', 'Create your account')
@section('subtitle', 'Start accepting UPI payments in minutes. Free, no setup fee.')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="grid sm:grid-cols-2 gap-x-3">
        <x-input name="name" label="Your name" required autofocus autocomplete="name" />
        <x-input name="business_name" label="Business name" required />
    </div>
    <x-input name="email" label="Work email" type="email" required autocomplete="email" placeholder="you@business.com" />
    <x-input name="phone" label="Mobile number" placeholder="Optional" autocomplete="tel" />
    <div class="grid sm:grid-cols-2 gap-x-3">
        <x-input name="password" label="Password" type="password" required autocomplete="new-password" placeholder="Min 8 chars" />
        <x-input name="password_confirmation" label="Confirm" type="password" required autocomplete="new-password" />
    </div>
    <button class="btn btn-primary w-full py-2.5 mt-1">Create account <x-icon name="arrow-right" class="w-4 h-4" /></button>
    <p class="text-xs text-center text-slate-400 mt-4">Payments go directly to your UPI account. We never hold your funds.</p>
</form>
<p class="text-sm text-center text-slate-500 mt-8">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Log in</a></p>
@endsection

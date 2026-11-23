@extends('site.layouts.master')

@section('title', 'Verifikasi Newsletter | SMKMUH1BLORA')

@section('content')
<main class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-md w-full text-center space-y-6">

        @if($status === 'success')
            <div class="w-20 h-20 mx-auto rounded-full bg-[#acf7f0]/30 border border-[#f0acf7] flex items-center justify-center text-slate-900">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Berhasil!</h1>
            <p class="text-slate-600">{{ $message }}</p>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold text-sm shadow-lg transition-all">
                Kembali ke Beranda
            </a>
        @elseif($status === 'already')
            <div class="w-20 h-20 mx-auto rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Sudah Terverifikasi</h1>
            <p class="text-slate-600">{{ $message }}</p>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold text-sm shadow-lg transition-all">
                Kembali ke Beranda
            </a>
        @else
            <div class="w-20 h-20 mx-auto rounded-full bg-red-50 border border-red-200 flex items-center justify-center text-red-600">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Link Tidak Valid</h1>
            <p class="text-slate-600">{{ $message }}</p>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#f0acf7] hover:bg-[#f0acf7] text-slate-950 font-bold text-sm shadow-lg transition-all">
                Kembali ke Beranda
            </a>
        @endif

    </div>
</main>
@endsection

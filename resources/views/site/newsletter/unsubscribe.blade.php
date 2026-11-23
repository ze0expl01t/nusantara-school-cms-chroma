@extends('site.layouts.master')

@section('title', 'Unsubscribe Newsletter | SMKMUH1BLORA')

@section('content')
<main class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-md w-full text-center space-y-6">

        @if($status === 'confirm')
            <div class="w-20 h-20 mx-auto rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Berhenti Berlangganan?</h1>
            <p class="text-slate-600">{{ $message }}</p>

            <form method="POST" action="{{ route('newsletter.unsubscribe', $token) }}" class="pt-2">
                @csrf
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-lg transition-all">
                        Ya, Unsubscribe
                    </button>
                    <a href="{{ url('/') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 font-bold text-sm shadow-sm transition-all">
                        Batal
                    </a>
                </div>
            </form>
        @elseif($status === 'done')
            <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Terima Kasih</h1>
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

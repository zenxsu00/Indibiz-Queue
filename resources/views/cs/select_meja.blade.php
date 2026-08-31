<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Loket Meja - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="bg-[#F8F9FA] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-white rounded-3xl border border-[#E0E3E8] shadow-2xl p-6 sm:p-8 text-center space-y-6">
        <div class="w-16 h-16 bg-[#00509E]/10 text-[#00509E] rounded-2xl flex items-center justify-center mx-auto">
            <span class="material-symbols-outlined text-3xl">desktop_windows</span>
        </div>

        <div>
            <h1 class="text-2xl font-black text-[#181C20]">Pilih Loket Meja CS</h1>
            <p class="text-xs text-[#5D3F3B] mt-1">Selamat datang, <strong>{{ Auth::user()->nama_lengkap }}</strong>! Silakan pilih lokasi meja tempat Anda bertugas hari ini.</p>
        </div>

        @if(session('error'))
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-bold flex items-center gap-2 text-left">
                <span class="material-symbols-outlined text-base">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('cs.process-meja') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-3.5 max-h-60 overflow-y-auto p-1">
                @forelse($masterMejas as $meja)
                    @php
                        $isTerpakai = in_array($meja->nomor_meja, $mejaTerpakai);
                    @endphp

                    <label class="relative border-2 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all
                        {{ $isTerpakai 
                            ? 'bg-gray-100 border-gray-200 opacity-50 cursor-not-allowed' 
                            : 'bg-white border-[#E0E3E8] hover:border-[#00509E] hover:shadow-md' }}">
                        
                        <input type="radio" name="nomor_meja" value="{{ $meja->nomor_meja }}" class="hidden peer" {{ $isTerpakai ? 'disabled' : '' }} required>
                        
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black transition-colors
                            {{ $isTerpakai ? 'bg-gray-200 text-gray-400' : 'bg-[#00509E]/10 text-[#00509E] peer-checked:bg-[#00509E] peer-checked:text-white' }}">
                            M{{ $meja->nomor_meja }}
                        </div>

                        <span class="text-xs font-bold text-[#181C20]">{{ $meja->nama_meja }}</span>

                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase
                            {{ $isTerpakai ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $isTerpakai ? 'Terpakai' : 'Tersedia' }}
                        </span>
                    </label>
                @empty
                    <div class="col-span-2 p-4 text-xs font-bold text-gray-400">Belum ada slot meja yang diaktifkan Admin.</div>
                @endforelse
            </div>

            <button type="submit" class="w-full bg-[#00509E] hover:bg-[#003C7E] text-white font-extrabold py-3.5 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <span>Mulai Melayani Loket</span>
                <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <!-- OPSI SPECTATOR KHUSUS AKUN ADMIN -->
        @if(Auth::user()->role === 'admin')
            <div class="pt-4 border-t border-[#E0E3E8]">
                <form action="{{ route('cs.process-meja') }}" method="POST">
                    @csrf
                    <input type="hidden" name="mode_spectator" value="1">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-extrabold py-3 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">visibility</span>
                        <span>Masuk Mode Spectator (Tanpa Meja)</span>
                    </button>
                </form>
            </div>
        @endif

        <!-- TOMBOL KELUAR / LEAVE KE HALAMAN LOGIN -->
        <div class="pt-2 border-t border-[#E0E3E8]">
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="w-full bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-600 font-extrabold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer border border-gray-200 text-xs">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Keluar / Ke Halaman Login</span>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
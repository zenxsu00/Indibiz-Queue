<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS Desk Console - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #F1F4F9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #C4C7CC; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #00509E; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F8F9FA] h-screen w-screen font-sans text-[#181C20] flex flex-col md:flex-row overflow-hidden antialiased selection:bg-[#EE2E24] selection:text-white"
      x-data="{ 
          showModalTransaksi: false, 
          adaTransaksi: false, 
          metodePembayaran: 'Cash', 
          isCuratedVal: '1',
          isSubmitting: false,
          selectedLayananId: '{{ $antreanAktif->layanan_id ?? '' }}'
      }">

    <!-- SIDEBAR CS LOKET (DESKTOP) -->
    <aside class="hidden md:flex flex-col w-[200px] lg:w-[220px] bg-[#00509E] text-white shrink-0 shadow-lg h-full justify-between z-20">
        <div>
            <div class="p-3.5 lg:p-4 border-b border-white/10 flex items-center gap-2.5">
                <img src="{{ asset('img/LogoIcon.png') }}" alt="Indibiz Icon" class="w-8 h-8 object-contain drop-shadow-md">
                <div class="flex flex-col">
                    <h2 class="text-sm lg:text-base font-black text-white leading-tight">CS Console</h2>
                    <span class="text-[9px] font-bold text-emerald-300 tracking-widest uppercase mt-0.5">Indibiz Queue</span>
                </div>
            </div>

            <nav class="py-3 space-y-1.5 px-2.5">
                <a href="{{ route('cs.index') }}" class="flex items-center px-3 py-2 text-xs font-bold bg-white/20 text-white rounded-lg shadow-sm border border-white/10 transition-all">
                    <span class="material-symbols-outlined mr-2 text-base">grid_view</span> 
                    Dashboard Loket
                </a>
                <a href="{{ route('cs.history') }}" class="flex items-center px-3 py-2 text-xs font-bold text-white/70 hover:bg-white/10 hover:text-white rounded-lg transition-all">
                    <span class="material-symbols-outlined mr-2 text-base">history</span> 
                    Riwayat Layanan
                </a>
            </nav>
        </div>

        <div class="p-3 border-t border-white/10 bg-black/10 space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-8 h-8 rounded-full bg-white text-[#00509E] font-black flex items-center justify-center text-xs shadow-sm shrink-0">
                    {{ $isSpectator ? 'ADM' : 'M' . ($nomorMejaTerpilih ?? auth()->user()->nomor_meja ?? '1') }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold truncate text-white">{{ auth()->user()->nama_lengkap }}</p>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isSpectator ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse' }}"></span>
                        <p class="text-white/70 text-[9px] uppercase tracking-wider font-semibold">
                            {{ $isSpectator ? 'Spectator' : 'Active Operator' }}
                        </p>
                    </div>
                </div>
            </div>

            @if(Auth::check() && (strtolower(Auth::user()->role) === 'admin' || strtolower(Auth::user()->username) === 'admin'))
                <a href="{{ route('cs.leave') }}" class="w-full py-2 px-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">swap_horiz</span>
                    <span>Switch ke Admin</span>
                </a>
            @endif

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2 bg-[#EE2E24] hover:bg-[#CE1111] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Keluar Loket</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE HEADER -->
    <div class="md:hidden bg-[#00509E] text-white p-3 flex justify-between items-center shadow-md shrink-0 z-30">
        <div class="font-bold text-xs flex items-center gap-2">
            <img src="{{ asset('img/LogoIcon.png') }}" alt="Indibiz" class="w-6 h-6 object-contain drop-shadow-md">
            <span>CS Console {{ $isSpectator ? '(Admin Mode)' : 'M' . ($nomorMejaTerpilih ?? auth()->user()->nomor_meja ?? '1') }}</span>
        </div>

        <div class="flex items-center gap-1.5">
            <a href="{{ route('cs.index') }}" title="Dashboard Loket" class="p-1 bg-white/20 text-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">grid_view</span>
            </a>
            <a href="{{ route('cs.history') }}" title="Riwayat Layanan" class="p-1 text-white/70 hover:bg-white/10 hover:text-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">history</span>
            </a>
            @if(Auth::check() && (strtolower(Auth::user()->role) === 'admin' || strtolower(Auth::user()->username) === 'admin'))
                <a href="{{ route('cs.leave') }}" class="text-xs bg-amber-500 hover:bg-amber-600 text-white font-bold px-2 py-1 rounded-lg flex items-center gap-1 shadow-sm ml-1">
                    <span class="material-symbols-outlined text-sm">swap_horiz</span>
                </a>
            @endif
            <form action="{{ route('logout') }}" method="POST" class="m-0 ml-1">
                @csrf
                <button type="submit" class="material-symbols-outlined text-white text-lg flex items-center p-1">logout</button>
            </form>
        </div>
    </div>

    <!-- MAIN CONTENT AREA CS -->
    <main id="main-cs-console" class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto md:overflow-hidden p-3 lg:p-4 gap-3">
        
        @if(session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-bold flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-base">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($isSpectator)
            <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex items-center justify-between text-xs font-bold shrink-0">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-lg">visibility</span>
                    <span>Anda dalam <strong>Mode Spectate (Super Admin)</strong> karena belum memilih meja.</span>
                </div>
                <a href="{{ route('cs.select-meja') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-extrabold px-3 py-1 rounded-lg transition-all shadow-sm">
                    Pilih Slot Meja
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 lg:gap-4 h-full min-h-0">
            
            <!-- KOLOM KIRI: DAFTAR ANTREAN MENUNGGU -->
            <div class="md:col-span-5 lg:col-span-4 bg-white rounded-xl shadow-sm border border-[#E0E3E8] p-3 lg:p-3.5 flex flex-col h-full min-h-0 overflow-hidden">
                
                @if(!$isSpectator)
                    <form action="{{ route('cs.panggil_selanjutnya') }}" method="POST" onsubmit="Alpine.$data(document.body).isSubmitting = true;" class="w-full shrink-0 mb-3">
                        @csrf
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full bg-[#EE2E24] hover:bg-[#CE1111] disabled:bg-gray-400 text-white py-2.5 px-3 rounded-lg font-black text-xs lg:text-sm tracking-wide flex items-center justify-center gap-1.5 shadow-md hover:shadow-lg transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-lg lg:text-xl" :class="isSubmitting ? 'animate-spin' : ''">
                                <template x-if="!isSubmitting">campaign</template>
                                <template x-if="isSubmitting">sync</template>
                            </span>
                            <span x-text="isSubmitting ? 'MEMPROSES PANGGILAN...' : 'PANGGIL ANTREAN SELANJUTNYA'"></span>
                        </button>
                    </form>
                @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 py-2.5 px-3 rounded-lg font-black text-xs lg:text-sm tracking-wide flex items-center justify-center gap-1.5 cursor-not-allowed mb-3">
                        <span class="material-symbols-outlined text-lg lg:text-xl">lock</span>
                        <span>PEMANGGILAN DIKUNCI (SPECTATOR)</span>
                    </button>
                @endif

                <div class="flex items-center justify-between border-b border-[#E0E3E8] pb-2 mb-2 shrink-0">
                    <h3 class="text-[11px] uppercase text-[#5D3F3B] font-black tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-[#00509E]">group</span>
                        Menunggu ({{ $antreanMenunggu->count() }})
                    </h3>
                </div>

                <div id="area-antrean-realtime" class="space-y-2 flex-1 overflow-y-auto pr-1 custom-scrollbar min-h-0">
                    @forelse($antreanMenunggu as $index => $tiket)
                        <div class="p-2.5 border rounded-lg flex justify-between items-center transition-all {{ $index === 0 ? 'border-[#00509E] bg-[#00509E]/5 shadow-sm' : 'border-[#E0E3E8] hover:bg-[#F8F9FA]' }}">
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="font-black text-[#181C20] text-sm lg:text-base">{{ $tiket->nomor_antrian }}</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#E0E3E8] text-[#5D3F3B] font-bold truncate max-w-[100px]">
                                        {{ $tiket->layanan->nama_layanan }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-[#181C20] truncate">{{ $tiket->pelanggan->nama }}</p>
                                <p class="text-[10px] text-[#5D3F3B] line-clamp-1 italic mt-0.5">"{{ $tiket->keluhan_awal }}"</p>
                            </div>

                            @if(!$isSpectator)
                                <form action="{{ route('cs.panggil_spesifik', $tiket->id) }}" method="POST" onsubmit="Alpine.$data(document.body).isSubmitting = true;" class="m-0 shrink-0">
                                    @csrf
                                    <button type="submit" :disabled="isSubmitting" class="text-[10px] bg-[#00509E] hover:bg-[#003C7E] disabled:bg-gray-400 text-white px-2.5 py-1.5 rounded-md font-bold transition-colors shadow-sm cursor-pointer">
                                        Panggil
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] bg-gray-100 text-gray-400 px-2 py-1 rounded font-bold">Spectate</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#5D3F3B] bg-[#F8F9FA] rounded-lg border border-dashed border-[#E0E3E8]">
                            <span class="material-symbols-outlined text-2xl text-gray-400 mb-1">coffee</span>
                            <p class="text-xs font-bold">Loket Sedang Kosong</p>
                            <p class="text-[10px] text-gray-400">Belum ada antrean baru.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KOLOM KANAN: PELAYANAN TIKET AKTIF & PROFILING -->
            <div class="md:col-span-7 lg:col-span-8 bg-white rounded-xl shadow-sm border border-[#E0E3E8] p-3.5 lg:p-4 flex flex-col h-full min-h-0 overflow-hidden">
                @if($antreanAktif)
                    <div class="flex flex-col h-full min-h-0 justify-between">
                        <div id="form-container-scroll" class="flex-1 overflow-y-auto pr-1 custom-scrollbar min-h-0 space-y-3">
                            <div class="border-b border-[#E0E3E8] pb-2.5 flex flex-wrap justify-between items-start gap-2">
                                <div>
                                    <span class="text-[9px] font-black text-[#5D3F3B] uppercase tracking-wider">Tiket Sedang Dilayani</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <h2 class="text-3xl lg:text-4xl font-black text-[#EE2E24] tracking-tight drop-shadow-sm">
                                            {{ $antreanAktif->nomor_antrian }}
                                        </h2>
                                        <span class="px-2 py-0.5 bg-[#EE2E24]/10 text-[#EE2E24] text-[9px] font-black rounded-full border border-[#EE2E24]/20 animate-pulse flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-xs">record_voice_over</span> Live
                                        </span>
                                    </div>
                                    @if(!empty($antreanAktif->kode_tiket))
                                        <p class="text-[10px] font-mono text-gray-400">Ref ID: {{ $antreanAktif->kode_tiket }}</p>
                                    @endif
                                </div>
                                <div class="text-right bg-[#F8F9FA] px-2.5 py-1 rounded-lg border border-[#E0E3E8]">
                                    <span class="text-[8px] text-[#5D3F3B] font-bold uppercase tracking-wider block">Waktu Ambil</span>
                                    <p class="text-xs font-black text-[#181C20]">
                                        {{ $antreanAktif->waktu_dibuat ? \Carbon\Carbon::parse($antreanAktif->waktu_dibuat)->format('H:i') : '-' }}
                                        <span class="text-[9px] font-bold text-gray-500">WIB</span>
                                    </p>
                                </div>
                            </div>

                            <form id="form-selesai-tiket" action="{{ route('cs.selesaikan', $antreanAktif->id) }}" method="POST" class="space-y-3">
                                @csrf
                                
                                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 space-y-2">
                                    <h4 class="text-xs font-black text-[#00509E] flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">person_edit</span> Profiling Data Pelanggan
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 block mb-0.5">Nama Pelanggan</label>
                                            <input type="text" name="nama_pelanggan" value="{{ $antreanAktif->pelanggan->nama ?? '' }}" {{ $isSpectator ? 'disabled' : '' }} required class="w-full p-2 border border-gray-300 rounded-lg text-xs focus:border-[#00509E] focus:ring-0">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 block mb-0.5">Email Pelanggan</label>
                                            <input type="email" name="email_pelanggan" value="{{ $antreanAktif->pelanggan->email ?? '' }}" {{ $isSpectator ? 'disabled' : '' }} placeholder="contoh@gmail.com" class="w-full p-2 border border-gray-300 rounded-lg text-xs focus:border-[#00509E] focus:ring-0">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 block mb-0.5">No. Indibiz / Service ID</label>
                                            <input type="text" name="no_indibiz" value="{{ $antreanAktif->pelanggan->no_indibiz ?? '' }}" {{ $isSpectator ? 'disabled' : '' }} placeholder="Contoh: 12233948" class="w-full p-2 border border-gray-300 rounded-lg text-xs focus:border-[#00509E] focus:ring-0">
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50/50 p-2.5 rounded-xl border border-blue-100 space-y-2">
                                    <h4 class="text-xs font-black text-[#00509E] flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">category</span> Koreksi Kategori & Sub-Layanan
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 block mb-0.5">Kategori Utama</label>
                                            <select name="layanan_id" x-model="selectedLayananId" {{ $isSpectator ? 'disabled' : '' }} class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white focus:border-[#00509E] focus:ring-0">
                                                @foreach($layanans as $lay)
                                                    <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 block mb-0.5">Sub-Layanan Sektoral</label>
                                            <select name="sub_layanan_id" {{ $isSpectator ? 'disabled' : '' }} class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white focus:border-[#00509E] focus:ring-0">
                                                <option value="">-- Pilih Sub Layanan --</option>
                                                @foreach($layanans as $lay)
                                                    @foreach($lay->subLayanans as $sub)
                                                        <option value="{{ $sub->id }}" x-show="selectedLayananId == '{{ $lay->id }}'" {{ $antreanAktif->sub_layanan_id == $sub->id ? 'selected' : '' }}>
                                                            {{ $sub->nama_sub_layanan }}
                                                        </option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[9px] text-[#5D3F3B] font-black uppercase tracking-wider mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">chat</span> Catatan Keluhan Awal Pelanggan
                                    </p>
                                    <div class="text-xs text-[#181C20] bg-yellow-50 border border-yellow-200 p-2.5 rounded-lg italic font-medium shadow-inner">
                                        "{{ $antreanAktif->keluhan_awal }}"
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-black text-[#181C20] flex items-center gap-1 mb-1">
                                        <span class="material-symbols-outlined text-sm text-[#00509E]">edit_document</span>
                                        Revisi Keluhan Pelanggan <span class="text-[10px] text-gray-400 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea id="keluhan_final" 
                                              name="keluhan_final" 
                                              rows="1" 
                                              oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                                              {{ $isSpectator ? 'disabled' : '' }} 
                                              placeholder="Ketik kustomisasi atau penyesuaian keluhan sesungguhnya jika berbeda dari Kiosk..." 
                                              class="w-full rounded-lg border border-[#E0E3E8] bg-white text-xs text-[#181C20] p-2.5 focus:border-[#00509E] focus:ring-0 transition-all font-medium resize-none overflow-hidden shadow-sm"></textarea>
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
                                        <label class="text-xs font-black text-[#181C20] flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-[#00509E]">note_add</span>
                                            Catatan Hasil Konsul / Resolusi <span class="text-[10px] text-gray-400 font-normal">(Opsional)</span>
                                        </label>
                                        
                                        @if(!$isSpectator)
                                            <div class="flex items-center gap-1 flex-wrap">
                                                <button type="button" 
                                                        onclick="tumpukTemplate('Telah dilakukan edukasi fitur dan penggunaan aplikasi Indibiz kepada pelanggan.')"
                                                        class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-[#00509E] text-[10px] font-bold rounded border border-blue-200 transition-all cursor-pointer">
                                                    + Edukasi Aplikasi
                                                </button>
                                                <button type="button" 
                                                        onclick="tumpukTemplate('Proses reset password dan konfigurasi ulang kredensial akun berhasil.')"
                                                        class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-[#00509E] text-[10px] font-bold rounded border border-blue-200 transition-all cursor-pointer">
                                                    + Reset Password
                                                </button>
                                                <button type="button" 
                                                        onclick="tumpukTemplate('Kendala teknis tercatat dan dijadwalkan untuk penanganan kunjungan teknisi lapangan.')"
                                                        class="px-2 py-0.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-[10px] font-bold rounded border border-amber-200 transition-all cursor-pointer">
                                                    + Jadwal Teknisi
                                                </button>
                                                <button type="button" 
                                                        onclick="tumpukTemplate('Penjelasan rincian skema tagihan dan metode pembayaran telah diselesaikan.')"
                                                        class="px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded border border-emerald-200 transition-all cursor-pointer">
                                                    + Info Tagihan
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <textarea id="catatan_cs" 
                                              name="catatan_cs" 
                                              rows="1" 
                                              oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                                              {{ $isSpectator ? 'disabled' : '' }} 
                                              placeholder="Ketik catatan solusi, langkah perbaikan, atau gunakan chip bantuan di atas..." 
                                              class="w-full rounded-lg border border-[#E0E3E8] bg-white text-xs text-[#181C20] p-2.5 focus:border-[#00509E] focus:ring-0 transition-all font-medium resize-none overflow-hidden shadow-sm"></textarea>
                                </div>

                                <input type="hidden" name="metode_pembayaran" id="input_metode_pembayaran" value="">
                                <input type="hidden" name="nominal_pembayaran" id="input_nominal_pembayaran" value="0">
                                <input type="hidden" name="bukti_pembayaran" id="input_bukti_pembayaran" value="">
                                <input type="hidden" name="is_curated" id="input_is_curated" value="1">
                            </form>

                            <form id="form-tidak-hadir" action="{{ route('cs.batal_atau_kembalikan', $antreanAktif->id) }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>

                        <!-- BARIS AKSI TIKET AKTIF -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-[#E0E3E8] shrink-0 mt-1">
                            @if(!$isSpectator)
                                <div class="flex items-center gap-1.5">
                                    <!-- RECALL AUDIO BUTTON (AJAX) -->
                                    <button type="button" 
                                            onclick="panggilUlangAudio('{{ $antreanAktif->id }}')"
                                            title="Panggil ulang audio antrean di Layar Display TV"
                                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-[10px] lg:text-[11px] shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-sm">campaign</span>
                                        <span>Panggil Ulang Audio</span>
                                    </button>

                                    <!-- TIDAK HADIR BUTTON -->
                                    <button type="button" 
                                            data-nomor="{{ $antreanAktif->nomor_antrian }}" 
                                            onclick="konfirmasiTidakHadir(this.getAttribute('data-nomor'))" 
                                            class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold text-[10px] lg:text-[11px] shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-sm">person_off</span>
                                        <span>Tidak Hadir</span>
                                    </button>
                                </div>

                                <button type="button" @click="showModalTransaksi = true" class="px-4 py-2 bg-[#00509E] hover:bg-[#003C7E] text-white rounded-lg font-black text-xs shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
                                    <span class="material-symbols-outlined text-base">check_circle</span>
                                    <span>SELESAIKAN LAYANAN</span>
                                </button>
                            @else
                                <div class="w-full text-center py-2 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold">
                                    Aksi Transaksi Dikunci untuk Spectator (Super Admin)
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-center py-6">
                        <div class="w-12 h-12 rounded-full bg-[#F1F4F9] text-[#00509E] flex items-center justify-center mb-2 shadow-inner">
                            <span class="material-symbols-outlined text-2xl">support_agent</span>
                        </div>
                        <h3 class="text-base font-black text-[#181C20]">CS Console (Live Monitor)</h3>
                        <p class="text-xs text-[#5D3F3B] max-w-xs mt-1 font-medium leading-relaxed">
                            {{ $isSpectator ? 'Mode pemantauan aktif. Silakan pilih slot meja untuk mulai melayani.' : 'Tekan tombol merah "PANGGIL ANTREAN SELANJUTNYA" di sebelah kiri untuk melayani pelanggan berikutnya.' }}
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </main>

    <!-- MODAL TRANSAKSI & STATUS PENANGANAN -->
    <div x-show="showModalTransaksi" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 lg:p-6 shadow-2xl border border-gray-100 space-y-4" @click.away="showModalTransaksi = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">payments</span>
                    Konfirmasi Layanan & Pembayaran
                </h3>
                <button @click="showModalTransaksi = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-3">
                <p class="text-xs text-gray-600 font-medium">Apakah ada transaksi keuangan / pembayaran pada tiket ini?</p>
                
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="adaTransaksi = false" :class="!adaTransaksi ? 'border-2 border-[#00509E] bg-[#00509E]/5 text-[#00509E]' : 'border border-gray-200 text-gray-600'" class="py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-sm">block</span> Tidak Ada (Gratis)
                    </button>
                    <button type="button" @click="adaTransaksi = true" :class="adaTransaksi ? 'border-2 border-[#EE2E24] bg-red-50 text-[#EE2E24]' : 'border border-gray-200 text-gray-600'" class="py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-sm">paid</span> Ada Transaksi
                    </button>
                </div>

                <div x-show="adaTransaksi" x-transition class="space-y-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="text-[11px] font-bold text-[#181C20] block mb-1">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="metodePembayaran = 'Cash'" :class="metodePembayaran === 'Cash' ? 'bg-[#00509E] text-white' : 'bg-gray-100 text-gray-700'" class="py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">local_atm</span> Cash / Tunai
                            </button>
                            <button type="button" @click="metodePembayaran = 'QRIS'" :class="metodePembayaran === 'QRIS' ? 'bg-[#00509E] text-white' : 'bg-gray-100 text-gray-700'" class="py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">qr_code_2</span> QRIS / Bank
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-[#181C20] block mb-1">
                            Nominal Pembayaran (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="modal_nominal" min="1" placeholder="Contoh: 150000" class="w-full text-xs p-2.5 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0">
                    </div>

                    <div x-show="metodePembayaran === 'QRIS'">
                        <label class="text-[11px] font-bold text-[#181C20] block mb-1">
                            Kode / Reff Bukti QRIS <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" id="modal_bukti" placeholder="Contoh: TRX-9821389" class="w-full text-xs p-2.5 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0">
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 space-y-1.5">
                    <label class="font-bold text-gray-700 block text-xs">Status Penanganan Pekerjaan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" 
                                @click="isCuratedVal = '1'" 
                                :class="isCuratedVal == '1' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md ring-2 ring-emerald-300' : 'bg-emerald-50 text-emerald-800 border-emerald-200 opacity-50 hover:opacity-100'"
                                class="py-2.5 px-3 rounded-xl border text-xs font-black flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            <span>Selesai di Loket</span>
                        </button>

                        <button type="button" 
                                @click="isCuratedVal = '0'" 
                                :class="isCuratedVal == '0' ? 'bg-amber-500 text-white border-amber-500 shadow-md ring-2 ring-amber-300' : 'bg-amber-50 text-amber-800 border-amber-200 opacity-50 hover:opacity-100'"
                                class="py-2.5 px-3 rounded-xl border text-xs font-black flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-base">engineering</span>
                            <span>Butuh Lapangan</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" @click="showModalTransaksi = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitSelesaiTiket()" class="px-5 py-2 bg-[#00509E] hover:bg-[#003C7E] text-white font-black text-xs rounded-lg shadow-md transition-all flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">check</span> Submit & Selesaikan
                </button>
            </div>
        </div>
    </div>

    <script>
        // PANGGIL ULANG VIA AJAX (TANPA RELOAD HALAMAN)
        function panggilUlangAudio(tiketId) {
            fetch('/cs-desk/panggil-ulang/' + tiketId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memanggil',
                        text: data.message || 'Terjadi kesalahan sistem.'
                    });
                }
            })
            .catch(err => {
                console.error('Recall Error:', err);
            });
        }

        function tumpukTemplate(teksTemplate) {
            let textarea = document.getElementById('catatan_cs');
            if (!textarea) return;

            if (textarea.value.trim() !== '') {
                textarea.value += '\n' + teksTemplate;
            } else {
                textarea.value = teksTemplate;
            }

            textarea.dispatchEvent(new Event('input'));
        }

        function submitSelesaiTiket() {
            let isAda = Alpine.$data(document.body).adaTransaksi;
            let curatedStatus = Alpine.$data(document.body).isCuratedVal;

            if (isAda) {
                let metode = Alpine.$data(document.body).metodePembayaran;
                let nominalInput = document.getElementById('modal_nominal');
                let nominalVal = nominalInput ? nominalInput.value.trim() : '';
                let bukti = document.getElementById('modal_bukti') ? document.getElementById('modal_bukti').value : '';

                if (!nominalVal || parseFloat(nominalVal) <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Wajib Diisi',
                        text: 'Silakan masukkan nominal pembayaran yang valid sebelum menyelesaikan layanan.',
                        confirmButtonColor: '#00509E',
                        customClass: { popup: 'rounded-2xl' }
                    });
                    if (nominalInput) nominalInput.focus();
                    return;
                }

                document.getElementById('input_metode_pembayaran').value = metode;
                document.getElementById('input_nominal_pembayaran').value = nominalVal;
                document.getElementById('input_bukti_pembayaran').value = bukti;
            } else {
                document.getElementById('input_metode_pembayaran').value = 'Tanpa Transaksi';
                document.getElementById('input_nominal_pembayaran').value = 0;
                document.getElementById('input_bukti_pembayaran').value = '';
            }

            document.getElementById('input_is_curated').value = curatedStatus;
            document.getElementById('form-selesai-tiket').submit();
        }

        function konfirmasiTidakHadir(nomorAntrian) {
            Swal.fire({
                title: 'Pelanggan Tidak Hadir?',
                text: 'Antrean ' + nomorAntrian + ' akan dipanggil ulang atau dibatalkan dari antrean jika sudah dipanggil 2 kali.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Kembalikan / Batalkan',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-2xl' }
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('form-tidak-hadir').submit();
                }
            });
        }

        function fetchConsoleRealtime() {
            var modalActive = Alpine.$data(document.body).showModalTransaksi;
            var isSubmitting = Alpine.$data(document.body).isSubmitting;
            
            var activeEl = document.activeElement;
            var isFocusInput = activeEl && (activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'INPUT' || activeEl.tagName === 'SELECT');

            var keluhanEl = document.getElementById('keluhan_final');
            var catatanEl = document.getElementById('catatan_cs');
            var isTextareaFilled = (keluhanEl && keluhanEl.value.trim() !== '') || (catatanEl && catatanEl.value.trim() !== '');

            if (modalActive || isFocusInput || isTextareaFilled || isSubmitting) return;

            fetch(window.location.href)
                .then(function(response) {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }
                    return response.text();
                })
                .then(function(html) {
                    if (!html) return;
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    
                    // HANYA UPDATE SISI KIRI (DAFTAR ANTREAN MENUNGGU) UNTUK MENJAGA TEXTAREA/FORM AKTIF DI SEBELAH KANAN
                    var antreanBaru = doc.getElementById('area-antrean-realtime');
                    var antreanLama = document.getElementById('area-antrean-realtime');

                    if (antreanBaru && antreanLama && antreanLama.innerHTML !== antreanBaru.innerHTML) {
                        var scrollPos = antreanLama.scrollTop;
                        antreanLama.innerHTML = antreanBaru.innerHTML;
                        antreanLama.scrollTop = scrollPos;
                    }
                })
                .catch(function(error) { console.error('Gagal memperbarui antrean:', error); });
        }

        setInterval(fetchConsoleRealtime, 2500);

        function sendHeartbeat() {
            fetch('{{ route("cs.ping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).catch(err => console.log('Heartbeat failed:', err));
        }

        sendHeartbeat();
        setInterval(sendHeartbeat, 30000);
    </script>
</body>
</html>
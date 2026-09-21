<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script defer src="{{ asset('js/admin-dashboard.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #F1F4F9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #C4C7CC; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #00509E; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F8F9FA] min-h-screen font-sans text-[#181C20] flex flex-col lg:flex-row antialiased overflow-x-hidden selection:bg-[#EE2E24] selection:text-white" 
      x-data="{ 
          activeTab: 'staff', 
          mobileMenu: false, 
          showModalAkun: false, 
          showModalEditAkun: false,
          showModalPassAkun: false,
          showModalMeja: false, 
          showModalLayanan: false,
          showModalEditLayanan: false,
          showModalSubLayanan: false,
          showModalEditSubLayanan: false,
          showModalDetail: false,
          showModalPdf: false,
          selectedTiket: null,
          selectedUser: null,
          selectedEditLayanan: null,
          selectedEditSubLayanan: null,
          selectedPeriod: '{{ request('period', 'all') }}',
          selectedLayananId: '{{ optional($layanans->first())->id ?? '' }}',
          selectedLayananNama: '{{ addslashes(optional($layanans->first())->nama_layanan ?? '') }}'
      }">

    <!-- HEADER MOBILE -->
    <header class="lg:hidden bg-[#00509E] text-white p-4 flex justify-between items-center shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('img/LogoIcon.png') }}" alt="Indibiz Icon" class="w-7 h-7 object-contain">
            <span class="font-black text-sm tracking-wide">Super Admin</span>
        </div>
        <button @click="mobileMenu = !mobileMenu" class="p-1 rounded-lg bg-white/10 hover:bg-white/20 transition-all">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
    </header>

    <!-- MOBILE DRAWER MENU -->
    <div x-show="mobileMenu" x-cloak class="lg:hidden fixed inset-0 bg-black/60 z-50 backdrop-blur-sm flex flex-col justify-end" @click.away="mobileMenu = false">
        <div class="bg-[#00509E] text-white rounded-t-3xl p-6 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-white/10 pb-3">
                <h3 class="font-black text-base">Menu Navigasi</h3>
                <button @click="mobileMenu = false" class="text-white/70 hover:text-white">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>
            <nav class="space-y-2">
                <button @click="activeTab = 'operations'; mobileMenu = false" :class="activeTab === 'operations' ? 'bg-white/20 text-white' : 'text-white/70'" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3"><span class="material-symbols-outlined">dashboard</span> Operations</button>
                <button @click="activeTab = 'analytics'; mobileMenu = false" :class="activeTab === 'analytics' ? 'bg-white/20 text-white' : 'text-white/70'" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3"><span class="material-symbols-outlined">analytics</span> Analitik & Grafik</button>
                <button @click="activeTab = 'history'; mobileMenu = false" :class="activeTab === 'history' ? 'bg-white/20 text-white' : 'text-white/70'" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3"><span class="material-symbols-outlined">history</span> Riwayat Bulanan</button>
                <button @click="activeTab = 'staff'; mobileMenu = false" :class="activeTab === 'staff' ? 'bg-white/20 text-white' : 'text-white/70'" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3"><span class="material-symbols-outlined">badge</span> Monitoring Akun & Meja</button>
                <button @click="activeTab = 'master_layanan'; mobileMenu = false" :class="activeTab === 'master_layanan' ? 'bg-white/20 text-white' : 'text-white/70'" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3"><span class="material-symbols-outlined">category</span> Kelola Layanan</button>
                <a href="{{ route('cs.select-meja') }}" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3 bg-amber-500 text-white shadow-sm mt-4"><span class="material-symbols-outlined">swap_horiz</span> Switch ke CS Console</a>
            </nav>
            <form action="{{ route('logout') }}" method="POST" class="pt-2 border-t border-white/10">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-[#EE2E24] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5"><span class="material-symbols-outlined text-base">logout</span> Keluar Sistem</button>
            </form>
        </div>
    </div>

    <!-- SIDEBAR DESKTOP -->
    <aside class="hidden lg:flex flex-col w-[240px] xl:w-[260px] bg-[#00509E] text-white shrink-0 shadow-lg min-h-screen sticky top-0 justify-between z-30">
        <div>
            <div class="p-5 border-b border-white/10 flex items-center gap-3">
                <img src="{{ asset('img/LogoIcon.png') }}" alt="Indibiz Icon" class="w-8 h-8 object-contain">
                <div>
                    <h2 class="text-base font-black leading-tight">Super Admin</h2>
                    <p class="text-[10px] text-emerald-300 font-bold uppercase tracking-widest">Indibiz Queue</p>
                </div>
            </div>

            <nav class="py-4 space-y-1.5 px-3">
                <button @click="activeTab = 'operations'" :class="activeTab === 'operations' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer"><span class="material-symbols-outlined mr-3 text-lg">dashboard</span> <span>Operations</span></button>
                <button @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer"><span class="material-symbols-outlined mr-3 text-lg">analytics</span> <span>Analitik & Grafik</span></button>
                <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer"><span class="material-symbols-outlined mr-3 text-lg">history</span> <span>Riwayat Bulanan</span></button>
                <button @click="activeTab = 'staff'" :class="activeTab === 'staff' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer"><span class="material-symbols-outlined mr-3 text-lg">badge</span> <span>Monitoring Akun & Meja</span></button>
                <button @click="activeTab = 'master_layanan'" :class="activeTab === 'master_layanan' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer"><span class="material-symbols-outlined mr-3 text-lg">category</span> <span>Kelola Layanan</span></button>

                <div class="pt-4 border-t border-white/10 mt-3">
                    <a href="{{ route('cs.select-meja') }}" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all shadow-sm"><span class="material-symbols-outlined mr-2.5 text-lg">swap_horiz</span><span>Switch ke CS Console</span></a>
                </div>
            </nav>
        </div>

        <div class="p-3.5 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-[#EE2E24] hover:bg-[#CE1111] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer"><span class="material-symbols-outlined text-base">logout</span><span>Keluar Sistem</span></button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 space-y-6 w-full">

        <!-- NOTIFIKASI -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-base">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        
        <!-- HEADER UTAMA -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 lg:p-6 rounded-2xl border border-[#E0E3E8] shadow-sm">
            <div>
                <h2 class="text-xl lg:text-2xl font-black text-[#181C20] tracking-tight" x-text="
                    activeTab === 'operations' ? 'Operations Dashboard' : 
                    (activeTab === 'analytics' ? 'Analitik Tren & Kepadatan Layanan' : 
                    (activeTab === 'history' ? 'Riwayat Operasional & Rekap Omset' : 
                    (activeTab === 'staff' ? 'Monitoring Akun & Meja' : 'Kelola Master Layanan & Sub-Layanan')))
                "></h2>
                <p class="text-xs text-[#5D3F3B] mt-0.5">
                    Pemantauan metrik antrean, rekapitulasi data, serta konfigurasi layanan secara real-time.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <button @click="showModalPdf = true" class="inline-flex items-center justify-center gap-1.5 bg-[#EE2E24] hover:bg-[#CE1111] text-white font-black px-4 py-2.5 rounded-xl text-xs shadow-md transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Cetak PDF
                </button>
                <a href="{{ route('admin.export') }}" class="inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs shadow-md transition-all">
                    <span class="material-symbols-outlined text-base">download</span> Export CSV
                </a>
            </div>
        </div>

        <!-- FORM FILTER UTAMA TAB OPERATIONS -->
        <form x-show="activeTab === 'operations'" action="{{ route('admin.dashboard') }}" method="GET" class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-end gap-3 text-xs">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Periode Waktu</label>
                <select name="period" x-model="selectedPeriod" @change="if(selectedPeriod !== 'custom') $el.form.submit()" class="w-full border border-gray-300 bg-gray-50 rounded-xl p-2 font-bold text-gray-700 focus:border-[#00509E] focus:ring-0 cursor-pointer">
                    <option value="all">Semua Waktu (All Time)</option>
                    <option value="today">Hari Ini (Today)</option>
                    <option value="wtd">Minggu Ini (WTD)</option>
                    <option value="mtd">Bulan Ini (MTD)</option>
                    <option value="last_30">30 Hari Terakhir</option>
                    <option value="ytd">Tahun Ini (YTD)</option>
                    <option value="custom">Rentang Tanggal (Date to Date)...</option>
                </select>
            </div>

            <template x-if="selectedPeriod === 'custom'">
                <div class="flex flex-wrap items-center gap-2">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="border border-gray-300 rounded-xl p-2 font-semibold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="border border-gray-300 rounded-xl p-2 font-semibold">
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-[#00509E] text-white font-bold rounded-xl flex items-center gap-1 shadow-sm hover:bg-[#003B75]">
                        <span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan
                    </button>
                </div>
            </template>

            <div class="flex-1 min-w-[180px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Filter Kategori</label>
                <select name="layanan_id" @change="$el.form.submit()" class="w-full border border-gray-300 rounded-xl p-2 font-semibold focus:border-[#00509E] focus:ring-0">
                    <option value="">Semua Kategori</option>
                    @foreach($layanans as $lay)
                        <option value="{{ $lay->id }}" {{ $layananId == $lay->id ? 'selected' : '' }}>{{ $lay->nama_layanan }}</option>
                    @endforeach
                </select>
            </div>

            @if(request('period') || request('layanan_id') || request('start_date'))
                <div class="pb-0.5">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl font-bold flex items-center gap-1 hover:bg-rose-100 transition-all">
                        <span class="material-symbols-outlined text-sm">restart_alt</span> Reset
                    </a>
                </div>
            @endif
        </form>

        <!-- TAB 1: OPERATIONS -->
        <div x-show="activeTab === 'operations'" class="space-y-6">
            <!-- RINGKASAN ANALISIS OTOMATIS TREN OPERASIONAL -->
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-[#00509E] text-2xl">insights</span>
                <div class="flex-1">
                    <span class="text-[10px] font-black text-gray-400 uppercase block">Ringkasan Eksekutif Analisis Otomatis</span>
                    <p id="summary-content-source" class="text-xs font-semibold text-gray-800">
                        {!! preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $analisisOtomatis['status_tiket'] ?? '') !!}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $analisisOtomatis['badge_tiket'] ?? 'bg-gray-100' }}">
                    Auto Insight
                </span>
            </div>

            <!-- METRIK CARDS (5 KARTU UTAMA) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-[#00509E]"></div>
                    <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider block mb-1">Total Antrean</span>
                    <span class="text-2xl lg:text-3xl font-black text-[#181C20]">{{ number_format($totalHariIni) }}</span>
                </div>
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Sedang Menunggu</span>
                        @if(isset($ditransfer) && $ditransfer > 0)
                            <span class="text-[8px] bg-indigo-100 text-indigo-800 font-black px-1.5 py-0.5 rounded">{{ $ditransfer }} Trf</span>
                        @endif
                    </div>
                    <span class="text-2xl lg:text-3xl font-black text-[#181C20]">{{ number_format($menunggu) }}</span>
                </div>
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-blue-500"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Avg Waktu Tunggu</span>
                        <span class="text-[8px] bg-blue-100 text-blue-800 font-black px-1 py-0.5 rounded">TV Display</span>
                    </div>
                    <span class="text-xl lg:text-2xl font-black text-blue-600">{{ $avgWaktuTungguText }}</span>
                </div>
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Avg Durasi CS</span>
                        <span class="text-[8px] bg-emerald-100 text-emerald-800 font-black px-1 py-0.5 rounded">SLA CS</span>
                    </div>
                    <span class="text-xl lg:text-2xl font-black text-emerald-600">{{ $avgDurasiLayananText }}</span>
                </div>
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-purple-600"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Total Omset</span>
                        <span class="text-[8px] bg-purple-100 text-purple-800 font-black px-1 py-0.5 rounded">Omset</span>
                    </div>
                    <span class="text-lg lg:text-xl font-black text-[#00509E]">Rp {{ number_format($totalOmset, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- TABEL ANTREAN -->
            <div class="bg-white rounded-2xl border border-[#E0E3E8] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#E0E3E8] flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-wider text-[#181C20]">Data Transaksi & Tiket Antrean</h3>
                    <span class="text-xs text-gray-400 font-semibold">Total {{ $allFilteredTickets->count() }} Data</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8F9FA] border-b border-[#E0E3E8] text-gray-500 font-black uppercase text-[10px]">
                            <tr>
                                <th class="p-3">Kode Tiket</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3">Kategori & Sub-Layanan</th>
                                <th class="p-3">Akun Petugas</th>
                                <th class="p-3">Waktu Ambil</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Catatan CS & Solusi</th>
                                <th class="p-3 text-right">Nominal</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E0E3E8]">
                            @forelse($allFilteredTickets as $tiket)
                                <tr class="hover:bg-[#F8F9FA] transition-colors">
                                    <td class="p-3 font-mono font-bold text-[#00509E]">{{ $tiket->nomor_antrian }}</td>
                                    <td class="p-3 font-bold">
                                        {{ optional($tiket->pelanggan)->nama ?? '-' }} 
                                        <br><span class="text-[10px] text-gray-400 font-normal">{{ optional($tiket->pelanggan)->no_hp ?? '-' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="bg-blue-50 text-[#00509E] px-2 py-0.5 rounded font-bold block mb-0.5">{{ optional($tiket->layanan)->nama_layanan ?? '-' }}</span>
                                        <span class="text-[10px] text-gray-500 italic">{{ optional($tiket->subLayanan)->nama_sub_layanan ?? 'Tanpa Sub-Layanan' }}</span>
                                    </td>
                                    <td class="p-3 font-bold">{{ optional($tiket->cs)->nama_lengkap ? optional($tiket->cs)->nama_lengkap . ' (M' . optional($tiket->cs)->nomor_meja . ')' : '-' }}</td>
                                    <td class="p-3 text-gray-500">{{ \Carbon\Carbon::parse($tiket->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ 
                                            $tiket->status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 
                                            ($tiket->status === 'Batal' ? 'bg-red-100 text-red-700' : 
                                            ($tiket->status === 'No Show' ? 'bg-purple-100 text-purple-700' : 
                                            ($tiket->status === 'Ditransfer' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'))) 
                                        }}">{{ $tiket->status }}</span>
                                    </td>
                                    <td class="p-3 text-gray-600 max-w-[200px] truncate" title="{{ $tiket->catatan_cs ?? $tiket->ringkasan_solusi ?? '-' }}">{{ $tiket->catatan_cs ?? $tiket->ringkasan_solusi ?? '-' }}</td>
                                    <td class="p-3 text-right font-black text-[#181C20]">Rp {{ number_format($tiket->nominal_pembayaran, 0, ',', '.') }}</td>
                                    <td class="p-3 text-center">
                                        <button @click="selectedTiket = @json($tiket); showModalDetail = true" class="px-2.5 py-1 bg-[#00509E]/10 text-[#00509E] hover:bg-[#00509E] hover:text-white rounded-lg font-bold text-[10px] transition-all cursor-pointer">Detail</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="p-6 text-center text-gray-400 font-bold">Tidak ada data antrean pada rentang waktu ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: ANALITIK LAYANAN -->
        <div x-show="activeTab === 'analytics'" x-data="chartFilterComponent()" class="space-y-6">
            <!-- Filter Analytics -->
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#00509E]">tune</span><span class="text-xs font-black uppercase tracking-wider text-[#181C20]">Filter Periode Grafik Analitik</span></div>
                <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 p-1 rounded-xl text-xs font-bold w-full md:w-auto">
                    <button type="button" @click="switchChartPeriod('wtd')" :class="chartPeriod === 'wtd' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">WTD</button>
                    <button type="button" @click="switchChartPeriod('mtd')" :class="chartPeriod === 'mtd' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">MTD</button>
                    <button type="button" @click="switchChartPeriod('mtm')" :class="chartPeriod === 'mtm' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">MTM</button>
                    <button type="button" @click="switchChartPeriod('last30')" :class="chartPeriod === 'last30' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">30 Hari</button>
                    <button type="button" @click="switchChartPeriod('custom')" :class="chartPeriod === 'custom' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1"><span class="material-symbols-outlined text-xs">calendar_today</span> Date to Date</button>
                </div>
            </div>

            <!-- FORM INPUT TANGGAL -->
            <form x-show="chartPeriod === 'custom'" x-cloak action="{{ route('admin.dashboard') }}" method="GET" class="bg-blue-50/70 border border-blue-200 p-4 rounded-2xl flex flex-wrap items-end gap-3 text-xs">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="text-[10px] font-black text-gray-500 uppercase block mb-1">Dari Tanggal (Start Date)</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="border border-gray-300 rounded-xl p-2 font-semibold bg-white">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-500 uppercase block mb-1">Sampai Tanggal (End Date)</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="border border-gray-300 rounded-xl p-2 font-semibold bg-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-[#00509E] text-white font-bold rounded-xl flex items-center gap-1 shadow-sm hover:bg-[#003B75] cursor-pointer">
                    <span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan Tanggal
                </button>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- CHART 1 -->
                <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm">
                    <h3 class="text-sm font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2 mb-4 border-b pb-3"><span class="material-symbols-outlined text-[#00509E]">show_chart</span>1. Pendaftaran vs Selesai</h3>
                    <div class="h-64 relative"><canvas id="queueChart" data-chart-sets='@json($chartDataSets ?? [])'></canvas></div>
                </div>
                <!-- CHART 2 -->
                <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                    <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2 border-b pb-2"><span class="material-symbols-outlined text-[#00509E]">pie_chart</span>2. Kepadatan Kategori Layanan</h3>
                    <div class="h-64 relative"><canvas id="categoryPieChart" data-dist='@json($distribusiLayanan ?? [])'></canvas></div>
                </div>
            </div>

            <!-- CHART 3 -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3 flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b pb-2">
                    <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2"><span class="material-symbols-outlined text-blue-600">timeline</span>3. Waktu Tunggu vs Durasi Konsul CS</h3>
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-[10px] font-bold">
                        <button @click="toggleSlaMode(true)" :class="isMerged ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600'" class="px-3 py-1.5 rounded-lg cursor-pointer">Gabung</button>
                        <button @click="toggleSlaMode(false)" :class="!isMerged ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600'" class="px-3 py-1.5 rounded-lg cursor-pointer">Pisah</button>
                    </div>
                </div>
                <div class="relative min-h-[260px] py-2">
                    <div x-show="isMerged" class="h-64 relative"><canvas id="slaMergedChart"></canvas></div>
                    <div x-show="!isMerged" x-cloak class="grid grid-cols-2 gap-4 h-64"><div class="relative"><canvas id="slaTungguSeparateChart"></canvas></div><div class="relative"><canvas id="slaKonsulSeparateChart"></canvas></div></div>
                </div>
            </div>

            <!-- CHART 4 -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3 relative">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-2 gap-3">
                    <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-600">bar_chart</span>
                        4. Performa Produktivitas Staf CS per Akun
                    </h3>
                    
                    <div x-data="{ showFilter: false }" class="relative z-20">
                        <button @click="showFilter = !showFilter" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg text-xs font-bold flex items-center gap-1 cursor-pointer transition-all">
                            <span class="material-symbols-outlined text-[14px]">filter_alt</span> Sembunyikan Akun
                        </button>
                        <div x-show="showFilter" @click.away="showFilter = false" x-transition x-cloak class="absolute right-0 mt-2 w-56 bg-white border border-[#E0E3E8] rounded-xl shadow-xl p-3">
                            <span class="text-[10px] font-black uppercase text-gray-400 block mb-2 border-b pb-1">Centang untuk sembunyikan</span>
                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1">
                                <template x-for="cs in rawCsData" :key="cs.id">
                                    <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer p-1 hover:bg-gray-50 rounded">
                                        <input type="checkbox" :value="cs.id.toString()" x-model="hiddenAccounts" @change="renderBarChart()" class="rounded text-purple-600 w-3.5 h-3.5 border-gray-300">
                                        <span x-text="cs.nama + ' (' + cs.role + ')' "></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-64 sm:h-72 relative">
                    <canvas id="csBarChart" data-cs='@json($mejaCs ?? [])'></canvas>
                </div>
            </div>
        </div>

        <!-- TAB 3: RIWAYAT OPERASIONAL REAL-TIME -->
        <div x-show="activeTab === 'history'" id="history-data-container" data-history='@json($historyBulanan ?? [])' x-data="historyFilterComponent()" class="bg-white border border-[#E0E3E8] rounded-2xl shadow-sm overflow-hidden flex flex-col space-y-4 p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-[#E0E3E8] pb-4 gap-4">
                <div>
                    <h3 class="text-lg font-black text-[#181C20]">Riwayat Operasional Antrean & Omset Harian</h3>
                    <p class="text-xs text-gray-500">Pemantauan volume antrean masuk, tiket diproses/menunggu, tipe layanan, serta total omset.</p>
                </div>
                <span class="text-xs font-bold bg-[#00509E]/10 text-[#00509E] px-3 py-1 rounded-full" x-text="'Total ' + filteredHistory.length + ' Baris Tampil'"></span>
            </div>

            <div class="bg-[#F8F9FA] p-3.5 rounded-xl border border-[#E0E3E8] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jangka Waktu Hari</label>
                    <select x-model="daysLimit" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white cursor-pointer">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="14">14 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="all">Semua Data (30 Hari Full)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Urutkan Berdasarkan</label>
                    <select x-model="sortField" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white cursor-pointer">
                        <option value="raw_date">Tanggal</option>
                        <option value="tiket_masuk">Tiket Masuk</option>
                        <option value="tiket_dilayani">Tiket Dilayani</option>
                        <option value="sudah_diproses">Sudah Diproses (Selesai)</option>
                        <option value="belum_diproses">Belum Diproses (Menunggu)</option>
                        <option value="total_omset">Jumlah Transaksi (Omset)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Arah Urutan</label>
                    <select x-model="sortOrder" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white cursor-pointer">
                        <option value="desc">Terbanyak / Terbaru</option>
                        <option value="asc">Tersedikit / Terlama</option>
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="hideEmpty" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-[#00509E] relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                        <span class="ml-2 text-xs font-bold text-[#181C20]">Sembunyikan Hari Kosong</span>
                    </label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8F9FA] border-b border-[#E0E3E8] text-gray-500 font-black uppercase text-[10px]">
                        <tr>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5 text-center">Masuk</th>
                            <th class="p-3.5 text-center">Dilayani</th>
                            <th class="p-3.5 text-center">Selesai</th>
                            <th class="p-3.5 text-center">Menunggu</th>
                            <th class="p-3.5">Breakdown Tipe Layanan</th>
                            <th class="p-3.5 text-right">Omset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E3E8]">
                        <template x-for="(row, idx) in filteredHistory" :key="idx">
                            <tr class="hover:bg-[#F8F9FA] transition-colors" :class="row.tiket_masuk === 0 ? 'bg-gray-50/50' : ''">
                                <td class="p-3.5 font-bold text-[#181C20]" x-text="row.tanggal"></td>
                                <td class="p-3.5 text-center font-bold text-[#00509E]" x-text="row.tiket_masuk"></td>
                                <td class="p-3.5 text-center font-bold text-indigo-600" x-text="row.tiket_dilayani"></td>
                                <td class="p-3.5 text-center"><span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800" x-text="row.sudah_diproses"></span></td>
                                <td class="p-3.5 text-center"><span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800" x-text="row.belum_diproses"></span></td>
                                <td class="p-3.5">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <template x-if="row.breakdown_layanan && row.breakdown_layanan.length > 0">
                                            <template x-for="(item, bIdx) in row.breakdown_layanan" :key="bIdx">
                                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-[10px] font-semibold border border-gray-200 shadow-sm whitespace-nowrap">
                                                    <span x-text="item.nama"></span>: <strong class="text-[#00509E]" x-text="item.jumlah"></strong>
                                                </span>
                                            </template>
                                        </template>
                                        <template x-if="!row.breakdown_layanan || row.breakdown_layanan.length === 0">
                                            <span class="text-gray-400 italic text-[10px]">-</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="p-3.5 text-right font-black text-[#181C20]" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(row.total_omset)"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredHistory.length === 0">
                            <td colspan="7" class="p-6 text-center text-gray-400 font-bold">Tidak ada data riwayat operasional.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: STAFF & AKUN MONITORING -->
        <div x-show="activeTab === 'staff'" class="space-y-6">
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-center justify-between gap-3">
                <h4 class="font-extrabold text-xs text-[#181C20]">Aksi Pengelolaan Staf & Loket:</h4>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="showModalAkun = true" class="px-3.5 py-2 bg-[#00509E] text-white font-bold text-xs rounded-xl flex items-center gap-1"><span class="material-symbols-outlined text-base">person_add</span> Tambah Akun Baru</button>
                    <button @click="showModalMeja = true" class="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl flex items-center gap-1"><span class="material-symbols-outlined text-base">add_box</span> Tambah Slot Meja</button>
                </div>
            </div>

            <!-- DAFTAR AKUN PENGGUNA -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-[#E0E3E8] pb-3 mb-2">
                    <h3 class="text-xs font-black uppercase text-[#181C20]">Katalog Akun Pengguna Sistem (Admin & CS)</h3>
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Total {{ isset($allUsers) ? $allUsers->count() : 0 }} Akun</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F8F9FA] border-b border-[#E0E3E8] text-gray-500 font-black uppercase text-[10px]">
                            <tr>
                                <th class="p-3">Info Pengguna</th>
                                <th class="p-3">Hak Akses (Role)</th>
                                <th class="p-3">Sesi Loket Realtime</th>
                                <th class="p-3 text-center">Manajemen Akun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E0E3E8]">
                            @forelse($allUsers ?? [] as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#00509E]/10 text-[#00509E] font-black flex items-center justify-center">{{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}</div>
                                        <div>
                                            <p class="font-extrabold text-[#181C20]">{{ $user->nama_lengkap }}</p>
                                            <p class="text-[10px] font-semibold text-gray-500">@ {{ $user->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($user->role === 'cs')
                                        @if($user->nomor_meja)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                                <span>Online (Meja {{ $user->nomor_meja }})</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                <span>Offline</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-[10px] italic">N/A (Admin)</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="selectedUser = @json($user); showModalEditAkun = true" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all" title="Edit Detail Profil">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button @click="selectedUser = @json($user); showModalPassAkun = true" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-lg transition-all" title="Ganti Password">
                                            <span class="material-symbols-outlined text-base">key</span>
                                        </button>
                                        <form action="{{ route('admin.staff.force_logout', $user->id) }}" method="POST" onsubmit="return confirm('Tendang/lepas lokasi meja untuk akun {{ $user->nama_lengkap }}?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-all shadow-sm flex items-center justify-center" title="Force Logout / Lepas Sesi">
                                                <span class="material-symbols-outlined text-base">power_settings_new</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.staff.delete', $user->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Yakin menghapus akun {{ $user->nama_lengkap }} secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-all shadow-sm flex items-center justify-center" title="Hapus Akun Permanen">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-400 font-bold">Belum ada akun di sistem.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KATALOG MEJA FISIK -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3 mt-4">
                <h3 class="text-xs font-black uppercase text-[#181C20] border-b border-[#E0E3E8] pb-3">Katalog Ketersediaan Slot Meja Loket Fisik</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pt-2">
                    @forelse($masterMejas ?? [] as $meja)
                        <div class="p-3.5 border rounded-xl flex items-center justify-between bg-[#F8F9FA] border-[#E0E3E8]">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-[#00509E]/10 text-[#00509E] font-black text-[10px] flex items-center justify-center">M{{ $meja->nomor_meja }}</span>
                                    <span class="font-extrabold text-xs text-[#181C20]">{{ $meja->nama_meja }}</span>
                                </div>
                                <span class="text-[9px] font-black uppercase mt-1 inline-block {{ $meja->is_available ? 'text-emerald-600' : 'text-rose-600' }}">{{ $meja->is_available ? 'Tersedia' : 'Nonaktif' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <form action="{{ route('admin.meja.toggle', $meja->id) }}" method="POST">@csrf <button type="submit" class="p-1.5 {{ $meja->is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }} rounded-lg text-xs"><span class="material-symbols-outlined text-sm">power_settings_new</span></button></form>
                                <form action="{{ route('admin.meja.delete', $meja->id) }}" method="POST" onsubmit="return confirm('Hapus slot meja ini?')">@csrf @method('DELETE') <button type="submit" class="p-1.5 bg-rose-100 text-rose-600 rounded-lg"><span class="material-symbols-outlined text-sm">delete</span></button></form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-4 text-center text-gray-400 font-bold text-xs">Belum ada slot meja fisik terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 5: KELOLA LAYANAN -->
        <div x-show="activeTab === 'master_layanan'" class="space-y-6">
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-sm text-[#181C20]">Pengaturan Parent-Child Layanan & Sub-Layanan</h3>
                    <p class="text-xs text-gray-500">Klik salah satu Kategori Utama di sebelah kiri untuk mengelola Sub-Layanan terkait.</p>
                </div>
                <button @click="showModalLayanan = true" class="px-3.5 py-2 bg-[#00509E] text-white font-bold text-xs rounded-xl flex items-center gap-1">+ Kategori Utama</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- KATEGORI UTAMA -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E0E3E8] shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 bg-[#F8F9FA] border-b flex justify-between items-center"><h4 class="font-black text-xs text-[#00509E] uppercase">Kategori Utama</h4></div>
                    <div class="p-3 space-y-2 max-h-[550px] overflow-y-auto custom-scrollbar">
                        @foreach($layanans as $lay)
                            @php $subCount = optional($lay->subLayanans)->count() ?? 0; @endphp
                            <div @click="selectedLayananId = '{{ $lay->id }}'; selectedLayananNama = '{{ addslashes($lay->nama_layanan) }}'" :class="selectedLayananId == '{{ $lay->id }}' ? 'border-[#00509E] bg-[#00509E]/5 ring-2 ring-[#00509E]/20' : 'border-[#E0E3E8]'" class="p-3 border rounded-xl flex items-center justify-between cursor-pointer">
                                <div><h5 class="font-extrabold text-xs text-[#181C20]">{{ $lay->nama_layanan }}</h5><span class="text-[10px] text-gray-500 font-semibold mt-0.5 block">{{ $subCount }} Sub-Layanan</span></div>
                                <div class="flex items-center gap-1" @click.stop>
                                    <button @click="selectedEditLayanan = @json($lay); showModalEditLayanan = true" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-all" title="Edit Kategori">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <form action="{{ route('admin.layanan.destroy', $lay->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf 
                                        @method('DELETE') 
                                        <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition-all">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- SUB LAYANAN -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E0E3E8] shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 bg-emerald-50/60 border-b flex items-center justify-between">
                        <div><span class="text-[9px] font-black text-emerald-700 uppercase">SUB-LAYANAN UNTUK:</span><h4 class="font-black text-sm text-[#181C20]" x-text="selectedLayananNama || 'Pilih Kategori'"></h4></div>
                        <button x-show="selectedLayananId" @click="showModalSubLayanan = true" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">+ Tambah Sub-Layanan</button>
                    </div>
                    <div class="p-4 min-h-[300px] max-h-[550px] overflow-y-auto custom-scrollbar">
                        @foreach($layanans as $lay)
                            @php $subList = optional($lay->subLayanans)->all() ?? []; @endphp
                            <div x-show="selectedLayananId == '{{ $lay->id }}'" class="space-y-2">
                                @forelse($subList as $sub)
                                    <div class="p-3 bg-[#F8F9FA] border border-[#E0E3E8] rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-emerald-600 text-base">subdirectory_arrow_right</span><span class="font-extrabold text-xs text-gray-800">{{ $sub->nama_sub_layanan }}</span></div>
                                        <div class="flex items-center gap-1">
                                            <button @click="selectedEditSubLayanan = @json($sub); showModalEditSubLayanan = true" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-all" title="Edit Sub-Layanan">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </button>
                                            <form action="{{ route('admin.sub_layanan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus sub-layanan?')">
                                                @csrf 
                                                @method('DELETE') 
                                                <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition-all">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-gray-400 font-bold text-xs">Belum ada sub-layanan.</div>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- MODAL POPUP -->
    <div x-show="showModalAkun" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalAkun = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-[#00509E]">person_add</span> Tambah Akun Baru</h3>
                <button type="button" @click="showModalAkun = false" class="text-gray-400 hover:text-black"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div><label class="font-bold block mb-1">Nama Lengkap</label><input type="text" name="nama_lengkap" required class="w-full p-2.5 border rounded-xl"></div>
                <div><label class="font-bold block mb-1">Username Login</label><input type="text" name="username" required class="w-full p-2.5 border rounded-xl"></div>
                <div>
                    <label class="font-bold block mb-1">Hak Akses (Role)</label>
                    <select name="role" required class="w-full p-2.5 border rounded-xl font-bold bg-gray-50">
                        <option value="cs">Petugas CS (Customer Service)</option>
                        <option value="admin">Administrator (Super Admin)</option>
                    </select>
                </div>
                <div><label class="font-bold block mb-1">Password Awal</label><input type="password" name="password" required minlength="6" class="w-full p-2.5 border rounded-xl" placeholder="Minimal 6 karakter"></div>
                
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showModalAkun = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showModalEditAkun" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalEditAkun = false">
            <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-[#00509E]">edit_square</span> Edit Detail Akun</h3>
            <template x-if="selectedUser">
                <form :action="`{{ url('/admin/staff/update') }}/${selectedUser.id}`" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div><label class="font-bold block mb-1">Nama Lengkap</label><input type="text" name="nama_lengkap" :value="selectedUser.nama_lengkap" required class="w-full p-2.5 border rounded-xl"></div>
                    <div><label class="font-bold block mb-1">Username</label><input type="text" name="username" :value="selectedUser.username" required class="w-full p-2.5 border rounded-xl"></div>
                    <div>
                        <label class="font-bold block mb-1">Role</label>
                        <select name="role" required class="w-full p-2.5 border rounded-xl font-bold">
                            <option value="cs" :selected="selectedUser.role === 'cs'">CS</option>
                            <option value="admin" :selected="selectedUser.role === 'admin'">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold block mb-1">Password Baru (Opsional)</label>
                        <input type="password" name="password" minlength="6" class="w-full p-2.5 border rounded-xl" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                    <div class="flex justify-end gap-2 pt-3"><button type="button" @click="showModalEditAkun = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Update Profil</button></div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="showModalPassAkun" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalPassAkun = false">
            <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-amber-500">lock_reset</span> Ganti Password</h3>
            <template x-if="selectedUser">
                <form :action="`{{ url('/admin/staff/password') }}/${selectedUser.id}`" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <p class="text-gray-500 mb-2">Reset kata sandi untuk akun: <strong class="text-gray-800" x-text="selectedUser.nama_lengkap"></strong></p>
                    <div><label class="font-bold block mb-1">Password Baru</label><input type="password" name="password" required minlength="6" class="w-full p-2.5 border rounded-xl" placeholder="Minimal 6 Karakter"></div>
                    <div class="flex justify-end gap-2 pt-3"><button type="button" @click="showModalPassAkun = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-amber-500 text-white font-bold rounded-xl shadow-sm">Ubah Password</button></div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="showModalEditLayanan" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalEditLayanan = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-[#00509E]">edit_note</span> Edit Kategori Utama</h3>
                <button type="button" @click="showModalEditLayanan = false" class="text-gray-400 hover:text-black"><span class="material-symbols-outlined">close</span></button>
            </div>
            <template x-if="selectedEditLayanan">
                <form :action="`{{ url('/admin/layanan/update') }}/${selectedEditLayanan.id}`" method="POST" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="font-bold block mb-1">Nama Kategori Utama</label>
                        <input type="text" name="nama_layanan" :value="selectedEditLayanan.nama_layanan" required class="w-full p-2.5 border rounded-xl font-bold">
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="showModalEditLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="showModalEditSubLayanan" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalEditSubLayanan = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-emerald-600">edit_note</span> Edit Sub-Layanan</h3>
                <button type="button" @click="showModalEditSubLayanan = false" class="text-gray-400 hover:text-black"><span class="material-symbols-outlined">close</span></button>
            </div>
            <template x-if="selectedEditSubLayanan">
                <form :action="`{{ url('/admin/sub-layanan/update') }}/${selectedEditSubLayanan.id}`" method="POST" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="font-bold block mb-1">Nama Sub-Layanan</label>
                        <input type="text" name="nama_sub_layanan" :value="selectedEditSubLayanan.nama_sub_layanan" required class="w-full p-2.5 border rounded-xl font-bold">
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" @click="showModalEditSubLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="showModalDetail" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalDetail = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">confirmation_number</span>
                    Detail Tiket & Evaluasi Layanan
                </h3>
                <button @click="showModalDetail = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
            </div>

            <template x-if="selectedTiket">
                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Nomor Antrean</span>
                            <span class="font-mono text-base font-black text-[#00509E]" x-text="selectedTiket.nomor_antrian"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Status Operasional</span>
                            <span class="font-bold text-xs uppercase" x-text="selectedTiket.status"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Pelanggan</span>
                            <span class="font-bold text-gray-800" x-text="selectedTiket.pelanggan ? selectedTiket.pelanggan.nama : '-'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Petugas CS / Loket</span>
                            <span class="font-bold text-gray-800" x-text="selectedTiket.cs ? selectedTiket.cs.nama_lengkap : '-'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl">
                            <span class="text-[10px] text-blue-600 font-bold uppercase block">Waktu Tunggu Pelanggan</span>
                            <span class="font-bold text-blue-900" x-text="selectedTiket.waktu_tunggu ? (Math.floor(selectedTiket.waktu_tunggu/60) + 'm ' + (selectedTiket.waktu_tunggu%60) + 's') : '-'"></span>
                        </div>
                        <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                            <span class="text-[10px] text-emerald-600 font-bold uppercase block">Durasi Layanan CS</span>
                            <span class="font-bold text-emerald-900" x-text="selectedTiket.waktu_layanan ? (Math.floor(selectedTiket.waktu_layanan/60) + 'm ' + (selectedTiket.waktu_layanan%60) + 's') : '-'"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50/60 border border-amber-200 rounded-xl">
                        <span class="text-[10px] text-amber-700 font-bold uppercase block mb-1">Catatan CS & Ringkasan Solusi</span>
                        <p class="text-gray-800 italic" x-text="selectedTiket.catatan_cs || selectedTiket.ringkasan_solusi || 'Tidak ada catatan khusus.'"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showModalPdf" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalPdf = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#EE2E24]">picture_as_pdf</span>
                    Kustomisasi Laporan PDF
                </h3>
                <button @click="showModalPdf = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
            </div>

            <form action="{{ route('admin.pdf') }}" method="GET" target="_blank" class="space-y-4 text-xs" onsubmit="return preparePdfSubmit(event)">
                <div class="space-y-2">
                    <label class="font-bold text-gray-700 block">1. Pilih Periode Waktu Laporan</label>
                    <select name="period" id="pdf_period_select" onchange="togglePdfCustomDates(this.value)" class="w-full p-2.5 border border-gray-300 rounded-xl font-bold text-gray-700">
                        <option value="today">Hari Ini (Today)</option>
                        <option value="wtd">Minggu Ini (WTD)</option>
                        <option value="mtd" selected>Bulan Ini (MTD)</option>
                        <option value="last_30">30 Hari Terakhir</option>
                        <option value="ytd">Tahun Ini (YTD)</option>
                        <option value="custom">Rentang Tanggal (Date to Date)...</option>
                    </select>

                    <div id="pdf_custom_dates_container" class="hidden grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Dari Tanggal (Start Date)</label>
                            <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Sampai Tanggal (End Date)</label>
                            <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">2. Filter Kategori Layanan</label>
                    <select name="layanan_id" class="w-full p-2.5 border border-gray-300 rounded-xl font-semibold">
                        <option value="">Semua Kategori Layanan</option>
                        @foreach($layanans as$lay)
                            <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="font-bold text-gray-700 block">3. Opsi Komponen Laporan</label>
                    <div class="space-y-2.5 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="inc_summary" value="1" checked class="rounded text-[#00509E] w-4 h-4">
                            <span class="font-bold text-gray-800">Sertakan Executive Summary & Analisis Otomatis</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="inc_charts" value="1" checked class="rounded text-[#00509E] w-4 h-4">
                            <span class="font-bold text-gray-800">Sertakan Visualisasi Grafik Analitik (Line & Pie Chart)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="inc_sla_charts" value="1" checked class="rounded text-[#00509E] w-4 h-4">
                            <span class="font-bold text-gray-800">Sertakan Tren Waktu Tunggu vs Durasi Konsul CS</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="inc_cs_chart" value="1" checked class="rounded text-[#00509E] w-4 h-4">
                            <span class="font-bold text-gray-800">Sertakan Performa Produktivitas Staf CS</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="inc_table" value="1" checked class="rounded text-[#00509E] w-4 h-4">
                            <span class="font-bold text-gray-800">Sertakan Tabel Detail Rincian Tiket Antrean</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="showModalPdf = false" class="px-4 py-2 bg-gray-100 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#EE2E24] hover:bg-[#CE1111] text-white font-bold rounded-xl shadow-md flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">print</span> Buka & Cetak PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showModalMeja" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalMeja = false">
            <h3 class="text-base font-black flex items-center gap-1.5"><span class="material-symbols-outlined text-emerald-600">add_box</span> Tambah Slot Meja</h3>
            <form action="{{ route('admin.meja.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="flex justify-end gap-2 pt-3"><button type="button" @click="showModalMeja = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl">Tambah Meja Otomatis</button></div>
            </form>
        </div>
    </div>

    <div x-show="showModalLayanan" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalLayanan = false">
            <h3 class="text-base font-black">Tambah Kategori Layanan Utama</h3>
            <form action="{{ route('admin.layanan.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div><label class="font-bold block mb-1">Nama Kategori</label><input type="text" name="nama_layanan" required class="w-full p-2.5 border rounded-xl"></div>
                <div class="flex justify-end gap-2 pt-3"><button type="button" @click="showModalLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Simpan</button></div>
            </form>
        </div>
    </div>

    <div x-show="showModalSubLayanan" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalSubLayanan = false">
            <h3 class="text-base font-black">Tambah Sub-Layanan</h3>
            <form action="{{ route('admin.sub_layanan.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="layanan_id" :value="selectedLayananId">
                <div><label class="font-bold block mb-1">Kategori Utama</label><input type="text" readonly :value="selectedLayananNama" class="w-full p-2.5 bg-gray-100 border rounded-xl font-bold text-[#00509E]"></div>
                <div><label class="font-bold block mb-1">Nama Sub-Layanan</label><input type="text" name="nama_sub_layanan" required class="w-full p-2.5 border rounded-xl"></div>
                <div class="flex justify-end gap-2 pt-3"><button type="button" @click="showModalSubLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl">Simpan Sub-Layanan</button></div>
            </form>
        </div>
    </div>

    <!-- SCRIPT INJECTION -->
    <script>
        var serverPeriod = "{{ request('period', 'all') }}";
    </script>
</body>
</html>
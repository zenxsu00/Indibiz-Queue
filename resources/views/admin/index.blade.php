<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
          activeTab: 'operations', 
          mobileMenu: false, 
          showModalCS: false, 
          showModalMeja: false, 
          showModalLayanan: false,
          showModalSubLayanan: false,
          showModalDetail: false,
          showModalPdf: false,
          selectedTiket: null,
          selectedPeriod: '{{ request('period', 'all') }}',
          selectedLayananId: '{{ $layanans->first()->id ?? '' }}',
          selectedLayananNama: '{{ $layanans->first()->nama_layanan ?? '' }}'
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
                <button @click="activeTab = 'operations'; mobileMenu = false" 
                    :class="activeTab === 'operations' ? 'bg-white/20 text-white' : 'text-white/70'"
                    class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3">
                    <span class="material-symbols-outlined">dashboard</span> Operations
                </button>
                <button @click="activeTab = 'analytics'; mobileMenu = false" 
                    :class="activeTab === 'analytics' ? 'bg-white/20 text-white' : 'text-white/70'"
                    class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3">
                    <span class="material-symbols-outlined">analytics</span> Analitik & Grafik
                </button>
                <button @click="activeTab = 'history'; mobileMenu = false" 
                    :class="activeTab === 'history' ? 'bg-white/20 text-white' : 'text-white/70'"
                    class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3">
                    <span class="material-symbols-outlined">history</span> Riwayat Bulanan
                </button>
                <button @click="activeTab = 'staff'; mobileMenu = false" 
                    :class="activeTab === 'staff' ? 'bg-white/20 text-white' : 'text-white/70'"
                    class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3">
                    <span class="material-symbols-outlined">badge</span> Staff & Meja Monitor
                </button>
                <button @click="activeTab = 'master_layanan'; mobileMenu = false" 
                    :class="activeTab === 'master_layanan' ? 'bg-white/20 text-white' : 'text-white/70'"
                    class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3">
                    <span class="material-symbols-outlined">category</span> Kelola Layanan
                </button>

                <a href="{{ route('cs.select-meja') }}" class="w-full flex items-center p-3 font-bold rounded-xl text-xs gap-3 bg-amber-500 text-white shadow-sm mt-4">
                    <span class="material-symbols-outlined">swap_horiz</span> Switch ke CS Console
                </a>
            </nav>
            <form action="{{ route('logout') }}" method="POST" class="pt-2 border-t border-white/10">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-[#EE2E24] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">logout</span> Keluar Sistem
                </button>
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
                <button @click="activeTab = 'operations'" 
                    :class="activeTab === 'operations' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    <span class="material-symbols-outlined mr-3 text-lg">dashboard</span> 
                    <span>Operations</span>
                </button>

                <button @click="activeTab = 'analytics'" 
                    :class="activeTab === 'analytics' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    <span class="material-symbols-outlined mr-3 text-lg">analytics</span> 
                    <span>Analitik & Grafik</span>
                </button>

                <button @click="activeTab = 'history'" 
                    :class="activeTab === 'history' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    <span class="material-symbols-outlined mr-3 text-lg">history</span> 
                    <span>Riwayat Bulanan</span>
                </button>

                <button @click="activeTab = 'staff'" 
                    :class="activeTab === 'staff' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    <span class="material-symbols-outlined mr-3 text-lg">badge</span> 
                    <span>Staff & Meja Monitor</span>
                </button>

                <button @click="activeTab = 'master_layanan'" 
                    :class="activeTab === 'master_layanan' ? 'bg-white/20 text-white shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'"
                    class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    <span class="material-symbols-outlined mr-3 text-lg">category</span> 
                    <span>Kelola Layanan</span>
                </button>

                <div class="pt-4 border-t border-white/10 mt-3">
                    <a href="{{ route('cs.select-meja') }}" class="w-full flex items-center px-3.5 py-2.5 text-xs font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all shadow-sm">
                        <span class="material-symbols-outlined mr-2.5 text-lg">swap_horiz</span>
                        <span>Switch ke CS Console</span>
                    </a>
                </div>
            </nav>
        </div>

        <div class="p-3.5 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-[#EE2E24] hover:bg-[#CE1111] text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Keluar Sistem</span>
                </button>
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
                    (activeTab === 'history' ? 'Riwayat & Rekap SLA / Omset Bulanan' : 
                    (activeTab === 'staff' ? 'Monitoring Staf CS & Slot Meja' : 'Kelola Master Layanan & Sub-Layanan')))
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

        <!-- FORM FILTER SIMPEL -->
        <form x-show="activeTab === 'operations'" action="{{ route('admin.dashboard') }}" method="GET" class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-center gap-3 text-xs">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Periode Waktu</label>
                <select name="period" x-model="selectedPeriod" @change="$el.form.submit()" class="w-full border border-gray-300 bg-gray-50 rounded-xl p-2 font-bold text-gray-700 focus:border-[#00509E] focus:ring-0 cursor-pointer">
                    <option value="all">Semua Waktu (All Time)</option>
                    <option value="today">Hari Ini (Today)</option>
                    <option value="wtd">Minggu Ini (WTD)</option>
                    <option value="mtd">Bulan Ini (MTD)</option>
                    <option value="last_30">30 Hari Terakhir</option>
                    <option value="ytd">Tahun Ini (YTD)</option>
                    <option value="custom">Kustom Tanggal...</option>
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
                <div class="self-end pb-0.5">
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
                    <p class="text-xs font-semibold text-gray-800">
                        {!! preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $analisisOtomatis['status_tiket'] ?? '') !!}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $analisisOtomatis['badge_tiket'] ?? 'bg-gray-100' }}">
                    Auto Insight
                </span>
            </div>

            <!-- METRIK CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-[#00509E]"></div>
                    <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider block mb-1.5">Total Antrean</span>
                    <span class="text-2xl lg:text-3xl font-black text-[#181C20]">{{ number_format($totalHariIni) }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>
                    <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider block mb-1.5">Sedang Menunggu</span>
                    <span class="text-2xl lg:text-3xl font-black text-[#181C20]">{{ number_format($menunggu) }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-blue-500"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Avg Waktu Tunggu</span>
                        <span class="text-[8px] bg-blue-100 text-blue-800 font-black px-1 py-0.5 rounded">TV Display</span>
                    </div>
                    <span class="text-2xl font-black text-blue-600">{{ $avgWaktuTungguText }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[9px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Avg Durasi CS</span>
                        <span class="text-[8px] bg-emerald-100 text-emerald-800 font-black px-1 py-0.5 rounded">SLA CS</span>
                    </div>
                    <span class="text-2xl font-black text-emerald-600">{{ $avgDurasiLayananText }}</span>
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
                                <th class="p-3">CS / Loket</th>
                                <th class="p-3">Status Kurasi</th>
                                <th class="p-3">Waktu Ambil</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Metode</th>
                                <th class="p-3 text-right">Nominal</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E0E3E8]">
                            @forelse($allFilteredTickets as $tiket)
                                <tr class="hover:bg-[#F8F9FA] transition-colors">
                                    <td class="p-3 font-mono font-bold text-[#00509E]">{{ $tiket->nomor_antrian }}</td>
                                    <td class="p-3 font-bold">
                                        {{ $tiket->pelanggan->nama ?? '-' }} 
                                        <br><span class="text-[10px] text-gray-400 font-normal">{{ $tiket->pelanggan->no_hp ?? '-' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="bg-blue-50 text-[#00509E] px-2 py-0.5 rounded font-bold block mb-0.5">{{ $tiket->layanan->nama_layanan ?? '-' }}</span>
                                        <span class="text-[10px] text-gray-500 italic">{{ $tiket->subLayanan->nama_sub_layanan ?? 'Tanpa Sub-Layanan' }}</span>
                                    </td>
                                    <td class="p-3 font-bold">{{ $tiket->cs ? $tiket->cs->nama_lengkap . ' (M'.$tiket->cs->nomor_meja.')' : '-' }}</td>
                                    <td class="p-3">
                                        <span class="text-[9px] px-2 py-0.5 rounded-full font-black {{ $tiket->is_curated ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $tiket->is_curated ? '✓ Selesai Loket' : '🛠 Lapangan' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-500">{{ \Carbon\Carbon::parse($tiket->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tiket->status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : ($tiket->status === 'Batal' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $tiket->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-semibold">{{ $tiket->metode_pembayaran ?? 'Tanpa Transaksi' }}</td>
                                    <td class="p-3 text-right font-black text-[#181C20]">Rp {{ number_format($tiket->nominal_pembayaran, 0, ',', '.') }}</td>
                                    <td class="p-3 text-center">
                                        <button @click="selectedTiket = {{ json_encode($tiket) }}; showModalDetail = true" class="px-2.5 py-1 bg-[#00509E]/10 text-[#00509E] hover:bg-[#00509E] hover:text-white rounded-lg font-bold text-[10px] transition-all cursor-pointer">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="p-6 text-center text-gray-400 font-bold">Tidak ada data antrean pada rentang waktu ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: ANALITIK LAYANAN -->
        <div x-show="activeTab === 'analytics'" x-data="chartFilterComponent()" class="space-y-6">
            
            <!-- LINE CHART TREN -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                    <h3 class="text-sm font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#00509E]">show_chart</span>
                        1. Grafik Tren Pendaftaran vs Layanan Selesai
                    </h3>

                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-bold">
                        <button @click="switchChartPeriod('wtd')" :class="chartPeriod === 'wtd' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">WTD</button>
                        <button @click="switchChartPeriod('mtd')" :class="chartPeriod === 'mtd' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">MTD</button>
                        <button @click="switchChartPeriod('mtm')" :class="chartPeriod === 'mtm' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">MTM</button>
                        <button @click="switchChartPeriod('last30')" :class="chartPeriod === 'last30' ? 'bg-[#00509E] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-lg transition-all cursor-pointer">30 Hari</button>
                    </div>
                </div>

                <div class="h-64 sm:h-80 relative">
                    <canvas id="queueChart" data-chart-sets='{{ json_encode($chartDataSets ?? []) }}'></canvas>
                </div>
                <p class="text-[11px] text-gray-500 italic mt-3 border-t pt-2">
                    💡 **Penjelasan:** Menampilkan fluktuasi harian antara tiket yang masuk dibandingkan tiket yang berhasil diselesaikan oleh staf loket CS.
                </p>
            </div>

            <!-- GRID 2 KOLOM: PIE & DONUT CHART -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- CHART 2: PIE KEPADATAN KATEOGRI -->
                <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                    <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2 border-b pb-2">
                        <span class="material-symbols-outlined text-[#00509E]">pie_chart</span>
                        2. Proporsi Kepadatan Kategori Layanan
                    </h3>
                    <div class="h-56 relative flex justify-center">
                        <canvas id="categoryPieChart" data-dist='{{ json_encode($distribusiLayanan ?? []) }}'></canvas>
                    </div>
                    <p class="text-[10px] text-gray-500 italic border-t pt-2">
                        💡 **Penjelasan:** Mengukur kategori mana yang paling mendominasi beban kerja loket pelayanan Indibiz.
                    </p>
                </div>

                <!-- CHART 3: DONUT RASIO STATUS -->
                <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                    <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2 border-b pb-2">
                        <span class="material-symbols-outlined text-emerald-600">donut_large</span>
                        3. Rasio Status Penyelesaian Tiket
                    </h3>
                    <div class="h-56 relative flex justify-center">
                        <canvas id="statusDonutChart" data-ratio='{{ json_encode($statusRatioData ?? []) }}'></canvas>
                    </div>
                    <p class="text-[10px] text-gray-500 italic border-t pt-2">
                        💡 **Penjelasan:** Mengidentifikasi tingkat keberhasilan (Selesai), beban menunggu, serta persentase pembatalan (*drop-out*).
                    </p>
                </div>
            </div>

            <!-- CHART 4: BAR PERFORMA CS -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                <h3 class="text-xs font-black text-[#181C20] uppercase tracking-wider flex items-center gap-2 border-b pb-2">
                    <span class="material-symbols-outlined text-purple-600">bar_chart</span>
                    4. Performa Produktivitas Staf CS per Meja
                </h3>
                <div class="h-60 relative">
                    <canvas id="csBarChart" data-cs='{{ json_encode($mejaCs ?? []) }}'></canvas>
                </div>
                <p class="text-[10px] text-gray-500 italic border-t pt-2">
                    💡 **Penjelasan:** Menilai kontribusi jumlah tiket yang berhasil diselesaikan oleh masing-masing meja CS pada rentang waktu terpilih.
                </p>
            </div>
        </div>

        <!-- TAB 3: RIWAYAT BULANAN -->
        <div x-show="activeTab === 'history'" id="history-data-container" data-history='{{ json_encode($historyBulanan) }}' x-data="historyFilterComponent()" class="bg-white border border-[#E0E3E8] rounded-2xl shadow-sm overflow-hidden flex flex-col space-y-4 p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-[#E0E3E8] pb-4 gap-4">
                <div>
                    <h3 class="text-lg font-black text-[#181C20]">Riwayat Kinerja, SLA & Omset Harian</h3>
                    <p class="text-xs text-gray-500">Rekapitulasi data transaksi dan performa layanan per hari.</p>
                </div>
                <span class="text-xs font-bold bg-[#00509E]/10 text-[#00509E] px-3 py-1 rounded-full" x-text="'Total ' + filteredHistory.length + ' Baris Tampil'"></span>
            </div>

            <div class="bg-[#F8F9FA] p-3.5 rounded-xl border border-[#E0E3E8] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jangka Waktu Hari</label>
                    <select x-model="daysLimit" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="14">14 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="all">Semua Data (30 Hari Full)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Urutkan Berdasarkan</label>
                    <select x-model="sortField" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white">
                        <option value="tanggal">Tanggal</option>
                        <option value="total_tiket">Total Tiket</option>
                        <option value="selesai">Tiket Selesai</option>
                        <option value="batal">Tiket Batal</option>
                        <option value="total_omset">Total Omset</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Arah Urutan</label>
                    <select x-model="sortOrder" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white">
                        <option value="desc">Terbanyak / Terbaru</option>
                        <option value="asc">Tersedikit / Terlama</option>
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="hideEmpty" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-[#00509E] relative after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                        <span class="ml-2 text-xs font-bold text-[#181C20]">Sembunyikan Kosong</span>
                    </label>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8F9FA] border-b border-[#E0E3E8] text-gray-500 font-black uppercase text-[10px]">
                        <tr>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5 text-center">Total Tiket</th>
                            <th class="p-3.5 text-center">Selesai</th>
                            <th class="p-3.5 text-center">Batal</th>
                            <th class="p-3.5 text-center">Rata-Rata SLA</th>
                            <th class="p-3.5 text-right">Total Omset Loket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E3E8]">
                        <template x-for="(row, idx) in filteredHistory" :key="idx">
                            <tr class="hover:bg-[#F8F9FA] transition-colors" :class="row.total_tiket === 0 ? 'bg-gray-50/50' : ''">
                                <td class="p-3.5 font-bold text-[#181C20]" x-text="row.tanggal"></td>
                                <td class="p-3.5 text-center font-bold" x-text="row.total_tiket"></td>
                                <td class="p-3.5 text-center font-bold text-emerald-600" x-text="row.selesai"></td>
                                <td class="p-3.5 text-center font-bold text-rose-600" x-text="row.batal"></td>
                                <td class="p-3.5 text-center font-mono font-extrabold text-[#00509E]" x-text="row.avg_sla"></td>
                                <td class="p-3.5 text-right font-black text-[#181C20]" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(row.total_omset)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: STAFF MONITORING -->
        <div x-show="activeTab === 'staff'" class="space-y-6">
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-center justify-between gap-3">
                <h4 class="font-extrabold text-xs text-[#181C20]">Aksi Pengelolaan Staf & Loket:</h4>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="showModalCS = true" class="px-3.5 py-2 bg-[#00509E] text-white font-bold text-xs rounded-xl flex items-center gap-1"><span class="material-symbols-outlined text-base">person_add</span> Tambah CS Baru</button>
                    <button @click="showModalMeja = true" class="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl flex items-center gap-1"><span class="material-symbols-outlined text-base">add_box</span> Tambah Slot Meja</button>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                <h3 class="text-xs font-black uppercase text-[#181C20]">Katalog Ketersediaan Meja Loket</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
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
                        <div class="col-span-full p-4 text-center text-gray-400 font-bold text-xs">Belum ada slot meja fisik.</div>
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
                <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E0E3E8] shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 bg-[#F8F9FA] border-b flex justify-between items-center"><h4 class="font-black text-xs text-[#00509E] uppercase">Kategori Utama</h4></div>
                    <div class="p-3 space-y-2 max-h-[550px] overflow-y-auto custom-scrollbar">
                        @foreach($layanans as $lay)
                            <div @click="selectedLayananId = '{{ $lay->id }}'; selectedLayananNama = '{{ addslashes($lay->nama_layanan) }}'" :class="selectedLayananId == '{{ $lay->id }}' ? 'border-[#00509E] bg-[#00509E]/5 ring-2 ring-[#00509E]/20' : 'border-[#E0E3E8]'" class="p-3 border rounded-xl flex items-center justify-between cursor-pointer">
                                <div><h5 class="font-extrabold text-xs text-[#181C20]">{{ $lay->nama_layanan }}</h5><span class="text-[10px] text-gray-500 font-semibold mt-0.5 block">{{ $lay->subLayanans->count() }} Sub-Layanan</span></div>
                                <form action="{{ route('admin.layanan.destroy', $lay->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE') <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 rounded-lg"><span class="material-symbols-outlined text-sm">delete</span></button></form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E0E3E8] shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 bg-emerald-50/60 border-b flex items-center justify-between">
                        <div><span class="text-[9px] font-black text-emerald-700 uppercase">SUB-LAYANAN UNTUK:</span><h4 class="font-black text-sm text-[#181C20]" x-text="selectedLayananNama || 'Pilih Kategori'"></h4></div>
                        <button x-show="selectedLayananId" @click="showModalSubLayanan = true" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">+ Tambah Sub-Layanan</button>
                    </div>
                    <div class="p-4 min-h-[300px] max-h-[550px] overflow-y-auto custom-scrollbar">
                        @foreach($layanans as $lay)
                            <div x-show="selectedLayananId == '{{ $lay->id }}'" class="space-y-2">
                                @forelse($lay->subLayanans as $sub)
                                    <div class="p-3 bg-[#F8F9FA] border border-[#E0E3E8] rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-emerald-600 text-base">subdirectory_arrow_right</span><span class="font-extrabold text-xs text-gray-800">{{ $sub->nama_sub_layanan }}</span></div>
                                        <form action="{{ route('admin.sub_layanan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus sub-layanan?')">@csrf @method('DELETE') <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 rounded-lg"><span class="material-symbols-outlined text-sm">delete</span></button></form>
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

    <!-- MODAL POPUP: KUSTOMISASI CETAK PDF INTERAKTIF -->
    <div x-show="showModalPdf" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalPdf = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#EE2E24]">picture_as_pdf</span>
                    Kustomisasi Laporan PDF
                </h3>
                <button @click="showModalPdf = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
            </div>

            <form action="{{ route('admin.pdf') }}" method="GET" target="_blank" class="space-y-4 text-xs">
                <!-- PRESET PERIODE TANGGAL -->
                <div class="space-y-2">
                    <label class="font-bold text-gray-700 block">1. Pilih Periode Waktu Laporan</label>
                    <select name="period" id="pdf_period_select" onchange="togglePdfCustomDates(this.value)" class="w-full p-2.5 border border-gray-300 rounded-xl font-bold text-gray-700">
                        <option value="today">Hari Ini (Today)</option>
                        <option value="wtd">Minggu Ini (WTD)</option>
                        <option value="mtd" selected>Bulan Ini (MTD)</option>
                        <option value="last_30">30 Hari Terakhir</option>
                        <option value="ytd">Tahun Ini (YTD)</option>
                        <option value="custom">Kustom Tanggal Spesifik...</option>
                    </select>

                    <div id="pdf_custom_dates_container" class="hidden grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- FILTER KATEGORI -->
                <div>
                    <label class="font-bold text-gray-700 block mb-1">2. Filter Kategori Layanan</label>
                    <select name="layanan_id" class="w-full p-2.5 border border-gray-300 rounded-xl font-semibold">
                        <option value="">Semua Kategori Layanan</option>
                        @foreach($layanans as $lay)
                            <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- KOMPONEN YANG INGIN DICETAK -->
                <div class="space-y-2">
                    <label class="font-bold text-gray-700 block">3. Komponen Dokumen PDF</label>
                    <div class="space-y-2 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="inc_summary" value="1" checked class="rounded text-[#00509E]">
                            <span class="font-bold text-gray-800">Sertakan Executive Summary & Analisis Otomatis</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="inc_charts" value="1" checked class="rounded text-[#00509E]">
                            <span class="font-bold text-gray-800">Sertakan Visualisasi Grafik Analitik (Line & Pie)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="inc_table" value="1" checked class="rounded text-[#00509E]">
                            <span class="font-bold text-gray-800">Sertakan Tabel Detail Rincian Tiket</span>
                        </label>
                    </div>
                </div>

                <!-- HIDDEN BASE64 CHART IMAGE DATA -->
                <input type="hidden" name="chart_line_base64" id="chart_line_base64">
                <input type="hidden" name="chart_pie_base64" id="chart_pie_base64">

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="showModalPdf = false" class="px-4 py-2 bg-gray-100 font-bold rounded-xl">Batal</button>
                    <button type="submit" onclick="preparePdfCharts()" class="px-5 py-2 bg-[#EE2E24] hover:bg-[#CE1111] text-white font-bold rounded-xl shadow-md flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">print</span> Generate & Cetak PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CS & MEJA & DETAIL TIKET -->
    <div x-show="showModalCS" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalCS = false">
            <h3 class="text-base font-black">Tambah Petugas CS Baru</h3>
            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div><label class="font-bold block mb-1">Nama Lengkap</label><input type="text" name="nama_lengkap" required class="w-full p-2.5 border rounded-xl"></div>
                <div><label class="font-bold block mb-1">Username</label><input type="text" name="username" required class="w-full p-2.5 border rounded-xl"></div>
                <div><label class="font-bold block mb-1">Password</label><input type="password" name="password" required class="w-full p-2.5 border rounded-xl"></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showModalCS = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Simpan</button></div>
            </form>
        </div>
    </div>

    <div x-show="showModalLayanan" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4" @click.away="showModalLayanan = false">
            <h3 class="text-base font-black">Tambah Kategori Layanan Utama</h3>
            <form action="{{ route('admin.layanan.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div><label class="font-bold block mb-1">Nama Kategori</label><input type="text" name="nama_layanan" required class="w-full p-2.5 border rounded-xl"></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showModalLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold rounded-xl">Simpan</button></div>
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
                <div class="flex justify-end gap-2"><button type="button" @click="showModalSubLayanan = false" class="px-4 py-2 bg-gray-100 rounded-xl font-bold">Batal</button><button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl">Simpan Sub-Layanan</button></div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS ENGINES -->
    <script>
        var globalQueueChart = null;
        var globalPieChart = null;
        var globalDonutChart = null;
        var globalBarChart = null;

        function togglePdfCustomDates(val) {
            const container = document.getElementById('pdf_custom_dates_container');
            if (val === 'custom') {
                container.classList.remove('hidden');
                container.classList.add('grid');
            } else {
                container.classList.add('hidden');
                container.classList.remove('grid');
            }
        }

        function preparePdfCharts() {
            const lineCanvas = document.getElementById('queueChart');
            const pieCanvas = document.getElementById('categoryPieChart');

            if (lineCanvas) {
                document.getElementById('chart_line_base64').value = lineCanvas.toDataURL('image/png');
            }
            if (pieCanvas) {
                document.getElementById('chart_pie_base64').value = pieCanvas.toDataURL('image/png');
            }
        }

        function chartFilterComponent() {
            return {
                chartPeriod: 'last30',
                allDataSets: {},
                
                init: function() {
                    const canvasLine = document.getElementById('queueChart');
                    if (canvasLine && canvasLine.dataset.chartSets) {
                        try {
                            this.allDataSets = JSON.parse(canvasLine.dataset.chartSets);
                            this.renderLineChart('last30');
                        } catch (e) { console.error(e); }
                    }
                    this.renderPieChart();
                    this.renderDonutChart();
                    this.renderBarChart();
                },

                switchChartPeriod: function(period) {
                    this.chartPeriod = period;
                    this.renderLineChart(period);
                },

                renderLineChart: function(periodKey) {
                    const dataSet = this.allDataSets[periodKey] || this.allDataSets['last30'];
                    const canvas = document.getElementById('queueChart');
                    if (!canvas || !dataSet) return;

                    const ctx = canvas.getContext('2d');
                    if (globalQueueChart) globalQueueChart.destroy();

                    globalQueueChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dataSet.dates,
                            datasets: [
                                { label: 'Total Tiket Masuk', data: dataSet.total, borderColor: '#00509E', backgroundColor: 'rgba(0, 80, 158, 0.1)', fill: true, tension: 0.3 },
                                { label: 'Layanan Selesai', data: dataSet.selesai, borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                    });
                },

                renderPieChart: function() {
                    const canvas = document.getElementById('categoryPieChart');
                    if (!canvas || !canvas.dataset.dist) return;

                    try {
                        const raw = JSON.parse(canvas.dataset.dist);
                        const labels = raw.map(i => i.nama);
                        const data = raw.map(i => i.total);

                        if (globalPieChart) globalPieChart.destroy();

                        globalPieChart = new Chart(canvas.getContext('2d'), {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: ['#00509E', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#64748B']
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                        });
                    } catch (e) { console.error(e); }
                },

                renderDonutChart: function() {
                    const canvas = document.getElementById('statusDonutChart');
                    if (!canvas || !canvas.dataset.ratio) return;

                    try {
                        const raw = JSON.parse(canvas.dataset.ratio);
                        if (globalDonutChart) globalDonutChart.destroy();

                        globalDonutChart = new Chart(canvas.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(raw),
                                datasets: [{
                                    data: Object.values(raw),
                                    backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444']
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                        });
                    } catch (e) { console.error(e); }
                },

                renderBarChart: function() {
                    const canvas = document.getElementById('csBarChart');
                    if (!canvas || !canvas.dataset.cs) return;

                    try {
                        const raw = JSON.parse(canvas.dataset.cs);
                        const labels = raw.map(i => i.nama + ' (M' + i.nomor_meja + ')');
                        const data = raw.map(i => i.total_dilayani);

                        if (globalBarChart) globalBarChart.destroy();

                        globalBarChart = new Chart(canvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Tiket Dilayani',
                                    data: data,
                                    backgroundColor: '#8B5CF6'
                                }]
                            },
                            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                        });
                    } catch (e) { console.error(e); }
                }
            };
        }

        function historyFilterComponent() {
            return {
                daysLimit: '30',
                hideEmpty: false,
                sortField: 'tanggal',
                sortOrder: 'desc',
                rawHistory: [],
                
                init: function() {
                    const elem = document.getElementById('history-data-container');
                    if (elem && elem.dataset.history) {
                        try { this.rawHistory = JSON.parse(elem.dataset.history); } catch (e) { this.rawHistory = []; }
                    }
                },
                
                get filteredHistory() {
                    let data = [...this.rawHistory];
                    if (this.daysLimit !== 'all') data = data.slice(0, parseInt(this.daysLimit));
                    if (this.hideEmpty) data = data.filter(i => i.total_tiket > 0);

                    var self = this;
                    data.sort((a, b) => {
                        let valA = a[self.sortField];
                        let valB = b[self.sortField];
                        return self.sortOrder === 'asc' ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1);
                    });
                    return data;
                }
            };
        }
    </script>
</body>
</html>
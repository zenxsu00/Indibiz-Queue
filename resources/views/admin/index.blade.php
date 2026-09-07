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
</head>
<body class="bg-[#F8F9FA] min-h-screen font-sans text-[#181C20] flex flex-col lg:flex-row antialiased overflow-x-hidden" 
      x-data="{ activeTab: 'operations', mobileMenu: false, showModalCS: false, showModalMeja: false, showModalDetail: false, selectedTiket: null }">

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

        <!-- NOTIFIKASI SUKSES / ERROR -->
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
                    (activeTab === 'history' ? 'Riwayat & Rekap SLA / Omset Bulanan' : 'Monitoring Staf CS & Slot Meja'))
                "></h2>
                <p class="text-xs text-[#5D3F3B] mt-0.5">
                    Pemantauan metrik antrean, grafik transaksi, dan ketersediaan meja CS secara real-time.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <a href="{{ route('admin.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'layanan_id' => $layananId]) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 bg-[#EE2E24] hover:bg-[#CE1111] text-white font-black px-4 py-2.5 rounded-xl text-xs shadow-md transition-all">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Cetak PDF
                </a>
                <a href="{{ route('admin.export') }}" class="inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs shadow-md transition-all">
                    <span class="material-symbols-outlined text-base">download</span> Export CSV
                </a>
            </div>
        </div>

        <!-- FORM FILTER OPERATIONS -->
        <form x-show="activeTab === 'operations'" action="{{ route('admin.dashboard') }}" method="GET" class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-end gap-3.5">
            <div class="flex-1 min-w-[160px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-xl p-2 text-xs font-semibold focus:border-[#00509E] focus:ring-0">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-xl p-2 text-xs font-semibold focus:border-[#00509E] focus:ring-0">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Filter Layanan</label>
                <select name="layanan_id" class="w-full border border-gray-300 rounded-xl p-2 text-xs font-semibold focus:border-[#00509E] focus:ring-0">
                    <option value="">Semua Kategori</option>
                    @foreach($layanans as $lay)
                        <option value="{{ $lay->id }}" {{ $layananId == $lay->id ? 'selected' : '' }}>{{ $lay->nama_layanan }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-[#00509E] hover:bg-[#003C7E] text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                <span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan Filter
            </button>
        </form>

        <!-- TAB 1: OPERATIONS -->
        <div x-show="activeTab === 'operations'" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-[#00509E]"></div>
                    <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider block mb-2">Total Antrean</span>
                    <span class="text-3xl font-black text-[#181C20]">{{ number_format($totalHariIni) }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>
                    <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider block mb-2">Sedang Menunggu</span>
                    <span class="text-3xl font-black text-[#181C20]">{{ number_format($menunggu) }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Rata-Rata SLA</span>
                        <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">1 Bulan</span>
                    </div>
                    <span class="text-3xl font-black text-[#181C20]">{{ $avgSla }}</span>
                </div>

                <div class="bg-white border border-[#E0E3E8] rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-purple-600"></div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] text-[#5D3F3B] font-extrabold uppercase tracking-wider">Total Omset Loket</span>
                        <span class="text-[9px] bg-purple-100 text-purple-800 font-bold px-1.5 py-0.5 rounded">1 Bulan</span>
                    </div>
                    <span class="text-xl font-black text-[#00509E]">Rp {{ number_format($totalOmset, 0, ',', '.') }}</span>
                </div>
            </div>

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
                                <th class="p-3">Layanan</th>
                                <th class="p-3">CS / Loket</th>
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
                                    <td class="p-3 font-bold">{{ $tiket->pelanggan->nama }} <br><span class="text-[10px] text-gray-400 font-normal">{{ $tiket->pelanggan->no_hp }}</span></td>
                                    <td class="p-3"><span class="bg-gray-100 px-2 py-0.5 rounded font-semibold">{{ $tiket->layanan->nama_layanan }}</span></td>
                                    <td class="p-3 font-bold">{{ $tiket->cs ? $tiket->cs->nama_lengkap . ' (M'.$tiket->cs->nomor_meja.')' : '-' }}</td>
                                    <td class="p-3 text-gray-500">{{ \Carbon\Carbon::parse($tiket->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $tiket->status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : ($tiket->status === 'Batal' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $tiket->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-semibold">{{ $tiket->metode_pembayaran ?? 'Tanpa Transaksi' }}</td>
                                    <td class="p-3 text-right font-black text-[#181C20]">Rp {{ number_format($tiket->nominal_pembayaran, 0, ',', '.') }}</td>
                                    <td class="p-3 text-center">
                                        <button @click="selectedTiket = {{ json_encode($tiket) }}; showModalDetail = true" class="px-2.5 py-1 bg-[#00509E]/10 text-[#00509E] hover:bg-[#00509E] hover:text-white rounded-lg font-bold text-[10px] transition-all">
                                            Detail
                                        </button>
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
        <div x-show="activeTab === 'analytics'" class="space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm">
                <h3 class="text-sm font-black text-[#181C20] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">show_chart</span>
                    Grafik Analitik Tren Pendaftaran vs Layanan Selesai (30 Hari Terakhir)
                </h3>
                <div class="h-64 sm:h-80">
                    <canvas id="queueChart" 
                        data-chart-dates='{{ json_encode($chartDates) }}' 
                        data-chart-total='{{ json_encode($chartTotal) }}' 
                        data-chart-selesai='{{ json_encode($chartSelesai) }}'>
                    </canvas>
                </div>
            </div>

            <div class="bg-white border border-[#E0E3E8] rounded-2xl p-6 shadow-sm space-y-6">
                <h3 class="text-lg font-black text-[#181C20] pb-3 border-b border-[#E0E3E8]">Detail Kepadatan per Kategori Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($distribusiLayanan as $layanan)
                        @php
                            $persenNum = $totalHariIni > 0 ? number_format(($layanan->total / $totalHariIni) * 100, 1) : 0;
                        @endphp
                        <div class="p-5 border border-[#E0E3E8] rounded-xl bg-[#F8F9FA] space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="font-extrabold text-sm text-[#181C20]">{{ $layanan->nama }}</span>
                                <span class="text-xs bg-[#00509E]/10 text-[#00509E] font-black px-2.5 py-1 rounded-md">{{ $layanan->total }} Tiket</span>
                            </div>
                            <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-[#00509E] rounded-full" style="--w: {{ $persenNum }}%; width: var(--w);"></div>
                            </div>
                            <div class="text-right text-xs font-bold text-gray-500">{{ $persenNum }}% dari total antrean</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 3: RIWAYAT BULANAN -->
        <div x-show="activeTab === 'history'" 
             id="history-data-container"
             data-history='{{ json_encode($historyBulanan) }}'
             x-data="historyFilterComponent()"
             class="bg-white border border-[#E0E3E8] rounded-2xl shadow-sm overflow-hidden flex flex-col space-y-4 p-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-[#E0E3E8] pb-4 gap-4">
                <div>
                    <h3 class="text-lg font-black text-[#181C20]">Riwayat Kinerja, SLA & Omset Harian</h3>
                    <p class="text-xs text-gray-500">Rekapitulasi data transaksi dan performa layanan per hari.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold bg-[#00509E]/10 text-[#00509E] px-3 py-1 rounded-full" x-text="'Total ' + filteredHistory.length + ' Baris Tampil'"></span>
                </div>
            </div>

            <!-- BAR FILTER & SORTING CLIENT-SIDE -->
            <div class="bg-[#F8F9FA] p-3.5 rounded-xl border border-[#E0E3E8] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Jangka Waktu Hari</label>
                    <select x-model="daysLimit" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white focus:border-[#00509E] focus:ring-0">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="14">14 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="all">Semua Data (30 Hari Full)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Urutkan Berdasarkan</label>
                    <select x-model="sortField" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white focus:border-[#00509E] focus:ring-0">
                        <option value="tanggal">Tanggal</option>
                        <option value="total_tiket">Total Tiket</option>
                        <option value="selesai">Tiket Selesai</option>
                        <option value="batal">Tiket Batal</option>
                        <option value="sla_seconds">Rata-Rata SLA</option>
                        <option value="total_omset">Total Omset</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Arah Urutan</label>
                    <select x-model="sortOrder" class="w-full border border-gray-300 rounded-lg p-2 font-semibold bg-white focus:border-[#00509E] focus:ring-0">
                        <option value="desc">Terbanyak / Terbaru (Descending)</option>
                        <option value="asc">Tersedikit / Terlama (Ascending)</option>
                    </select>
                </div>

                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="hideEmpty" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#00509E] relative"></div>
                        <span class="ml-2 text-xs font-bold text-[#181C20]">Sembunyikan Hari Kosong</span>
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
                        <template x-if="filteredHistory.length === 0">
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400 font-bold">Tidak ada data yang memenuhi kriteria filter.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: STAFF MONITORING (REAL-TIME STATUS MEJA CS + KATALOG SLOT MEJA TERSEDIA) -->
        <div x-show="activeTab === 'staff'" class="space-y-6">
            
            <!-- PANEL MANAJEMEN AKUN & SLOT MEJA -->
            <div class="bg-white p-4 rounded-2xl border border-[#E0E3E8] shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">tune</span>
                    <h4 class="font-extrabold text-xs text-[#181C20]">Aksi Pengelolaan Staf & Loket:</h4>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="showModalCS = true" class="px-3.5 py-2 bg-[#00509E] hover:bg-[#003C7E] text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">person_add</span> Tambah CS Baru
                    </button>
                    <button @click="showModalMeja = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">add_box</span> Tambah Slot Meja
                    </button>
                </div>
            </div>

            <!-- KATALOG SLOT MEJA FISIK YANG TERSEDIA / DIRENCANAKAN -->
            <div class="bg-white p-5 rounded-2xl border border-[#E0E3E8] shadow-sm space-y-3">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="text-xs font-black uppercase text-[#181C20] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[#00509E] text-lg">desk</span>
                        Katalog Ketersediaan Meja Loket
                    </h3>
                    <span class="text-[10px] font-extrabold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">
                        Master Meja
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($masterMejas ?? [] as $meja)
                        <div class="p-3.5 border rounded-xl flex items-center justify-between bg-[#F8F9FA] shadow-sm border-[#E0E3E8]">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-[#00509E]/10 text-[#00509E] font-black text-[10px] flex items-center justify-center">
                                        M{{ $meja->nomor_meja }}
                                    </span>
                                    <span class="font-extrabold text-xs text-[#181C20]">{{ $meja->nama_meja }}</span>
                                </div>
                                <span class="text-[9px] font-black uppercase mt-1 inline-block {{ $meja->is_available ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $meja->is_available ? 'Tersedia Operasional' : 'Nonaktif / Dikunci' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1">
                                <form action="{{ route('admin.meja.toggle', $meja->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Ubah Status Ketersediaan" class="p-1.5 {{ $meja->is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }} rounded-lg font-bold text-xs">
                                        <span class="material-symbols-outlined text-sm">power_settings_new</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.meja.delete', $meja->id) }}" method="POST" onsubmit="return confirm('Hapus slot meja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Slot Meja" class="p-1.5 bg-rose-100 text-rose-600 rounded-lg">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-4 text-center text-gray-400 font-bold border border-dashed rounded-xl text-xs">
                            Belum ada slot meja fisik yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TABLE LIVEMONITOR PETUGAS CS -->
            <div class="bg-white border border-[#E0E3E8] rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-[#E0E3E8] flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#181C20]">Status Lengkap & Performa Petugas CS</h3>
                    <span class="text-xs text-[#00509E] font-bold bg-[#00509E]/10 px-3 py-1 rounded-full">Live Monitor</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#F8F9FA] text-xs font-extrabold text-[#5D3F3B] border-b border-[#E0E3E8] uppercase tracking-wider">
                                <th class="py-4 px-6">Nomor Loket</th>
                                <th class="py-4 px-6">Nama Petugas</th>
                                <th class="py-4 px-6">Status Loket</th>
                                <th class="py-4 px-6">Tiket Sedang Dilayani</th>
                                <th class="py-4 px-6 text-center">Total Tiket Diselesaikan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E0E3E8]">
                            @forelse($mejaCs as $cs)
                                <tr class="hover:bg-[#F8F9FA] transition-colors">
                                    <td class="py-4 px-6 font-extrabold text-[#181C20]">Loket M{{ $cs->nomor_meja }}</td>
                                    <td class="py-4 px-6 font-bold text-[#181C20] flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#00509E] text-white font-black text-xs flex items-center justify-center shadow-sm">
                                            {{ $cs->inisial }}
                                        </div>
                                        {{ $cs->nama }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border 
                                            {{ $cs->status === 'Melayani Pelanggan' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($cs->status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                                            <span class="w-2 h-2 rounded-full {{ $cs->status === 'Melayani Pelanggan' ? 'bg-amber-500 animate-pulse' : ($cs->status === 'Aktif' ? 'bg-emerald-600' : 'bg-rose-600') }}"></span>
                                            {{ $cs->status === 'Melayani Pelanggan' ? 'Melayani Pelanggan' : ($cs->status === 'Aktif' ? 'Standby (Siap Memanggil)' : 'Offline / Tutup') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-extrabold {{ $cs->tiket_aktif != '-' ? 'text-[#EE2E24]' : 'text-gray-400' }}">
                                        {{ $cs->tiket_aktif }}
                                    </td>
                                    <td class="py-4 px-6 font-black text-[#00509E] text-center text-base">
                                        {{ $cs->total_dilayani }} Tiket
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-[#5D3F3B] text-sm">Belum ada akun CS yang terdaftar di sistem.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- MODAL POPUP 1: TAMBAH PETUGAS CS -->
    <div x-show="showModalCS" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalCS = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">person_add</span>
                    Tambah Petugas CS Baru
                </h3>
                <button @click="showModalCS = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="font-bold block mb-1">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_lengkap" required placeholder="Contoh: Abdul Jamal Ahmad Kusnandar" class="w-full p-2.5 border border-gray-300 rounded-xl focus:border-[#00509E] focus:ring-0">
                </div>
                <div>
                    <label class="font-bold block mb-1">Username Login</label>
                    <input type="text" name="username" required placeholder="Contoh: cs_abdul" class="w-full p-2.5 border border-gray-300 rounded-xl focus:border-[#00509E] focus:ring-0">
                </div>
                <div>
                    <label class="font-bold block mb-1">Password Initial</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full p-2.5 border border-gray-300 rounded-xl focus:border-[#00509E] focus:ring-0">
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="showModalCS = false" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#00509E] text-white font-bold text-xs rounded-xl shadow-md">Simpan CS Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP 2: TAMBAH MASTER MEJA -->
    <div x-show="showModalMeja" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalMeja = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">add_box</span>
                    Tambah Slot Loket Meja
                </h3>
                <button @click="showModalMeja = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('admin.meja.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="font-bold block mb-1">Nomor Meja (Angka)</label>
                    <input type="number" name="nomor_meja" required placeholder="Contoh: 3" class="w-full p-2.5 border border-gray-300 rounded-xl focus:border-[#00509E] focus:ring-0">
                </div>
                <div>
                    <label class="font-bold block mb-1">Nama Deskripsi Meja</label>
                    <input type="text" name="nama_meja" required placeholder="Contoh: Loket Meja 03" class="w-full p-2.5 border border-gray-300 rounded-xl focus:border-[#00509E] focus:ring-0">
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="showModalMeja = false" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md">Tambah Meja</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP 3: DETAIL TIKET & CATATAN CS -->
    <div x-show="showModalDetail" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showModalDetail = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">receipt_long</span>
                    Detail Tiket <span x-text="selectedTiket?.nomor_antrian" class="text-[#00509E]"></span>
                </h3>
                <button @click="showModalDetail = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Pelanggan</span>
                        <span class="font-extrabold text-[#181C20]" x-text="selectedTiket?.pelanggan?.nama"></span>
                        <span class="block text-gray-500 text-[10px]" x-text="selectedTiket?.pelanggan?.no_hp"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Layanan & CS</span>
                        <span class="font-extrabold text-[#181C20]" x-text="selectedTiket?.layanan?.nama_layanan"></span>
                        <span class="block text-[#00509E] font-bold text-[10px]" x-text="selectedTiket?.cs ? selectedTiket?.cs?.nama_lengkap : 'Belum Melayani'"></span>
                    </div>
                </div>

                <div>
                    <span class="text-gray-400 font-bold block text-[10px] uppercase mb-1">Keluhan Awal Pelanggan</span>
                    <p class="p-2.5 bg-amber-50/50 border border-amber-100 rounded-xl text-gray-700 italic" x-text="selectedTiket?.keluhan_awal ?? '-'"></p>
                </div>

                <div>
                    <span class="text-gray-400 font-bold block text-[10px] uppercase mb-1">Hasil Tindakan / Keluhan Final</span>
                    <p class="p-2.5 bg-blue-50/50 border border-blue-100 rounded-xl text-gray-800 font-medium" x-text="selectedTiket?.keluhan_final || 'Belum diisi.'"></p>
                </div>

                <div>
                    <span class="text-gray-400 font-bold block text-[10px] uppercase mb-1">Catatan Konsultasi CS</span>
                    <p class="p-2.5 bg-emerald-50/50 border border-emerald-100 rounded-xl text-gray-800 font-medium" x-text="selectedTiket?.catatan_cs || 'Belum ada catatan dari CS.'"></p>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex justify-end">
                <button type="button" @click="showModalDetail = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- DATA JAVASCRIPT MURNI -->
    <script>
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
                        try {
                            this.rawHistory = JSON.parse(elem.dataset.history);
                        } catch (e) {
                            this.rawHistory = [];
                        }
                    }
                },
                
                get filteredHistory() {
                    let data = [...this.rawHistory];
                    
                    if (this.daysLimit !== 'all') {
                        let limit = parseInt(this.daysLimit);
                        data = data.slice(0, limit);
                    }
                    
                    if (this.hideEmpty) {
                        data = data.filter(function(item) { return item.total_tiket > 0; });
                    }

                    var self = this;
                    data.sort(function(a, b) {
                        let valA = a[self.sortField];
                        let valB = b[self.sortField];

                        if (self.sortField === 'sla_seconds') {
                            valA = self.parseSlaToSeconds(a.avg_sla);
                            valB = self.parseSlaToSeconds(b.avg_sla);
                        }

                        if (typeof valA === 'string' && self.sortField !== 'sla_seconds') {
                            return self.sortOrder === 'asc' 
                                ? valA.localeCompare(valB) 
                                : valB.localeCompare(valA);
                        }

                        return self.sortOrder === 'asc' ? valA - valB : valB - valA;
                    });

                    return data;
                },

                parseSlaToSeconds: function(slaStr) {
                    if (!slaStr) return 0;
                    let m = slaStr.match(/(\d+)m/);
                    let s = slaStr.match(/(\d+)s/);
                    let minutes = m ? parseInt(m[1]) : 0;
                    let seconds = s ? parseInt(s[1]) : 0;
                    return (minutes * 60) + seconds;
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const chartCanvas = document.getElementById('queueChart');
            if (chartCanvas) {
                const chartDates = JSON.parse(chartCanvas.getAttribute('data-chart-dates') || '[]');
                const chartTotal = JSON.parse(chartCanvas.getAttribute('data-chart-total') || '[]');
                const chartSelesai = JSON.parse(chartCanvas.getAttribute('data-chart-selesai') || '[]');

                const ctx = chartCanvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartDates,
                        datasets: [
                            {
                                label: 'Total Tiket Masuk',
                                data: chartTotal,
                                borderColor: '#00509E',
                                backgroundColor: 'rgba(0, 80, 158, 0.1)',
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Layanan Selesai',
                                data: chartSelesai,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } }
                    }
                });
            }
        });
    </script>
</body>
</html>
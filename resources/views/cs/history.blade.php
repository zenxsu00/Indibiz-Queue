<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Layanan CS - Indibiz Queue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #F1F4F9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #C4C7CC; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #00509E; }
    </style>
</head>
<body class="bg-[#F8F9FA] h-screen w-screen font-sans text-[#181C20] flex flex-col md:flex-row overflow-hidden antialiased selection:bg-[#EE2E24] selection:text-white"
      x-data="{ 
          showModalKurasi: false, 
          kurasiTiket: {},
          selectedLayananId: '',
          selectedPeriod: '{{ $period ?? 'all' }}',
          
          openKurasiModal(tiket) {
              this.kurasiTiket = JSON.parse(JSON.stringify(tiket));
              if(!this.kurasiTiket.pelanggan) {
                  this.kurasiTiket.pelanggan = { nama: '', email: '', no_indibiz: '', no_hp: '' };
              }
              this.selectedLayananId = this.kurasiTiket.layanan_id || '';
              this.showModalKurasi = true;
          },

          confirmSubmit(closeAfterSave) {
              let self = this;
              Swal.fire({
                  title: 'Konfirmasi Perubahan Data',
                  text: 'Apakah Anda yakin ingin menyimpan perubahan data kurasi ini?',
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonColor: '#00509E',
                  cancelButtonColor: '#6B7280',
                  confirmButtonText: 'Ya, Simpan',
                  cancelButtonText: 'Batal',
                  customClass: { popup: 'rounded-2xl' }
              }).then((result) => {
                  if (result.isConfirmed) {
                      self.submitKurasi(closeAfterSave);
                  }
              });
          },

          submitKurasi(closeAfterSave) {
              let form = document.getElementById('form-edit-kurasi');
              let formData = new FormData(form);

              fetch(form.action, {
                  method: 'POST',
                  body: formData,
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  Swal.fire({
                      title: 'Berhasil!',
                      text: 'Data kurasi berhasil diperbarui.',
                      icon: 'success',
                      timer: 1500,
                      showConfirmButton: false
                  }).then(() => {
                      if (closeAfterSave) {
                          this.showModalKurasi = false;
                          window.location.reload();
                      } else {
                          window.location.reload();
                      }
                  });
              })
              .catch(() => {
                  form.submit();
              });
          }
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
                <a href="{{ route('cs.index') }}" class="flex items-center px-3 py-2 text-xs font-bold text-white/70 hover:bg-white/10 hover:text-white rounded-lg transition-all">
                    <span class="material-symbols-outlined mr-2 text-base">grid_view</span> 
                    Dashboard Loket
                </a>
                <a href="{{ route('cs.history') }}" class="flex items-center px-3 py-2 text-xs font-bold bg-white/20 text-white rounded-lg shadow-sm border border-white/10 transition-all">
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
            <a href="{{ route('cs.index') }}" title="Dashboard Loket" class="p-1 text-white/70 hover:bg-white/10 hover:text-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">grid_view</span>
            </a>
            <a href="{{ route('cs.history') }}" title="Riwayat Layanan" class="p-1 bg-white/20 text-white rounded-lg flex items-center justify-center">
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

    <!-- MAIN CONTENT AREA CS HISTORY -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto p-3 lg:p-4 gap-3">
        
        @if(session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-bold flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- HEADER BANNER & SIMPLE SELECT BAR FILTER -->
        <div class="bg-white p-3.5 lg:p-4 rounded-xl border border-[#E0E3E8] shadow-sm shrink-0 space-y-3">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-sm lg:text-base font-black text-[#181C20] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#00509E]">history_edu</span>
                        Riwayat Layanan Selesai & Kurasi CS
                    </h2>
                    <p class="text-[11px] text-gray-500 font-medium">Hanya menampilkan tiket berstatus 'Selesai' untuk pencarian profil dan kurasi data.</p>
                </div>
            </div>

            <!-- FORM FILTER SIMPEL DENGAN SELECT BAR -->
            <form action="{{ route('cs.history') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                
                <!-- SEARCH TEXT INPUT -->
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari No HP / Email / Indibiz / Nama..." class="w-full text-xs p-2 pl-8 border border-[#E0E3E8] rounded-lg focus:border-[#00509E] focus:ring-0 font-medium">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-gray-400 text-base">search</span>
                </div>

                <!-- SELECT BAR PERIODE -->
                <div class="w-full sm:w-auto min-w-[180px]">
                    <select name="period" x-model="selectedPeriod" @change="$el.form.submit()" class="w-full p-2 border border-[#E0E3E8] bg-gray-50 rounded-lg text-xs font-bold text-gray-700 focus:border-[#00509E] focus:ring-0 cursor-pointer">
                        <option value="all">Semua Waktu (All Time)</option>
                        <option value="today">Hari Ini (Today)</option>
                        <option value="mtd">Bulan Ini (Month to Date)</option>
                        <option value="last_30">30 Hari Terakhir</option>
                        <option value="ytd">Tahun Ini (Year to Date)</option>
                        <option value="custom">Kustom Tanggal...</option>
                    </select>
                </div>

                <!-- INPUT TANGGAL KUSTOM (Hanya tampil jika memilih 'Kustom Tanggal') -->
                <template x-if="selectedPeriod === 'custom'">
                    <div class="flex items-center gap-1.5 bg-gray-50 p-1 border border-[#E0E3E8] rounded-lg">
                        <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="bg-transparent text-xs font-medium focus:outline-none">
                        <span class="text-gray-400 font-bold">-</span>
                        <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="bg-transparent text-xs font-medium focus:outline-none">
                        <button type="submit" class="px-2 py-1 bg-[#00509E] text-white rounded font-bold text-[10px]">Terapkan</button>
                    </div>
                </template>

                <!-- TOMBOL RESET FILTER -->
                @if(($period && $period !== 'all') || !empty($searchQuery))
                    <a href="{{ route('cs.history') }}" title="Reset Filter" class="py-2 px-2.5 bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 rounded-lg font-bold flex items-center justify-center transition-all text-xs gap-1">
                        <span class="material-symbols-outlined text-sm">restart_alt</span>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- TABEL DATA RIWAYAT SELESAI -->
        <div class="bg-white rounded-xl border border-[#E0E3E8] shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto flex-1 custom-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8F9FA] border-b border-[#E0E3E8] text-gray-500 font-black uppercase text-[10px] sticky top-0 bg-[#F8F9FA] z-10">
                        <tr>
                            <th class="p-3">No. Antrean</th>
                            <th class="p-3">Data Pelanggan (Lengkap)</th>
                            <th class="p-3">Layanan / Sub-Layanan</th>
                            <th class="p-3">Hasil Final / Catatan CS</th>
                            <th class="p-3">Status Lapangan</th>
                            <th class="p-3">Waktu Selesai</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E3E8]">
                        @forelse($riwayatTiket as $tiket)
                            @php
                                $isDone = $tiket->is_curated;
                            @endphp
                            <tr class="hover:bg-[#F8F9FA] transition-colors">
                                <td class="p-3 font-mono font-black text-[#00509E] text-sm">{{ $tiket->nomor_antrian }}</td>
                                <td class="p-3 font-bold">
                                    <div class="text-gray-800 text-xs">{{ $tiket->pelanggan->nama ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-500 font-normal mt-0.5 space-y-0.5">
                                        <div><span class="font-semibold text-gray-400">HP:</span> {{ $tiket->pelanggan->no_hp ?? '-' }}</div>
                                        <div><span class="font-semibold text-gray-400">Email:</span> {{ $tiket->pelanggan->email ?? '-' }}</div>
                                        <div><span class="font-semibold text-gray-400">Indibiz ID:</span> <span class="font-bold text-[#00509E]">{{ $tiket->pelanggan->no_indibiz ?? '-' }}</span></div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="bg-blue-50 text-[#00509E] px-2 py-0.5 rounded font-bold inline-block mb-0.5 border border-blue-100">
                                        {{ $tiket->layanan->nama_layanan ?? '-' }}
                                    </span>
                                    <div class="text-[10px] text-gray-500 italic">{{ $tiket->subLayanan->nama_sub_layanan ?? 'Tanpa Sub-Layanan' }}</div>
                                </td>
                                <td class="p-3 max-w-xs">
                                    <p class="font-bold text-gray-800 line-clamp-1">{{ $tiket->keluhan_final ?? '-' }}</p>
                                    <p class="text-[10px] text-gray-500 italic line-clamp-1">Catatan CS: "{{ $tiket->catatan_cs ?? 'Belum ada' }}"</p>
                                </td>
                                <td class="p-3">
                                    <span class="text-[9px] px-2 py-0.5 rounded-full font-black inline-flex items-center gap-1 {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $isDone ? '✓ Selesai / Dikurasi' : '⚠ Perlu Lapangan / Belum' }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500 text-[11px]">
                                    {{ $tiket->waktu_selesai ? \Carbon\Carbon::parse($tiket->waktu_selesai)->format('d/m/Y H:i') : '-' }} WIB
                                </td>
                                <td class="p-3 text-center">
                                    <button type="button" 
                                            @click='openKurasiModal(@json($tiket))' 
                                            class="px-2.5 py-1.5 bg-[#00509E] text-white rounded-lg font-bold text-[10px] hover:bg-[#003C7E] transition-all inline-flex items-center gap-1 shadow-sm cursor-pointer">
                                        <span class="material-symbols-outlined text-xs">edit_note</span> Edit Kurasi
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-400 font-bold">
                                    <span class="material-symbols-outlined text-2xl block mb-1">history</span>
                                    Tidak ada data riwayat layanan yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-[#E0E3E8] bg-[#F8F9FA]">
                {{ $riwayatTiket->appends(request()->query())->links() }}
            </div>
        </div>
    </main>

    <!-- MODAL EDIT KURASI LENGKAP -->
    <div x-show="showModalKurasi" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-xl w-full p-5 lg:p-6 shadow-2xl border border-gray-100 space-y-4 my-8" @click.away="showModalKurasi = false">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100">
                <h3 class="text-base font-black text-[#181C20] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00509E]">edit_note</span>
                    Form Edit & Kurasi Tiket <span x-text="kurasiTiket.nomor_antrian" class="text-[#00509E]"></span>
                </h3>
                <button @click="showModalKurasi = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="form-edit-kurasi" :action="'{{ url('/cs-desk/kurasi') }}/' + kurasiTiket.id" method="POST" class="space-y-3 text-xs" @submit.prevent>
                @csrf

                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-2">
                    <h4 class="font-black text-[#00509E] flex items-center gap-1 uppercase text-[10px]">
                        <span class="material-symbols-outlined text-sm">person_edit</span> Edit Profiling Pelanggan
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="font-bold text-gray-600 block mb-0.5">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" x-model="kurasiTiket.pelanggan.nama" class="w-full p-2 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0">
                        </div>
                        <div>
                            <label class="font-bold text-gray-600 block mb-0.5">Email Pelanggan</label>
                            <input type="email" name="email_pelanggan" x-model="kurasiTiket.pelanggan.email" class="w-full p-2 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0">
                        </div>
                        <div>
                            <label class="font-bold text-gray-600 block mb-0.5">No. Indibiz / Service ID</label>
                            <input type="text" name="no_indibiz" x-model="kurasiTiket.pelanggan.no_indibiz" class="w-full p-2 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0">
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 space-y-2">
                    <h4 class="font-black text-[#00509E] flex items-center gap-1 uppercase text-[10px]">
                        <span class="material-symbols-outlined text-sm">category</span> Edit Kategori & Sub-Layanan
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="font-bold text-gray-600 block mb-0.5">Kategori Layanan</label>
                            <select name="layanan_id" x-model="selectedLayananId" class="w-full p-2 border border-gray-300 rounded-lg bg-white focus:border-[#00509E] focus:ring-0">
                                @foreach($layanans as $lay)
                                    <option value="{{ $lay->id }}">{{ $lay->nama_layanan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-gray-600 block mb-0.5">Sub-Layanan</label>
                            <select name="sub_layanan_id" x-model="kurasiTiket.sub_layanan_id" class="w-full p-2 border border-gray-300 rounded-lg bg-white focus:border-[#00509E] focus:ring-0">
                                <option value="">-- Tanpa Sub Layanan --</option>
                                @foreach($layanans as $lay)
                                    @foreach($lay->subLayanans as $sub)
                                        <option value="{{ $sub->id }}" x-show="selectedLayananId == '{{ $lay->id }}'">
                                            {{ $sub->nama_sub_layanan }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Hasil Tindakan / Keluhan Final</label>
                    <textarea name="keluhan_final" x-model="kurasiTiket.keluhan_final" rows="2" placeholder="Hasil akhir keluhan..." class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0 font-medium"></textarea>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Catatan Konsultasi CS</label>
                    <textarea name="catatan_cs" x-model="kurasiTiket.catatan_cs" rows="2" placeholder="Catatan internal..." class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[#00509E] focus:ring-0 font-medium"></textarea>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Status Penanganan / Kurasi Lapangan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2 border rounded-lg flex items-center gap-2 cursor-pointer" :class="kurasiTiket.is_curated == 1 ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="is_curated" value="1" x-model="kurasiTiket.is_curated" class="sr-only">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            <span>✓ Selesai / Dikurasi</span>
                        </label>
                        <label class="p-2 border rounded-lg flex items-center gap-2 cursor-pointer" :class="kurasiTiket.is_curated == 0 ? 'border-amber-500 bg-amber-50 text-amber-800 font-bold' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="is_curated" value="0" x-model="kurasiTiket.is_curated" class="sr-only">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            <span>⚠ Perlu Lapangan / Belum</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex flex-wrap justify-between items-center gap-2">
                    <button type="button" @click="showModalKurasi = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                        Exit (Batal)
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="confirmSubmit(false)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">save</span> Save
                        </button>

                        <button type="button" @click="confirmSubmit(true)" class="px-5 py-2 bg-[#00509E] hover:bg-[#003C7E] text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center gap-1 cursor-pointer">
                            <span class="material-symbols-outlined text-sm">done_all</span> Save & Exit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
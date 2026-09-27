<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrean - Indibiz Queue</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Alpine.js untuk interaksi UI -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-b from-[#0A3967]/10 via-[#F1F5F9] to-white min-h-screen font-sans text-[#181C20] selection:bg-[#00509E] selection:text-white antialiased" 
      x-data="{ 
          step: 1, 
          selectedService: null,
          nama: '',
          no_hp: '',
          no_indibiz: '',
          errorMsg: '',
          isSubmitting: false,
          validasiStep1() {
              if(!this.no_hp.trim() || !this.nama.trim()){
                  this.errorMsg = 'Harap isi Nomor HP dan Nama Lengkap terlebih dahulu!';
                  return;
              }
              if(this.no_indibiz && this.no_indibiz.length > 12){
                  this.errorMsg = 'Nomor Indibiz maksimal 12 karakter!';
                  return;
              }
              this.errorMsg = '';
              this.step = 2;
          }
      }">

    <!-- Navbar / Header Sederhana Dominan Biru -->
    <header class="bg-[#0A3967] border-b border-[#00509E]/30 px-4 sm:px-8 py-3.5 flex justify-between items-center sticky top-0 z-40 shadow-md">
        <div class="flex items-center gap-3">
            <div class="flex flex-col justify-center">
                <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-6 sm:h-7 object-contain brightness-0 invert">
                <div class="flex items-center gap-1.5 mt-0.5 ml-1">
                    <div class="w-3 h-[1px] bg-[#EE2E24]"></div>
                    <span class="text-[9px] font-black tracking-[0.3em] text-cyan-300 uppercase">Queue System</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-white bg-white/10 px-3 py-1.5 rounded-full border border-white/20 backdrop-blur-sm">
            <span class="material-symbols-outlined text-sm text-cyan-300">person_pin</span>
            <span class="hidden sm:inline">Portal Pelanggan</span>
        </div>
    </header>

    <main class="relative overflow-hidden min-h-[calc(100vh-80px)] flex flex-col items-center p-4 sm:p-6 lg:p-8">
        <!-- Watermark -->
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-[0.03] z-0">
            <span class="material-symbols-outlined text-[400px] sm:text-[800px] text-[#00509E]">grid_view</span>
        </div>

        @if(!$isOperational)
            <!-- ================= STATUS LOKET TUTUP / NON-OPERASIONAL ================= -->
            <div class="w-full max-w-lg mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center z-10 my-auto space-y-4">
                <div class="w-20 h-20 bg-blue-50 text-[#00509E] rounded-3xl flex items-center justify-center mx-auto border border-blue-100 shadow-inner">
                    <span class="material-symbols-outlined text-5xl">schedule</span>
                </div>
                
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 text-rose-800 text-[10px] font-black rounded-full uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Loket Ditutup
                    </span>
                    <h1 class="text-2xl font-black text-[#0A3967]">Layanan Antrean Tutup</h1>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        {{ $pesanTutup ?? 'Saat ini layanan antrean publik tidak sedang beroperasi.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-400 font-medium">
                    <span class="material-symbols-outlined text-base">info</span>
                    <span>Silakan kembali pada jadwal operasional kerja</span>
                </div>
            </div>
        @else
            <!-- ================= FORM UTAMA PENGAMBILAN TIKET ================= -->
            <form action="{{ route('antrean.store') }}" method="POST" @submit="isSubmitting = true" class="w-full max-w-6xl z-10 my-auto">
                @csrf

                <!-- ALERT NOTIFIKASI ERROR (FLASH SESSION BACKEND) -->
                @if(session('error'))
                    <div class="w-full max-w-xl mx-auto mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- STEP 1: LOGIN / DATA DIRI -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms class="w-full max-w-xl mx-auto flex flex-col items-center text-center">
                    <div class="mb-4 flex flex-col items-center">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 bg-[#00509E]/10 text-[#00509E] text-xs font-extrabold rounded-full uppercase tracking-wider mb-2 border border-[#00509E]/20">
                            <span class="w-2 h-2 rounded-full bg-[#EE2E24] animate-pulse"></span>
                            Sistem Antrean Digital Indibiz
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#0A3967] tracking-tight">Ambil Nomor Antrean</h1>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1.5 max-w-md">Silakan masukkan data diri Anda untuk mendapatkan tiket antrean instan.</p>
                    </div>

                    <div class="w-full bg-white rounded-2xl shadow-xl border border-slate-200/80 p-6 sm:p-8 text-left">
                        <template x-if="errorMsg">
                            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">error</span>
                                <span x-text="errorMsg"></span>
                            </div>
                        </template>

                        <div class="space-y-4">
                            <!-- NAMA LENGKAP -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-bold text-[#0A3967]">Nama Lengkap <span class="text-[#EE2E24]">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#00509E]/60 text-xl">person</span>
                                    <input type="text" name="nama" x-model="nama" required maxlength="100" placeholder="Masukkan nama Anda" class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-slate-300 rounded-xl text-[#181C20] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00509E] transition-all font-semibold">
                                </div>
                            </div>

                            <!-- NOMOR HP -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-bold text-[#0A3967]">Nomor HP / WhatsApp <span class="text-[#EE2E24]">*</span></label>
                                <div class="flex flex-col gap-1.5">
                                    <input 
                                        type="tel" 
                                        name="no_hp" 
                                        x-model="no_hp"
                                        @input="no_hp = no_hp.replace(/[^0-9]/g, '')"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        placeholder="Contoh: 081234567890" 
                                        maxlength="15"
                                        required 
                                        class="w-full rounded-xl border border-slate-300 bg-[#F8FAFC] text-sm focus:bg-white focus:border-[#00509E] focus:ring-2 focus:ring-[#00509E] transition-all p-3 font-semibold"
                                    >
                                    <p class="text-xs text-slate-500">Nomor HP aktif untuk kirim status antrean</p>
                                </div>
                            </div>

                            <!-- NOMOR INDIBIZ -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-bold text-[#0A3967]">Nomor Indibiz / ID Pelanggan <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                                    <span class="text-[10px] font-bold text-slate-400">Maks. 12 Karakter</span>
                                </div>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#00509E]/60 text-xl">badge</span>
                                    <input 
                                        type="text" 
                                        name="no_indibiz" 
                                        x-model="no_indibiz" 
                                        maxlength="12" 
                                        placeholder="Contoh: 122345678901" 
                                        class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-slate-300 rounded-xl text-[#181C20] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00509E] transition-all font-mono font-semibold"
                                    >
                                </div>
                            </div>

                            <button type="button" @click="validasiStep1()" class="w-full bg-[#00509E] hover:bg-[#0A3967] text-white font-black py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-[#00509E]/20 flex items-center justify-center gap-2 mt-2 cursor-pointer">
                                <span>Lanjut Pilih Layanan</span>
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PILIH LAYANAN & DETAIL KEPERLUAN -->
                <div x-show="step === 2" style="display: none;" x-transition.opacity.duration.300ms class="w-full flex flex-col justify-center">
                    <button type="button" @click="step = 1" class="inline-flex items-center gap-2 text-[#00509E] hover:text-[#0A3967] transition-colors font-semibold text-sm mb-6 w-fit cursor-pointer">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span>Kembali ke Data Diri</span>
                    </button>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Kiri: Pilihan Layanan -->
                        <section class="lg:col-span-7 flex flex-col gap-6">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0A3967] mb-2">Pilih Layanan</h2>
                                <p class="text-sm text-slate-600">Pilih jenis layanan yang Anda butuhkan.</p>
                            </div>
                            
                            <!-- Looping Data Layanan -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($layanans as $layanan)
                                <label class="relative rounded-xl p-6 flex flex-col items-center text-center gap-3 transition-all cursor-pointer bg-white border border-slate-200 hover:border-[#00509E] hover:shadow-md"
                                       :class="{ 'border-2 border-[#00509E] shadow-lg ring-2 ring-[#00509E]/20 bg-blue-50/30': selectedService == {{ $layanan->id }} }">
                                    
                                    <input type="radio" name="layanan_id" value="{{ $layanan->id }}" class="hidden" x-model="selectedService" required>
                                    
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center transition-colors"
                                         :class="selectedService == {{ $layanan->id }} ? 'bg-[#00509E] text-white shadow-md' : 'bg-slate-100 text-[#00509E]'">
                                        <span class="material-symbols-outlined text-3xl">home_repair_service</span>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-base font-bold text-[#0A3967] mb-1">{{ $layanan->nama_layanan }}</h3>
                                        <p class="text-xs text-slate-500">Layanan kode {{ $layanan->kode_layanan }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </section>

                        <!-- Kanan: Form Keluhan -->
                        <section class="lg:col-span-5 flex flex-col gap-4">
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-md">
                                <h3 class="text-lg font-extrabold text-[#0A3967] mb-4 pb-3 border-b border-slate-100">Detail Keperluan</h3>
                                
                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-bold text-[#0A3967]">Alamat Lengkap <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                                        <textarea name="alamat" rows="2" maxlength="255" placeholder="Masukkan alamat lengkap Anda" class="w-full rounded-xl border border-slate-300 bg-[#F8FAFC] text-sm focus:bg-white focus:border-[#00509E] focus:ring-2 focus:ring-[#00509E] transition-all p-3"></textarea>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-bold text-[#0A3967]">Detail Keluhan / Keperluan <span class="text-[#EE2E24]">*</span></label>
                                        <textarea name="keluhan_awal" rows="4" required maxlength="500" placeholder="Jelaskan detail keluhan Anda secara singkat" class="w-full rounded-xl border border-slate-300 bg-[#F8FAFC] text-sm focus:bg-white focus:border-[#00509E] focus:ring-2 focus:ring-[#00509E] transition-all p-3"></textarea>
                                    </div>

                                    <!-- TOMBOL SUBMIT -->
                                    <button type="submit" 
                                            :disabled="isSubmitting"
                                            :class="isSubmitting ? 'bg-slate-400 cursor-not-allowed' : 'bg-[#EE2E24] hover:bg-[#CE1111] cursor-pointer shadow-lg shadow-[#EE2E24]/20'"
                                            class="w-full text-white text-sm font-extrabold py-3.5 px-6 rounded-xl transition-all flex items-center justify-center gap-2 mt-2">
                                        
                                        <template x-if="!isSubmitting">
                                            <div class="flex items-center gap-2">
                                                <span>Ambil Tiket Antrean</span>
                                                <span class="material-symbols-outlined text-lg">confirmation_number</span>
                                            </div>
                                        </template>

                                        <template x-if="isSubmitting">
                                            <div class="flex items-center gap-2">
                                                <span class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                                                <span>Memproses Antrean...</span>
                                            </div>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </form>
        @endif
    </main>
</body>
</html>
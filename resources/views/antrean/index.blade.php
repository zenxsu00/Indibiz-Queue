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
<body class="bg-[#F8F9FA] min-h-screen font-sans text-[#181C20] selection:bg-[#EE2E24] selection:text-white" 
      x-data="{ 
          step: 1, 
          selectedService: null,
          nama: '',
          no_hp: '',
          errorMsg: '',
          isSubmitting: false,
          validasiStep1() {
              if(!this.no_hp.trim() || !this.nama.trim()){
                  this.errorMsg = 'Harap isi Nomor HP dan Nama Lengkap terlebih dahulu!';
                  return;
              }
              this.errorMsg = '';
              this.step = 2;
          }
      }">

    <!-- Navbar / Header Sederhana -->
    <header class="bg-white border-b border-[#E0E3E8] px-4 sm:px-8 py-3.5 flex justify-between items-center sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex flex-col justify-center">
                <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-6 sm:h-7 object-contain">
                <div class="flex items-center gap-1.5 mt-0.5 ml-1">
                    <div class="w-3 h-[1px] bg-[#EE2E24]"></div>
                    <span class="text-[9px] font-black tracking-[0.3em] text-[#EE2E24] uppercase">Queue System</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-[#00509E] bg-[#00509E]/10 px-3 py-1.5 rounded-full border border-[#00509E]/20">
            <span class="material-symbols-outlined text-sm">person_pin</span>
            <span class="hidden sm:inline">Portal Pelanggan</span>
        </div>
    </header>

    <main class="relative overflow-hidden min-h-[calc(100vh-80px)] flex flex-col items-center p-4 sm:p-6 lg:p-8">
        <!-- Watermark -->
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-[0.02] z-0">
            <span class="material-symbols-outlined text-[400px] sm:text-[800px] text-[#EE2E24]">grid_view</span>
        </div>

        @if(!$isOperational)
            <!-- ================= STATUS LOKET TUTUP / NON-OPERASIONAL ================= -->
            <div class="w-full max-w-lg mx-auto bg-white rounded-3xl border border-[#E0E3E8] shadow-xl p-8 text-center z-10 my-auto space-y-4">
                <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center mx-auto border border-amber-100 shadow-inner">
                    <span class="material-symbols-outlined text-5xl">support_agent</span>
                </div>
                
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-800 text-[10px] font-black rounded-full uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Loket Tidak Aktif
                    </span>
                    <h1 class="text-2xl font-black text-[#181C20]">Layanan Antrean Tutup</h1>
                    <p class="text-xs text-[#5D3F3B] mt-2 leading-relaxed">
                        Saat ini belum ada petugas Customer Service yang bertugas atau layanan sedang di luar jam operasional. Pengambilan tiket antrean publik sementara ditutup.
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-center gap-2 text-xs text-gray-400 font-medium">
                    <span class="material-symbols-outlined text-base">schedule</span>
                    <span>Silakan kembali pada jam operasional kerja</span>
                </div>
            </div>
        @else
            <!-- ================= FORM UTAMA PENGAMBILAN TIKET ================= -->
            <form action="{{ route('antrean.store') }}" method="POST" @submit="isSubmitting = true" class="w-full max-w-6xl z-10 my-auto">
                @csrf

                <!-- ALERTI NOTIFIKASI ERROR (BAIK DARI FRONTEND MAUPUN BACKEND FLASH SESSION) -->
                @if(session('error'))
                    <div class="w-full max-w-xl mx-auto mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- STEP 1: LOGIN / DATA DIRI -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms class="w-full max-w-xl mx-auto flex flex-col items-center text-center">
                    <div class="mb-4 flex flex-col items-center">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 bg-[#FFDAD5] text-[#930004] text-xs font-extrabold rounded-full uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#EE2E24] animate-pulse"></span>
                            Sistem Antrean Digital Indibiz
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#181C20] tracking-tight">Ambil Nomor Antrean</h1>
                        <p class="text-xs sm:text-sm text-[#5D3F3B] mt-1.5 max-w-md">Silakan masukkan data diri Anda untuk mendapatkan tiket antrean instan.</p>
                    </div>

                    <div class="w-full bg-white rounded-2xl shadow-xl border border-[#E0E3E8] p-6 sm:p-8 text-left">
                        <template x-if="errorMsg">
                            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">error</span>
                                <span x-text="errorMsg"></span>
                            </div>
                        </template>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-bold text-[#181C20]">Nama Lengkap <span class="text-[#EE2E24]">*</span></label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#5D3F3B]/60 text-xl">person</span>
                                    <input type="text" name="nama" x-model="nama" required placeholder="Masukkan nama Anda" class="w-full pl-11 pr-4 py-3 bg-[#F8F9FA] border border-[#E7BDB7] rounded-xl text-[#181C20] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#EE2E24] transition-all font-semibold">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-bold text-[#181C20]">Nomor HP / WhatsApp <span class="text-[#EE2E24]">*</span></label>
                                <div class="flex flex-col gap-1.5">
                                    <input 
                                        type="tel" 
                                        name="no_hp" 
                                        x-model="no_hp"
                                        @input="no_hp = no_hp.replace(/[^0-9]/g, '')"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        placeholder="Contoh: 081234567890" 
                                        maxlength="14"
                                        required 
                                        class="w-full rounded-lg border border-[#E7BDB7] bg-[#F8F9FA] text-sm focus:bg-white focus:border-[#EE2E24] focus:ring-1 focus:ring-[#EE2E24] transition-colors p-3"
                                    >
                                    <p class="text-xs text-gray-500">nomor hp aktif</p>
                                </div>
                            </div>

                            <button type="button" @click="validasiStep1()" class="w-full bg-[#EE2E24] hover:bg-[#CE1111] text-white font-black py-3.5 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 mt-2 cursor-pointer">
                                <span>Lanjut Pilih Layanan</span>
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PILIH LAYANAN -->
                <div x-show="step === 2" style="display: none;" x-transition.opacity.duration.300ms class="w-full flex flex-col justify-center">
                    <button type="button" @click="step = 1" class="inline-flex items-center gap-2 text-[#5D3F3B] hover:text-[#EE2E24] transition-colors font-semibold text-sm mb-6 w-fit cursor-pointer">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span>Kembali ke Data Diri</span>
                    </button>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Kiri: Pilihan Layanan -->
                        <section class="lg:col-span-7 flex flex-col gap-6">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#181C20] mb-2">Pilih Layanan</h2>
                                <p class="text-sm text-[#5D3F3B]">Pilih jenis layanan yang Anda butuhkan.</p>
                            </div>
                            
                            <!-- Looping Data Layanan dari Database -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($layanans as $layanan)
                                <label class="relative rounded-xl p-6 flex flex-col items-center text-center gap-3 transition-all cursor-pointer bg-white border border-[#E0E3E8] hover:border-[#EE2E24]/60 hover:shadow-sm"
                                       :class="{ 'border-2 border-[#EE2E24] shadow-md ring-2 ring-[#EE2E24]/20': selectedService == {{ $layanan->id }} }">
                                    
                                    <input type="radio" name="layanan_id" value="{{ $layanan->id }}" class="hidden" x-model="selectedService" required>
                                    
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center transition-colors"
                                         :class="selectedService == {{ $layanan->id }} ? 'bg-[#FFDAD5] text-[#EE2E24]' : 'bg-[#F1F4F9] text-[#00509E]'">
                                        <span class="material-symbols-outlined text-3xl">home_repair_service</span>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-base font-bold text-[#181C20] mb-1">{{ $layanan->nama_layanan }}</h3>
                                        <p class="text-xs text-[#5D3F3B]">Layanan kode {{ $layanan->kode_layanan }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </section>

                        <!-- Kanan: Form Keluhan -->
                        <section class="lg:col-span-5 flex flex-col gap-4">
                            <div class="bg-white border border-[#E0E3E8] rounded-xl p-6 shadow-sm">
                                <h3 class="text-lg font-extrabold text-[#181C20] mb-4 pb-3 border-b border-[#E0E3E8]">Detail Keperluan</h3>
                                
                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-bold text-[#181C20]">Alamat Lengkap <span class="text-xs text-[#5D3F3B] font-normal">(Opsional)</span></label>
                                        <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap Anda" class="w-full rounded-lg border border-[#E7BDB7] bg-[#F8F9FA] text-sm focus:bg-white focus:border-[#EE2E24] focus:ring-1 focus:ring-[#EE2E24] transition-colors p-3"></textarea>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-sm font-bold text-[#181C20]">Detail Keluhan / Keperluan <span class="text-[#EE2E24]">*</span></label>
                                        <textarea name="keluhan_awal" rows="4" required placeholder="Jelaskan detail keluhan Anda secara singkat" class="w-full rounded-lg border border-[#E7BDB7] bg-[#F8F9FA] text-sm focus:bg-[#F8F9FA] focus:border-[#EE2E24] focus:ring-1 focus:ring-[#EE2E24] transition-colors p-3"></textarea>
                                    </div>

                                    <!-- TOMBOL SUBMIT DENGAN INDIKATOR LOADING & DISABLE AUTO -->
                                    <button type="submit" 
                                            :disabled="isSubmitting"
                                            :class="isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#EE2E24] hover:bg-[#CE1111] cursor-pointer'"
                                            class="w-full text-white text-sm font-extrabold py-3.5 px-6 rounded-lg transition-all shadow-md flex items-center justify-center gap-2 mt-2">
                                        
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
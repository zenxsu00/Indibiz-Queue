<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Tiket Antrean - Indibiz Service Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      body { font-family: 'Plus Jakarta Sans', sans-serif; }
      @keyframes flash-alert {
        0%, 100% { background-color: #eeff00; }
        50% { background-color: #10B981; }
      }
      .animate-flash {
        animation: flash-alert 0.6s infinite;
      }
    </style>
</head>
<body id="body-container" class="bg-[#F8F9FA] text-[#181C20] min-h-screen flex flex-col justify-between antialiased selection:bg-[#EE2E24] selection:text-white transition-colors duration-300">

    <header class="bg-white/80 backdrop-blur-md border-b border-[#E0E3E8] sticky top-0 z-50">
      <div class="max-w-xl mx-auto px-4 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex flex-col justify-center">
              <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-6 sm:h-7 object-contain">
              <div class="flex items-center gap-1.5 mt-0.5 ml-1">
                  <div class="w-3 h-[1px] bg-[#EE2E24]"></div>
                  <span class="text-[9px] font-black tracking-[0.3em] text-[#EE2E24] uppercase">Queue System</span>
              </div>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
           <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
             <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Live Sync
           </span>
        </div>
      </div>
    </header>

    <main id="area-tiket-realtime" 
          data-status="{{ $tiket->status }}" 
          data-dipanggil="{{ $tiket->jumlah_dipanggil ?? 0 }}" 
          class="max-w-xl w-full mx-auto px-4 py-6 sm:py-8 flex-1">

      @if($tiket->status == 'Menunggu')
          <div class="bg-white rounded-3xl border border-[#E0E3E8] shadow-sm overflow-hidden transition-all">
              <div class="bg-amber-500/10 border-b border-amber-500/20 px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                  <span class="text-xs font-bold text-amber-700 tracking-wide uppercase">Dalam Antrean</span>
                </div>
                <span class="text-[11px] text-amber-600 font-medium">Harap Menunggu Call</span>
              </div>

              <div class="p-6 sm:p-8 text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Nomor Antrean Anda</p>
                
                <h2 class="text-6xl sm:text-7xl font-black text-[#181C20] tracking-tight my-2">
                  {{ $tiket->nomor_antrian }}
                </h2>

                @if(!empty($tiket->kode_tiket))
                    <p class="text-xs font-mono text-gray-400 tracking-wider">
                        Ref ID: <span class="font-semibold text-gray-500">{{ $tiket->kode_tiket }}</span>
                    </p>
                @endif

                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gray-100 text-[#181C20] text-xs font-semibold my-3">
                  <span class="material-symbols-outlined text-base text-[#EE2E24]">category</span>
                  <span>{{ $tiket->layanan->nama_layanan }}</span>
                </div>

                <div class="mt-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl p-4 border border-red-100/60 text-left flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#EE2E24]/10 text-[#EE2E24] flex items-center justify-center font-bold">
                      <span class="material-symbols-outlined text-xl">groups</span>
                    </div>
                    <div>
                      <p class="text-[11px] font-medium text-gray-500">Estimasi Antrean Di Depan Anda</p>
                      <p class="text-sm font-bold text-[#181C20]">
                        @if(($sisaAntrean ?? 0) > 0)
                          <span class="text-[#EE2E24] font-extrabold text-base">{{ $sisaAntrean }}</span> Orang Lagi
                        @else
                          <span class="text-emerald-600 font-bold">Giliran Anda Berikutnya!</span>
                        @endif
                      </p>
                    </div>
                  </div>
                  <span class="material-symbols-outlined text-gray-300">hourglass_top</span>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100 text-left space-y-3">
                  <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                      <span class="text-gray-400 font-medium block">Nama Pemegang Tiket</span>
                      <span class="font-bold text-[#181C20]">{{ $tiket->pelanggan->nama }}</span>
                    </div>
                    <div>
                      <span class="text-gray-400 font-medium block">Waktu Pengambilan</span>
                      <span class="font-semibold text-gray-700">
                          {{ $tiket->waktu_dibuat ? \Carbon\Carbon::parse($tiket->waktu_dibuat)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}
                      </span>
                    </div>
                  </div>

                  @if($tiket->keluhan_awal)
                  <div class="pt-2">
                    <span class="text-gray-400 text-[11px] font-medium block">Catatan Keluhan Awal</span>
                    <p class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-xl border border-gray-100 italic mt-1">
                      "{{ $tiket->keluhan_awal }}"
                    </p>
                  </div>
                  @endif
                </div>
              </div>

              <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 text-center text-[11px] text-gray-400 flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-gray-400 animate-pulse">sync</span>
                <span>Halaman memperbarui status secara otomatis</span>
              </div>
          </div>
      @endif

      @if($tiket->status == 'Diproses')
          <div class="bg-gradient-to-br from-emerald-500 via-teal-600 to-emerald-700 rounded-3xl shadow-xl overflow-hidden text-white transition-all transform scale-[1.01]">
              <div class="bg-white/10 backdrop-blur-md px-6 py-3 flex items-center justify-between border-b border-white/10">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-white animate-bounce"></span>
                  <span class="text-xs font-extrabold tracking-wider uppercase text-emerald-100">Dipanggil Petugas CS</span>
                </div>
                <span class="text-[11px] font-bold bg-white/20 px-2.5 py-0.5 rounded-full">CALL #{{ ($tiket->jumlah_dipanggil ?? 0) + 1 }}</span>
              </div>

              <div class="p-6 sm:p-8 text-center">
                <p class="text-xs font-medium uppercase tracking-wider text-emerald-100">Silakan Menuju Loket</p>
                <h2 class="text-6xl sm:text-7xl font-black tracking-tight my-2 drop-shadow-md">
                  {{ $tiket->nomor_antrian }}
                </h2>

                @if(!empty($tiket->kode_tiket))
                    <p class="text-xs font-mono text-emerald-100 tracking-wider">
                        Ref ID: <span class="font-semibold text-white">{{ $tiket->kode_tiket }}</span>
                    </p>
                @endif

                <div class="mt-6 bg-white/15 backdrop-blur-md rounded-2xl p-4 text-center border border-white/20 shadow-inner">
                  <p class="text-[11px] text-emerald-100 font-medium">Petugas yang Melayani Anda:</p>
                  <p class="text-xl font-extrabold mt-0.5 text-white">{{ $tiket->cs->nama_lengkap ?? 'Customer Service' }}</p>
                  
                  <div class="mt-3 inline-flex items-center gap-1.5 bg-white text-emerald-800 px-4 py-1.5 rounded-xl font-extrabold text-sm shadow-sm">
                    <span class="material-symbols-outlined text-base text-emerald-600">desktop_windows</span>
                    <span>MEJA LOKET {{ $tiket->cs->nomor_meja ?? '1' }}</span>
                  </div>
                </div>

                <div class="mt-6 pt-4 border-t border-white/10 text-xs text-emerald-100 space-y-1">
                  <p><strong class="text-white">Nama:</strong> {{ $tiket->pelanggan->nama }}</p>
                  <p><strong class="text-white">Layanan:</strong> {{ $tiket->layanan->nama_layanan }}</p>
                </div>
              </div>

              <div class="bg-emerald-800/40 px-6 py-3 text-center text-[11px] text-emerald-100 font-medium flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-sm">campaign</span>
                <span>Harap bawa tiket ini saat menuju meja petugas CS</span>
              </div>
          </div>
      @endif

      @if($tiket->status == 'Selesai')
          <div x-data="{ 
                  count: 4, 
                  init() {
                      let timer = setInterval(() => {
                          this.count--;
                          if (this.count <= 0) {
                              clearInterval(timer);
                              window.location.href = '{{ route('antrean.index') }}';
                          }
                      }, 1000);
                  }
               }" 
               class="bg-white rounded-3xl border border-emerald-100 shadow-lg overflow-hidden text-center p-6 sm:p-8">
              
              <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm shadow-emerald-200">
                <span class="material-symbols-outlined text-4xl">task_alt</span>
              </div>

              <h3 class="text-xl font-extrabold text-[#181C20]">Layanan Telah Selesai</h3>
              <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">Terima kasih telah menggunakan layanan Indibiz Customer Service Desk.</p>

              <div class="mt-5 bg-gray-50 p-4 rounded-2xl border border-gray-100 text-left text-xs space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-400">Nomor Antrean:</span>
                  <span class="font-bold text-[#181C20]">{{ $tiket->nomor_antrian }}</span>
                </div>
                @if(!empty($tiket->kode_tiket))
                <div class="flex justify-between font-mono">
                  <span class="text-gray-400">Ref ID:</span>
                  <span class="font-semibold text-gray-600">{{ $tiket->kode_tiket }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                  <span class="text-gray-400">Petugas CS:</span>
                  <span class="font-semibold text-gray-700">{{ $tiket->cs->nama_lengkap ?? 'CS Staff' }}</span>
                </div>
                @if($tiket->keluhan_final)
                <div class="pt-2 border-t border-gray-200/60">
                  <span class="text-gray-400 text-[11px] block">Catatan Penyelesaian:</span>
                  <span class="font-medium text-gray-700 italic">"{{ $tiket->keluhan_final }}"</span>
                </div>
                @endif
              </div>

              <div class="mt-6 p-3 bg-red-50/60 rounded-xl border border-red-100 text-xs text-gray-600 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base text-[#EE2E24] animate-spin">progress_activity</span>
                <span>Mengalihkan ke halaman utama dalam <strong class="text-[#EE2E24] font-bold text-sm" x-text="count">4</strong> detik...</span>
              </div>
          </div>
      @endif

      @if($tiket->status == 'Batal')
          <div class="bg-white rounded-3xl border border-red-100 shadow-lg overflow-hidden text-center p-6 sm:p-8">
              
              <div class="w-16 h-16 bg-red-100 text-[#EE2E24] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm shadow-red-200">
                <span class="material-symbols-outlined text-4xl">event_busy</span>
              </div>

              <h3 class="text-xl font-extrabold text-[#181C20]">Tiket Antrean Dibatalkan</h3>
              <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                Maaf, tiket antrean Anda otomatis dibatalkan karena tidak merespon panggilan petugas CS.
              </p>

              <div class="mt-5 bg-red-50/40 p-4 rounded-2xl border border-red-100 text-left text-xs space-y-1.5">
                <div class="flex justify-between">
                  <span class="text-gray-500">Nomor Tiket:</span>
                  <span class="font-bold text-[#EE2E24]">{{ $tiket->nomor_antrian }}</span>
                </div>
                @if(!empty($tiket->kode_tiket))
                <div class="flex justify-between font-mono">
                  <span class="text-gray-500">Ref ID:</span>
                  <span class="font-semibold text-gray-600">{{ $tiket->kode_tiket }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                  <span class="text-gray-500">Jumlah Panggilan:</span>
                  <span class="font-semibold text-gray-700">{{ $tiket->jumlah_dipanggil ?? 2 }}x Dipanggil</span>
                </div>
              </div>

              <div class="mt-6">
                <a href="{{ route('antrean.index') }}" 
                   class="w-full inline-flex items-center justify-center gap-2 bg-[#EE2E24] hover:bg-[#C01006] text-white font-extrabold text-xs py-3.5 px-6 rounded-xl shadow-md transition-all">
                  <span class="material-symbols-outlined text-base">add_card</span>
                  <span>Ambil Tiket Baru</span>
                </a>
              </div>
          </div>
      @endif

    </main>

    <footer class="py-4 text-center text-[11px] text-gray-400 border-t border-gray-100 bg-white">
      <p>&copy; {{ date('Y') }} Indibiz Service Desk &bull; All Rights Reserved</p>
    </footer>

    <script>
        var audioCtx = null;
        var intervalBuzzer = null;
        var lastCallStatus = '';
        var lastCallCount = -1;

        // 1. MINTA IZIN WEB NOTIFICATION SAAT HALAMAN DIBUKA
        document.addEventListener('DOMContentLoaded', function() {
            if ("Notification" in window) {
                if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                    Notification.requestPermission();
                }
            }
        });

        // UNLOCK IZIN AUDIO DENGAN TOUCH/KLIK
        function unlockAudio() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            } catch (e) {
                console.log("Audio Unlock Err:", e);
            }
        }
        document.addEventListener('touchstart', unlockAudio, { once: true });
        document.addEventListener('click', unlockAudio, { once: true });

        // 2. FUNGSI UNTUK MEMUNCULKAN NOTIFIKASI BANNER SISTEM HP
        function showPopUpNotification(nomorAntrian, nomorMeja) {
            if ("Notification" in window && Notification.permission === "granted") {
                try {
                    var options = {
                        body: 'Nomor Antrean ' + nomorAntrian + ' sedang dipanggil di MEJA LOKET ' + nomorMeja + '. Silakan menuju ke loket CS sekarang!',
                        icon: '{{ asset("img/LogoIcon.png") }}',
                        badge: '{{ asset("img/LogoIcon.png") }}',
                        vibrate: [500, 200, 500, 200, 500],
                        requireInteraction: true
                    };
                    var notif = new Notification('📢 GILDAN ANTREAN ANDA DIPANGGIL!', options);
                    notif.onclick = function() {
                        window.focus();
                        this.close();
                    };
                } catch(e) {
                    console.log("Web Notification error:", e);
                }
            }
        }

        // 3. FUNGSI FLASH KEDIP LAYAR BERWARNA (VISUAL ALERT)
        function triggerFlashVisual() {
            var body = document.getElementById('body-container');
            if (body) {
                body.classList.add('animate-flash');
                setTimeout(function() {
                    body.classList.remove('animate-flash');
                }, 5000);
            }
        }

        // 4. TRIGER UTAMA SAAT DIPANGGIL
        function triggerNotifikasiPanggilan(nomorAntrian, nomorMeja) {
            if (intervalBuzzer) clearInterval(intervalBuzzer);

            // A. Panggil Web Banner Notification
            showPopUpNotification(nomorAntrian, nomorMeja);

            // B. Panggil Flash Kedip Layar Visual
            triggerFlashVisual();

            // C. Suara Dering Web Audio API (jika volume dinyalakan)
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                function playLoudRingtone() {
                    if (!audioCtx) return;
                    var osc1 = audioCtx.createOscillator();
                    var gain1 = audioCtx.createGain();
                    osc1.type = 'sawtooth';
                    osc1.frequency.setValueAtTime(850, audioCtx.currentTime);
                    gain1.gain.setValueAtTime(0.8, audioCtx.currentTime);
                    gain1.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.35);
                    osc1.connect(gain1);
                    gain1.connect(audioCtx.destination);
                    osc1.start();
                    osc1.stop(audioCtx.currentTime + 0.35);
                }

                playLoudRingtone();
                intervalBuzzer = setInterval(function() {
                    playLoudRingtone();
                }, 800);

            } catch (e) {
                console.log("Audio Error:", e);
            }

            // D. Auto Stop Setelah 5 Detik
            setTimeout(function() {
                if (intervalBuzzer) clearInterval(intervalBuzzer);
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('area-tiket-realtime');
            if (el) {
                lastCallStatus = el.getAttribute('data-status');
                lastCallCount = parseInt(el.getAttribute('data-dipanggil') || '0');

                if (lastCallStatus === 'Diproses') {
                    triggerNotifikasiPanggilan('{{ $tiket->nomor_antrian }}', '{{ $tiket->cs->nomor_meja ?? "1" }}');
                }
            }
        });

        // REALTIME POLLING UPDATE
        setInterval(function() {
            var elemenLama = document.getElementById('area-tiket-realtime');
            if (!elemenLama) return;

            var statusSaatIni = elemenLama.getAttribute('data-status');
            if (statusSaatIni === 'Selesai' || statusSaatIni === 'Batal') return;

            fetch(window.location.href)
                .then(function(response) { 
                    return response.text(); 
                })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    
                    var elemenBaru = doc.getElementById('area-tiket-realtime');
                    if (elemenBaru && elemenLama) {
                        var statusBaru = elemenBaru.getAttribute('data-status');
                        var countBaru = parseInt(elemenBaru.getAttribute('data-dipanggil') || '0');

                        elemenLama.innerHTML = elemenBaru.innerHTML;
                        elemenLama.setAttribute('data-status', statusBaru);
                        elemenLama.setAttribute('data-dipanggil', countBaru);

                        var isFirstCall = (lastCallStatus !== 'Diproses' && statusBaru === 'Diproses');
                        var isRecall = (statusBaru === 'Diproses' && countBaru !== lastCallCount);

                        if (isFirstCall || isRecall) {
                            lastCallStatus = statusBaru;
                            lastCallCount = countBaru;
                            triggerNotifikasiPanggilan('{{ $tiket->nomor_antrian }}', '{{ $tiket->cs->nomor_meja ?? "1" }}');
                        }
                    }
                })
                .catch(function(error) { 
                    console.error('Gagal memperbarui status tiket:', error); 
                });
        }, 3000);
    </script>

</body>
</html>
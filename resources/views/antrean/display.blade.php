<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Monitor Antrean - Indibiz Service Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0F172A; }
    </style>
</head>
<body class="text-white h-screen flex flex-col justify-between overflow-hidden select-none">

    <!-- HEADER / TOP BAR -->
    <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-8 py-4 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-9 object-contain brightness-0 invert">
            <div class="h-6 w-[1px] bg-slate-700"></div>
            <span class="text-sm font-bold tracking-widest text-red-500 uppercase">Ruang Tunggu Customer Service Desk</span>
        </div>
        <div class="flex items-center gap-6 text-slate-300 font-bold text-lg">
            <div id="live-clock" class="font-mono bg-slate-800 px-4 py-1.5 rounded-xl border border-slate-700">00:00:00 WIB</div>
        </div>
    </header>

    <!-- MAIN CONTENT GRID (TV LAYOUT) -->
    <main class="grid grid-cols-12 gap-6 p-6 flex-1 overflow-hidden">
        
        <!-- KOLOM KIRI: SEDANG DIPANGGIL -->
        <div class="col-span-8 flex flex-col gap-4">
            <div class="bg-gradient-to-r from-red-600 to-rose-700 px-6 py-3 rounded-2xl flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl animate-bounce">campaign</span>
                    <h2 class="text-lg font-extrabold uppercase tracking-wider">Sedang Dipanggil</h2>
                </div>

                <!-- DROPDOWN PILIHAN SUARA & TEST BUTTON -->
                <div class="flex items-center gap-2">
                    <select id="voice-select" class="bg-slate-900/80 text-white border border-white/20 text-xs rounded-xl px-3 py-1.5 font-semibold focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option value="">Memuat Pilihan Suara...</option>
                    </select>
                    
                    <button onclick="playTestCall()" class="text-xs bg-black/30 hover:bg-black/50 text-white border border-white/20 px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">volume_up</span>
                        <span>Tes Suara</span>
                    </button>
                </div>
            </div>

            <!-- CONTAINER CARD PANGGILAN UTAMA -->
            <div id="box-sedang-dipanggil" class="grid grid-cols-2 gap-4 flex-1 overflow-y-auto pr-2">
                <div class="col-span-2 bg-slate-800/60 border border-slate-700 rounded-3xl flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <span class="material-symbols-outlined text-6xl mb-2 animate-spin">progress_activity</span>
                    <p class="text-base font-semibold">Menunggu panggilan antrean berikutnya...</p>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: DAFTAR ANTREAN BERIKUTNYA -->
        <div class="col-span-4 flex flex-col gap-4">
            <div class="bg-slate-800 border border-slate-700 px-6 py-3 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">hourglass_top</span>
                    <h2 class="text-base font-bold uppercase tracking-wider text-slate-200">Antrean Berikutnya</h2>
                </div>
                <span class="text-xs bg-slate-700 px-2.5 py-1 rounded-lg text-slate-300 font-mono" id="total-menunggu">0 Orang</span>
            </div>

            <!-- LIST ANTREAN MENUNGGU -->
            <div id="box-antrean-menunggu" class="flex flex-col gap-3 flex-1 overflow-y-auto pr-1">
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-900 border-t border-slate-800 px-8 py-3 text-center text-xs text-slate-500 shrink-0 flex items-center justify-between">
        <p>&copy; {{ date('Y') }} Indibiz Service Desk &bull; Sistem Antrean Terpadu</p>
        <p class="text-slate-400 font-medium" id="voice-status">Pilih karakter suara pada header di atas.</p>
    </footer>

    <!-- JAVASCRIPT LOGIC SELECTABLE & FORCED INDONESIAN VOICE -->
    <script>
        var lastCallUniqueKey = '';
        var audioCtx = null;
        var availableVoices = [];

        // Jam Digital Realtime
        setInterval(function() {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').innerText = hours + ':' + minutes + ':' + seconds + ' WIB';
        }, 1000);

        // Populate Dropdown Suara yang Tersedia di Browser
        function populateVoiceList() {
            if (!('speechSynthesis' in window)) return;
            
            availableVoices = window.speechSynthesis.getVoices();
            var selectElem = document.getElementById('voice-select');
            selectElem.innerHTML = '';

            if (availableVoices.length === 0) return;

            // Prioritaskan Suara Bahasa Indonesia
            var indoVoices = availableVoices.filter(function(v) {
                return v.lang.includes('id') || v.lang.includes('ID') || v.name.toLowerCase().includes('indonesi');
            });

            // Jika ada suara Bahasa Indonesia, tampilkan paling atas
            var displayVoices = indoVoices.length > 0 ? indoVoices : availableVoices;

            displayVoices.forEach(function(voice, index) {
                var option = document.createElement('option');
                option.value = voice.name;
                option.textContent = voice.name + ' (' + voice.lang + ')';
                
                // Set default jika mengandung kata Gadis/Indonesian/Google
                if (voice.name.includes('Gadis') || voice.name.includes('Indonesian') || voice.lang === 'id-ID') {
                    option.selected = true;
                }
                
                selectElem.appendChild(option);
            });

            var statusElem = document.getElementById('voice-status');
            if (indoVoices.length > 0) {
                statusElem.innerText = "Ditemukan " + indoVoices.length + " Karakter Suara Bahasa Indonesia di Perangkat Ini.";
            } else {
                statusElem.innerText = "Sistem menggunakan paksaan bahasa Indonesia (id-ID) pada engine bawaan.";
            }
        }

        if ('speechSynthesis' in window) {
            populateVoiceList();
            window.speechSynthesis.onvoiceschanged = populateVoiceList;
        }

        // 1. BEL DING-DONG ANTREAN
        function playChime(onComplete) {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();

                function tone(freq, duration, delay) {
                    setTimeout(() => {
                        var osc = audioCtx.createOscillator();
                        var gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + duration);
                    }, delay);
                }

                tone(587.33, 0.6, 0);   // Ding
                tone(783.99, 0.8, 500); // Dong

                if (onComplete) setTimeout(onComplete, 1200);
            } catch (e) {
                if (onComplete) onComplete();
            }
        }

        // Konversi Angka ke Kata Bahasa Indonesia Murni
        function formatEjaanIndonesia(nomor) {
            return nomor
                .replace(/0/g, ' nol ')
                .replace(/1/g, ' satu ')
                .replace(/2/g, ' dua ')
                .replace(/3/g, ' tiga ')
                .replace(/4/g, ' empat ')
                .replace(/5/g, ' lima ')
                .replace(/6/g, ' enam ')
                .replace(/7/g, ' tujuh ')
                .replace(/8/g, ' delapan ')
                .replace(/9/g, ' sembilan ')
                .replace(/-/g, ' ');
        }

        // 2. SUARA PANGGILAN ANTREAN
        function speakQueueCall(nomorAntrian, nomorMeja) {
            playChime(function() {
                var ejaanNomor = formatEjaanIndonesia(nomorAntrian);
                var ejaanMeja = formatEjaanIndonesia(nomorMeja.toString());

                var teksNarasi = 'Nomor tiket ' + ejaanNomor + ', silakan menuju ke meja ' + ejaanMeja;

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel(); // Riset antrean audio sebelumnya
                    
                    var utterance = new SpeechSynthesisUtterance(teksNarasi);
                    
                    // MEMAKSAKAN BAHASA KE INDONESIA
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85;  // Tempo pengucapan
                    utterance.pitch = 1.1;   // Pitch agak tinggi (suara wanita)

                    // Ambil suara terpilih dari Dropdown Select
                    var selectedVoiceName = document.getElementById('voice-select').value;
                    if (selectedVoiceName && availableVoices.length > 0) {
                        var chosenVoice = availableVoices.find(v => v.name === selectedVoiceName);
                        if (chosenVoice) {
                            utterance.voice = chosenVoice;
                        }
                    }

                    window.speechSynthesis.speak(utterance);
                }
            });
        }

        function playTestCall() {
            speakQueueCall('A-005', '1');
        }

        // 3. FETCH DATA REALTIME KE SERVER
        function fetchDisplayData() {
            fetch('{{ url("/api/display-antrean-data") }}')
                .then(response => response.json())
                .then(data => {
                    renderSedangDipanggil(data.sedangDipanggil);
                    renderAntreanMenunggu(data.antreanMenunggu);
                })
                .catch(error => console.error('Gagal mengambil data display:', error));
        }

        function renderSedangDipanggil(listDipanggil) {
            var container = document.getElementById('box-sedang-dipanggil');
            
            if (!listDipanggil || listDipanggil.length === 0) {
                container.innerHTML = `
                    <div class="col-span-2 bg-slate-800/40 border border-slate-800 rounded-3xl flex flex-col items-center justify-center p-12 text-center text-slate-500 h-full">
                        <span class="material-symbols-outlined text-5xl mb-2 text-slate-600">desktop_windows</span>
                        <p class="text-sm font-semibold">Belum ada antrean yang dipanggil saat ini.</p>
                    </div>
                `;
                return;
            }

            var currentActive = listDipanggil[0];

            if (currentActive) {
                var countDipanggil = currentActive.jumlah_dipanggil || 0;
                var currentKey = currentActive.id + '_' + countDipanggil;

                if (currentKey !== lastCallUniqueKey) {
                    lastCallUniqueKey = currentKey;
                    var nomorMeja = currentActive.cs ? (currentActive.cs.nomor_meja || '1') : '1';
                    speakQueueCall(currentActive.nomor_antrian, nomorMeja);
                }
            }

            var html = '';
            listDipanggil.forEach(function(item) {
                var namaCs = item.cs ? item.cs.nama_lengkap : 'Customer Service';
                var noMeja = item.cs ? (item.cs.nomor_meja || '1') : '1';
                var namaLayanan = item.layanan ? item.layanan.nama_layanan : 'Layanan';
                var callCount = (item.jumlah_dipanggil || 0) + 1;

                html += `
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 border-2 border-emerald-500/50 rounded-3xl p-6 flex flex-col justify-between shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-emerald-500 text-slate-950 font-extrabold text-[10px] px-4 py-1 rounded-bl-xl uppercase tracking-widest">
                            CALL #${callCount}
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nomor Antrean</span>
                            <h1 class="text-6xl font-black text-emerald-400 tracking-tight my-1">${item.nomor_antrian}</h1>
                            <p class="text-xs font-semibold text-slate-300 bg-slate-800/80 px-3 py-1 rounded-lg inline-block border border-slate-700">${namaLayanan}</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-700/60 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400">Petugas CS</p>
                                <p class="text-xs font-extrabold text-white">${namaCs}</p>
                            </div>
                            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-2 rounded-2xl text-center">
                                <span class="text-[10px] font-bold block uppercase">Loket</span>
                                <span class="text-lg font-black">MEJA ${noMeja}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function renderAntreanMenunggu(listMenunggu) {
            var container = document.getElementById('box-antrean-menunggu');
            document.getElementById('total-menunggu').innerText = listMenunggu.length + ' Orang';

            if (!listMenunggu || listMenunggu.length === 0) {
                container.innerHTML = `
                    <div class="bg-slate-800/40 border border-slate-800 rounded-2xl p-6 text-center text-slate-500">
                        <p class="text-xs font-medium">Tidak ada antrean menunggu.</p>
                    </div>
                `;
                return;
            }

            var html = '';
            listMenunggu.forEach(function(item, index) {
                var namaLayanan = item.layanan ? item.layanan.nama_layanan : 'Layanan';
                var badgeColor = index === 0 ? 'bg-amber-500 text-slate-950 border-amber-400 animate-pulse' : 'bg-slate-800 text-slate-300 border-slate-700';

                html += `
                    <div class="bg-slate-800/70 border border-slate-700/80 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-black font-mono px-3 py-1.5 rounded-xl border ${badgeColor}">
                                ${item.nomor_antrian}
                            </span>
                            <div>
                                <p class="text-xs font-bold text-white">${item.pelanggan ? item.pelanggan.nama : 'Tamu'}</p>
                                <p class="text-[10px] text-slate-400 font-medium">${namaLayanan}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-900 px-2.5 py-1 rounded-lg">Menunggu</span>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        fetchDisplayData();
        setInterval(fetchDisplayData, 3000);
    </script>
</body>
</html>
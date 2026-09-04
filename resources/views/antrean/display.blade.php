<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Monitor Antrean (9:16) - Indibiz Service Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #0A0F1D; 
        }
        /* Custom Scrollbar minimalis */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
    </style>
</head>
<body class="text-white h-screen w-screen flex flex-col justify-between overflow-hidden select-none p-4 gap-3">

    <!-- HEADER TOP BAR (OPTIMIZED FOR 9:16 PORTRAIT) -->
    <header class="bg-[#111827]/90 backdrop-blur-md border border-slate-800/80 rounded-2xl p-3.5 flex flex-col gap-2 shrink-0 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-6 object-contain brightness-0 invert">
                <span class="text-[10px] font-extrabold tracking-widest bg-red-600/20 text-red-500 border border-red-500/30 px-2 py-0.5 rounded-md uppercase">Service Desk</span>
            </div>
            <div id="live-clock" class="font-mono text-sm font-black bg-slate-900 border border-slate-700/80 px-3 py-1 rounded-xl text-emerald-400">
                00:00:00 WIB
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-800/80 pt-2 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-red-500">graphic_eq</span>
                <select id="voice-select" class="bg-slate-900 text-slate-300 border border-slate-700 text-[11px] rounded-lg px-2 py-1 font-semibold focus:outline-none focus:ring-1 focus:ring-red-500 max-w-[180px] truncate">
                    <option value="">Memuat Suara...</option>
                </select>
            </div>
            <button onclick="playTestCall()" class="bg-red-600/20 hover:bg-red-600/40 text-red-400 border border-red-500/30 px-2.5 py-1 rounded-lg font-bold text-[10px] transition-all flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-xs">volume_up</span>
                <span>PANGGIL ULANG</span>
            </button>
        </div>
    </header>

    <!-- MAIN CONTAINER (LAYOUT PORTRAIT 9:16) -->
    <main class="flex-1 flex flex-col gap-3 min-h-0 overflow-hidden">
        
        <!-- SECTION 1: HERO CONTAINER - SEDANG DIPANGGIL -->
        <div class="bg-gradient-to-b from-[#161F36] to-[#0F172A] border-2 border-red-500/40 rounded-3xl p-5 flex flex-col justify-between shadow-2xl relative overflow-hidden shrink-0">
            <!-- Header Label -->
            <div class="flex items-center justify-between border-b border-white/10 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                    <span class="text-xs font-black tracking-widest text-red-500 uppercase">Sedang Dipanggil</span>
                </div>
                <div class="flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-xs">graphic_eq</span>
                    <span>Panggilan Suara Aktif</span>
                </div>
            </div>

            <!-- Utama Display Nomor Tiket -->
            <div id="box-sedang-dipanggil" class="py-4 text-center">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Nomor Tiket Antrean</span>
                <h1 id="hero-nomor-antrian" class="text-7xl font-black text-white tracking-tight drop-shadow-[0_0_25px_rgba(238,46,36,0.3)]">
                    ---
                </h1>
                
                <div class="inline-flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 px-4 py-1.5 rounded-xl font-bold text-xs mt-3">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span>SILAKAN MENUJU MEJA PELAYANAN SEKARANG</span>
                </div>

                <!-- Detail Meja CS & Layanan -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/10 text-left">
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Tujuan Loket</span>
                        <p id="hero-nama-meja" class="text-lg font-black text-white leading-tight">MEJA CS --</p>
                    </div>
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Kategori Layanan</span>
                        <p id="hero-nama-layanan" class="text-xs font-bold text-red-400 truncate mt-0.5">--</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: STATUS MEJA PELAYANAN (GRID 2x2) -->
        <div class="bg-[#111827]/80 border border-slate-800/80 rounded-2xl p-3 flex flex-col gap-2 shrink-0">
            <div class="flex items-center justify-between text-[11px] font-bold border-b border-slate-800 pb-1.5">
                <span class="text-slate-300 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-red-500">desktop_windows</span>
                    Status Meja Pelayanan
                </span>
                <span class="text-slate-500 text-[10px]">Update Realtime</span>
            </div>

            <div id="grid-meja-pelayanan" class="grid grid-cols-2 gap-2">
                <!-- Meja Cards akan dirender via JS di sini -->
            </div>
        </div>

        <!-- SECTION 3: DAFTAR ANTREAN BERIKUTNYA -->
        <div class="bg-[#111827]/80 border border-slate-800/80 rounded-2xl p-3 flex-1 flex flex-col min-h-0">
            <div class="flex items-center justify-between text-[11px] font-bold border-b border-slate-800 pb-2 mb-2">
                <span class="text-slate-300 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-amber-400">hourglass_top</span>
                    Antrean Berikutnya
                </span>
                <span id="total-menunggu" class="text-[10px] bg-slate-800 text-amber-400 border border-slate-700 px-2 py-0.5 rounded-md font-mono">0 Menunggu</span>
            </div>

            <div id="box-antrean-menunggu" class="flex flex-col gap-2 overflow-y-auto custom-scrollbar flex-1 pr-1">
                <!-- List Antrean Menunggu dirender via JS -->
            </div>
        </div>

    </main>

    <!-- FOOTER MARQUEE / RUNNING TEXT -->
    <footer class="bg-red-600 text-white rounded-xl px-4 py-2 flex items-center gap-3 shrink-0 overflow-hidden text-xs shadow-lg">
        <span class="bg-black/30 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded shrink-0 flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">info</span> Informasi
        </span>
        <marquee behavior="scroll" direction="left" class="font-bold tracking-wide">
            Selamat Datang di Telkom Indibiz Service Desk &bull; Ciptakan Peluang, Wujudkan Harapan Bersama Ekosistem Solusi Digital Dunia Usaha &bull; Harap Siapkan Kartu Identitas dan Nomor Tiket Antrean Anda saat Menuju Loket Pelayanan.
        </marquee>
    </footer>

    <!-- JAVASCRIPT LOGIC REALTIME & TTS VOICE -->
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

        // Populate Dropdown Suara Browser
        function populateVoiceList() {
            if (!('speechSynthesis' in window)) return;
            
            availableVoices = window.speechSynthesis.getVoices();
            var selectElem = document.getElementById('voice-select');
            selectElem.innerHTML = '';

            if (availableVoices.length === 0) return;

            var indoVoices = availableVoices.filter(function(v) {
                return v.lang.includes('id') || v.lang.includes('ID') || v.name.toLowerCase().includes('indonesi');
            });

            var displayVoices = indoVoices.length > 0 ? indoVoices : availableVoices;

            displayVoices.forEach(function(voice) {
                var option = document.createElement('option');
                option.value = voice.name;
                option.textContent = voice.name + ' (' + voice.lang + ')';
                if (voice.name.includes('Gadis') || voice.name.includes('Indonesian') || voice.lang === 'id-ID') {
                    option.selected = true;
                }
                selectElem.appendChild(option);
            });
        }

        if ('speechSynthesis' in window) {
            populateVoiceList();
            window.speechSynthesis.onvoiceschanged = populateVoiceList;
        }

        // Chime Bel Antrean
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

                tone(587.33, 0.6, 0);   
                tone(783.99, 0.8, 500); 

                if (onComplete) setTimeout(onComplete, 1200);
            } catch (e) {
                if (onComplete) onComplete();
            }
        }

        function formatEjaanIndonesia(nomor) {
            var bagian = nomor.split('-');
            if (bagian.length > 1) {
                var kodeHuruf = bagian[0]; 
                var angkaStr = bagian[1];   
                var ejaanAngka = angkaStr
                    .replace(/0/g, ' nol ').replace(/1/g, ' satu ').replace(/2/g, ' dua ')
                    .replace(/3/g, ' tiga ').replace(/4/g, ' empat ').replace(/5/g, ' lima ')
                    .replace(/6/g, ' enam ').replace(/7/g, ' tujuh ').replace(/8/g, ' delapan ')
                    .replace(/9/g, ' sembilan ');

                return kodeHuruf + ', ' + ejaanAngka;
            }

            return nomor
                .replace(/0/g, ' nol ').replace(/1/g, ' satu ').replace(/2/g, ' dua ')
                .replace(/3/g, ' tiga ').replace(/4/g, ' empat ').replace(/5/g, ' lima ')
                .replace(/6/g, ' enam ').replace(/7/g, ' tujuh ').replace(/8/g, ' delapan ')
                .replace(/9/g, ' sembilan ');
        }

        // Suara Panggilan Antrean
        function speakQueueCall(nomorAntrian, nomorMeja) {
            playChime(function() {
                var ejaanNomor = formatEjaanIndonesia(nomorAntrian);
                var ejaanMeja = formatEjaanIndonesia(nomorMeja.toString());
                var teksNarasi = 'Nomor tiket ' + ejaanNomor + ', silakan menuju ke meja ' + ejaanMeja;

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel(); 
                    var utterance = new SpeechSynthesisUtterance(teksNarasi);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85;  
                    utterance.pitch = 1.1;   

                    var selectedVoiceName = document.getElementById('voice-select').value;
                    if (selectedVoiceName && availableVoices.length > 0) {
                        var chosenVoice = availableVoices.find(v => v.name === selectedVoiceName);
                        if (chosenVoice) utterance.voice = chosenVoice;
                    }

                    window.speechSynthesis.speak(utterance);
                }
            });
        }

        function playTestCall() {
            var num = document.getElementById('hero-nomor-antrian').innerText;
            var meja = document.getElementById('hero-nama-meja').innerText.replace('MEJA CS ', '');
            speakQueueCall(num !== '---' ? num : 'A-024', meja !== '--' ? meja : '1');
        }

        // Fetch Data Display dari API
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
            if (!listDipanggil || listDipanggil.length === 0) {
                document.getElementById('hero-nomor-antrian').innerText = '---';
                document.getElementById('hero-nama-meja').innerText = 'MEJA CS --';
                document.getElementById('hero-nama-layanan').innerText = 'Belum Ada Panggilan';
                document.getElementById('grid-meja-pelayanan').innerHTML = '<div class="col-span-2 text-center text-xs text-slate-500 py-3">Semua loket standby</div>';
                return;
            }

            var activeCall = listDipanggil[0];

            if (activeCall) {
                var noMeja = activeCall.cs ? (activeCall.cs.nomor_meja || '1') : '1';
                var namaLayanan = activeCall.layanan ? activeCall.layanan.nama_layanan : 'Layanan CS';

                document.getElementById('hero-nomor-antrian').innerText = activeCall.nomor_antrian;
                document.getElementById('hero-nama-meja').innerText = 'MEJA CS ' + String(noMeja).padStart(2, '0');
                document.getElementById('hero-nama-layanan').innerText = namaLayanan;

                var countDipanggil = activeCall.jumlah_dipanggil || 0;
                var csId = activeCall.user_id || (activeCall.cs ? activeCall.cs.id : '0');
                var currentKey = activeCall.id + '_cs' + csId + '_' + countDipanggil;

                if (currentKey !== lastCallUniqueKey) {
                    lastCallUniqueKey = currentKey;
                    speakQueueCall(activeCall.nomor_antrian, noMeja);
                }
            }

            // Render Grid 2x2 Status Loket Meja
            var gridHtml = '';
            listDipanggil.slice(0, 4).forEach(function(item) {
                var noMeja = item.cs ? (item.cs.nomor_meja || '1') : '1';
                var namaCs = item.cs ? item.cs.nama_lengkap : 'CS Staff';
                
                gridHtml += `
                    <div class="bg-slate-900/90 border border-emerald-500/30 rounded-xl p-2.5 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-bold text-slate-400 uppercase">MEJA CS ${String(noMeja).padStart(2, '0')}</span>
                            <span class="bg-emerald-500/20 text-emerald-400 font-extrabold px-1.5 py-0.5 rounded text-[9px] uppercase">MELAYANI</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl font-black text-emerald-400 font-mono tracking-tight">${item.nomor_antrian}</span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate border-t border-slate-800 pt-1">
                            Petugas: <strong class="text-slate-200">${namaCs}</strong>
                        </div>
                    </div>
                `;
            });

            document.getElementById('grid-meja-pelayanan').innerHTML = gridHtml;
        }

        function renderAntreanMenunggu(listMenunggu) {
            var container = document.getElementById('box-antrean-menunggu');
            document.getElementById('total-menunggu').innerText = (listMenunggu ? listMenunggu.length : 0) + ' Menunggu';

            if (!listMenunggu || listMenunggu.length === 0) {
                container.innerHTML = `
                    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-4 text-center text-slate-500 text-xs">
                        Tidak ada antrean menunggu saat ini.
                    </div>
                `;
                return;
            }

            var html = '';
            listMenunggu.forEach(function(item, index) {
                var namaLayanan = item.layanan ? item.layanan.nama_layanan : 'Layanan';
                var isNext = index === 0;

                html += `
                    <div class="bg-slate-900/80 border ${isNext ? 'border-amber-500/50 bg-amber-500/5' : 'border-slate-800'} rounded-xl p-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-black font-mono px-2 py-1 rounded-lg ${isNext ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300'}">
                                ${item.nomor_antrian}
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-white truncate max-w-[130px]">${item.pelanggan ? item.pelanggan.nama : 'Tamu'}</p>
                                <p class="text-[10px] text-slate-400 truncate max-w-[130px]">${namaLayanan}</p>
                            </div>
                        </div>
                        <span class="text-[9px] font-bold uppercase ${isNext ? 'text-amber-400 bg-amber-500/10 border border-amber-500/30' : 'text-slate-500 bg-slate-900'} px-2 py-0.5 rounded">
                            ${isNext ? 'Siap Dipanggil' : 'Menunggu'}
                        </span>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Poll Data Realtime setiap 2,5 detik
        fetchDisplayData();
        setInterval(fetchDisplayData, 2500);
    </script>
</body>
</html>
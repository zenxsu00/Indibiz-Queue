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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #060913; 
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    </style>
</head>
<body class="text-white h-screen w-screen flex flex-col justify-between overflow-hidden select-none p-2.5 gap-2">

    <!-- TOP BAR HEADER -->
    <header class="bg-[#0D1322] border border-slate-800/80 rounded-2xl px-3.5 py-2 flex items-center justify-between shrink-0 shadow-lg">
        <div class="flex items-center gap-3">
            <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-5 object-contain brightness-0 invert">
            <div class="h-4 w-[1px] bg-slate-700"></div>
            <div>
                <h1 class="text-[9px] font-extrabold tracking-wider text-slate-400 uppercase leading-none">Pusat Layanan Terpadu Enterprise</h1>
                <p class="text-xs font-black text-slate-200 mt-0.5 leading-none">RUANG TUNGGU CUSTOMER SERVICE & ENTERPRISE DESK</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- WIDGET RATA-RATA DURASI SERVIS -->
            <div class="flex items-center gap-2 bg-slate-900/90 border border-slate-800 px-3 py-1 rounded-xl">
                <span class="material-symbols-outlined text-amber-400 text-sm">avg_time</span>
                <div>
                    <span class="text-[8px] font-bold text-slate-400 uppercase block leading-none">RATA-RATA PROSES</span>
                    <span id="label-avg-durasi" class="text-xs font-black text-amber-400 leading-none">± 10 Mnt / Tiket</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-xl">
                <span class="material-symbols-outlined text-xs text-red-500">graphic_eq</span>
                <span class="text-[10px] text-slate-400 font-bold">Synthesizer:</span>
                <select id="voice-select" class="bg-transparent text-slate-200 text-[11px] font-bold focus:outline-none max-w-[130px] truncate">
                    <option value="">Memuat Suara...</option>
                </select>
            </div>

            <button onclick="playTestCall()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 px-3 py-1 rounded-xl font-bold text-[10px] transition-all flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-sm">volume_up</span>
                <span>TEST SUARA</span>
            </button>

            <div id="live-clock" class="font-mono text-xs font-black bg-slate-900 border border-slate-700/80 px-2.5 py-1 rounded-xl text-emerald-400">
                00:00:00 WIB
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT: 2 KOLOM LAYOUT -->
    <main class="flex-1 grid grid-cols-12 gap-2.5 min-h-0 overflow-hidden">
        
        <!-- KOLOM KIRI (SPAN 8/9): DISPLAY PANGGILAN + STATUS MEJA -->
        <div class="col-span-8 lg:col-span-9 flex flex-col gap-2.5 min-h-0">
            
            <!-- BOX HERO: SEDANG DIPANGGIL -->
            <div id="box-hero-panggilan" class="flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border border-slate-800 rounded-2xl p-3.5 flex flex-col justify-between shadow-2xl relative overflow-hidden transition-all duration-500">
                
                <!-- HEADER SEDANG DIPANGGIL -->
                <div class="flex items-center justify-between border-b border-white/5 pb-2 shrink-0">
                    <div class="flex items-center gap-2">
                        <span id="hero-status-dot" class="w-2.5 h-2.5 rounded-full bg-slate-600"></span>
                        <span id="hero-status-title" class="text-xs font-black tracking-widest text-slate-400 uppercase">STANDBY</span>
                        <span id="hero-status-sub" class="text-xs font-bold text-slate-500 ml-2">Menunggu Panggilan</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] text-slate-500 font-mono">
                        <span class="material-symbols-outlined text-sm">graphic_eq</span>
                    </div>
                </div>

                <!-- CONTENT AREA: SEJAJAR NOMOR ANTREAN (KIRI) DAN DETAIL LOKET (KANAN) -->
                <div class="grid grid-cols-12 gap-3 my-auto items-center py-1">
                    
                    <!-- KIRI: NOMOR TIKET + BADGE (SPAN 7) -->
                    <div class="col-span-7 text-center border-r border-white/5 pr-3">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-0.5">NOMOR TIKET ANTREAN</span>
                        <h1 id="hero-nomor-antrian" class="text-6xl lg:text-7xl font-black text-slate-600 tracking-tight leading-none my-1">
                            ---
                        </h1>
                        
                        <div id="hero-badge-direksi" class="inline-flex items-center gap-1.5 bg-slate-800/50 border border-slate-700/50 text-slate-400 px-3 py-1 rounded-lg font-bold text-[11px] mt-1">
                            <span class="material-symbols-outlined text-xs">info</span>
                            <span id="hero-badge-text">LOKET SIAP MELAYANI</span>
                        </div>
                    </div>

                    <!-- KANAN: TUJUAN LOKET & KATEGORI LAYANAN (SPAN 5) -->
                    <div class="col-span-5 flex flex-col gap-2 pl-1">
                        <div class="bg-[#080D1A]/90 p-2.5 rounded-xl border border-slate-800/80">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">TUJUAN LOKET</span>
                            <p id="hero-nama-meja" class="text-lg lg:text-xl font-black text-white mt-0.5 leading-tight">MEJA CS --</p>
                        </div>
                        <div class="bg-[#080D1A]/90 p-2.5 rounded-xl border border-slate-800/80">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">KATEGORI LAYANAN</span>
                            <p id="hero-nama-layanan" class="text-xs font-bold text-slate-400 truncate mt-0.5 leading-tight">Belum Ada Panggilan</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- STATUS MEJA PELAYANAN (BARIS BWAH - DINAMIS) -->
            <div class="bg-[#0D1322] border border-slate-800/80 rounded-2xl p-2.5 flex flex-col gap-1.5 shrink-0">
                <div class="flex items-center justify-between text-[10px] font-bold border-b border-slate-800 pb-1">
                    <span class="text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        STATUS MEJA PELAYANAN CUSTOMER SERVICE
                    </span>
                    <span id="label-total-meja" class="text-slate-500 text-[9px]">Update Realtime</span>
                </div>

                <div id="grid-meja-pelayanan" class="grid gap-2">
                    <!-- Cards CS di-render secara dinamis via JS -->
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (SPAN 4/3): ANTREAN BERIKUTNYA -->
        <div class="col-span-4 lg:col-span-3 bg-[#0D1322] border border-slate-800/80 rounded-2xl p-3 flex flex-col h-full min-h-0 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2 mb-2 shrink-0">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-amber-400 text-sm">schedule</span>
                    <h2 class="text-xs font-black uppercase text-white leading-tight">ANTREAN BERIKUTNYA</h2>
                </div>
                <span id="total-menunggu" class="text-[9px] bg-slate-900 text-amber-400 border border-slate-800 px-2 py-0.5 rounded-full font-bold">0 Menunggu</span>
            </div>

            <div id="box-antrean-menunggu" class="flex flex-col gap-2 overflow-y-auto custom-scrollbar flex-1 pr-1">
                <!-- List Antrean Menunggu dirender via JS -->
            </div>

            <div class="mt-2 pt-2 border-t border-slate-800 shrink-0 bg-slate-900/50 p-2 rounded-xl border border-slate-800/50 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-white leading-tight">Pantau Antrean di Ponsel</p>
                    <p class="text-[8px] text-slate-400 mt-0.5">Scan QR Code pada struk nomor tiket Anda</p>
                </div>
                <div class="w-7 h-7 bg-white text-slate-900 rounded-lg flex items-center justify-center font-black text-xs shrink-0">
                    <span class="material-symbols-outlined text-sm">qr_code_2</span>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER MARQUEE / RUNNING TEXT -->
    <footer class="bg-[#0D1322] border border-slate-800 text-white rounded-xl p-1 flex items-center gap-2.5 shrink-0 overflow-hidden text-xs shadow-md">
        <span class="bg-red-600 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-lg shrink-0 flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">info</span> INFORMASI
        </span>
        <marquee behavior="scroll" direction="left" class="font-semibold text-slate-300 text-xs">
            Selamat Datang di Telkom Indibiz Service Desk &bull; Ciptakan Peluang, Wujudkan Harapan Bersama Ekosistem Solusi Digital Dunia Usaha &bull; Harap Siapkan Kartu Identitas dan Nomor Tiket Antrean Anda saat Menuju Loket Pelayanan.
        </marquee>
        <span class="text-[9px] font-mono text-emerald-400 shrink-0 px-2 border-l border-slate-800 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Server Antrean: Online
        </span>
    </footer>

    <!-- SCRIPT REALTIME & SPEECH SYNTHESIS -->
    <script>
        var lastCallUniqueKey = '';
        var audioCtx = null;
        var availableVoices = [];
        var heroHideTimer = null;

        setInterval(function() {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            var seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').innerText = hours + ':' + minutes + ':' + seconds + ' WIB';
        }, 1000);

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
                option.textContent = voice.name;
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

        function triggerHeroCall(activeCall, noMeja, namaLayanan) {
            var box = document.getElementById('box-hero-panggilan');
            var statusDot = document.getElementById('hero-status-dot');
            var statusTitle = document.getElementById('hero-status-title');
            var statusSub = document.getElementById('hero-status-sub');
            var numElem = document.getElementById('hero-nomor-antrian');
            var badgeText = document.getElementById('hero-badge-text');
            var badgeBox = document.getElementById('hero-badge-direksi');

            box.className = "flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border-2 border-red-500/80 rounded-2xl p-3.5 flex flex-col justify-between shadow-[0_0_30px_rgba(238,46,36,0.25)] relative overflow-hidden transition-all duration-500";
            statusDot.className = "w-2.5 h-2.5 rounded-full bg-red-500 animate-ping";
            statusTitle.className = "text-xs font-black tracking-widest text-red-500 uppercase";
            statusTitle.innerText = "SEDANG DIPANGGIL";
            statusSub.className = "text-xs font-bold text-slate-300 ml-2";
            statusSub.innerText = "PANGGILAN SUARA AKTIF";

            numElem.className = "text-6xl lg:text-7xl font-black text-white tracking-tight drop-shadow-[0_0_35px_rgba(238,46,36,0.4)] leading-none my-1";
            numElem.innerText = activeCall.nomor_antrian;

            badgeBox.className = "inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-3 py-1 rounded-lg font-bold text-[11px] mt-1";
            badgeText.innerText = "SILAKAN MENUJU MEJA PELAYANAN SEKARANG";

            document.getElementById('hero-nama-meja').innerText = 'MEJA CS ' + String(noMeja).padStart(2, '0');
            document.getElementById('hero-nama-layanan').innerText = namaLayanan;

            if (heroHideTimer) clearTimeout(heroHideTimer);

            heroHideTimer = setTimeout(function() {
                resetHeroToStandby();
            }, 5000);
        }

        function resetHeroToStandby() {
            var box = document.getElementById('box-hero-panggilan');
            var statusDot = document.getElementById('hero-status-dot');
            var statusTitle = document.getElementById('hero-status-title');
            var statusSub = document.getElementById('hero-status-sub');
            var numElem = document.getElementById('hero-nomor-antrian');
            var badgeText = document.getElementById('hero-badge-text');
            var badgeBox = document.getElementById('hero-badge-direksi');

            box.className = "flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border border-slate-800 rounded-2xl p-3.5 flex flex-col justify-between shadow-2xl relative overflow-hidden transition-all duration-500";
            statusDot.className = "w-2.5 h-2.5 rounded-full bg-slate-600";
            statusTitle.className = "text-xs font-black tracking-widest text-slate-400 uppercase";
            statusTitle.innerText = "STANDBY";
            statusSub.className = "text-xs font-bold text-slate-500 ml-2";
            statusSub.innerText = "Menunggu Panggilan";

            numElem.className = "text-6xl lg:text-7xl font-black text-slate-600 tracking-tight leading-none my-1";
            numElem.innerText = "---";

            badgeBox.className = "inline-flex items-center gap-1.5 bg-slate-800/50 border border-slate-700/50 text-slate-400 px-3 py-1 rounded-lg font-bold text-[11px] mt-1";
            badgeText.innerText = "LOKET SIAP MELAYANI";

            document.getElementById('hero-nama-meja').innerText = "MEJA CS --";
            document.getElementById('hero-nama-layanan').innerText = "Belum Ada Panggilan";
        }

        function playTestCall() {
            speakQueueCall('A-001', '1');
        }

        function fetchDisplayData() {
            fetch('{{ url("/api/display-antrean-data") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.avgDurasiMenit) {
                        document.getElementById('label-avg-durasi').innerText = '± ' + data.avgDurasiMenit + ' Mnt / Tiket';
                    }
                    renderSedangDipanggil(data.sedangDipanggil);
                    renderMejaGridDinamis(data.mejaList);
                    renderAntreanMenunggu(data.antreanMenunggu);
                })
                .catch(error => console.error('Gagal mengambil data display:', error));
        }

        function renderSedangDipanggil(listDipanggil) {
            if (!listDipanggil || listDipanggil.length === 0) {
                return;
            }

            var activeCall = listDipanggil[0];
            if (activeCall) {
                var noMeja = activeCall.cs ? (activeCall.cs.nomor_meja || '1') : '1';
                var namaLayanan = activeCall.layanan ? activeCall.layanan.nama_layanan : 'Layanan CS';

                var csId = activeCall.user_id || (activeCall.cs ? activeCall.cs.id : '0');
                var waktuDipanggil = activeCall.waktu_dipanggil || activeCall.waktu_diproses || '';

                var currentKey = activeCall.id + '_cs' + csId + '_' + waktuDipanggil;

                if (currentKey !== lastCallUniqueKey) {
                    lastCallUniqueKey = currentKey;
                    triggerHeroCall(activeCall, noMeja, namaLayanan);
                    speakQueueCall(activeCall.nomor_antrian, noMeja);
                }
            }
        }

        function renderMejaGridDinamis(mejaList) {
            var gridElem = document.getElementById('grid-meja-pelayanan');
            if (!mejaList || mejaList.length === 0) {
                gridElem.innerHTML = '<div class="col-span-full text-center text-xs text-slate-500 py-2">Belum ada meja dikonfigurasi.</div>';
                return;
            }

            var count = mejaList.length;
            var colsClass = "grid-cols-" + Math.min(count, 4);
            gridElem.className = "grid " + colsClass + " gap-2";

            document.getElementById('label-total-meja').innerText = "Total " + count + " Meja Terdaftar • Update Realtime";

            var html = '';
            mejaList.forEach(function(meja) {
                var noMejaPad = String(meja.nomor_meja).padStart(2, '0');

                if (meja.is_occupied) {
                    if (meja.is_calling) {
                        html += `
                            <div class="bg-slate-900/90 border border-red-500/60 rounded-xl p-2 flex flex-col justify-between shadow-[0_0_15px_rgba(238,46,36,0.15)] transition-all">
                                <div class="flex items-center justify-between text-[9px]">
                                    <span class="font-black text-white uppercase">${meja.nama_meja}</span>
                                    <span class="bg-red-500/20 text-red-400 border border-red-500/30 font-black px-1.5 py-0.5 rounded text-[8px] uppercase animate-pulse">DIPANGGIL</span>
                                </div>
                                <div class="my-0.5">
                                    <span class="text-xl font-black text-white font-mono tracking-tight">${meja.tiket_aktif}</span>
                                    <p class="text-[8px] text-slate-300 font-medium truncate">${meja.nama_layanan || ''}</p>
                                </div>
                                <div class="text-[8px] text-slate-400 truncate border-t border-slate-800 pt-0.5 flex justify-between">
                                    <span>Petugas: <strong class="text-white">${meja.nama_cs}</strong></span>
                                    <span class="text-red-400 font-bold">LIVE</span>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="bg-slate-900/80 border border-emerald-500/40 rounded-xl p-2 flex flex-col justify-between shadow-sm transition-all">
                                <div class="flex items-center justify-between text-[9px]">
                                    <span class="font-black text-white uppercase">${meja.nama_meja}</span>
                                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold px-1.5 py-0.5 rounded text-[8px] uppercase">SIAP MELAYANI</span>
                                </div>
                                <div class="my-0.5">
                                    <span class="text-xl font-black text-slate-500 font-mono tracking-tight">---</span>
                                </div>
                                <div class="text-[8px] text-slate-400 truncate border-t border-slate-800 pt-0.5 flex justify-between">
                                    <span>Petugas: <strong class="text-emerald-300">${meja.nama_cs}</strong></span>
                                    <span class="text-emerald-400 font-bold">ONLINE</span>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    html += `
                        <div class="bg-slate-900/20 border border-slate-800/40 rounded-xl p-2 flex flex-col justify-between opacity-40 grayscale transition-all">
                            <div class="flex items-center justify-between text-[9px]">
                                <span class="font-bold text-slate-500 uppercase">${meja.nama_meja}</span>
                                <span class="bg-slate-800/50 text-slate-500 font-bold px-1.5 py-0.5 rounded text-[8px] uppercase">OFFLINE</span>
                            </div>
                            <div class="my-0.5">
                                <span class="text-xl font-black text-slate-700 font-mono tracking-tight">---</span>
                            </div>
                            <div class="text-[8px] text-slate-600 truncate border-t border-slate-800/40 pt-0.5 flex justify-between">
                                <span>Petugas: Unassigned</span>
                                <span>STANDBY</span>
                            </div>
                        </div>
                    `;
                }
            });

            gridElem.innerHTML = html;
        }

        function renderAntreanMenunggu(listMenunggu) {
            var container = document.getElementById('box-antrean-menunggu');
            document.getElementById('total-menunggu').innerText = (listMenunggu ? listMenunggu.length : 0) + ' Menunggu';

            if (!listMenunggu || listMenunggu.length === 0) {
                container.innerHTML = `
                    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-4 text-center text-slate-500 text-xs">
                        Belum ada antrean berikutnya.
                    </div>
                `;
                return;
            }

            var html = '';
            listMenunggu.forEach(function(item, index) {
                var namaLayanan = item.layanan ? item.layanan.nama_layanan : 'Layanan';
                var isNext = index === 0;
                var estTunggu = item.estimasi_tunggu_menit || ((index + 1) * 10);

                html += `
                    <div class="bg-slate-900/90 border ${isNext ? 'border-amber-500/50 bg-amber-500/5' : 'border-slate-800'} rounded-xl p-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 text-center text-xs font-black text-slate-500">${index + 1}</span>
                            <div>
                                <span class="text-sm font-black font-mono text-white block leading-none">${item.nomor_antrian}</span>
                                <span class="text-[9px] text-slate-400 font-bold block mt-1 truncate max-w-[110px]">${namaLayanan}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[8px] font-black uppercase ${isNext ? 'text-amber-400 bg-amber-500/10 border border-amber-500/30' : 'text-slate-400 bg-slate-800'} px-2 py-0.5 rounded-md block">
                                ${isNext ? 'Siap Dipanggil' : 'Menunggu'}
                            </span>
                            <span class="text-[8px] text-amber-400/80 font-bold block mt-1">
                                Est. ± ${estTunggu} mnt
                            </span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        fetchDisplayData();
        setInterval(fetchDisplayData, 2000);
    </script>
</body>
</html>
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
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
    </style>
</head>
<body class="text-white h-screen w-screen flex flex-col justify-between overflow-hidden select-none p-3 lg:p-4 gap-3 lg:gap-4">

    <!-- TOP BAR HEADER (LEBIH BERSIH & LEGA) -->
    <header class="bg-[#0D1322] border border-slate-800/80 rounded-2xl px-6 py-3.5 flex items-center justify-between shrink-0 shadow-lg">
        <div class="flex items-center gap-5">
            <img src="{{ asset('img/LogoTeks.png') }}" alt="Logo Indibiz" class="h-7 lg:h-8 object-contain brightness-0 invert">
            <div class="h-8 w-[1px] bg-slate-700"></div>
            <div>
                <h1 class="text-[11px] lg:text-xs font-extrabold tracking-wider text-slate-400 uppercase leading-none mb-1.5">Pusat Layanan Terpadu Enterprise</h1>
                <p class="text-sm lg:text-lg font-black text-slate-200 leading-none">RUANG TUNGGU CUSTOMER SERVICE</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- WIDGET SYNTHESIZER -->
            <div class="hidden lg:flex items-center gap-2.5 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-base text-red-500">graphic_eq</span>
                <span class="text-xs text-slate-400 font-bold">Suara:</span>
                <select id="voice-select" class="bg-transparent text-slate-200 text-sm font-bold focus:outline-none max-w-[160px] truncate cursor-pointer">
                    <option value="">Memuat Suara...</option>
                </select>
            </div>

            <!-- TOMBOL TEST -->
            <button onclick="playTestCall()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 px-4 py-2 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-sm">
                <span class="material-symbols-outlined text-base">volume_up</span>
                <span>TEST AUDIO</span>
            </button>

            <!-- JAM DIGITAL -->
            <div id="live-clock" class="font-mono text-base lg:text-xl font-black bg-slate-900 border border-slate-700/80 px-5 py-2 rounded-xl text-emerald-400 tracking-wider shadow-inner">
                00:00:00 WIB
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT: 2 KOLOM LAYOUT -->
    <main class="flex-1 grid grid-cols-12 gap-3 lg:gap-4 min-h-0 overflow-hidden">
        
        <!-- KOLOM KIRI (SPAN 8/9): DISPLAY PANGGILAN + STATUS MEJA -->
        <div class="col-span-8 lg:col-span-9 flex flex-col gap-3 lg:gap-4 min-h-0">
            
            <!-- BOX HERO: SEDANG DIPANGGIL -->
            <div id="box-hero-panggilan" class="flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border border-slate-800 rounded-2xl p-6 lg:p-8 flex flex-col justify-between shadow-2xl relative overflow-hidden transition-all duration-500">
                
                <!-- HEADER HERO & WIDGET RATA-RATA WAKTU (DIPINDAHKAN KE SINI) -->
                <div class="flex items-start justify-between border-b border-white/5 pb-4 shrink-0">
                    <div class="flex items-center gap-3 mt-2">
                        <span id="hero-status-dot" class="w-3.5 h-3.5 rounded-full bg-slate-600"></span>
                        <span id="hero-status-title" class="text-base lg:text-lg font-black tracking-widest text-slate-400 uppercase">STANDBY</span>
                        <span id="hero-status-sub" class="text-sm lg:text-base font-bold text-slate-500 ml-2">Menunggu Panggilan</span>
                    </div>
                    
                    <!-- WIDGET RATA-RATA WAKTU TUNGGU & PROSES -->
                    <div class="flex items-center gap-5 bg-slate-900/80 border border-slate-700/50 px-5 py-2.5 rounded-xl shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-400 text-xl">hourglass_top</span>
                            </div>
                            <div>
                                <span class="text-[10px] lg:text-xs font-bold text-slate-400 uppercase block leading-none mb-1.5">Rata-Rata Tunggu</span>
                                <span id="label-avg-tunggu" class="text-sm lg:text-base font-black text-blue-400 leading-none">Belum Ada Data</span>
                            </div>
                        </div>
                        <div class="w-px h-10 bg-slate-700/50"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-amber-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-400 text-xl">avg_time</span>
                            </div>
                            <div>
                                <span class="text-[10px] lg:text-xs font-bold text-slate-400 uppercase block leading-none mb-1.5">Rata-Rata Proses</span>
                                <span id="label-avg-durasi" class="text-sm lg:text-base font-black text-amber-400 leading-none">Belum Ada Data</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTENT AREA -->
                <div class="grid grid-cols-12 gap-4 my-auto items-center py-2">
                    
                    <!-- KIRI: NOMOR TIKET + BADGE (SPAN 7) -->
                    <div class="col-span-7 text-center border-r border-white/5 pr-4">
                        <span class="text-sm lg:text-base font-bold uppercase tracking-[0.25em] text-slate-400 block mb-2">NOMOR TIKET ANTREAN</span>
                        <h1 id="hero-nomor-antrian" class="text-[80px] lg:text-[110px] font-black text-slate-600 tracking-tight leading-none my-4">
                            ---
                        </h1>
                        
                        <div id="hero-badge-direksi" class="inline-flex items-center gap-2.5 bg-slate-800/50 border border-slate-700/50 text-slate-400 px-5 py-2 rounded-xl font-bold text-xs lg:text-sm mt-3">
                            <span class="material-symbols-outlined text-base">info</span>
                            <span id="hero-badge-text">LOKET SIAP MELAYANI</span>
                        </div>
                    </div>

                    <!-- KANAN: TUJUAN LOKET & KATEGORI LAYANAN (SPAN 5) -->
                    <div class="col-span-5 flex flex-col gap-5 pl-6">
                        <div class="bg-[#080D1A]/90 p-5 rounded-2xl border border-slate-800/80 shadow-inner">
                            <span class="text-[11px] lg:text-xs uppercase font-bold text-slate-500 block mb-1">TUJUAN LOKET</span>
                            <p id="hero-nama-meja" class="text-4xl lg:text-5xl font-black text-white mt-1.5 leading-tight">MEJA CS --</p>
                        </div>
                        <div class="bg-[#080D1A]/90 p-5 rounded-2xl border border-slate-800/80 shadow-inner">
                            <span class="text-[11px] lg:text-xs uppercase font-bold text-slate-500 block mb-1">KATEGORI LAYANAN</span>
                            <p id="hero-nama-layanan" class="text-base lg:text-xl font-bold text-slate-400 truncate mt-1.5 leading-tight">Belum Ada Panggilan</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- STATUS MEJA PELAYANAN -->
            <div class="bg-[#0D1322] border border-slate-800/80 rounded-2xl p-5 lg:p-6 flex flex-col gap-4 shrink-0 shadow-lg">
                <div class="flex items-center justify-between text-xs lg:text-sm font-bold border-b border-slate-800 pb-3">
                    <span class="text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        STATUS KESIAPAN LOKET
                    </span>
                    <span id="label-total-meja" class="text-slate-500 text-[10px] lg:text-xs">Update Realtime</span>
                </div>

                <div id="grid-meja-pelayanan" class="grid gap-3 lg:gap-4">
                    <!-- Cards CS di-render secara dinamis via JS -->
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (SPAN 4/3): ANTREAN BERIKUTNYA -->
        <div class="col-span-4 lg:col-span-3 bg-[#0D1322] border border-slate-800/80 rounded-2xl p-5 flex flex-col h-full min-h-0 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-4 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-amber-400 text-xl">schedule</span>
                    <h2 class="text-sm lg:text-base font-black uppercase text-white leading-tight">ANTREAN BERIKUTNYA</h2>
                </div>
                <span id="total-menunggu" class="text-xs lg:text-sm bg-slate-900 text-amber-400 border border-slate-800 px-3 py-1 rounded-full font-bold">0 Menunggu</span>
            </div>

            <div id="box-antrean-menunggu" class="flex flex-col gap-3 overflow-y-auto custom-scrollbar flex-1 pr-2">
                <!-- List Antrean Menunggu dirender via JS -->
            </div>

            <div class="mt-4 pt-4 border-t border-slate-800 shrink-0 bg-slate-900/60 p-4 rounded-xl border border-slate-800/50 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-white leading-tight">Pantau di Ponsel</p>
                    <p class="text-[10px] lg:text-xs text-slate-400 mt-1">Scan QR Code pada struk tiket</p>
                </div>
                <div class="w-10 h-10 bg-white text-slate-900 rounded-xl flex items-center justify-center font-black shrink-0 shadow-md">
                    <span class="material-symbols-outlined text-2xl">qr_code_2</span>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER MARQUEE / RUNNING TEXT -->
    <footer class="bg-[#0D1322] border border-slate-800 text-white rounded-xl p-2 flex items-center gap-4 shrink-0 overflow-hidden shadow-md">
        <span class="bg-red-600 text-white text-[10px] lg:text-xs font-black uppercase px-3 py-1.5 rounded-lg shrink-0 flex items-center gap-1.5 shadow-sm">
            <span class="material-symbols-outlined text-sm">info</span> INFORMASI
        </span>
        <marquee behavior="scroll" direction="left" class="font-semibold text-slate-300 text-xs lg:text-sm tracking-wide">
            Selamat Datang di Telkom Indibiz Service Desk &bull; Ciptakan Peluang, Wujudkan Harapan Bersama Ekosistem Solusi Digital Dunia Usaha &bull; Harap Siapkan Kartu Identitas dan Nomor Tiket Antrean Anda saat Menuju Loket Pelayanan.
        </marquee>
        <span class="text-[10px] lg:text-xs font-mono text-emerald-400 shrink-0 px-4 border-l border-slate-800 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Server: Online
        </span>
    </footer>

    <!-- SCRIPT REALTIME, SPEECH SYNTHESIS & STOPWATCH TV -->
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

            box.className = "flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border-2 border-red-500/80 rounded-2xl p-6 lg:p-8 flex flex-col justify-between shadow-[0_0_40px_rgba(238,46,36,0.25)] relative overflow-hidden transition-all duration-500";
            statusDot.className = "w-3.5 h-3.5 rounded-full bg-red-500 animate-ping";
            statusTitle.className = "text-base lg:text-lg font-black tracking-widest text-red-500 uppercase";
            statusTitle.innerText = "SEDANG DIPANGGIL";
            statusSub.className = "text-sm lg:text-base font-bold text-slate-300 ml-2";
            statusSub.innerText = "PANGGILAN SUARA AKTIF";

            numElem.className = "text-[80px] lg:text-[120px] font-black text-white tracking-tight drop-shadow-[0_0_35px_rgba(238,46,36,0.4)] leading-none my-4";
            numElem.innerText = activeCall.nomor_antrian;

            badgeBox.className = "inline-flex items-center gap-2.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-5 py-2 rounded-xl font-bold text-xs lg:text-sm mt-3";
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

            box.className = "flex-1 bg-gradient-to-b from-[#11182B] to-[#0A0F1D] border border-slate-800 rounded-2xl p-6 lg:p-8 flex flex-col justify-between shadow-2xl relative overflow-hidden transition-all duration-500";
            statusDot.className = "w-3.5 h-3.5 rounded-full bg-slate-600";
            statusTitle.className = "text-base lg:text-lg font-black tracking-widest text-slate-400 uppercase";
            statusTitle.innerText = "STANDBY";
            statusSub.className = "text-sm lg:text-base font-bold text-slate-500 ml-2";
            statusSub.innerText = "Menunggu Panggilan";

            numElem.className = "text-[80px] lg:text-[110px] font-black text-slate-600 tracking-tight leading-none my-4";
            numElem.innerText = "---";

            badgeBox.className = "inline-flex items-center gap-2.5 bg-slate-800/50 border border-slate-700/50 text-slate-400 px-5 py-2 rounded-xl font-bold text-xs lg:text-sm mt-3";
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
                    var avgDurasiLabel = document.getElementById('label-avg-durasi');
                    if (data.avgDurasiLayanan !== null && data.avgDurasiLayanan !== undefined) {
                        avgDurasiLabel.innerText = '± ' + data.avgDurasiLayanan + ' Mnt / Tiket';
                    } else {
                        avgDurasiLabel.innerText = 'Belum Ada Data';
                    }

                    var avgTungguLabel = document.getElementById('label-avg-tunggu');
                    if (data.avgWaktuTunggu !== null && data.avgWaktuTunggu !== undefined) {
                        avgTungguLabel.innerText = '± ' + data.avgWaktuTunggu + ' Mnt';
                    } else {
                        avgTungguLabel.innerText = 'Belum Ada Data';
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
                gridElem.innerHTML = '<div class="col-span-full text-center text-sm text-slate-500 py-4">Belum ada meja dikonfigurasi.</div>';
                return;
            }

            var count = mejaList.length;
            var colsClass = "grid-cols-" + Math.min(count, 4);
            gridElem.className = "grid " + colsClass + " gap-3 lg:gap-4";

            document.getElementById('label-total-meja').innerText = "Total " + count + " Meja Terdaftar • Update Realtime";

            var html = '';
            mejaList.forEach(function(meja) {
                if (meja.is_occupied) {
                    if (meja.is_calling) {
                        var startTimeVal = meja.waktu_mulai_konsul || meja.waktu_diproses || meja.waktu_dipanggil || (new Date().toISOString());

                        html += `
                            <div class="bg-slate-900/90 border border-red-500/60 rounded-2xl p-4 lg:p-5 flex flex-col justify-between shadow-[0_0_20px_rgba(238,46,36,0.15)] transition-all gap-2">
                                <div class="flex items-center justify-between text-[11px] lg:text-xs">
                                    <span class="font-black text-white uppercase">${meja.nama_meja}</span>
                                    <span class="bg-red-500/20 text-red-400 border border-red-500/30 font-black px-2 py-1 rounded text-[10px] uppercase animate-pulse">DIPANGGIL</span>
                                </div>
                                <div class="my-1.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="text-3xl lg:text-4xl font-black text-white font-mono tracking-tight leading-none">${meja.tiket_aktif}</span>
                                        <p class="text-[10px] lg:text-xs text-slate-300 font-medium truncate w-full mt-1.5">${meja.nama_layanan || ''}</p>
                                    </div>
                                    <div class="text-right bg-slate-950/80 px-3 py-1.5 rounded-xl border border-slate-800 shrink-0">
                                        <span class="text-[8px] lg:text-[9px] text-slate-400 font-bold block uppercase leading-none mb-1">DURASI</span>
                                        <span id="timer-meja-${meja.nomor_meja}" data-start="${startTimeVal}" class="font-mono text-base lg:text-lg font-black text-amber-400 leading-none">00:00</span>
                                    </div>
                                </div>
                                <div class="text-[10px] lg:text-xs text-slate-400 truncate border-t border-slate-800 pt-3 flex justify-between items-center">
                                    <span>Petugas: <strong class="text-white">${meja.nama_cs}</strong></span>
                                    <span class="text-red-400 font-bold text-[9px] px-1.5 py-0.5 bg-red-500/10 rounded">LIVE</span>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="bg-slate-900/80 border border-emerald-500/40 rounded-2xl p-4 lg:p-5 flex flex-col justify-between shadow-sm transition-all gap-2">
                                <div class="flex items-center justify-between text-[11px] lg:text-xs">
                                    <span class="font-black text-white uppercase">${meja.nama_meja}</span>
                                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold px-2 py-1 rounded text-[10px] uppercase">SIAP MELAYANI</span>
                                </div>
                                <div class="my-1.5">
                                    <span class="text-3xl lg:text-4xl font-black text-slate-600 font-mono tracking-tight leading-none">---</span>
                                </div>
                                <div class="text-[10px] lg:text-xs text-slate-400 truncate border-t border-slate-800 pt-3 flex justify-between items-center">
                                    <span>Petugas: <strong class="text-emerald-300">${meja.nama_cs}</strong></span>
                                    <span class="text-emerald-400 font-bold text-[9px] px-1.5 py-0.5 bg-emerald-500/10 rounded">ONLINE</span>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    html += `
                        <div class="bg-slate-900/20 border border-slate-800/40 rounded-2xl p-4 lg:p-5 flex flex-col justify-between opacity-50 grayscale transition-all gap-2">
                            <div class="flex items-center justify-between text-[11px] lg:text-xs">
                                <span class="font-bold text-slate-500 uppercase">${meja.nama_meja}</span>
                                <span class="bg-slate-800/50 text-slate-500 font-bold px-2 py-1 rounded text-[10px] uppercase">OFFLINE</span>
                            </div>
                            <div class="my-1.5">
                                <span class="text-3xl lg:text-4xl font-black text-slate-700 font-mono tracking-tight leading-none">---</span>
                            </div>
                            <div class="text-[10px] lg:text-xs text-slate-600 truncate border-t border-slate-800/40 pt-3 flex justify-between items-center">
                                <span>Petugas: Unassigned</span>
                                <span class="text-slate-500 font-bold text-[9px] px-1.5 py-0.5 bg-slate-800/50 rounded">STANDBY</span>
                            </div>
                        </div>
                    `;
                }
            });

            gridElem.innerHTML = html;
            updateMejaTimers();
        }

        function updateMejaTimers() {
            var timerElems = document.querySelectorAll('[id^="timer-meja-"]');
            timerElems.forEach(function(elem) {
                var startStr = elem.getAttribute('data-start');
                if (!startStr) return;

                var startTime = new Date(startStr.replace(/-/g, "/")).getTime();
                var now = new Date().getTime();
                var diffSec = Math.floor((now - startTime) / 1000);

                if (isNaN(diffSec) || diffSec < 0) diffSec = 0;

                var minutes = Math.floor(diffSec / 60);
                var seconds = diffSec % 60;

                elem.innerText = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            });
        }

        setInterval(updateMejaTimers, 1000);

        function renderAntreanMenunggu(listMenunggu) {
            var container = document.getElementById('box-antrean-menunggu');
            document.getElementById('total-menunggu').innerText = (listMenunggu ? listMenunggu.length : 0) + ' Menunggu';

            if (!listMenunggu || listMenunggu.length === 0) {
                container.innerHTML = `
                    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 text-center text-slate-500 text-sm">
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
                    <div class="bg-slate-900/90 border ${isNext ? 'border-amber-500/50 bg-amber-500/10 shadow-md' : 'border-slate-800 shadow-sm'} rounded-xl p-3.5 lg:p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 w-1/2">
                            <div class="w-8 h-8 rounded-full ${isNext ? 'bg-amber-500 text-white shadow-[0_0_10px_rgba(245,158,11,0.5)]' : 'bg-slate-800 text-slate-400'} flex items-center justify-center text-sm font-black shrink-0">
                                ${index + 1}
                            </div>
                            <div class="min-w-0">
                                <span class="text-lg lg:text-xl font-black font-mono ${isNext ? 'text-amber-400' : 'text-white'} block leading-none truncate">${item.nomor_antrian}</span>
                                <span class="text-[10px] lg:text-xs text-slate-400 font-semibold block mt-1.5 truncate w-full">${namaLayanan}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end justify-center w-1/2 shrink-0">
                            <span class="text-[10px] lg:text-[11px] font-black uppercase tracking-wider ${isNext ? 'text-amber-400 bg-amber-500/10 border border-amber-500/30' : 'text-slate-400 bg-slate-800'} px-3 py-1 rounded block text-center min-w-[100px]">
                                ${isNext ? 'Siap Dipanggil' : 'Menunggu'}
                            </span>
                            <span class="text-[11px] lg:text-xs ${isNext ? 'text-amber-300' : 'text-slate-400'} font-bold block mt-2 bg-slate-950/50 px-2.5 py-1 rounded-md border border-slate-800">
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
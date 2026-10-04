<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Situ Awi - Resto & Saung Lesehan Parahyangan</title>

    {{-- Google Fonts: Plus Jakarta Sans & Playfair Display --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #FBF8F1;
            color: #2B2A26;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-display {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .font-mono-lcd {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-[#FBF8F1] antialiased selection:bg-gold-500 selection:text-forest-900 min-h-screen">

    {{-- Root Alpine Application Container --}}
    <div x-data="pelangganApp({{ $initialStep ?? 1 }})" 
         x-init="initApp()" 
         x-cloak 
         class="min-h-screen flex flex-col justify-between">

        {{-- Toast Urgensi / Panggil Pelayan (Sesuai Design System v1.3) --}}
        <div x-show="toast.visible" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-12 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-12 opacity-0"
             class="fixed top-4 inset-x-4 max-w-sm mx-auto z-50 pointer-events-none">
            <div class="rounded-2xl p-4 shadow-xl border flex items-center gap-3 pointer-events-auto"
                 :class="{
                     'bg-gold-50 border-gold-500 text-forest-900 shadow-gold-500/20': toast.type === 'waiter',
                     'bg-forest-800 border-forest-600 text-cream-50 shadow-forest-900/30': toast.type === 'success',
                     'bg-white border-forest-200 text-forest-900': toast.type === 'info'
                 }">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 font-bold"
                     :class="toast.type === 'waiter' ? 'bg-gold-500 text-forest-900 animate-bounce' : 'bg-forest-600 text-cream-50'">
                    <span x-html="toast.icon"></span>
                </div>
                <div class="flex-1">
                    <div class="text-xs font-bold leading-tight" x-text="toast.title"></div>
                    <div class="text-[11px] opacity-80 mt-0.5 leading-snug" x-text="toast.message"></div>
                </div>
                <button type="button" 
                        @click="toast.visible = false"
                        class="text-charcoal-400 hover:text-charcoal-800 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Top Application Header (Konsisten Sesuai Desain PDF Halaman 1, 2, 3) --}}
        <header class="sticky top-0 z-30 bg-[#FBF8F1]/95 backdrop-blur-md border-b border-forest-100/60 transition-all duration-300">
            <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between gap-3">
                {{-- Left: Back Arrow & Branding --}}
                <div class="flex items-center gap-2.5">
                    {{-- Tombol Back: Kembali ke step sebelumnya atau reset jika di katalog --}}
                    <button type="button" 
                            @click="handleBackNavigation()"
                            class="w-8 h-8 rounded-full bg-white border border-forest-100 flex items-center justify-center text-forest-900 shadow-2xs hover:bg-forest-50 transition active:scale-95"
                            title="Kembali">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </button>

                    <div>
                        <div class="flex items-center gap-1.5 text-[10px] font-extrabold tracking-wider text-forest-800 uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>SAUNG SITU AWI</span>
                        </div>
                        <div class="text-xs font-bold text-forest-900 leading-tight" x-text="pageTitles[step]">
                            Katalog Menu
                        </div>
                    </div>
                </div>

                {{-- Right: Badge Saung 02 & Logo Situ Awi --}}
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-forest-100/90 text-forest-900 text-xs font-bold border border-forest-200/60 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-forest-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>Saung 02</span>
                    </div>

                    {{-- Saung Situ Awi Circular Logo --}}
                    <div class="w-9 h-9 rounded-full bg-forest-900 border-2 border-gold-400 overflow-hidden flex items-center justify-center shadow-sm shrink-0">
                        <img src="{{ asset('asset/Logo.png') }}" 
                             alt="Saung Situ Awi Logo" 
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-gold-300 font-bold text-xs\'>SA</span>';">
                    </div>
                </div>
            </div>
        </header>

        {{-- Main Container (Single-Page Flow Steps) --}}
        <main class="flex-1 max-w-md mx-auto w-full px-4 pt-4">
            
            {{-- STEP 1: KATALOG E-MENU --}}
            <div x-show="step === 1" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                @include('pelanggan.katalog', ['isSinglePage' => true])
            </div>

            {{-- STEP 2: RINGKASAN PESANAN --}}
            <div x-show="step === 2" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                @include('pelanggan.ringkasan', ['isSinglePage' => true])
            </div>

            {{-- STEP 3: PEMBAYARAN & PELACAKAN STATUS --}}
            <div x-show="step === 3" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                @include('pelanggan.pembayaran', ['isSinglePage' => true])
            </div>

        </main>

        {{-- Filter Modal / Sheet --}}
        <div x-show="showFilterModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-forest-900/60 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div @click.away="showFilterModal = false"
                 class="bg-white w-full max-w-sm rounded-t-3xl sm:rounded-2xl p-5 shadow-2xl space-y-4 border border-forest-100">
                <div class="flex items-center justify-between border-b border-forest-100 pb-3">
                    <h4 class="font-bold text-forest-900 text-sm flex items-center gap-2">
                        <span>Filter Kategori Menu</span>
                    </h4>
                    <button type="button" @click="showFilterModal = false" class="text-charcoal-400 hover:text-charcoal-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" 
                                @click="activeCategory = cat.id; showFilterModal = false"
                                class="p-3 rounded-xl border text-xs font-semibold text-left flex items-center gap-2 transition"
                                :class="activeCategory === cat.id 
                                    ? 'bg-forest-600 text-cream-50 border-forest-600' 
                                    : 'bg-cream-50 text-forest-900 border-forest-100 hover:bg-forest-50'">
                            <span x-text="cat.icon" class="text-base"></span>
                            <span x-text="cat.name"></span>
                        </button>
                    </template>
                </div>

                <div class="pt-2">
                    <button type="button" 
                            @click="activeCategory = 'semua'; searchQuery = ''; showFilterModal = false"
                            class="w-full py-2.5 rounded-xl bg-forest-100 text-forest-800 font-bold text-xs hover:bg-forest-200 transition">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Interactive Alpine Application Script --}}
    <script>
        function pelangganApp(initialStep = 1) {
            return {
                step: initialStep, // 1: Katalog, 2: Ringkasan, 3: Pembayaran & Pelacakan
                pageTitles: {
                    1: 'Katalog Menu',
                    2: 'Ringkasan Pesanan',
                    3: 'Pelacakan & Status Pesanan'
                },
                orderRef: '#SA-204',
                orderId: 'Order #SA-20260915-084',
                searchQuery: '',
                activeCategory: 'semua',
                showFilterModal: false,
                paymentMethod: 'qris', // 'qris' or 'cash'
                orderPaid: false,
                iotChecking: false,
                qrisSecondsLeft: 885, // 14 mins 45 seconds (14:45)
                liveTimeString: '12:05 WIB',
                
                toast: {
                    visible: false,
                    type: 'info',
                    icon: 'ℹ️',
                    title: '',
                    message: '',
                    timeout: null
                },

                categories: [
                    { id: 'semua', name: 'Semua', icon: '🔀' },
                    { id: 'paket', name: 'Paket Saung', icon: '🍽️' },
                    { id: 'makanan', name: 'Makanan Utama', icon: '🍲' },
                    { id: 'minuman', name: 'Minuman Segar', icon: '🥤' },
                    { id: 'camilan', name: 'Camilan', icon: '🍟' },
                    { id: 'lalapan', name: 'Lalapan', icon: '🥗' },
                ],

                // Master Menu Data Sesuai Desain PDF & PRD
                menuItems: [
                    {
                        id: 1,
                        name: 'Gurame Bakar Situ',
                        fullName: 'Gurame Bakar Situ Awi',
                        price: 68000,
                        desc: 'Ikan gurame segar danau, bumbu rempah...',
                        badge: 'Bestseller',
                        badgeType: 'bestseller',
                        category: 'makanan',
                        image: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: false,
                        defaultNote: 'Pedas manis, bakar kering, sambal terasi dipisah'
                    },
                    {
                        id: 2,
                        name: 'Nasi Liwet Kastrol',
                        fullName: 'Nasi Liwet Kastrol Komplit',
                        price: 45000,
                        desc: 'Harum serai, daun salam, teri gurih renyah & pete...',
                        badge: 'Favorit',
                        badgeType: 'favorite',
                        category: 'paket',
                        image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: false,
                        defaultNote: 'Porsi 2 orang, asin pedas sedang'
                    },
                    {
                        id: 3,
                        name: 'Es Kelapa Jeruk',
                        fullName: 'Es Kelapa Muda Jeruk',
                        price: 18000,
                        desc: 'Kelapa degan muda berpadu perasan jeruk...',
                        badge: 'Bar Segar',
                        badgeType: 'bar',
                        category: 'minuman',
                        image: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: false,
                        defaultNote: 'Sedikit gula, es batu dipisah'
                    },
                    {
                        id: 4,
                        name: 'Kopi Robusta...',
                        fullName: 'Kopi Robusta Tubruk',
                        price: 15000,
                        desc: 'Biji kopi lokal Jawa Barat seduh tubruk, aroma...',
                        badge: 'Kopi Khas',
                        badgeType: 'kopi',
                        category: 'minuman',
                        image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: false,
                        defaultNote: 'Gula aren pisah'
                    },
                    {
                        id: 5,
                        name: 'Sate Maranggi (10',
                        fullName: 'Sate Maranggi (10 Tusuk)',
                        price: 42000,
                        desc: 'Daging sapi empuk marinasi ketumbar manis',
                        badge: 'Stok Habis',
                        badgeType: 'soldout',
                        category: 'makanan',
                        image: 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: true,
                        defaultNote: ''
                    },
                    {
                        id: 6,
                        name: 'Karedok Leunca...',
                        fullName: 'Karedok Leunca Khas Sunda',
                        price: 16000,
                        desc: 'Leunca segar dipadu ulekan kencur, terasi...',
                        badge: 'Lalapan Asli',
                        badgeType: 'lalap',
                        category: 'lalapan',
                        image: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&auto=format&fit=crop&q=80',
                        isSoldOut: false,
                        defaultNote: 'Pedas sedang'
                    }
                ],

                // Keranjang Pesanan (Cart Items)
                // Default: Kosong sesuai permintaan ("Default nya tidak memilih apa-apa dan tidak ada tombol mengambang di bawah.")
                cartItems: [],

                initApp() {
                    // Start clock for ESP32 LCD display
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // Start QRIS Countdown timer
                    setInterval(() => {
                        if (this.qrisSecondsLeft > 0) {
                            this.qrisSecondsLeft--;
                        }
                    }, 1000);

                    // Check if URL has ?step= query parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const stepParam = parseInt(urlParams.get('step'));
                    if (stepParam && [1, 2, 3].includes(stepParam)) {
                        this.step = stepParam;
                        // If direct into step 2 or 3, pre-populate demo items for optimal experience
                        if (this.cartItems.length === 0) {
                            this.populateDefaultDemoCart();
                        }
                    }
                },

                updateClock() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    this.liveTimeString = `${hours}:${minutes} WIB`;
                },

                get qrisCountdownString() {
                    const mins = Math.floor(this.qrisSecondsLeft / 60);
                    const secs = this.qrisSecondsLeft % 60;
                    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                },

                get filteredMenuItems() {
                    return this.menuItems.filter(item => {
                        const matchCat = this.activeCategory === 'semua' || item.category === this.activeCategory;
                        const matchSearch = !this.searchQuery || 
                            item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                            item.desc.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchCat && matchSearch;
                    });
                },

                get totalCartItemsCount() {
                    return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
                },

                get cartSubtotal() {
                    return this.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get cartTax() {
                    return Math.round(this.cartSubtotal * 0.10); // 10% PB1
                },

                get cartGrandTotal() {
                    return this.cartSubtotal + this.cartTax;
                },

                formatRupiah(number) {
                    return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
                },

                // Cart Actions
                addToCart(menuItem) {
                    if (menuItem.isSoldOut) return;

                    const existing = this.cartItems.find(i => i.id === menuItem.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cartItems.push({
                            id: menuItem.id,
                            name: menuItem.fullName || menuItem.name,
                            price: menuItem.price,
                            qty: 1,
                            image: menuItem.image,
                            note: menuItem.defaultNote || ''
                        });
                    }

                    this.showToast('success', '✓', 'Menu Ditambahkan', `${menuItem.name} masuk ke bon pesanan.`);
                },

                increaseQty(id) {
                    const item = this.cartItems.find(i => i.id === id);
                    if (item) item.qty++;
                },

                decreaseQty(id) {
                    const item = this.cartItems.find(i => i.id === id);
                    if (item) {
                        if (item.qty > 1) {
                            item.qty--;
                        } else {
                            this.removeFromCart(id);
                        }
                    }
                },

                removeFromCart(id) {
                    this.cartItems = this.cartItems.filter(i => i.id !== id);
                    // If cart becomes empty and we're on step 2, offer going back to step 1
                    if (this.cartItems.length === 0 && this.step === 2) {
                        this.showToast('info', 'ℹ️', 'Keranjang Kosong', 'Silakan pilih menu terlebih dahulu.');
                        this.goToStep(1);
                    }
                },

                populateDefaultDemoCart() {
                    // Populate exact 3 items from screenshot design
                    this.cartItems = [
                        {
                            id: 1,
                            name: 'Gurame Bakar Situ Awi',
                            price: 68000,
                            qty: 1,
                            image: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&auto=format&fit=crop&q=80',
                            note: 'Pedas manis, bakar kering, sambal terasi dipisah'
                        },
                        {
                            id: 2,
                            name: 'Nasi Liwet Kastrol Komplit',
                            price: 45000,
                            qty: 1,
                            image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=600&auto=format&fit=crop&q=80',
                            note: 'Porsi 2 orang, asin pedas sedang'
                        },
                        {
                            id: 3,
                            name: 'Es Kelapa Muda Jeruk',
                            price: 18000,
                            qty: 1,
                            image: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=600&auto=format&fit=crop&q=80',
                            note: 'Sedikit gula, es batu dipisah'
                        }
                    ];
                },

                // Navigation Flow
                goToStep(newStep) {
                    // Prevent advancing to ringkasan if cart is completely empty
                    if (newStep === 2 && this.cartItems.length === 0) {
                        this.showToast('info', '⚠️', 'Pilih Menu Dulu', 'Pilih minimal 1 menu sebelum melihat bon.');
                        return;
                    }

                    this.step = newStep;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                handleBackNavigation() {
                    if (this.step === 3) {
                        this.goToStep(2);
                    } else if (this.step === 2) {
                        this.goToStep(1);
                    } else {
                        // On step 1: reload or notify
                        this.showToast('info', '🌿', 'Saung Situ Awi', 'Anda berada di halaman Katalog e-Menu.');
                    }
                },

                // IoT Simulations
                checkIotStatus() {
                    this.iotChecking = true;
                    setTimeout(() => {
                        this.iotChecking = false;
                        this.showToast('success', '✓', 'IoT Node Online', 'ESP32 Saung 02 & MQTT Broker terhubung optimal.');
                    }, 800);
                },

                triggerWaiterCall() {
                    // Audio beep sound simulation
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 880;
                        gain.gain.setValueAtTime(0.1, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.25);
                    } catch(e) {}

                    this.showToast('waiter', '🔔', 'Panggilan Terkirim!', 'Sensor TTP223 tersentuh. Pelayan Saung segera menuju Saung 02.');
                },

                checkPaymentManual() {
                    this.showToast('info', '🔄', 'Mengecek Pembayaran...', 'Memverifikasi status transaksi ke Midtrans Sandbox.');
                    setTimeout(() => {
                        this.orderPaid = true;
                        this.showToast('success', '✓', 'Pembayaran Lunas!', 'Transaksi QRIS berhasil diverifikasi. Pesanan diteruskan ke dapur.');
                    }, 1200);
                },

                switchToCash() {
                    this.paymentMethod = 'cash';
                    this.showToast('info', '💵', 'Metode Bayar Diubah', 'Silakan lakukan pembayaran tunai di kasir utama.');
                },

                showToast(type, icon, title, message) {
                    clearTimeout(this.toast.timeout);
                    this.toast = {
                        visible: true,
                        type: type,
                        icon: icon,
                        title: title,
                        message: message,
                        timeout: null
                    };
                    this.toast.timeout = setTimeout(() => {
                        this.toast.visible = false;
                    }, 4000);
                }
            };
        }
    </script>
</body>
</html>


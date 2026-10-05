<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Situ Awi - Resto & Saung Lesehan Parahyangan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: { 900: '#052E1B', 800: '#06381F', 700: '#0A4A28', 600: '#0B4D2B', 400: '#12703F', 100: '#D9EBE0' },
                        gold: { 600: '#C9982F', 500: '#E0B24E', 300: '#F3E27A', 50: '#FBF3DD' },
                        leaf: { 600: '#5C7A22', 400: '#8BAA3F', 100: '#EEF3DE' },
                        cream: { 50: '#FBF8F1', 100: '#F3EEDF' },
                        charcoal: { 800: '#2B2A26', 500: '#6B6A63' }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Playfair Display"', 'Georgia', 'serif']
                    }
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
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
    <div x-data="pelangganApp()"
         x-init="initApp()"
         x-cloak
         class="min-h-screen flex flex-col justify-between">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-12 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100"
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
                    <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-width="2" d="M12 11v5m0-8v.01"></path>
                    </svg>
                    <svg x-show="toast.type === 'waiter'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
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
        <header class="sticky top-0 z-30 bg-[#FBF8F1]/95 backdrop-blur-md border-b border-forest-100/60 transition-all duration-300">
            <div class="max-w-md lg:max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
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
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-forest-100/90 text-forest-900 text-xs font-bold border border-forest-200/60 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-forest-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>{{ $meja->kode_meja }}</span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-forest-900 border-2 border-gold-400 overflow-hidden flex items-center justify-center shadow-sm shrink-0">
                        <img src="{{ asset('asset/Logo.png') }}"
                             alt="Saung Situ Awi Logo"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-gold-300 font-bold text-xs\'>SA</span>';">
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 max-w-md lg:max-w-5xl mx-auto w-full px-4 pt-4">
            <div x-show="step === 1"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                @include('pelanggan.katalog', ['isSinglePage' => true])
            </div>
            <div x-show="step === 2"
                 class="lg:max-w-2xl lg:mx-auto"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">
                @include('pelanggan.ringkasan', ['isSinglePage' => true])
            </div>
        </main>
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
                                class="p-3 rounded-xl border text-xs font-semibold text-left transition"
                                :class="activeCategory === cat.id
                                    ? 'bg-forest-600 text-cream-50 border-forest-600'
                                    : 'bg-cream-50 text-forest-900 border-forest-100 hover:bg-forest-50'">
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
    <form id="formPanggil" method="POST" action="{{ route('emenu.panggil', $meja->id) }}" class="hidden">
        @csrf
    </form>
    <script>
        function pelangganApp() {
            return {
                step: 1,
                pageTitles: {
                    1: 'Katalog Menu',
                    2: 'Ringkasan Pesanan'
                },
                mejaId: {{ $meja->id }},
                mejaKode: @json($meja->kode_meja),
                mejaArea: @json($meja->area),
                orderRef: @json($orderRef),
                checkoutUrl: @json(route('emenu.checkout', $meja->id)),
                searchQuery: '',
                activeCategory: 'semua',
                showFilterModal: false,
                paymentMethod: 'qris',
                iotChecking: false,
                liveTimeString: '',
                toast: {
                    visible: false,
                    type: 'info',
                    title: '',
                    message: '',
                    timeout: null
                },
                categories: @json($kategoriJson),
                menuItems: @json($menuJson),
                cartItems: [],
                initApp() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },
                updateClock() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    this.liveTimeString = `${hours}:${minutes} WIB`;
                },
                get filteredMenuItems() {
                    return this.menuItems.filter(item => {
                        const matchCat = this.activeCategory === 'semua' || item.category == this.activeCategory;
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
                    return Math.round(this.cartSubtotal * 0.10);
                },
                get cartGrandTotal() {
                    return this.cartSubtotal + this.cartTax;
                },
                formatRupiah(number) {
                    return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
                },
                addToCart(menuItem) {
                    if (menuItem.isSoldOut) return;
                    const existing = this.cartItems.find(i => i.id === menuItem.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cartItems.push({
                            id: menuItem.id,
                            name: menuItem.name,
                            price: menuItem.price,
                            qty: 1,
                            image: menuItem.image,
                            note: ''
                        });
                    }
                    this.showToast('success', 'Menu Ditambahkan', `${menuItem.name} masuk ke bon pesanan.`);
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
                    if (this.cartItems.length === 0 && this.step === 2) {
                        this.showToast('info', 'Keranjang Kosong', 'Silakan pilih menu terlebih dahulu.');
                        this.goToStep(1);
                    }
                },
                goToStep(newStep) {
                    if (newStep === 2 && this.cartItems.length === 0) {
                        this.showToast('info', 'Pilih Menu Dulu', 'Pilih minimal 1 menu sebelum melihat bon.');
                        return;
                    }
                    this.step = newStep;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                handleBackNavigation() {
                    if (this.step === 2) {
                        this.goToStep(1);
                    } else {
                        window.location.href = '/';
                    }
                },
                checkIotStatus() {
                    this.iotChecking = true;
                    setTimeout(() => {
                        this.iotChecking = false;
                        this.showToast('success', 'IoT Node Online', 'Meja ' + this.mejaKode + ' terhubung optimal.');
                    }, 800);
                },
                triggerWaiterCall() {
                    document.getElementById('formPanggil').submit();
                },
                kirimBon() {
                    if (this.cartItems.length === 0) {
                        this.showToast('info', 'Pilih Menu Dulu', 'Pilih minimal 1 menu sebelum membayar.');
                        return;
                    }
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.checkoutUrl;
                    const tambah = (nama, nilai) => {
                        const isi = document.createElement('input');
                        isi.type = 'hidden';
                        isi.name = nama;
                        isi.value = nilai;
                        form.appendChild(isi);
                    };
                    tambah('_token', token);
                    tambah('metode', this.paymentMethod === 'cash' ? 'tunai' : 'qris');
                    this.cartItems.forEach(item => {
                        tambah(`jumlah[${item.id}]`, item.qty);
                        tambah(`catatan[${item.id}]`, item.note || '');
                    });
                    document.body.appendChild(form);
                    form.submit();
                },
                showToast(type, title, message) {
                    clearTimeout(this.toast.timeout);
                    this.toast = {
                        visible: true,
                        type: type,
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

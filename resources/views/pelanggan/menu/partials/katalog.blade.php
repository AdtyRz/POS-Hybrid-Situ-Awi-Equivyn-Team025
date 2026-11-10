<div class="pb-28 lg:pb-10">

    <section class="relative overflow-hidden rounded-3xl border border-forest-100/70 bg-white p-5 shadow-sm lg:border-forest-700/40 lg:bg-gradient-to-br lg:from-forest-900 lg:via-forest-800 lg:to-forest-700 lg:p-8 lg:shadow-xl lg:shadow-forest-900/10">
        <div class="pointer-events-none absolute inset-0 hidden lg:block">
            <div class="absolute -right-24 -top-28 h-72 w-72 rounded-full bg-gold-500/15 blur-3xl"></div>
            <div class="absolute -bottom-32 right-32 h-72 w-72 rounded-full bg-forest-400/20 blur-3xl"></div>
            <div class="absolute left-1/2 top-0 h-px w-2/3 -translate-x-1/2 bg-gradient-to-r from-transparent via-gold-400/40 to-transparent"></div>
        </div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between lg:gap-8">
            <div class="max-w-xl space-y-2">
                <div class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-gold-600 lg:text-gold-300">
                    <svg class="w-3.5 h-3.5 text-gold-500 lg:text-gold-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 2.438 4.877a1 1 0 01-.894 1.447H2.242a1 1 0 01-.894-1.447l2.438-4.877-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/>
                    </svg>
                    <span>Wilujeng Sumping</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-forest-900 lg:text-4xl lg:text-cream-50">
                    Halo, <span x-text="(namaPelangganInput || '').trim() || namaPelanggan || 'Tamu {{ $meja->kode_meja }}'">{{ $namaPelanggan !== '' ? $namaPelanggan : 'Tamu '.$meja->kode_meja }}</span>!
                </h1>
                <p class="text-xs leading-relaxed text-charcoal-500 lg:max-w-lg lg:text-sm lg:text-cream-100/80">
                    Nikmati sajian otentik khas bumi Parahyangan di saung tenang tepi danau.
                    Pilih menu favorit, kirim bon, dan bayar langsung dari meja Anda.
                </p>
                <div class="hidden lg:flex flex-wrap items-center gap-2 pt-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-bold text-cream-50 backdrop-blur-sm">
                        <svg class="w-3 h-3 text-gold-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        Meja {{ $meja->kode_meja }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-bold text-cream-50 backdrop-blur-sm">
                        <svg class="w-3 h-3 text-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <circle cx="12" cy="11" r="2.5" stroke-width="2"></circle>
                        </svg>
                        {{ $meja->area }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-bold text-cream-50 backdrop-blur-sm">
                        Order Ref {{ $orderRef }}
                    </span>
                </div>
            </div>

            <div class="hidden shrink-0 flex-col items-end gap-2 text-right lg:flex">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold text-gold-300 backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    Dapur &amp; kasir tersinkron
                </div>
                <p class="max-w-[17rem] text-[11px] leading-relaxed text-cream-100/70">
                    Pesanan Anda langsung diteruskan ke Kitchen Display System setelah bon dikonfirmasi.
                </p>
            </div>
        </div>
    </section>


    <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-forest-600 bg-gradient-to-r from-forest-800 to-forest-700 px-4 py-3.5 text-cream-50 shadow-md sm:flex-row sm:items-center sm:justify-between lg:mt-5 lg:rounded-2xl lg:px-5 lg:py-3.5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-forest-900/60 border border-forest-400/40 flex items-center justify-center shrink-0">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
            </div>
            <div>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-white">
                    <span>Meja Anda Terhubung</span>
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-forest-100/90 font-medium">
                    <svg class="w-3 h-3 text-gold-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <span>IoT Node {{ $meja->kode_meja }} Online &amp; Sinkron Dapur</span>
                </div>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-forest-400/30 bg-forest-900/50 px-3 py-1.5 text-[10px] font-bold tracking-wider text-gold-300">
                ORDER REF {{ $orderRef }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-forest-400/30 bg-forest-900/50 px-3 py-1.5 text-[10px] font-bold text-cream-50">
                <svg class="w-3 h-3 text-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                    <path stroke-linecap="round" stroke-width="2" d="M12 7v5l3 2"></path>
                </svg>
                <span x-text="liveTimeString">{{ now('Asia/Jakarta')->format('H:i') }} WIB</span>
            </span>
        </div>
    </div>

    <div class="mt-5 lg:mt-6 lg:grid lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start lg:gap-7">
        <div class="min-w-0">

            <div class="space-y-3 lg:rounded-2xl lg:border lg:border-forest-100/70 lg:bg-white lg:p-4 lg:shadow-sm">
                <div class="relative flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari Gurame, Nasi Liwet, Kopi..."
                               class="w-full pl-9 pr-10 py-2.5 lg:py-3 bg-white border border-forest-100 rounded-xl text-xs sm:text-sm text-charcoal-800 placeholder-charcoal-500 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition shadow-sm">
                        <button x-show="searchQuery.length > 0"
                                @click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-charcoal-500 hover:text-charcoal-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <button type="button"
                            @click="showFilterModal = true"
                            class="w-10 h-10 lg:w-auto lg:h-[46px] lg:px-4 rounded-xl bg-white border border-forest-100 flex items-center justify-center lg:gap-2 text-forest-700 hover:bg-forest-100/50 shadow-sm transition active:scale-95 shrink-0"
                            title="Filter Menu">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <span class="hidden lg:inline text-xs font-bold">Filter</span>
                    </button>
                </div>

                <div class="hidden lg:flex items-center justify-between gap-3 pt-1">
                    <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-charcoal-500">Kategori Menu</span>
                    <button type="button"
                            x-show="activeCategory !== 'semua' || searchQuery.length > 0"
                            @click="activeCategory = 'semua'; searchQuery = ''"
                            class="text-[10px] font-bold text-forest-700 hover:text-forest-900 transition">
                        Reset filter
                    </button>
                </div>

                <div class="overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none flex items-center gap-2 lg:mx-0 lg:px-0 lg:flex-wrap lg:overflow-visible">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button"
                                @click="activeCategory = cat.id"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 lg:px-4 lg:py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all shadow-sm active:scale-95"
                                :class="activeCategory === cat.id
                                    ? 'bg-forest-600 text-cream-50 ring-2 ring-forest-600/30'
                                    : 'bg-white text-forest-900 border border-forest-100/80 hover:bg-cream-100'">
                            <span x-text="cat.name"></span>
                        </button>
                    </template>
                </div>
            </div>


            <div class="mt-5 flex items-end justify-between gap-3 lg:mt-6">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-forest-900 lg:text-lg">Pilihan Menu</h2>
                        <span class="inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-forest-100 px-2 py-0.5 text-[10px] font-bold text-forest-800"
                              x-text="filteredMenuItems.length">{{ count($menuJson) }}</span>
                    </div>
                    <p class="mt-0.5 text-[11px] text-charcoal-500">Harga sudah termasuk pelayanan saung.</p>
                </div>
                <div class="hidden lg:flex items-center gap-1.5 text-[11px] font-semibold text-forest-700">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Bahan segar setiap hari
                </div>
            </div>


            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-5 pt-3">
                <template x-for="item in filteredMenuItems" :key="item.id">
                    <div class="group bg-white rounded-2xl border border-forest-100/70 overflow-hidden shadow-sm flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:border-gold-300 hover:shadow-lg cursor-pointer"
                         @click="openMenu(item)"
                         @keydown.enter.self.prevent="openMenu(item)"
                         @keydown.space.self.prevent="openMenu(item)"
                         role="button"
                         tabindex="0"
                         :class="item.isSoldOut ? 'opacity-85' : ''">
                        <div>
                            <div class="relative aspect-[4/3] w-full overflow-hidden bg-forest-100/40">
                                <img :src="item.image"
                                     :alt="item.name"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                     :class="item.isSoldOut ? 'grayscale contrast-75' : ''"
                                     loading="lazy">

                                <div class="absolute top-2 left-2 lg:top-3 lg:left-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 lg:px-2.5 lg:py-1 rounded-md text-[10px] font-bold shadow-sm"
                                          :class="{
                                              'bg-amber-400 text-forest-900': item.badgeType === 'bestseller',
                                              'bg-emerald-600 text-white': item.badgeType === 'favorite' || item.badgeType === 'lalap',
                                              'bg-cream-100/90 text-forest-900 border border-forest-200': item.badgeType === 'bar' || item.badgeType === 'kopi',
                                              'bg-red-600 text-white': item.badgeType === 'soldout'
                                          }">
                                        <span x-text="item.badge"></span>
                                    </span>
                                </div>

                                <span x-show="cartQty(item.id) > 0"
                                      class="absolute bottom-2 right-2 lg:bottom-3 lg:right-3 inline-flex items-center gap-1 rounded-full bg-forest-900/90 px-2 py-1 text-[9px] font-bold text-cream-50 shadow-sm backdrop-blur-sm"
                                      x-text="cartQty(item.id) + ' di bon'"></span>
                            </div>

                            <div class="p-3 lg:p-4 space-y-1">
                                <h3 class="font-bold text-xs sm:text-sm text-forest-900 line-clamp-1 lg:text-[15px]"
                                    :class="item.isSoldOut ? 'text-charcoal-500' : ''"
                                    x-text="item.name"></h3>
                                <p class="text-[11px] text-charcoal-500 line-clamp-2 leading-relaxed"
                                   x-text="item.desc"></p>
                                <div class="flex items-center justify-between gap-2 pt-0.5">
                                    <p class="text-[10px] font-bold"
                                       :class="item.isSoldOut ? 'text-red-600' : (item.stock <= 3 ? 'text-amber-700' : 'text-charcoal-500')"
                                       x-text="item.isSoldOut ? 'Stok habis' : 'Stok ' + item.stock + ' porsi'"></p>
                                    <span class="hidden lg:inline-flex items-center gap-1 text-[10px] font-semibold text-forest-700 opacity-0 transition group-hover:opacity-100">
                                        Lihat detail
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="px-3 pb-3 pt-1 lg:px-4 lg:pb-4 flex items-center justify-between border-t border-forest-100/40">
                            <div>
                                <div class="text-[10px] text-charcoal-500 font-medium">Harga</div>
                                <div class="text-xs sm:text-sm lg:text-[15px] font-bold text-forest-900"
                                     :class="item.isSoldOut ? 'text-charcoal-500' : ''"
                                     x-text="formatRupiah(item.price)"></div>
                            </div>

                            <div>
                                <template x-if="!item.isSoldOut">
                                    <button type="button"
                                            @click.stop="addToCart(item)"
                                            class="w-8 h-8 lg:w-9 lg:h-9 rounded-full bg-gold-500 hover:bg-gold-600 text-forest-900 font-bold flex items-center justify-center shadow-sm active:scale-90 transition-all"
                                            :class="cartQty(item.id) >= item.stock ? 'opacity-40 cursor-not-allowed' : ''"
                                            :title="cartQty(item.id) >= item.stock ? 'Semua stok sudah masuk bon' : 'Tambah ke Pesanan'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </template>
                                <template x-if="item.isSoldOut">
                                    <button type="button"
                                            disabled
                                            class="w-8 h-8 lg:w-9 lg:h-9 rounded-full bg-charcoal-500/20 text-charcoal-500 font-bold flex items-center justify-center cursor-not-allowed"
                                            title="Stok Habis">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredMenuItems.length === 0"
                     class="col-span-full rounded-2xl border border-dashed border-forest-200 bg-white/80 px-6 py-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-forest-100 text-forest-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-3 text-sm font-bold text-forest-900">Menu tidak ditemukan</h3>
                    <p class="mt-1 text-xs text-charcoal-500">Coba kata kunci lain atau reset filter kategori.</p>
                    <button type="button"
                            @click="searchQuery = ''; activeCategory = 'semua'"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-forest-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-forest-800 active:scale-95">
                        Reset Pencarian
                    </button>
                </div>
            </div>
        </div>


        <aside class="mt-5 space-y-4 lg:mt-0 lg:sticky lg:top-24">
            <div class="hidden lg:flex flex-col overflow-hidden rounded-3xl border border-forest-100/70 bg-white shadow-sm">
                <div class="border-b border-forest-600/40 bg-gradient-to-r from-forest-900 to-forest-800 px-5 py-4 text-cream-50">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-gold-300">Bon Pesanan</p>
                            <h2 class="text-sm font-bold text-white">Meja {{ $meja->kode_meja }}</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/10 px-2.5 py-1 text-[10px] font-bold text-cream-50">
                            <span class="h-1.5 w-1.5 rounded-full bg-gold-300"></span>
                            <span x-text="totalCartItemsCount + ' item'">0 item</span>
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div x-show="totalCartItemsCount === 0" class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-cream-100 text-forest-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <p class="mt-3 text-xs font-bold text-forest-900">Bon masih kosong</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-charcoal-500">Tambahkan menu favorit Anda, lalu lihat bon di sini.</p>
                    </div>

                    <div class="divide-y divide-forest-100/60">
                        <template x-for="item in cartItems" :key="'sidebar-' + item.id">
                            <div class="flex gap-3 py-3 first:pt-0">
                                <img :src="item.image"
                                     :alt="item.name"
                                     class="h-12 w-12 shrink-0 rounded-xl border border-forest-100 object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate text-xs font-bold text-forest-900" x-text="item.name"></p>
                                        <button type="button"
                                                @click="removeFromCart(item.id)"
                                                class="shrink-0 rounded-md p-0.5 text-charcoal-400 transition hover:text-red-500"
                                                title="Hapus menu">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="mt-0.5 text-[10px] text-charcoal-500" x-text="formatRupiah(item.price) + ' / porsi'"></p>
                                    <div class="mt-1.5 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="decreaseQty(item.id)"
                                                    class="flex h-6 w-6 items-center justify-center rounded-full border border-forest-200 bg-cream-50 text-sm font-bold text-forest-900 transition hover:bg-cream-100 active:scale-90">
                                                &minus;
                                            </button>
                                            <span class="w-5 text-center text-[11px] font-bold text-forest-900" x-text="item.qty"></span>
                                            <button type="button"
                                                    @click="increaseQty(item.id)"
                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-forest-600 text-sm font-bold text-cream-50 transition hover:bg-forest-700 active:scale-90">
                                                +
                                            </button>
                                        </div>
                                        <span class="text-xs font-bold text-forest-900" x-text="formatRupiah(item.price * item.qty)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="totalCartItemsCount > 0" class="mt-3 space-y-3 border-t border-forest-100/70 pt-4">
                        <div class="flex items-center justify-between text-[11px] text-charcoal-500">
                            <span x-text="'Subtotal (' + totalCartItemsCount + ' item)'"></span>
                            <span class="font-semibold text-charcoal-800" x-text="formatRupiah(cartSubtotal)"></span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-charcoal-500">
                            <span>Biaya layanan</span>
                            <span class="font-bold text-emerald-600">Gratis</span>
                        </div>
                        <div class="flex items-end justify-between rounded-2xl bg-cream-50 px-3.5 py-3">
                            <div>
                                <p class="text-[9px] font-bold uppercase tracking-wider text-charcoal-500">Total</p>
                                <p class="text-[10px] text-charcoal-400">Termasuk pelayanan</p>
                            </div>
                            <p class="text-base font-extrabold text-forest-900" x-text="formatRupiah(cartGrandTotal)"></p>
                        </div>
                        <button type="button"
                                @click="goToStep(2)"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-forest-700 px-4 py-3 text-xs font-bold text-white shadow-md transition hover:bg-forest-800 active:scale-[0.98]">
                            <span>Lihat Bon &amp; Lanjut</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                        <p class="text-center text-[10px] text-charcoal-400">Tanpa biaya tersembunyi</p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-gold-300/70 bg-gradient-to-br from-gold-50 to-cream-100 p-4 shadow-sm lg:p-5">
                <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gold-300/30 blur-2xl"></div>
                <div class="relative flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-400 text-forest-900 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>
                        </svg>
                    </div>
                    <div class="space-y-1 text-xs">
                        <h4 class="font-bold text-forest-900 text-xs sm:text-sm">Butuh Bantuan Pelayan?</h4>
                        <p class="text-charcoal-800 leading-relaxed">
                            Cukup sentuh tombol di bawah tanpa perlu memanggil keras. Kru kami akan segera menghampiri meja {{ $meja->kode_meja }}.
                        </p>
                        <div class="pt-1">
                            <button type="button"
                                    @click="triggerWaiterCall()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gold-100 text-forest-900 rounded-full border border-gold-400 text-[11px] font-semibold transition active:scale-95 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Sentuh Sensor Virtual Meja</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>


    <div x-show="totalCartItemsCount > 0"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-24 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-24 opacity-0"
         class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-40 lg:hidden">
        <div class="bg-forest-900 text-cream-50 rounded-full px-4 py-2.5 shadow-2xl border border-forest-600/60 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="relative w-10 h-10 rounded-full bg-forest-800 border border-forest-600 flex items-center justify-center text-gold-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center ring-2 ring-forest-900"
                          x-text="totalCartItemsCount"></span>
                </div>

                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-gold-300">
                        PESANAN {{ $meja->kode_meja }}
                    </div>
                    <div class="text-sm sm:text-base font-extrabold text-white"
                         x-text="formatRupiah(cartSubtotal)"></div>
                </div>
            </div>

            <button type="button"
                    @click="goToStep(2)"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gold-500 hover:bg-gold-400 text-forest-900 font-bold text-xs sm:text-sm shadow-md transition active:scale-95 shrink-0">
                <span>Lihat Bon</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

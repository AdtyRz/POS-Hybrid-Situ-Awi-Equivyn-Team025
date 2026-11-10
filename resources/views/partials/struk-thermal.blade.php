<script>
    /**
     * Renderer struk thermal bersama untuk kasir dan pratinjau admin.
     * Dipakai agar hasil pratinjau di Pengaturan identik dengan struk kasir.
     */
    window.cetakStrukThermal = function (payload) {
        const opsi = payload || {};
        const paperSize = opsi.paperSize === '58mm' ? '58mm' : '80mm';
        const orders = Array.isArray(opsi.orders) ? opsi.orders : [];

        const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (karakter) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[karakter]));

        const formatRupiah = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        const formatNumber = (value) => Number(value || 0).toLocaleString('id-ID');

        const jendela = window.open(
            '',
            '_blank',
            paperSize === '58mm' ? 'width=320,height=640' : 'width=380,height=680'
        );

        if (! jendela) {
            return null;
        }

        const dokumen = jendela.document;

        // about:blank berjalan dalam quirks mode. Tanpa doctype, tinggi body
        // mengikuti viewport dan bisa menghasilkan halaman kedua yang kosong.
        dokumen.open();
        dokumen.write('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"></head><body></body></html>');
        dokumen.close();
        dokumen.title = opsi.title || 'Struk Kasir';

        const gayaHalaman = dokumen.createElement('style');
        gayaHalaman.id = 'page-size';
        gayaHalaman.textContent = `@page { size: ${paperSize} 200mm; margin: 0; }`;
        dokumen.head.appendChild(gayaHalaman);

        const gaya = dokumen.createElement('style');
        gaya.textContent = `
            * { box-sizing: border-box; }
            html, body { margin: 0; padding: 0; background: #fff; }
            body {
                width: ${paperSize};
                padding: ${paperSize === '58mm' ? '3mm 5mm 5mm' : '4mm 4mm 6mm'};
                color: #000;
                font-family: 'Courier New', Courier, monospace;
                font-size: ${paperSize === '58mm' ? '9px' : '10px'};
                line-height: 1.35;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .receipt { width: 100%; break-inside: avoid; page-break-inside: avoid; }
            .logo {
                display: block;
                margin: 0 auto 2mm;
                width: 28mm;
                height: 14mm;
                object-fit: contain;
            }
            .brand {
                text-align: center;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.3px;
                text-transform: uppercase;
            }
            .address, .phone {
                text-align: center;
                font-size: 9px;
                line-height: 1.3;
            }
            .divider { border-top: 1px dashed #000; margin: 2mm 0; }
            table { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .meta td { padding: 0.4mm 0; font-size: 9px; vertical-align: top; }
            .meta .label { width: 22mm; }
            .meta .sep { width: 3mm; }
            .meta .value { overflow-wrap: anywhere; word-break: break-word; }
            .items th {
                padding: 0 0 1mm;
                border-bottom: 1px dashed #000;
                font-size: 8.5px;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
            }
            .items td { padding: 1mm 0 0; font-size: 9.5px; vertical-align: top; }
            .items .qty { width: 9mm; }
            .items .nama { padding-right: 2mm; overflow-wrap: anywhere; word-break: break-word; }
            .items .harga { width: 22mm; text-align: right; white-space: nowrap; }
            .items .unit { margin-top: 0.4mm; font-size: 8px; color: #333; }
            .items .empty { text-align: center; }
            .summary td { padding: 0.6mm 0; font-size: 9.5px; }
            .summary .nominal { text-align: right; white-space: nowrap; }
            .summary .total td {
                padding-top: 1.5mm;
                border-top: 1px solid #000;
                font-size: 11px;
                font-weight: 700;
            }
            .status {
                margin: 1mm 0;
                text-align: center;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 1px;
            }
            .payment { text-align: center; font-size: 9px; }
            .footer {
                text-align: center;
                font-size: 9px;
                line-height: 1.4;
                white-space: pre-line;
                overflow-wrap: anywhere;
            }
            .paper-58 .brand { font-size: 11px; }
            .paper-58 .meta .label { width: 17mm; }
            .paper-58 .items .qty { width: 7mm; }
            .paper-58 .items .harga { width: 18mm; }
            @media screen {
                html { background: #e7e5df; }
                body {
                    margin: 14px auto;
                    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.18);
                }
            }
            @media print {
                html, body { height: auto !important; min-height: 0 !important; margin: 0 !important; }
                .receipt { break-after: avoid; page-break-after: avoid; }
            }
        `;
        dokumen.head.appendChild(gaya);
        dokumen.body.className = paperSize === '58mm' ? 'paper-58' : 'paper-80';

        const itemMap = new Map();

        orders.forEach((order) => {
            (order.items || []).forEach((item) => {
                const harga = Number(item.price || 0);
                const kunci = String(item.name || 'Menu') + '|' + harga;
                const baris = itemMap.get(kunci) || {
                    name: item.name || 'Menu',
                    qty: 0,
                    price: harga,
                };

                baris.qty += Number(item.qty || 0);
                itemMap.set(kunci, baris);
            });
        });

        const itemRows = Array.from(itemMap.values()).map((item) => {
            const unit = item.qty > 1
                ? `<div class="unit">${formatRupiah(item.price)} / item</div>`
                : '';

            return `
                <tr>
                    <td class="qty">${esc(item.qty)}x</td>
                    <td class="nama">${esc(item.name)}${unit}</td>
                    <td class="harga">${formatNumber(item.qty * item.price)}</td>
                </tr>
            `;
        }).join('');

        const subtotal = orders.reduce((total, order) => {
            const dariOrder = Number(order.subtotal || 0)
                || (order.items || []).reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);

            return total + dariOrder;
        }, 0);

        const total = orders.reduce((sum, order) => sum + Number(order.total_bayar || 0), 0);
        const namaPemesan = [...new Set(orders.map(order => order.nama_pelanggan || 'Tamu'))].join(', ');
        const waktuList = orders.map(order => order.waktu_pesan).filter(Boolean);
        const waktu = waktuList.length > 1 && waktuList[0] !== waktuList[waktuList.length - 1]
            ? waktuList[0] + ' s/d ' + waktuList[waktuList.length - 1]
            : (waktuList[0] || '');

        const metode = [...new Set(orders.map(order => order.metode_pembayaran).filter(Boolean))]
            .map((metode) => metode === 'qris' ? 'QRIS Midtrans' : (metode === 'tunai' ? 'Tunai Kasir' : metode))
            .join(' / ');

        const kodeOrder = orders.map(order => '#' + order.kode_pesanan).join(' / ');
        const metaRows = [
            ['No. Order', kodeOrder],
            ['Pemesan', namaPemesan || 'Tamu'],
            ['Meja', opsi.tableName || '-'],
            ['Kasir', opsi.cashierName || '-'],
            ['Waktu', waktu ? waktu + ' WIB' : '-'],
            ['Pembayaran', metode || '-'],
        ].map(([label, value]) => `
            <tr>
                <td class="label">${esc(label)}</td>
                <td class="sep">:</td>
                <td class="value">${esc(value)}</td>
            </tr>
        `).join('');

        const bagian = dokumen.createElement('section');
        bagian.className = 'receipt';
        bagian.innerHTML = `
            ${opsi.logo ? `<img class="logo" src="${esc(opsi.logo)}" alt="Logo">` : ''}
            <div class="brand">${esc((opsi.header || 'SAUNG SITU AWI').toUpperCase())}</div>
            ${opsi.address ? `<div class="address">${esc(opsi.address)}</div>` : ''}
            ${opsi.phone ? `<div class="phone">Telp: ${esc(opsi.phone)}</div>` : ''}
            <div class="divider"></div>

            <table class="meta"><tbody>${metaRows}</tbody></table>
            <div class="divider"></div>

            <table class="items">
                <thead>
                    <tr>
                        <th class="qty">Qty</th>
                        <th class="nama">Item</th>
                        <th class="harga">Jumlah</th>
                    </tr>
                </thead>
                <tbody>${itemRows || '<tr><td class="empty" colspan="3">Tidak ada item</td></tr>'}</tbody>
            </table>

            <div class="divider"></div>

            <table class="summary">
                <tbody>
                    <tr>
                        <td>Subtotal</td>
                        <td class="nominal">${esc(formatRupiah(subtotal))}</td>
                    </tr>
                    <tr class="total">
                        <td>TOTAL</td>
                        <td class="nominal">${esc(formatRupiah(total))}</td>
                    </tr>
                </tbody>
            </table>

            <div class="divider"></div>

            <div class="status">LUNAS</div>
            ${metode ? `<div class="payment">Pembayaran: ${esc(metode)}</div>` : ''}
            <div class="divider"></div>

            ${opsi.footer ? `<div class="footer">${esc(opsi.footer)}</div>` : ''}
        `;
        dokumen.body.appendChild(bagian);

        const skrip = dokumen.createElement('script');
        skrip.textContent = `
            (function () {
                let selesai = false;
                const paperSize = ${JSON.stringify(paperSize)};
                const autoPrint = ${opsi.autoPrint === false ? 'false' : 'true'};

                const aturUkuranKertas = () => {
                    const receipt = document.querySelector('.receipt');
                    const gayaBody = getComputedStyle(document.body);
                    const tinggiPx = (receipt ? receipt.getBoundingClientRect().height : 0)
                        + parseFloat(gayaBody.paddingTop || 0)
                        + parseFloat(gayaBody.paddingBottom || 0);
                    // Layout cetak bisa sedikit lebih tinggi dari ukuran layar.
                    // Beri ruang aman agar footer tidak jatuh ke halaman kedua.
                    const tinggiMm = Math.max(
                        80,
                        Math.ceil((tinggiPx * 25.4) / 96) + 6
                    );
                    const gayaHalaman = document.getElementById('page-size');

                    if (gayaHalaman) {
                        gayaHalaman.textContent =
                            '@page { size: ' + paperSize + ' ' + tinggiMm + 'mm; margin: 0; }';
                    }
                };

                const cetak = () => {
                    if (selesai) return;
                    selesai = true;
                    aturUkuranKertas();

                    if (! autoPrint) return;

                    window.focus();
                    window.requestAnimationFrame(function () {
                        window.setTimeout(function () {
                            window.print();
                        }, 40);
                    });
                };

                const logo = document.querySelector('img.logo');

                if (logo) {
                    if (logo.complete) {
                        setTimeout(cetak, 60);
                    } else {
                        logo.addEventListener('load', cetak);
                        logo.addEventListener('error', cetak);
                    }
                } else {
                    setTimeout(cetak, 100);
                }

                window.addEventListener('load', cetak);
                setTimeout(cetak, 1500);
            })();
        `;
        dokumen.body.appendChild(skrip);

        return jendela;
    };
</script>


const TOAST_ID = 'admin-ajax-toast';
let toastTimer = null;

const pastikanToast = () => {
    let toast = document.getElementById(TOAST_ID);

    if (toast) return toast;

    toast = document.createElement('div');
    toast.id = TOAST_ID;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.className = 'fixed bottom-8 right-8 z-[100] max-w-sm px-5 py-3.5 rounded-2xl shadow-xl text-sm font-semibold text-white flex items-start gap-3 border border-white/10 opacity-0 translate-y-3 pointer-events-none transition-all duration-200';
    toast.innerHTML = '<span class="mt-0.5 flex-shrink-0" data-toast-icon></span><span data-toast-message></span>';
    document.body.appendChild(toast);

    return toast;
};

const tampilkanToast = (pesan, galat = false) => {
    const toast = pastikanToast();

    toast.classList.toggle('bg-[#00341A]', !galat);
    toast.classList.toggle('bg-[#93000A]', galat);
    toast.querySelector('[data-toast-icon]').innerHTML = galat
        ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>'
        : '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
    toast.querySelector('[data-toast-message]').textContent = pesan;

    toast.classList.remove('opacity-0', 'translate-y-3', 'pointer-events-none');
    toast.classList.add('opacity-100', 'translate-y-0');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-3', 'pointer-events-none');
    }, 3600);
};

const pesanPertama = (payload, bawaan) => {
    if (!payload) return bawaan;

    if (payload.errors) {
        const nilai = Object.values(payload.errors).flat();

        if (nilai.length) return String(nilai[0]);
    }

    return payload.message ? String(payload.message) : bawaan;
};

const tandaiFieldGalat = (form, errors) => {
    if (!errors || typeof errors !== 'object') return;

    Object.keys(errors).forEach((nama) => {
        let field = null;

        try {
            field = form.querySelector('[name="' + CSS.escape(nama) + '"]');
        } catch (error) {
            field = null;
        }

        if (!field || !field.classList) return;

        field.classList.add('border-[#BA1A1A]', 'ring-2', 'ring-[#BA1A1A]/20');
        field.setAttribute('aria-invalid', 'true');

        const bersihkan = () => {
            field.classList.remove('border-[#BA1A1A]', 'ring-2', 'ring-[#BA1A1A]/20');
            field.removeAttribute('aria-invalid');
            field.removeEventListener('input', bersihkan);
            field.removeEventListener('change', bersihkan);
        };

        field.addEventListener('input', bersihkan);
        field.addEventListener('change', bersihkan);
    });
};

const tombolForm = (form, submitter) => {
    if (submitter instanceof HTMLElement) return submitter;

    return form.querySelector('button[type="submit"], input[type="submit"]');
};

const bersihkanGalatField = (form) => {
    form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
        field.classList.remove('border-[#BA1A1A]', 'ring-2', 'ring-[#BA1A1A]/20');
        field.removeAttribute('aria-invalid');
    });
};

const kirimForm = async (form, submitter = null) => {
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.adminSubmitting === '1') return;

    bersihkanGalatField(form);

    const tombol = tombolForm(form, submitter);
    const stateTombol = tombol ? {
        disabled: tombol.disabled,
        kelas: ['opacity-60', 'cursor-wait'],
    } : null;

    form.dataset.adminSubmitting = '1';

    if (tombol) {
        tombol.disabled = true;
        tombol.classList.add(...stateTombol.kelas);
    }

    try {
        const respons = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-Admin-Ajax': '1',
            },
            body: new FormData(form),
            credentials: 'same-origin',
        });

        const tipe = respons.headers.get('content-type') || '';
        const payload = tipe.includes('application/json')
            ? await respons.json().catch(() => null)
            : null;

        if (!payload) {
            if (respons.redirected) {
                window.location.assign(respons.url);
                return;
            }

            throw new Error('Respons server tidak dikenali. Muat ulang halaman lalu coba lagi.');
        }

        if (respons.status === 419) {
            throw new Error('Sesi Anda sudah kedaluwarsa. Halaman akan dimuat ulang.');
        }

        if (!respons.ok) {
            tandaiFieldGalat(form, payload.errors);
            throw new Error(pesanPertama(payload, 'Perubahan gagal disimpan.'));
        }

        const detail = {
            form,
            payload,
            message: payload.message || 'Perubahan berhasil disimpan.',
            handled: false,
        };

        window.dispatchEvent(new CustomEvent('admin:form-saved', { detail }));

        if (!detail.handled) {
            tampilkanToast(detail.message);
        }

        if (typeof window.refreshLiveRegions === 'function') {
            window.refreshLiveRegions({ immediate: true });
        }
    } catch (error) {
        tampilkanToast(error.message || 'Perubahan gagal disimpan.', true);

        if (String(error.message || '').includes('kedaluwarsa')) {
            setTimeout(() => window.location.reload(), 1400);
        }
    } finally {
        form.dataset.adminSubmitting = '';

        if (tombol && stateTombol) {
            tombol.disabled = stateTombol.disabled;
            tombol.classList.remove(...stateTombol.kelas);
        }
    }
};

const formPerluDiabaikan = (form) => {
    if (form.hasAttribute('data-no-ajax')) return true;
    if ((form.getAttribute('target') || '').toLowerCase() === '_blank') return true;
    if ((form.getAttribute('method') || 'GET').toUpperCase() === 'GET') return true;

    const aksi = form.getAttribute('action') || '';

    return aksi.includes('/logout');
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;
    if (event.defaultPrevented) return;
    if (formPerluDiabaikan(form)) return;

    event.preventDefault();
    kirimForm(form, event.submitter || null);
});

window.adminSubmitForm = (form, submitter = null) => kirimForm(form, submitter);

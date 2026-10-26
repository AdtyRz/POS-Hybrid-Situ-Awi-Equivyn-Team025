import QRCode from 'qrcode';

window.QRCode = QRCode;

const render = async (canvas) => {
    const value = canvas.dataset.qr;

    if (!value) return;

    try {
        await QRCode.toCanvas(canvas, value, {
            width: 220,
            margin: 1,
            color: {
                dark: '#00341A',
                light: '#FFFFFF',
            },
        });
    } catch (error) {

    }
};

document.querySelectorAll('canvas[data-qr]').forEach(render);

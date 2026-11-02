document.addEventListener('DOMContentLoaded', () => {
  if (typeof QRCodeStyling === 'undefined') return;

  const els = document.querySelectorAll('.promo-perigee-qr');
  if (!els.length) return;

  // Такой "view size" у тебя в верстке: w-[84px] h-[84px] (можно 88 если хочешь)
  const VIEW_SIZE = 84;

  // Как в loyalty: учитываем DPR и рендерим крупнее, а показываем меньше
  const dpr = Math.max(1, Math.ceil(window.devicePixelRatio || 1));
  const RENDER_SCALE = 3;
  const RENDER_SIZE = VIEW_SIZE * dpr * RENDER_SCALE;

  els.forEach((el) => {
    let qrData = el.dataset.qr;
    if (!qrData) return;

    // Как в loyalty: если в data-qr лежит JSON со строкой — достаем строку
    try {
      const parsed = JSON.parse(qrData);
      if (typeof parsed === 'string') qrData = parsed;
    } catch (e) {}

    const qr = new QRCodeStyling({
      width: RENDER_SIZE,
      height: RENDER_SIZE,
      data: qrData,

      // Пропорционально как в loyalty (там 18 при 220). Для 84 получается ~7.
      margin: Math.round(7 * dpr * RENDER_SCALE),

      qrOptions: {
        typeNumber: 0,
        // Чтобы было максимально похоже на loyalty:
        errorCorrectionLevel: "Q"
      },

      dotsOptions: {
        type: "rounded",
        gradient: {
          type: "linear",
          rotation: 90,
          colorStops: [
            { offset: 0, color: "#B9A5FF" },
            { offset: 1, color: "#F2F1F7" }
          ]
        }
      },

      backgroundOptions: { color: "rgba(0,0,0,0.55)" },

      cornersSquareOptions: {
        type: "extra-rounded",
        color: "rgba(255,255,255,0.95)"
      },

      cornersDotOptions: { type: "dot", color: "#B9A5FF" },

      image: el.dataset.logo,
      imageOptions: {
        crossOrigin: "anonymous",
        // В loyalty 0.42. Для маленького QR можно чуть больше, но если хочешь 1:1 — поставь 0.42
        imageSize: 0.42,
        margin: Math.round(2 * dpr * RENDER_SCALE),
        hideBackgroundDots: true
      }
    });

    el.innerHTML = '';
    qr.append(el);

    // ВАЖНО: принудительно задаем "видимый" размер, как в loyalty
    el.style.width = `${VIEW_SIZE}px`;
    el.style.height = `${VIEW_SIZE}px`;

    requestAnimationFrame(() => {
      const canvas = el.querySelector('canvas');
      if (canvas) {
        canvas.style.width = `${VIEW_SIZE}px`;
        canvas.style.height = `${VIEW_SIZE}px`;
        const ctx = canvas.getContext('2d');
        if (ctx) ctx.imageSmoothingEnabled = true;
      }
      const svg = el.querySelector('svg');
      if (svg) {
        svg.style.width = `${VIEW_SIZE}px`;
        svg.style.height = `${VIEW_SIZE}px`;
      }
    });
  });
});

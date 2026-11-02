document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('perigee-qr');
  if (!el || typeof QRCodeStyling === 'undefined') return;

  const VIEW_SIZE = 220;

  const dpr = Math.max(1, Math.ceil(window.devicePixelRatio || 1));
  const RENDER_SCALE = 3;
  const RENDER_SIZE = VIEW_SIZE * dpr * RENDER_SCALE;

  let qrData = el.dataset.qr;
  try {
    const parsed = JSON.parse(qrData);
    if (typeof parsed === 'string') qrData = parsed;
  } catch (e) {}

  const qr = new QRCodeStyling({
    width: RENDER_SIZE,
    height: RENDER_SIZE,
    data: qrData,

    margin: Math.round(18 * dpr * RENDER_SCALE),

    qrOptions: {
      typeNumber: 0,              
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
      imageSize: 0.42, 
      margin: Math.round(2 * dpr * RENDER_SCALE),
      hideBackgroundDots: true
    }
  });

  el.innerHTML = '';
  qr.append(el);

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

// Floor tabs and zoomable plans for every page that shows the lounge plan.
//
// Markup contract:
//   [data-floor-tab="<id>"]    a tab button
//   [data-floor-panel="<id>"]  anything that belongs to one floor (plan, legend)
//   [data-plan-viewport]       a plan that can be zoomed and dragged; holds [data-plan-stage]
//   [data-plan-zoom="in|out|fit"]  zoom buttons inside a viewport
//
// Plans are drawn in percentages of the building, so they fit any width. Zoom goes up to 4x.
// Ctrl/Cmd + wheel and a two-finger pinch zoom around the pointer, dragging pans the plan,
// and a drag never counts as a click on a table.

const MAX_ZOOM = 4;
const DRAG_THRESHOLD = 5;

function showFloor(id) {
    const floorId = String(id);

    document.querySelectorAll('[data-floor-panel]').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.floorPanel !== floorId);
    });

    document.querySelectorAll('[data-floor-tab]').forEach((tab) => {
        tab.setAttribute('aria-selected', String(tab.dataset.floorTab === floorId));
    });

    document.dispatchEvent(new CustomEvent('floor:change', { detail: { id: floorId } }));
}

function initViewport(viewport) {
    const stage = viewport.querySelector('[data-plan-stage]');
    if (!stage) return () => {};

    let scale = 1;
    let x = 0;
    let y = 0;
    const pointers = new Map();
    let gesture = null;
    let dragged = false;

    function clamp() {
        const width = viewport.clientWidth;
        const height = stage.offsetHeight;
        x = Math.min(0, Math.max(width - width * scale, x));
        y = Math.min(0, Math.max(viewport.clientHeight - height * scale, y));
        if (scale === 1) {
            x = 0;
            y = 0;
        }
    }

    function apply() {
        clamp();
        stage.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
        // Zoomed in, a finger drags the plan. At full view, it scrolls the page as usual.
        viewport.style.touchAction = scale > 1 ? 'none' : 'pan-y';
        viewport.dataset.zoomed = scale > 1 ? 'true' : 'false';
    }

    // Zooms so that the point (px, py) of the viewport stays where it is.
    function zoomAt(next, px, py) {
        next = Math.min(MAX_ZOOM, Math.max(1, next));
        x = px - ((px - x) / scale) * next;
        y = py - ((py - y) / scale) * next;
        scale = next;
        apply();
    }

    const local = (event) => {
        const bounds = viewport.getBoundingClientRect();
        return [event.clientX - bounds.left, event.clientY - bounds.top];
    };

    viewport.querySelectorAll('[data-plan-zoom]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const centre = [viewport.clientWidth / 2, viewport.clientHeight / 2];
            if (button.dataset.planZoom === 'in') zoomAt(scale * 1.5, ...centre);
            if (button.dataset.planZoom === 'out') zoomAt(scale / 1.5, ...centre);
            if (button.dataset.planZoom === 'fit') zoomAt(1, 0, 0);
        });
    });

    viewport.addEventListener('wheel', (event) => {
        // A plain wheel keeps scrolling the page; with Ctrl/Cmd (or a trackpad pinch) it zooms.
        if (!event.ctrlKey && !event.metaKey) return;
        event.preventDefault();
        zoomAt(scale * Math.exp(-event.deltaY * 0.0025), ...local(event));
    }, { passive: false });

    viewport.addEventListener('pointerdown', (event) => {
        if (event.target.closest('[data-plan-zoom]')) return;
        pointers.set(event.pointerId, local(event));
        dragged = false;

        if (pointers.size === 1) {
            gesture = { type: 'pan', start: local(event), x, y };
        } else if (pointers.size === 2) {
            const [a, b] = [...pointers.values()];
            gesture = { type: 'pinch', distance: Math.hypot(a[0] - b[0], a[1] - b[1]), scale };
        }
    });

    viewport.addEventListener('pointermove', (event) => {
        if (!pointers.has(event.pointerId) || !gesture) return;
        pointers.set(event.pointerId, local(event));

        if (gesture.type === 'pinch' && pointers.size === 2) {
            const [a, b] = [...pointers.values()];
            const distance = Math.hypot(a[0] - b[0], a[1] - b[1]);
            zoomAt(gesture.scale * (distance / gesture.distance), (a[0] + b[0]) / 2, (a[1] + b[1]) / 2);
            dragged = true;
            return;
        }

        const [px, py] = local(event);
        const dx = px - gesture.start[0];
        const dy = py - gesture.start[1];
        if (!dragged && Math.hypot(dx, dy) < DRAG_THRESHOLD) return;
        if (scale === 1) return; // nothing to pan at full view

        if (!dragged) viewport.setPointerCapture(event.pointerId);
        dragged = true;
        x = gesture.x + dx;
        y = gesture.y + dy;
        apply();
    });

    const release = (event) => {
        pointers.delete(event.pointerId);
        if (pointers.size === 0) gesture = null;
    };
    viewport.addEventListener('pointerup', release);
    viewport.addEventListener('pointercancel', release);

    // A drag that ends over a table must not select it.
    viewport.addEventListener('click', (event) => {
        if (dragged) {
            event.stopPropagation();
            event.preventDefault();
            dragged = false;
        }
    }, true);

    apply();
    const observer = new ResizeObserver(apply);
    observer.observe(viewport);

    return () => observer.disconnect();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-floor-tab]').forEach((tab) => {
        tab.addEventListener('click', () => showFloor(tab.dataset.floorTab));
    });

    const cleanups = [...document.querySelectorAll('[data-plan-viewport]')].map(initViewport);
    window.addEventListener('pagehide', () => cleanups.forEach((cleanup) => cleanup()), { once: true });
});

// Lets page scripts switch floors, for example to the floor of a selected table.
window.PerigeeFloorPlan = { showFloor };

// Floor tabs and table scaling for every page that draws the lounge plan.
//
// Markup contract:
//   [data-floor-tab="<id>"]    a tab button
//   [data-floor-panel="<id>"]  anything that belongs to one floor (plan, legend)
//   [data-floor-plan="auto"]   a plan whose tables are scaled here; table sizes are
//                              stored in pixels of a plan that is 1200 px wide

const DESIGN_WIDTH = 1200;
const MIN_SCALE = 0.3;

function scalePlan(plan) {
    const width = plan.clientWidth;
    if (!width) return; // a hidden floor is scaled when it is shown

    const scale = Math.max(MIN_SCALE, Math.min(1, width / DESIGN_WIDTH));

    plan.querySelectorAll('[data-base-width]').forEach((table) => {
        const baseWidth = parseFloat(table.dataset.baseWidth);
        const baseHeight = parseFloat(table.dataset.baseHeight);
        if (baseWidth > 0 && baseHeight > 0) {
            table.style.width = `${baseWidth * scale}px`;
            table.style.height = `${baseHeight * scale}px`;
        }
    });
}

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

document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('[data-floor-tab]');
    tabs.forEach((tab) => tab.addEventListener('click', () => showFloor(tab.dataset.floorTab)));

    const plans = document.querySelectorAll('[data-floor-plan="auto"]');
    if (!plans.length) return;

    // The observer also fires when a hidden floor becomes visible.
    const observer = new ResizeObserver((entries) => entries.forEach((entry) => scalePlan(entry.target)));
    plans.forEach((plan) => {
        scalePlan(plan);
        observer.observe(plan);
    });

    window.addEventListener('pagehide', () => observer.disconnect(), { once: true });
});

// Lets page scripts switch floors, for example to the floor of a selected table.
window.PerigeeFloorPlan = { showFloor };

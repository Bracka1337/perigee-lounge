// 2D / 3D switch for the client reservations page.
//
// 2D stays the default and the only view on small screens or without WebGL. three.js is
// downloaded the first time 3D is opened. The 2D plan remains the source of truth: table
// status and availability are read from its buttons, and picking a table in 3D clicks the
// matching 2D button, so both views fill the same form by the same rules.

const DESKTOP = window.matchMedia('(min-width: 1024px)');

function hasWebGL() {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
}

function init() {
    const toggle = document.querySelector('[data-view-toggle]');
    const dataElement = document.getElementById('loungeLayoutData');
    const plan2d = document.querySelector('[data-plan-2d]');
    const plan3d = document.querySelector('[data-plan-3d]');
    const container = document.querySelector('[data-lounge-viewer]');
    if (!toggle || !dataElement || !plan2d || !plan3d || !container || !hasWebGL()) return;

    const tableInput = document.getElementById('client_table_id');
    const buttonFor = (id) => plan2d.querySelector(`.js-client-table[data-id="${id}"]`);
    let viewer = null;
    let loading = false;

    toggle.classList.add('lg:inline-flex');

    function setPressed(view) {
        toggle.querySelectorAll('[data-view]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.view === view));
        });
    }

    function activeFloorId() {
        return document.querySelector('[data-floor-tab][aria-selected="true"]')?.dataset.floorTab ?? container.dataset.activeFloor;
    }

    function show2d() {
        viewer?.dispose();
        viewer = null;
        plan3d.classList.add('hidden');
        plan2d.classList.remove('hidden');
        setPressed('2d');
    }

    async function show3d() {
        if (viewer || loading) return;

        loading = true;
        plan2d.classList.add('hidden');
        plan3d.classList.remove('hidden');
        setPressed('3d');

        try {
            const { createLoungeViewer } = await import('./lounge3d/viewer.js');

            // Availability colors come from the 2D buttons, so both views always agree.
            const layout = JSON.parse(dataElement.textContent);
            for (const floor of layout.floors) {
                for (const table of floor.tables) table.status = buttonFor(table.id)?.dataset.status ?? 'free';
            }

            container.querySelector('[data-lounge-loading]')?.classList.add('hidden');
            viewer = createLoungeViewer(container, layout, {
                floorId: activeFloorId(),
                selectedId: tableInput?.value ? Number(tableInput.value) : null,
                canSelect: (id) => buttonFor(id)?.dataset.disabled === '0',
                labels: { day: container.dataset.labelDay, evening: container.dataset.labelEvening },
                onSelect: (id) => {
                    buttonFor(id)?.click();
                    viewer?.setSelected(id);
                },
            });
        } catch (error) {
            console.error('The 3D view could not start.', error);
            show2d();
        } finally {
            loading = false;
        }
    }

    toggle.addEventListener('click', (event) => {
        const view = event.target.closest('[data-view]')?.dataset.view;
        if (view === '3d') show3d();
        if (view === '2d') show2d();
    });

    const onFloorChange = (event) => viewer?.setFloor(event.detail.id);
    const onBreakpoint = () => { if (!DESKTOP.matches) show2d(); };

    document.addEventListener('floor:change', onFloorChange);
    DESKTOP.addEventListener('change', onBreakpoint);

    window.addEventListener('pagehide', () => {
        document.removeEventListener('floor:change', onFloorChange);
        DESKTOP.removeEventListener('change', onBreakpoint);
        viewer?.dispose();
        viewer = null;
    }, { once: true });
}

init();

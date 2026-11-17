// 2D / 3D switch for the pages that book tables: client reservations, staff reservations and
// staff new order.
//
// 2D stays the default and the only view on phones or without WebGL. three.js is downloaded the
// first time 3D is opened, and the choice is remembered in this browser. The 2D plan remains the
// source of truth: table status and availability are read from its buttons, and picking a table
// or a room in 3D clicks the matching 2D button, so both views fill the same form by the same rules.
//
// Page contract, on [data-view-toggle]:
//   data-table-selector  CSS selector of the 2D table buttons (each has data-id, data-name,
//                        data-capacity, data-status and data-disabled)
//   data-input           id of the hidden table_id input
// Rooms that can be booked whole have .js-room-book buttons (data-room-id, data-name, data-seats,
// data-taken). Texts come from [data-lounge-viewer] data-label-* attributes.

const WIDE_ENOUGH = window.matchMedia('(min-width: 768px)');
const STORAGE_KEY = 'perigee.planView';

function hasWebGL() {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
}

function remembered() {
    try {
        return window.localStorage.getItem(STORAGE_KEY);
    } catch {
        return null;
    }
}

function remember(view) {
    try {
        window.localStorage.setItem(STORAGE_KEY, view);
    } catch {
        // Private mode or blocked storage: the choice just isn't kept.
    }
}

function init() {
    const toggle = document.querySelector('[data-view-toggle]');
    const dataElement = document.getElementById('loungeLayoutData');
    const plan2d = document.querySelector('[data-plan-2d]');
    const plan3d = document.querySelector('[data-plan-3d]');
    const container = document.querySelector('[data-lounge-viewer]');
    if (!toggle || !dataElement || !plan2d || !plan3d || !container || !hasWebGL()) return;

    const tableSelector = toggle.dataset.tableSelector ?? '.js-client-table';
    const tableInput = document.getElementById(toggle.dataset.input ?? 'client_table_id');
    const text = container.dataset;
    const buttonFor = (id) => plan2d.querySelector(`${tableSelector}[data-id="${id}"]`);
    const roomButtonFor = (id) => document.querySelector(`.js-room-book[data-room-id="${id}"]`);
    let viewer = null;
    let loading = false;

    toggle.classList.add('md:inline-flex');

    function setPressed(view) {
        toggle.querySelectorAll('[data-view]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.view === view));
        });
    }

    function activeFloorId() {
        return document.querySelector('[data-floor-tab][aria-selected="true"]')?.dataset.floorTab ?? container.dataset.activeFloor;
    }

    function describeTable(id) {
        const button = buttonFor(id);
        if (!button) return null;
        const seats = (text.labelSeats ?? ':count seats').replace(':count', button.dataset.capacity);
        return [button.dataset.name, seats, button.dataset.statusLabel].filter(Boolean).join(' · ');
    }

    function describeRoom(id) {
        const button = roomButtonFor(id);
        if (!button) return null;
        return `${button.dataset.name} · ${button.dataset.taken === '1' ? (text.labelRoomTaken ?? '') : (text.labelRoomBook ?? '')}`;
    }

    function show2d(save = true) {
        viewer?.dispose();
        viewer = null;
        plan3d.classList.add('hidden');
        plan2d.classList.remove('hidden');
        setPressed('2d');
        if (save) remember('2d');
    }

    async function show3d() {
        if (viewer || loading) return;

        loading = true;
        plan2d.classList.add('hidden');
        plan3d.classList.remove('hidden');
        setPressed('3d');
        remember('3d');

        try {
            const { createLoungeViewer } = await import('./lounge3d/viewer.js');

            // Availability colours come from the 2D buttons, so both views always agree.
            const layout = JSON.parse(dataElement.textContent);
            for (const floor of layout.floors) {
                for (const table of floor.tables) table.status = buttonFor(table.id)?.dataset.status ?? 'free';
            }

            container.querySelector('[data-lounge-loading]')?.classList.add('hidden');
            viewer = createLoungeViewer(container, layout, {
                floorId: activeFloorId(),
                selectedId: tableInput?.value ? Number(tableInput.value) : null,
                canSelect: (id) => buttonFor(id)?.dataset.disabled === '0',
                onSelect: (id) => {
                    buttonFor(id)?.click();
                    viewer?.setSelected(id);
                },
                canSelectRoom: (id) => roomButtonFor(id)?.disabled === false,
                onSelectRoom: (id) => {
                    roomButtonFor(id)?.click();
                    viewer?.setSelected(null);
                },
                describeTable,
                describeRoom,
                onFloorChange: (id) => window.PerigeeFloorPlan?.showFloor(id),
                labels: { day: text.labelDay, evening: text.labelEvening, wholeFloor: text.labelWholeFloor, canvas: text.labelCanvas },
            });
        } catch (error) {
            console.error('The 3D view could not start.', error);
            show2d(false);
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
    const onWidthChange = () => { if (!WIDE_ENOUGH.matches) show2d(false); };

    document.addEventListener('floor:change', onFloorChange);
    WIDE_ENOUGH.addEventListener('change', onWidthChange);

    window.addEventListener('pagehide', () => {
        document.removeEventListener('floor:change', onFloorChange);
        WIDE_ENOUGH.removeEventListener('change', onWidthChange);
        viewer?.dispose();
        viewer = null;
    }, { once: true });

    if (remembered() === '3d' && WIDE_ENOUGH.matches) show3d();
}

init();

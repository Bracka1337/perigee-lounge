// Plan editor for the rooms of one floor (admin, floors and rooms page).
//
// Rooms are rectangles in percent of the plan. They can be added, dragged, resized by the
// corner handle, renamed, recolored and deleted. Nothing is stored until "Save rooms" posts
// the whole list as JSON. Every edit also fires a `rooms:change` event on document, so other
// scripts on the page can follow along.

const GRID = 0.5; // rooms snap to half a percent, which keeps walls aligned

const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
const snap = (value) => Math.round(value / GRID) * GRID;

function readDraft(draft, savedRooms) {
    if (typeof draft !== 'string' || draft === '') return null;

    try {
        const rooms = JSON.parse(draft);
        if (!Array.isArray(rooms)) return null;

        // The draft is what the admin tried to save. Table counts come from the saved rooms.
        return rooms.map((room) => ({
            ...room,
            id: room.id ?? null,
            tables: savedRooms.find((saved) => saved.id === room.id)?.tables ?? 0,
        }));
    } catch {
        return null;
    }
}

function initRoomEditor(root) {
    const data = JSON.parse(document.getElementById('roomEditorData').textContent);
    const plan = root.querySelector('[data-room-plan]');
    const form = root.querySelector('[data-room-form]');
    const panel = root.querySelector('[data-room-panel]');
    const emptyHint = root.querySelector('[data-room-empty]');
    const dirtyHint = root.querySelector('[data-room-dirty]');
    const deleteButton = root.querySelector('[data-room-delete]');
    const deleteHelp = root.querySelector('[data-room-delete-help]');
    const deleteForm = root.querySelector('[data-room-delete-form]');
    const tablesLabel = root.querySelector('[data-room-tables]');
    const kindSelect = root.querySelector('[data-room-kind]');
    const nameInputs = [...root.querySelectorAll('[data-room-name]')];
    const colorInputs = [...root.querySelectorAll('[data-room-color]')];

    const draft = readDraft(data.draft, data.rooms);
    let rooms = (draft ?? data.rooms).map((room, index) => ({ ...room, key: `room-${index}` }));
    let nextKey = rooms.length;
    let selectedKey = null;
    let deleteArmed = false;
    let drag = null;

    const elements = new Map();
    const selected = () => rooms.find((room) => room.key === selectedKey) ?? null;

    function paint(room) {
        let element = elements.get(room.key);

        if (!element) {
            element = document.createElement('div');
            element.className = 'floor-room';
            element.tabIndex = 0;
            element.dataset.key = room.key;
            element.innerHTML = '<span class="floor-room__label"></span><span class="room-editor__handle" data-handle></span>';
            plan.prepend(element);
            elements.set(room.key, element);
        }

        element.className = `floor-room floor-room--${room.kind}${room.key === selectedKey ? ' is-selected' : ''}`;
        element.style.left = `${room.x}%`;
        element.style.top = `${room.y}%`;
        element.style.width = `${room.width}%`;
        element.style.height = `${room.height}%`;
        // The selected room is on top, and small rooms sit above large ones so they stay reachable.
        element.style.zIndex = room.key === selectedKey ? '2000' : String(Math.round(1000 - (room.width * room.height) / 10));
        element.style.setProperty('--room', data.colors[room.color] ?? '#8A8FB0');
        element.setAttribute('aria-label', room.name.en);
        element.querySelector('.floor-room__label').textContent = room.name.en;
    }

    function changed() {
        dirtyHint.classList.remove('hidden');
        document.dispatchEvent(new CustomEvent('rooms:change', { detail: { rooms: payload() } }));
    }

    function payload() {
        return rooms.map(({ id, name, kind, color, x, y, width, height }) => ({ id, name, kind, color, x, y, width, height }));
    }

    function disarmDelete() {
        deleteArmed = false;
        deleteButton.textContent = data.labels.delete;
        deleteButton.classList.remove('border-red-500/80', 'bg-red-950/40', 'text-red-100');
    }

    function select(key) {
        selectedKey = key;
        disarmDelete();
        rooms.forEach(paint);

        const room = selected();
        panel.classList.toggle('hidden', room === null);
        emptyHint.classList.toggle('hidden', room !== null);
        if (room === null) return;

        nameInputs.forEach((input) => { input.value = room.name[input.dataset.roomName] ?? ''; });
        kindSelect.value = room.kind;
        colorInputs.forEach((input) => { input.checked = input.value === room.color; });

        const hasTables = room.tables > 0;
        tablesLabel.textContent = data.labels.tables.replace('__COUNT__', room.tables);
        deleteButton.disabled = hasTables;
        deleteHelp.classList.toggle('hidden', !hasTables);
    }

    function addRoom() {
        const used = new Set(rooms.map((room) => room.color));
        const color = Object.keys(data.colors).find((name) => !used.has(name)) ?? Object.keys(data.colors)[0];
        const room = {
            key: `room-${nextKey++}`,
            id: null,
            name: { en: data.labels.newRoom, lv: '', ru: '' },
            kind: 'seating',
            color,
            x: 40, y: 40, width: 20, height: 20,
            tables: 0,
        };

        rooms.push(room);
        select(room.key);
        changed();
        nameInputs[0]?.focus();
        nameInputs[0]?.select();
    }

    function removeSelected() {
        const room = selected();
        if (!room || room.tables > 0) return;

        if (!deleteArmed) {
            deleteArmed = true;
            deleteButton.textContent = data.labels.confirmDelete;
            deleteButton.classList.add('border-red-500/80', 'bg-red-950/40', 'text-red-100');
            return;
        }

        if (room.id !== null) {
            // A saved room is deleted on the server, which checks again that it has no tables.
            deleteForm.action = data.deleteUrl.replace(/0$/, String(room.id));
            deleteForm.submit();
            return;
        }

        elements.get(room.key)?.remove();
        elements.delete(room.key);
        rooms = rooms.filter((other) => other.key !== room.key);
        select(null);
        changed();
    }

    // Moves or resizes by a delta in percent, keeping the room inside the plan.
    function apply(room, mode, startRect, dx, dy) {
        if (mode === 'resize') {
            room.width = clamp(snap(startRect.width + dx), data.minSize, 100 - room.x);
            room.height = clamp(snap(startRect.height + dy), data.minSize, 100 - room.y);
        } else {
            room.x = clamp(snap(startRect.x + dx), 0, 100 - room.width);
            room.y = clamp(snap(startRect.y + dy), 0, 100 - room.height);
        }
        paint(room);
    }

    plan.addEventListener('pointerdown', (event) => {
        const element = event.target.closest('.floor-room');
        if (!element || event.button !== 0) return;

        select(element.dataset.key);
        const room = selected();

        drag = {
            room,
            mode: event.target.closest('[data-handle]') ? 'resize' : 'move',
            startX: event.clientX,
            startY: event.clientY,
            startRect: { x: room.x, y: room.y, width: room.width, height: room.height },
            moved: false,
        };
        element.setPointerCapture(event.pointerId);
        event.preventDefault();
        element.focus({ preventScroll: true });
    });

    plan.addEventListener('pointermove', (event) => {
        if (!drag) return;

        const bounds = plan.getBoundingClientRect();
        const dx = ((event.clientX - drag.startX) / bounds.width) * 100;
        const dy = ((event.clientY - drag.startY) / bounds.height) * 100;

        apply(drag.room, drag.mode, drag.startRect, dx, dy);
        drag.moved = true;
    });

    const endDrag = () => {
        if (drag?.moved) changed();
        drag = null;
    };
    plan.addEventListener('pointerup', endDrag);
    plan.addEventListener('pointercancel', endDrag);

    // Arrow keys move the focused room, with Shift they resize it.
    plan.addEventListener('keydown', (event) => {
        const steps = { ArrowLeft: [-GRID, 0], ArrowRight: [GRID, 0], ArrowUp: [0, -GRID], ArrowDown: [0, GRID] };
        const step = steps[event.key];
        const element = event.target.closest('.floor-room');
        if (!step || !element) return;

        if (selectedKey !== element.dataset.key) select(element.dataset.key);
        const room = selected();
        const rect = { x: room.x, y: room.y, width: room.width, height: room.height };

        apply(room, event.shiftKey ? 'resize' : 'move', rect, step[0], step[1]);
        changed();
        event.preventDefault();
    });

    plan.addEventListener('focusin', (event) => {
        const element = event.target.closest('.floor-room');
        if (element && selectedKey !== element.dataset.key && !drag) select(element.dataset.key);
    });

    nameInputs.forEach((input) => input.addEventListener('input', () => {
        const room = selected();
        if (!room) return;
        room.name[input.dataset.roomName] = input.value;
        paint(room);
        changed();
    }));

    kindSelect.addEventListener('change', () => {
        const room = selected();
        if (!room) return;
        room.kind = kindSelect.value;
        paint(room);
        changed();
    });

    colorInputs.forEach((input) => input.addEventListener('change', () => {
        const room = selected();
        if (!room || !input.checked) return;
        room.color = input.value;
        paint(room);
        changed();
    }));

    root.querySelector('[data-room-add]').addEventListener('click', addRoom);
    deleteButton.addEventListener('click', removeSelected);

    form.addEventListener('submit', () => {
        form.querySelector('input[name="rooms_payload"]').value = JSON.stringify(payload());
    });

    rooms.forEach(paint);
    if (draft) dirtyHint.classList.remove('hidden');
    document.dispatchEvent(new CustomEvent('rooms:change', { detail: { rooms: payload() } }));
}

// Two-step delete buttons: the first click reveals the red confirm button.
function initConfirmForms() {
    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        const ask = form.querySelector('[data-confirm-ask]');
        const yes = form.querySelector('[data-confirm-yes]');
        if (!ask || !yes) return;

        ask.addEventListener('click', () => {
            ask.classList.add('hidden');
            yes.classList.remove('hidden');
            yes.classList.add('inline-flex');
            yes.focus();
        });
    });
}

// Live 3D preview of the lounge with the rooms as they are being edited, unsaved changes
// included. Large screens with WebGL only; three.js is downloaded after the page is ready.
function initPreview() {
    const card = document.querySelector('[data-room-preview-card]');
    const container = document.querySelector('[data-room-preview]');
    const layoutElement = document.getElementById('loungeLayoutData');
    const editorElement = document.getElementById('roomEditorData');
    const desktop = window.matchMedia('(min-width: 1024px)');
    if (!card || !container || !layoutElement || !editorElement || !desktop.matches) return;

    try {
        const canvas = document.createElement('canvas');
        if (!(canvas.getContext('webgl2') ?? canvas.getContext('webgl'))) return;
    } catch {
        return;
    }

    const baseLayout = JSON.parse(layoutElement.textContent);
    const colors = JSON.parse(editorElement.textContent).colors;
    const floorId = container.dataset.floorId;
    let viewer = null;
    let latestRooms = null;
    let timer = 0;

    const layoutWith = (rooms) => ({
        floors: baseLayout.floors.map((floor) => (String(floor.id) !== floorId || !rooms ? floor : {
            ...floor,
            rooms: rooms.map((room) => ({ ...room, name: room.name.en, color: colors[room.color] ?? '#8A8FB0' })),
        })),
    });

    const onRoomsChange = (event) => {
        latestRooms = event.detail.rooms;
        clearTimeout(timer);
        timer = setTimeout(() => viewer?.rebuild(layoutWith(latestRooms)), 200);
    };
    document.addEventListener('rooms:change', onRoomsChange);

    card.classList.remove('hidden');

    import('./lounge3d/viewer.js')
        .then(({ createLoungeViewer }) => {
            viewer = createLoungeViewer(container, layoutWith(latestRooms), { floorId, canSelect: () => false });
        })
        .catch((error) => {
            console.error('The 3D preview could not start.', error);
            card.classList.add('hidden');
        });

    window.addEventListener('pagehide', () => {
        clearTimeout(timer);
        document.removeEventListener('rooms:change', onRoomsChange);
        viewer?.dispose();
    }, { once: true });
}

const editorRoot = document.getElementById('roomEditor');
if (editorRoot) {
    initPreview();
    initRoomEditor(editorRoot);
}
initConfirmForms();

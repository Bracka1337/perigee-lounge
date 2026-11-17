// Floor plan editor (admin): rooms and tables of one floor on a canvas measured in metres.
//
// Tools: Select (move, resize, rotate), Room (drag to draw), Table (click to place).
// The canvas pans by dragging empty space (or with Space / the middle button held) and zooms
// with the wheel. Rooms snap to a 0.25 m grid and to the edges of other rooms, so they meet
// without gaps. Overlapping rooms and tables outside every room are shown in red and block Save.
// Nothing is stored until Save posts the whole floor as JSON.

const SVG = 'http://www.w3.org/2000/svg';
const GRID = 0.25;
const TABLE_GRID = 0.05;
const MIN_ROOM = 1;
const SNAP_PX = 8;
const MIN_ZOOM = 4;
const MAX_ZOOM = 160;

const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
const round2 = (value) => Math.round(value * 100) / 100;
const snapTo = (value, step) => round2(Math.round(value / step) * step);

function element(name, attributes = {}, parent = null) {
    const node = document.createElementNS(SVG, name);
    for (const [key, value] of Object.entries(attributes)) node.setAttribute(key, value);
    parent?.append(node);
    return node;
}

function readJson(id) {
    const node = document.getElementById(id);
    return node ? JSON.parse(node.textContent) : null;
}

function initEditor(root) {
    const data = readJson('planEditorData');
    const text = readJson('planEditorText');
    const svg = root.querySelector('[data-plan-canvas]');
    const view3d = root.querySelector('[data-plan-3d]');
    const hint = root.querySelector('[data-tool-hint]');
    const problemsBox = root.querySelector('[data-problems]');
    const saveForm = root.querySelector('[data-save-form]');
    const saveButton = root.querySelector('[data-action="save"]');
    const saveState = root.querySelector('[data-save-state]');

    // --- State ------------------------------------------------------------------------------
    let nextKey = 0;
    const withKey = (item) => ({ ...item, key: `k${nextKey++}` });

    function fromDraft() {
        try {
            const draft = data.draft ? JSON.parse(data.draft) : null;
            return draft && Array.isArray(draft.rooms) && Array.isArray(draft.tables) ? draft : null;
        } catch {
            return null;
        }
    }

    const initial = fromDraft() ?? { rooms: data.rooms, tables: data.tables };
    let state = {
        rooms: initial.rooms.map((room) => withKey({ is_bookable_whole: false, ...room, name: { en: '', lv: '', ru: '', ...room.name } })),
        tables: initial.tables.map((table) => withKey({ ...table })),
    };

    const serialise = (current = state) => ({
        rooms: current.rooms.map(({ id, name, kind, color, is_bookable_whole, x, y, width, height }) => ({ id: id ?? null, name, kind, color, is_bookable_whole, x, y, width, height })),
        tables: current.tables.map(({ id, name, capacity, x, y, width, height, rotation, is_active }) => ({ id: id ?? null, name, capacity, x, y, width, height, rotation, is_active })),
    });

    const savedJson = JSON.stringify(serialise({ rooms: data.rooms.map((room) => ({ is_bookable_whole: false, ...room })), tables: data.tables }));
    const isDirty = () => JSON.stringify(serialise()) !== savedJson;

    let selected = null; // { type: 'room' | 'table', key }
    let tool = 'select';
    let undoStack = [];
    let redoStack = [];

    const snapshot = () => JSON.stringify({ rooms: state.rooms, tables: state.tables });
    let lastSnapshot = snapshot();

    // Records a finished change, so it can be undone.
    function commit() {
        const now = snapshot();
        if (now === lastSnapshot) return;
        undoStack.push(lastSnapshot);
        if (undoStack.length > 100) undoStack.shift();
        redoStack = [];
        lastSnapshot = now;
        changed();
    }

    function restore(json) {
        state = JSON.parse(json);
        lastSnapshot = json;
        if (selected && !find(selected)) selected = null;
        changed();
    }

    const find = (selection) => selection && (selection.type === 'room' ? state.rooms : state.tables).find((item) => item.key === selection.key);
    const roomAt = (x, y) => state.rooms.find((room) => x >= room.x && x <= room.x + room.width && y >= room.y && y <= room.y + room.height);
    const tablesIn = (room) => state.tables.filter((table) => roomAt(table.x, table.y) === room);

    // --- Camera -----------------------------------------------------------------------------
    const camera = { cx: data.frame.x + data.frame.width / 2, cy: data.frame.y + data.frame.depth / 2, zoom: 20 };

    function fit() {
        const items = [...state.rooms.map((room) => [room.x, room.y, room.x + room.width, room.y + room.height]),
            ...state.tables.map((table) => [table.x - table.width / 200, table.y - table.height / 200, table.x + table.width / 200, table.y + table.height / 200])];
        if (!items.length) {
            camera.zoom = 20;
            return render();
        }
        const minX = Math.min(...items.map((b) => b[0]));
        const minY = Math.min(...items.map((b) => b[1]));
        const maxX = Math.max(...items.map((b) => b[2]));
        const maxY = Math.max(...items.map((b) => b[3]));
        camera.cx = (minX + maxX) / 2;
        camera.cy = (minY + maxY) / 2;
        camera.zoom = clamp(Math.min((svg.clientWidth - 48) / (maxX - minX + 1), (svg.clientHeight - 48) / (maxY - minY + 1)), MIN_ZOOM, MAX_ZOOM);
        render();
    }

    function viewBox() {
        const width = svg.clientWidth / camera.zoom;
        const height = svg.clientHeight / camera.zoom;
        return { x: camera.cx - width / 2, y: camera.cy - height / 2, width, height };
    }

    function toWorld(event) {
        const bounds = svg.getBoundingClientRect();
        const box = viewBox();
        return { x: box.x + (event.clientX - bounds.left) / camera.zoom, y: box.y + (event.clientY - bounds.top) / camera.zoom };
    }

    function zoomAt(factor, event = null) {
        const before = event ? toWorld(event) : { x: camera.cx, y: camera.cy };
        camera.zoom = clamp(camera.zoom * factor, MIN_ZOOM, MAX_ZOOM);
        if (event) {
            const after = toWorld(event);
            camera.cx += before.x - after.x;
            camera.cy += before.y - after.y;
        }
        render();
    }

    // --- Problems ---------------------------------------------------------------------------
    function problems() {
        const list = [];
        const bad = new Set();
        state.rooms.forEach((a, i) => {
            state.rooms.slice(i + 1).forEach((b) => {
                const overlaps = a.x < b.x + b.width - 0.01 && b.x < a.x + a.width - 0.01 && a.y < b.y + b.height - 0.01 && b.y < a.y + a.height - 0.01;
                if (overlaps) {
                    bad.add(a.key).add(b.key);
                    list.push(text.overlap.replace('__A__', a.name.en || '?').replace('__B__', b.name.en || '?'));
                }
            });
        });
        state.tables.forEach((table) => {
            if (!roomAt(table.x, table.y)) {
                bad.add(table.key);
                list.push(text.outside.replace('__NAME__', table.name || '?'));
            }
        });
        return { list, bad };
    }

    // --- Drawing ----------------------------------------------------------------------------
    element('rect', { class: 'plan-grid-bg', x: -1000, y: -1000, width: 2000, height: 2000 }, svg);
    const layers = {};
    for (const name of ['grid', 'below', 'rooms', 'tables', 'overlay']) layers[name] = element('g', { 'data-layer': name }, svg);

    function drawGrid(box) {
        layers.grid.replaceChildren();
        const step = camera.zoom >= 60 ? GRID : camera.zoom >= 14 ? 1 : 5;
        const startX = Math.floor(box.x / step) * step;
        const startY = Math.floor(box.y / step) * step;
        for (let x = startX; x <= box.x + box.width; x += step) {
            const major = Math.abs(x / 5 - Math.round(x / 5)) < 1e-6;
            element('line', { x1: x, y1: box.y, x2: x, y2: box.y + box.height, class: major ? 'plan-grid-major' : 'plan-grid-line' }, layers.grid);
        }
        for (let y = startY; y <= box.y + box.height; y += step) {
            const major = Math.abs(y / 5 - Math.round(y / 5)) < 1e-6;
            element('line', { x1: box.x, y1: y, x2: box.x + box.width, y2: y, class: major ? 'plan-grid-major' : 'plan-grid-line' }, layers.grid);
        }
    }

    let guides = [];
    let draft = null; // a room being drawn

    function render() {
        const box = viewBox();
        svg.setAttribute('viewBox', `${box.x} ${box.y} ${box.width} ${box.height}`);
        drawGrid(box);

        const px = 1 / camera.zoom; // one screen pixel in metres
        const { bad } = problems();

        layers.below.replaceChildren();
        for (const room of data.below) {
            element('rect', { x: room.x, y: room.y, width: room.width, height: room.height, class: 'plan-below' }, layers.below);
        }

        layers.rooms.replaceChildren();
        // Large rooms first, so small rooms stay on top and reachable.
        for (const room of [...state.rooms].sort((a, b) => b.width * b.height - a.width * a.height)) {
            const color = data.colors[room.color]?.hex ?? '#8A8FB0';
            const isSelected = selected?.type === 'room' && selected.key === room.key;
            const group = element('g', { 'data-room': room.key, class: `plan-room${isSelected ? ' is-selected' : ''}${bad.has(room.key) ? ' is-invalid' : ''}${room.kind === 'corridor' ? ' is-corridor' : ''}` }, layers.rooms);
            group.style.setProperty('--room', color);
            element('rect', { x: room.x, y: room.y, width: room.width, height: room.height }, group);
            // A label is shown only where it fits, so it never runs into the next room.
            const name = (room.name.en || '…').toUpperCase() + (room.is_bookable_whole ? '  ◆' : '');
            if (room.width * camera.zoom > name.length * 7.5 + 12 && room.height * camera.zoom > 22) {
                const label = element('text', { x: room.x + 6 * px, y: room.y + 16 * px, 'font-size': 11 * px }, group);
                label.textContent = name;
            }
            if (isSelected && room.width * camera.zoom > 70 && room.height * camera.zoom > 40) {
                const size = element('text', { x: room.x + room.width - 6 * px, y: room.y + room.height - 6 * px, 'font-size': 10 * px, 'text-anchor': 'end', class: 'plan-size' }, group);
                size.textContent = `${round2(room.width)} × ${round2(room.height)} m`;
            }
        }

        layers.tables.replaceChildren();
        for (const table of state.tables) {
            const isSelected = selected?.type === 'table' && selected.key === table.key;
            const group = element('g', {
                'data-table': table.key,
                transform: `translate(${table.x} ${table.y}) rotate(${table.rotation})`,
                class: `plan-table${isSelected ? ' is-selected' : ''}${bad.has(table.key) ? ' is-invalid' : ''}${table.is_active ? '' : ' is-inactive'}`,
            }, layers.tables);
            const w = table.width / 100;
            const h = table.height / 100;
            element('rect', { x: -w / 2, y: -h / 2, width: w, height: h, rx: Math.min(w, h) * 0.18 }, group);
            if (Math.min(w, h) * camera.zoom > 18) {
                const label = element('text', { x: 0, y: 0, 'font-size': 11 * px, 'text-anchor': 'middle', 'dominant-baseline': 'central', transform: `rotate(${-table.rotation})` }, group);
                label.textContent = table.name;
            }
        }

        drawOverlay(px);
    }

    function drawOverlay(px) {
        layers.overlay.replaceChildren();
        const size = 10 * px;

        for (const guide of guides) {
            element('line', { ...guide, class: 'plan-guide' }, layers.overlay);
        }

        if (draft) {
            element('rect', { x: draft.x, y: draft.y, width: draft.width, height: draft.height, class: 'plan-draft' }, layers.overlay);
            const label = element('text', { x: draft.x + draft.width / 2, y: draft.y + draft.height / 2, 'font-size': 12 * px, 'text-anchor': 'middle', class: 'plan-size' }, layers.overlay);
            label.textContent = `${round2(draft.width)} × ${round2(draft.height)} m`;
        }

        const item = find(selected);
        if (!item) return;

        if (selected.type === 'room') {
            const handles = { nw: [0, 0], n: [0.5, 0], ne: [1, 0], e: [1, 0.5], se: [1, 1], s: [0.5, 1], sw: [0, 1], w: [0, 0.5] };
            for (const [name, [fx, fy]] of Object.entries(handles)) {
                element('rect', {
                    x: item.x + item.width * fx - size / 2, y: item.y + item.height * fy - size / 2, width: size, height: size,
                    class: `plan-handle plan-handle--${name}`, 'data-handle': name,
                }, layers.overlay);
            }
        } else {
            const group = element('g', { transform: `translate(${item.x} ${item.y}) rotate(${item.rotation})` }, layers.overlay);
            const w = item.width / 100;
            const h = item.height / 100;
            element('line', { x1: 0, y1: -h / 2, x2: 0, y2: -h / 2 - 18 * px, class: 'plan-guide' }, group);
            element('circle', { cx: 0, cy: -h / 2 - 18 * px, r: size / 1.6, class: 'plan-handle plan-handle--rotate', 'data-handle': 'rotate' }, group);
            element('rect', { x: w / 2 - size / 2, y: h / 2 - size / 2, width: size, height: size, class: 'plan-handle plan-handle--se', 'data-handle': 'table-size' }, group);
        }
    }

    // --- Snapping ---------------------------------------------------------------------------
    // Moves a value onto the nearest edge of another room, or else onto the grid.
    function snapEdges(values, axis, ignore) {
        const threshold = SNAP_PX / camera.zoom;
        const edges = state.rooms.filter((room) => room !== ignore)
            .flatMap((room) => axis === 'x' ? [room.x, room.x + room.width] : [room.y, room.y + room.height]);

        let best = null;
        for (const value of values) {
            for (const edge of edges) {
                const delta = edge - value;
                if (Math.abs(delta) <= threshold && (best === null || Math.abs(delta) < Math.abs(best.delta))) best = { delta, edge };
            }
        }
        return best;
    }

    function guideFor(axis, at) {
        const box = viewBox();
        return axis === 'x'
            ? { x1: at, y1: box.y, x2: at, y2: box.y + box.height }
            : { x1: box.x, y1: at, x2: box.x + box.width, y2: at };
    }

    // --- Pointer interaction ------------------------------------------------------------------
    let drag = null;
    let spaceHeld = false;

    svg.addEventListener('pointerdown', (event) => {
        if (event.button === 2) return;
        svg.setPointerCapture(event.pointerId);
        const point = toWorld(event);
        const handle = event.target.closest('[data-handle]')?.dataset.handle;
        const tableKey = event.target.closest('[data-table]')?.dataset.table;
        const roomKey = event.target.closest('[data-room]')?.dataset.room;

        if (event.button === 1 || spaceHeld) {
            drag = { type: 'pan', start: { x: event.clientX, y: event.clientY }, camera: { ...camera } };
            return;
        }

        if (tool === 'room') {
            const start = { x: snapTo(point.x, GRID), y: snapTo(point.y, GRID) };
            drag = { type: 'draw', start };
            draft = { x: start.x, y: start.y, width: 0, height: 0 };
            return;
        }

        if (tool === 'table') {
            drag = { type: 'place', point };
            return;
        }

        if (handle) {
            const item = find(selected);
            drag = { type: handle === 'rotate' ? 'rotate' : handle === 'table-size' ? 'table-size' : 'resize', handle, item, origin: { ...item }, point };
            return;
        }

        if (tableKey) {
            select({ type: 'table', key: tableKey });
            const item = find(selected);
            drag = { type: 'move-table', item, origin: { ...item }, point, moved: false };
            return;
        }

        if (roomKey) {
            select({ type: 'room', key: roomKey });
            const item = find(selected);
            // The tables inside a room move with it.
            drag = { type: 'move-room', item, origin: { ...item }, point, moved: false,
                tables: tablesIn(item).map((table) => ({ table, x: table.x, y: table.y })) };
            return;
        }

        select(null);
        drag = { type: 'pan', start: { x: event.clientX, y: event.clientY }, camera: { ...camera } };
    });

    svg.addEventListener('pointermove', (event) => {
        if (!drag) {
            svg.style.cursor = spaceHeld ? 'grab' : tool === 'select' ? '' : 'crosshair';
            return;
        }
        const point = toWorld(event);
        guides = [];

        if (drag.type === 'pan') {
            camera.cx = drag.camera.cx - (event.clientX - drag.start.x) / camera.zoom;
            camera.cy = drag.camera.cy - (event.clientY - drag.start.y) / camera.zoom;
            svg.style.cursor = 'grabbing';
        }

        if (drag.type === 'draw') {
            let x2 = snapTo(point.x, GRID);
            let y2 = snapTo(point.y, GRID);
            const sx = snapEdges([point.x], 'x', null);
            const sy = snapEdges([point.y], 'y', null);
            if (sx) { x2 = sx.edge; guides.push(guideFor('x', sx.edge)); }
            if (sy) { y2 = sy.edge; guides.push(guideFor('y', sy.edge)); }
            draft = { x: Math.min(drag.start.x, x2), y: Math.min(drag.start.y, y2), width: Math.abs(x2 - drag.start.x), height: Math.abs(y2 - drag.start.y) };
        }

        if (drag.type === 'move-room') {
            const { item, origin } = drag;
            let x = snapTo(origin.x + point.x - drag.point.x, GRID);
            let y = snapTo(origin.y + point.y - drag.point.y, GRID);
            const sx = snapEdges([origin.x + point.x - drag.point.x, origin.x + point.x - drag.point.x + item.width], 'x', item);
            const sy = snapEdges([origin.y + point.y - drag.point.y, origin.y + point.y - drag.point.y + item.height], 'y', item);
            if (sx) { x = round2(origin.x + point.x - drag.point.x + sx.delta); guides.push(guideFor('x', sx.edge)); }
            if (sy) { y = round2(origin.y + point.y - drag.point.y + sy.delta); guides.push(guideFor('y', sy.edge)); }
            item.x = x;
            item.y = y;
            for (const entry of drag.tables) {
                entry.table.x = round2(entry.x + x - origin.x);
                entry.table.y = round2(entry.y + y - origin.y);
            }
            drag.moved = true;
        }

        if (drag.type === 'resize') {
            const { item, origin, handle } = drag;
            let left = origin.x;
            let top = origin.y;
            let right = origin.x + origin.width;
            let bottom = origin.y + origin.height;
            const edge = (value, axis) => {
                const snapped = snapEdges([value], axis, item);
                if (snapped) {
                    guides.push(guideFor(axis, snapped.edge));
                    return snapped.edge;
                }
                return snapTo(value, GRID);
            };
            if (handle.includes('w')) left = Math.min(edge(point.x, 'x'), right - MIN_ROOM);
            if (handle.includes('e')) right = Math.max(edge(point.x, 'x'), left + MIN_ROOM);
            if (handle.includes('n')) top = Math.min(edge(point.y, 'y'), bottom - MIN_ROOM);
            if (handle.includes('s')) bottom = Math.max(edge(point.y, 'y'), top + MIN_ROOM);
            Object.assign(item, { x: round2(left), y: round2(top), width: round2(right - left), height: round2(bottom - top) });
        }

        if (drag.type === 'move-table') {
            drag.item.x = snapTo(drag.origin.x + point.x - drag.point.x, TABLE_GRID);
            drag.item.y = snapTo(drag.origin.y + point.y - drag.point.y, TABLE_GRID);
            drag.moved = true;
        }

        if (drag.type === 'rotate') {
            const angle = (Math.atan2(point.y - drag.item.y, point.x - drag.item.x) * 180) / Math.PI + 90;
            drag.item.rotation = ((Math.round(angle / 15) * 15) % 360 + 360) % 360;
        }

        if (drag.type === 'table-size') {
            // Distance from the centre, measured along the table's own axes.
            const radians = (-drag.item.rotation * Math.PI) / 180;
            const dx = point.x - drag.item.x;
            const dy = point.y - drag.item.y;
            const localX = dx * Math.cos(radians) - dy * Math.sin(radians);
            const localY = dx * Math.sin(radians) + dy * Math.cos(radians);
            drag.item.width = clamp(Math.round((Math.abs(localX) * 200) / 10) * 10, 40, 2000);
            drag.item.height = clamp(Math.round((Math.abs(localY) * 200) / 10) * 10, 40, 2000);
        }

        render();
        if (selected) fillInspector();
    });

    svg.addEventListener('pointerup', (event) => {
        if (!drag) return;
        const finished = drag;
        drag = null;
        guides = [];
        svg.style.cursor = '';

        if (finished.type === 'draw') {
            const room = draft;
            draft = null;
            if (room.width >= MIN_ROOM && room.height >= MIN_ROOM) addRoom(room);
            setTool('select');
        } else if (finished.type === 'place') {
            addTable(toWorld(event));
            setTool('select');
        } else if (finished.type !== 'pan') {
            commit();
        }
        render();
    });

    svg.addEventListener('wheel', (event) => {
        event.preventDefault();
        zoomAt(Math.exp(-event.deltaY * 0.0015), event);
    }, { passive: false });

    // --- Adding and removing ------------------------------------------------------------------
    function addRoom(rect) {
        const used = new Set(state.rooms.map((room) => room.color));
        const color = Object.keys(data.colors).find((name) => !used.has(name) && name !== 'slate') ?? 'violet';
        const room = withKey({ id: null, name: { en: text.newRoom, lv: '', ru: '' }, kind: 'seating', color, is_bookable_whole: false, ...rect });
        state.rooms.push(room);
        selected = { type: 'room', key: room.key };
        commit();
        fillInspector();
        root.querySelector('[data-room-field="name.en"]')?.select();
    }

    function nextTableName() {
        const names = new Set(state.tables.map((table) => table.name.toLowerCase()));
        for (let i = 1; ; i++) if (!names.has(`a${i}`)) return `A${i}`;
    }

    function addTable(point) {
        const table = withKey({ id: null, name: nextTableName(), capacity: 4, x: snapTo(point.x, TABLE_GRID), y: snapTo(point.y, TABLE_GRID), width: 200, height: 200, rotation: 0, is_active: true });
        state.tables.push(table);
        selected = { type: 'table', key: table.key };
        commit();
        fillInspector();
    }

    function removeSelected() {
        const item = find(selected);
        if (!item) return;
        if (selected.type === 'room') {
            if (tablesIn(item).length) return;
            state.rooms = state.rooms.filter((room) => room !== item);
        } else {
            // A saved table is deleted on the server, after confirming (its bookings go too).
            if (item.id) {
                root.querySelector('[data-action="delete-table"]')?.focus();
                return;
            }
            state.tables = state.tables.filter((table) => table !== item);
        }
        selected = null;
        commit();
    }

    // --- Tools, selection and inspector -------------------------------------------------------
    function setTool(name) {
        tool = name;
        root.querySelectorAll('[data-tool]').forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.tool === name)));
        hint.textContent = text.hints[name];
        svg.style.cursor = name === 'select' ? '' : 'crosshair';
    }

    function select(selection) {
        selected = selection;
        resetDeleteTable();
        fillInspector();
        render();
    }

    const panels = Object.fromEntries([...root.querySelectorAll('[data-panel]')].map((panel) => [panel.dataset.panel, panel]));

    // Builds the static parts of the inspector once.
    const kindSelect = root.querySelector('[data-room-field="kind"]');
    for (const [value, label] of Object.entries(data.kinds)) kindSelect.append(new Option(label, value));
    const swatches = root.querySelector('[data-color-swatches]');
    for (const [value, color] of Object.entries(data.colors)) {
        const label = document.createElement('label');
        label.className = 'plan-swatch';
        label.title = color.label;
        label.innerHTML = '<input type="radio" name="room_color" class="sr-only"><span></span><span class="sr-only"></span>';
        label.querySelector('input').value = value;
        label.querySelector('span').style.setProperty('--room', color.hex);
        label.querySelector('.sr-only:last-child').textContent = color.label;
        swatches.append(label);
    }

    function listButton(label, onClick) {
        const item = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'min-h-9 rounded-full border border-line px-3 text-xs text-ink-soft hover:border-line-strong hover:text-ink';
        button.textContent = label;
        button.addEventListener('click', onClick);
        item.append(button);
        return item;
    }

    function focusOn(x, y) {
        camera.cx = x;
        camera.cy = y;
        render();
    }

    function fillInspector() {
        const item = find(selected);
        for (const [name, panel] of Object.entries(panels)) panel.classList.toggle('hidden', name !== (item ? selected.type : 'none'));

        if (!item) {
            root.querySelector('[data-floor-summary]').textContent = text.summary.replace('__ROOMS__', state.rooms.length).replace('__TABLES__', state.tables.length);
            const rooms = root.querySelector('[data-room-list]');
            const tables = root.querySelector('[data-table-list]');
            rooms.replaceChildren(...state.rooms.map((room) => listButton(room.name.en || '…', () => {
                select({ type: 'room', key: room.key });
                focusOn(room.x + room.width / 2, room.y + room.height / 2);
            })));
            tables.replaceChildren(...[...state.tables].sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true })).map((table) => listButton(table.name, () => {
                select({ type: 'table', key: table.key });
                focusOn(table.x, table.y);
            })));
            return;
        }

        const fields = root.querySelectorAll(selected.type === 'room' ? '[data-room-field]' : '[data-table-field]');
        for (const field of fields) {
            // Never overwrite text the admin is typing; checkboxes always show the current value.
            if (field === document.activeElement && field.type !== 'checkbox') continue;
            const path = (field.dataset.roomField ?? field.dataset.tableField).split('.');
            const value = path.reduce((current, key) => current?.[key], item);
            if (field.type === 'checkbox') field.checked = Boolean(value);
            else field.value = value ?? '';
        }

        if (selected.type === 'room') {
            swatches.querySelectorAll('input').forEach((input) => { input.checked = input.value === item.color; });
            const count = tablesIn(item).length;
            root.querySelector('[data-room-tables]').textContent = text.tables.replace('__COUNT__', count);
            root.querySelector('[data-action="delete-room"]').disabled = count > 0;
            root.querySelector('[data-room-delete-help]').classList.toggle('hidden', count === 0);
        } else {
            const room = roomAt(item.x, item.y);
            root.querySelector('[data-table-room]').textContent = room ? text.inRoom.replace('__ROOM__', room.name.en) : text.outside.replace('__NAME__', item.name);
            fillTableForms(item);
        }
    }

    function fillTableForms(item) {
        const moveForm = root.querySelector('[data-move-form]');
        const deleteForm = root.querySelector('[data-delete-table-form]');
        const saved = Boolean(item.id);
        moveForm.classList.toggle('hidden', !saved || !data.otherFloors.length);
        deleteForm.classList.toggle('hidden', !saved);
        if (!saved) return;

        moveForm.action = data.urls.tableUpdate.replace('__ID__', item.id);
        deleteForm.action = data.urls.tableDestroy.replace('__ID__', item.id);
        moveForm.querySelector('[name="name"]').value = item.name;
        moveForm.querySelector('[name="capacity"]').value = item.capacity;
        moveForm.querySelector('[name="is_active"]').value = item.is_active ? '1' : '0';

        const select = moveForm.querySelector('select');
        if (!select.options.length) {
            select.append(new Option(text.chooseRoom, ''));
            for (const floor of data.otherFloors) {
                const group = document.createElement('optgroup');
                group.label = floor.name;
                floor.rooms.forEach((room) => group.append(new Option(room.name, room.id)));
                select.append(group);
            }
        }

        // Moving or deleting reloads the page, so pending edits have to be saved first.
        const dirty = isDirty();
        moveForm.querySelector('button').disabled = dirty;
        moveForm.querySelector('[data-move-help]').classList.toggle('hidden', !dirty);
        root.querySelector('[data-action="delete-table"]').disabled = dirty;
    }

    function resetDeleteTable() {
        root.querySelector('[data-action="delete-table"]')?.classList.remove('hidden');
        root.querySelector('[data-delete-table-yes]')?.classList.add('hidden');
    }

    // Inspector edits: live while typing, recorded for undo when the field is left.
    root.querySelectorAll('[data-room-field], [data-table-field]').forEach((field) => {
        const apply = () => {
            const item = find(selected);
            if (!item) return;
            const path = (field.dataset.roomField ?? field.dataset.tableField).split('.');
            let value = field.type === 'checkbox' ? field.checked : field.value;
            if (field.type === 'number') {
                value = Number(value);
                if (!Number.isFinite(value)) return;
                if (['width', 'height'].includes(path[0]) && selected.type === 'room') value = Math.max(MIN_ROOM, value);
            }
            const target = path.slice(0, -1).reduce((current, key) => current[key], item);
            target[path.at(-1)] = value;
            render();
        };
        field.addEventListener('input', apply);
        field.addEventListener('change', () => {
            apply();
            commit();
            fillInspector();
        });
    });

    swatches.addEventListener('change', (event) => {
        const item = find(selected);
        if (!item || !event.target.checked) return;
        item.color = event.target.value;
        commit();
        render();
    });

    root.querySelectorAll('[data-tool]').forEach((button) => button.addEventListener('click', () => setTool(button.dataset.tool)));
    root.querySelectorAll('[data-action="deselect"]').forEach((button) => button.addEventListener('click', () => select(null)));
    root.querySelector('[data-action="delete-room"]').addEventListener('click', removeSelected);
    root.querySelector('[data-action="delete-table"]').addEventListener('click', (event) => {
        event.currentTarget.classList.add('hidden');
        const yes = root.querySelector('[data-delete-table-yes]');
        yes.classList.remove('hidden');
        yes.classList.add('inline-flex');
        yes.focus();
    });
    root.querySelector('[data-action="undo"]').addEventListener('click', undo);
    root.querySelector('[data-action="redo"]').addEventListener('click', redo);
    root.querySelector('[data-action="zoom-in"]').addEventListener('click', () => zoomAt(1.4));
    root.querySelector('[data-action="zoom-out"]').addEventListener('click', () => zoomAt(1 / 1.4));
    root.querySelector('[data-action="fit"]').addEventListener('click', fit);

    function undo() {
        if (undoStack.length) {
            redoStack.push(snapshot());
            restore(undoStack.pop());
        }
    }

    function redo() {
        if (redoStack.length) {
            undoStack.push(snapshot());
            restore(redoStack.pop());
        }
    }

    // --- Saving -----------------------------------------------------------------------------
    let submitting = false;

    function changed() {
        const { list } = problems();
        const dirty = isDirty();
        problemsBox.classList.toggle('hidden', list.length === 0);
        problemsBox.textContent = list.slice(0, 4).join(' ');
        saveButton.disabled = !dirty || list.length > 0;
        saveState.textContent = dirty ? text.unsaved : text.saved;
        root.querySelector('[data-action="undo"]').disabled = undoStack.length === 0;
        root.querySelector('[data-action="redo"]').disabled = redoStack.length === 0;
        fillInspector();
        render();
        if (viewer) refresh3d();
    }

    saveForm.addEventListener('submit', (event) => {
        if (saveButton.disabled) {
            event.preventDefault();
            return;
        }
        submitting = true;
        saveForm.querySelector('[name="plan_payload"]').value = JSON.stringify(serialise());
    });

    root.querySelectorAll('[data-move-form], [data-delete-table-form]').forEach((form) => form.addEventListener('submit', () => { submitting = true; }));

    const onBeforeUnload = (event) => {
        if (!submitting && isDirty()) {
            event.preventDefault();
            event.returnValue = text.leave;
        }
    };
    window.addEventListener('beforeunload', onBeforeUnload);

    // --- Keyboard ---------------------------------------------------------------------------
    const onKeyDown = (event) => {
        // Checkboxes and colour swatches keep the shortcuts; text fields and selects do not.
        const typing = event.target.closest('input:not([type=checkbox]):not([type=radio]), select, textarea');
        const mod = event.ctrlKey || event.metaKey;

        if (mod && event.key.toLowerCase() === 's') {
            event.preventDefault();
            saveForm.requestSubmit();
            return;
        }
        if (typing) return;

        if (mod && event.key.toLowerCase() === 'z') {
            event.preventDefault();
            event.shiftKey ? redo() : undo();
        } else if (mod && event.key.toLowerCase() === 'y') {
            event.preventDefault();
            redo();
        } else if (event.key === ' ') {
            spaceHeld = true;
            svg.style.cursor = 'grab';
            event.preventDefault();
        } else if (event.key === 'Escape') {
            draft = null;
            drag = null;
            setTool('select');
            select(null);
        } else if (event.key === 'Delete' || event.key === 'Backspace') {
            removeSelected();
        } else if (['v', 'r', 't'].includes(event.key.toLowerCase()) && !mod) {
            setTool({ v: 'select', r: 'room', t: 'table' }[event.key.toLowerCase()]);
        } else if (event.key.startsWith('Arrow') && selected) {
            const item = find(selected);
            const step = event.shiftKey ? 1 : (selected.type === 'room' ? GRID : TABLE_GRID);
            const [dx, dy] = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }[event.key];
            const moving = selected.type === 'room' ? [item, ...tablesIn(item)] : [item];
            moving.forEach((thing) => { thing.x = round2(thing.x + dx); thing.y = round2(thing.y + dy); });
            commit();
            event.preventDefault();
        }
    };
    const onKeyUp = (event) => {
        if (event.key === ' ') {
            spaceHeld = false;
            svg.style.cursor = '';
        }
    };
    window.addEventListener('keydown', onKeyDown);
    window.addEventListener('keyup', onKeyUp);

    // --- 3D view ----------------------------------------------------------------------------
    let viewer = null;
    let refreshTimer = 0;
    const base = readJson('loungeLayoutData');

    function layout3d() {
        const rooms = state.rooms.map((room, index) => ({
            id: room.id ?? -(index + 1), name: room.name.en, kind: room.kind, color: data.colors[room.color]?.hex ?? '#8A8FB0',
            bookableWhole: room.is_bookable_whole, x: room.x, y: room.y, width: room.width, height: room.height,
        }));
        const tables = state.tables.map((table, index) => {
            const room = roomAt(table.x, table.y);
            return {
                id: table.id ?? -(index + 1), name: table.name, capacity: table.capacity, roomId: room ? rooms[state.rooms.indexOf(room)].id : null,
                x: table.x, y: table.y, width: table.width, height: table.height, rotation: table.rotation, active: table.is_active, status: 'free',
            };
        });
        const floors = base.floors.map((floor) => (floor.id === data.floor.id ? { ...floor, rooms, tables } : floor));
        const all = floors.flatMap((floor) => floor.rooms);
        const minX = Math.min(...all.map((room) => room.x), base.frame.x);
        const minY = Math.min(...all.map((room) => room.y), base.frame.y);
        const maxX = Math.max(...all.map((room) => room.x + room.width), base.frame.x + base.frame.width);
        const maxY = Math.max(...all.map((room) => room.y + room.height), base.frame.y + base.frame.depth);
        return { frame: { x: minX, y: minY, width: maxX - minX, depth: maxY - minY }, floors };
    }

    function refresh3d() {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(() => viewer?.rebuild(layout3d()), 250);
    }

    async function setView(view) {
        root.querySelectorAll('[data-view]').forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.view === view)));
        const is3d = view === '3d';
        svg.classList.toggle('hidden', is3d);
        view3d.classList.toggle('hidden', !is3d);
        root.querySelectorAll('[data-tool], [data-zoom-controls] button').forEach((button) => { button.disabled = is3d; });
        hint.textContent = is3d ? text.hints.view3d : text.hints[tool];

        if (!is3d) {
            viewer?.dispose();
            viewer = null;
            render();
            return;
        }
        try {
            const { createLoungeViewer } = await import('./lounge3d/viewer.js');
            viewer?.dispose();
            viewer = createLoungeViewer(view3d, layout3d(), { floorId: data.floor.id, canSelect: () => false });
        } catch (error) {
            console.error('The 3D view could not start.', error);
            setView('2d');
        }
    }
    root.querySelectorAll('[data-view]').forEach((button) => button.addEventListener('click', () => setView(button.dataset.view)));

    // --- Start ------------------------------------------------------------------------------
    const resizeObserver = new ResizeObserver(() => render());
    resizeObserver.observe(svg);

    window.addEventListener('pagehide', () => {
        resizeObserver.disconnect();
        window.removeEventListener('keydown', onKeyDown);
        window.removeEventListener('keyup', onKeyUp);
        window.removeEventListener('beforeunload', onBeforeUnload);
        clearTimeout(refreshTimer);
        viewer?.dispose();
    }, { once: true });

    setTool('select');
    fit();
    changed();
}

// Two-step delete buttons: the first click reveals the red confirm button.
function initConfirmForms() {
    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        const ask = form.querySelector('[data-confirm-ask]');
        const yes = form.querySelector('[data-confirm-yes]');
        ask?.addEventListener('click', () => {
            ask.classList.add('hidden');
            yes.classList.remove('hidden');
            yes.classList.add('inline-flex');
            yes.focus();
        });
    });
}

const editorRoot = document.getElementById('planEditor');
if (editorRoot) initEditor(editorRoot);
initConfirmForms();

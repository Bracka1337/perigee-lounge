// Builds the lounge from the same floors, rooms and tables the 2D plan draws.
//
// Layout data: { floors: [{ id, name, rooms: [{ id, name, kind, color, x, y, width, height }],
//   tables: [{ id, name, capacity, roomId, x, y, width, height, rotation, active }] }] }
// Room rectangles are percentages of the plan. Table x/y are centre percentages and
// width/height are pixels of the 1200 px wide reference plan, exactly as in the database.
import {
    AmbientLight,
    BoxGeometry,
    CanvasTexture,
    DirectionalLight,
    Group,
    HemisphereLight,
    Mesh,
    MeshBasicMaterial,
    PlaneGeometry,
    PointLight,
    Sprite,
    SpriteMaterial,
    SRGBColorSpace,
} from 'three';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';
import { addBar, addEntrance, addLamp, addPlant, addScreen, addStairs, addTableSet } from './furniture.js';
import { Batch, COLORS, STOREY, frameCentre, mix, seededRandom } from './kit.js';

const WALL_HEIGHT = 0.9;
const WALL_THICKNESS = 0.12;

export const STATUS_COLORS = {
    free: COLORS.free,
    pending: COLORS.pending,
    active: COLORS.active,
    inactive: COLORS.inactive,
    selected: COLORS.selected,
};

// Lights shared by the viewer and the render studio: cool fill from above, warm amber
// and violet pools at floor level.
export function addLoungeLights(scene, y = 0) {
    const lights = [
        new HemisphereLight('#8d93d6', '#120e1a', 0.55),
        new AmbientLight('#ffffff', 0.1),
    ];

    const sun = new DirectionalLight('#ffe6c4', 0.85);
    sun.position.set(-12, 30, 18);
    lights.push(sun);

    for (const [color, x, z] of [[COLORS.amber, 10, -9], [COLORS.violet, -4, 5], [COLORS.amber, -12, -8], [COLORS.violet, 11, 8]]) {
        const lamp = new PointLight(color, 95, 26, 1.5);
        lamp.position.set(x, y + 4.2, z);
        lamp.userData.followsFloor = true;
        lights.push(lamp);
    }

    scene.add(...lights);
    return lights;
}

function roomBox(room, centre) {
    const x0 = room.x - centre.x;
    const z0 = room.y - centre.z;
    const width = room.width;
    const depth = room.height;

    return { x0, z0, x1: x0 + width, z1: z0 + depth, cx: x0 + width / 2, cz: z0 + depth / 2, width, depth };
}

// How long a stretch of one side of a room touches another room (0 when they do not touch).
function sharedEdge(room, other, side) {
    const near = (a, b) => Math.abs(a - b) < 0.05;
    const overlap = (a0, a1, b0, b1) => Math.max(0, Math.min(a1, b1) - Math.max(a0, b0));
    const [rx1, ry1, ox1, oy1] = [room.x + room.width, room.y + room.height, other.x + other.width, other.y + other.height];

    if (side === 'top' && near(room.y, oy1)) return overlap(room.x, rx1, other.x, ox1);
    if (side === 'bottom' && near(ry1, other.y)) return overlap(room.x, rx1, other.x, ox1);
    if (side === 'left' && near(room.x, ox1)) return overlap(room.y, ry1, other.y, oy1);
    if (side === 'right' && near(rx1, other.x)) return overlap(room.y, ry1, other.y, oy1);
    return 0;
}

// The doorway goes in the wall that borders a corridor the most. A room without a corridor
// next to it opens towards the middle of the building.
function doorSide(room, rooms, centre) {
    let best = null;
    let bestLength = 0;
    for (const side of ['top', 'bottom', 'left', 'right']) {
        for (const other of rooms) {
            if (other === room || other.kind !== 'corridor') continue;
            const length = sharedEdge(room, other, side);
            if (length > bestLength) {
                best = side;
                bestLength = length;
            }
        }
    }
    if (best) return best;

    const dx = centre.x - (room.x + room.width / 2);
    const dy = centre.z - (room.y + room.height / 2);
    if (Math.abs(dx) / room.width > Math.abs(dy) / room.height) return dx > 0 ? 'right' : 'left';
    return dy > 0 ? 'bottom' : 'top';
}

function addWall(batch, colors, side, box, hasDoor) {
    const horizontal = side === 'top' || side === 'bottom';
    const length = horizontal ? box.width : box.depth;
    const gap = hasDoor ? Math.min(1.8, length * 0.5) : 0;
    const pieces = hasDoor ? [[-length / 2, -gap / 2], [gap / 2, length / 2]] : [[-length / 2, length / 2]];

    for (const [from, to] of pieces) {
        const size = to - from;
        const middle = (from + to) / 2;
        const inset = WALL_THICKNESS / 2;
        const x = horizontal ? box.cx + middle : (side === 'left' ? box.x0 + inset : box.x1 - inset);
        const z = horizontal ? (side === 'top' ? box.z0 + inset : box.z1 - inset) : box.cz + middle;
        const [width, depth] = horizontal ? [size, WALL_THICKNESS] : [WALL_THICKNESS, size];

        batch.box(colors.wall, width, WALL_HEIGHT, depth, x, 0, z);
        batch.box(colors.neon, width, 0.04, depth * 0.5, x, WALL_HEIGHT, z);
    }
}

function addRoom(batch, room, rooms, centre, isTopFloor) {
    const box = roomBox(room, centre);
    const random = seededRandom(`room-${room.name}-${room.x}-${room.y}`);
    const colors = { wall: mix(room.color, COLORS.wall, 0.78), neon: `glow:${room.color}` };

    batch.at(0, 0, 0);
    batch.box(COLORS.slab, box.width, 0.3, box.depth, box.cx, -0.3, box.cz);

    if (room.kind === 'corridor') {
        // Walkways are open floor: no walls, a darker floor and a faint guide line.
        batch.box(COLORS.walkway, box.width, 0.03, box.depth, box.cx, 0, box.cz);
        return;
    }

    batch.box(mix(room.color, COLORS.slab, 0.88), box.width, 0.04, box.depth, box.cx, 0, box.cz);

    const door = doorSide(room, rooms, centre);
    for (const side of ['top', 'bottom', 'left', 'right']) {
        // The entrance is open to the street, where its door frame stands.
        if (room.kind === 'entrance' && side === 'bottom') continue;
        addWall(batch, colors, side, box, side === door);
    }

    if (room.kind === 'bar') addBar(batch, box, random);
    if (room.kind === 'stairs') addStairs(batch, box, isTopFloor);
    if (room.kind === 'entrance') addEntrance(batch, box);

    // Rooms built around a screen get one on their back wall, whatever language they are named in.
    if (/cinema|kino|кино|karaoke|караоке|game|spēļu|игров/i.test(room.name)) addScreen(batch, box, room.color);

    if (room.kind === 'seating' && box.width > 4 && box.depth > 4) {
        const inset = 0.55;
        const corners = [[box.x0 + inset, box.z0 + inset], [box.x1 - inset, box.z0 + inset], [box.x0 + inset, box.z1 - inset], [box.x1 - inset, box.z1 - inset]];
        const first = Math.floor(random() * 4);
        addLamp(batch, ...corners[first], random() > 0.5 ? COLORS.amber : COLORS.violet);
        addPlant(batch, ...corners[(first + 2) % 4]);
    }
}

function tableLabel(text) {
    const canvas = document.createElement('canvas');
    canvas.width = 160;
    canvas.height = 80;

    const context = canvas.getContext('2d');
    context.fillStyle = 'rgba(7, 8, 15, 0.82)';
    context.beginPath();
    context.roundRect(8, 12, 144, 56, 18);
    context.fill();
    context.fillStyle = '#f2f1f7';
    context.font = '600 34px "Manrope Variable", Manrope, system-ui, sans-serif';
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText(text, 80, 42, 128);

    const texture = new CanvasTexture(canvas);
    texture.colorSpace = SRGBColorSpace;

    const sprite = new Sprite(new SpriteMaterial({ map: texture, depthTest: false, transparent: true }));
    sprite.scale.set(2, 1, 1);
    sprite.renderOrder = 10;
    return sprite;
}

// A rectangle outline on the floor around a table, in the color of its status.
function statusFrame(width, depth) {
    const line = 0.07;
    const parts = [
        new BoxGeometry(width, 0.03, line).translate(0, 0, -depth / 2),
        new BoxGeometry(width, 0.03, line).translate(0, 0, depth / 2),
        new BoxGeometry(line, 0.03, depth).translate(-width / 2, 0, 0),
        new BoxGeometry(line, 0.03, depth).translate(width / 2, 0, 0),
    ];
    const merged = mergeGeometries(parts, false);
    parts.forEach((part) => part.dispose());
    return merged;
}

/**
 * @returns {{ root: Group, floors: Array, tables: Map<number, object>, setStatus: Function, height: Function }}
 */
export function buildLounge(layout, { labels = true } = {}) {
    const root = new Group();
    const tables = new Map();

    const materials = {};
    for (const [status, color] of Object.entries(STATUS_COLORS)) {
        materials[status] = {
            frame: new MeshBasicMaterial({ color }),
            fill: new MeshBasicMaterial({ color, transparent: true, opacity: status === 'selected' ? 0.45 : 0.14, depthWrite: false }),
        };
    }
    const pickMaterial = new MeshBasicMaterial({ visible: false });

    const centre = frameCentre(layout.frame);

    const floors = layout.floors.map((floor, index) => {
        const group = new Group();
        const labelGroup = new Group();
        const picks = [];
        const y = index * STOREY;
        const isTopFloor = index === layout.floors.length - 1;
        const roomsById = new Map(floor.rooms.map((room) => [room.id, room]));

        group.position.y = y;

        const batch = new Batch();
        floor.rooms.forEach((room) => addRoom(batch, room, floor.rooms, centre, isTopFloor));

        for (const table of floor.tables) {
            const room = roomsById.get(table.roomId);
            const width = table.width / 100;
            const depth = table.height / 100;
            const upright = depth > width;
            const rotation = (-table.rotation * Math.PI) / 180;
            const x = table.x - centre.x;
            const z = table.y - centre.z;

            batch.at(x, 0.04, z, rotation + (upright ? Math.PI / 2 : 0));
            addTableSet(batch, {
                width,
                depth,
                capacity: table.capacity,
                roomColor: room?.color ?? COLORS.violet,
                premium: table.capacity >= 10 || /vip/i.test(room?.name ?? ''),
                highTable: room?.kind === 'bar' && table.capacity <= 2,
            }, seededRandom(`table-${table.name}`));

            const status = table.active === false ? 'inactive' : (table.status ?? 'free');

            const frame = new Mesh(statusFrame(width + 0.3, depth + 0.3), materials[status].frame);
            frame.position.set(x, 0.07, z);
            frame.rotation.y = rotation;

            const fill = new Mesh(new PlaneGeometry(width + 0.3, depth + 0.3), materials[status].fill);
            fill.position.set(x, 0.065, z);
            fill.rotation.set(-Math.PI / 2, 0, rotation);

            const pick = new Mesh(new BoxGeometry(width + 0.3, 1.3, depth + 0.3), pickMaterial);
            pick.position.set(x, 0.65, z);
            pick.rotation.y = rotation;
            pick.userData.tableId = table.id;

            group.add(frame, fill, pick);
            picks.push(pick);

            if (labels) {
                const label = tableLabel(table.name);
                label.position.set(x, 2.1, z);
                labelGroup.add(label);
            }

            tables.set(table.id, { ...table, status, floorId: floor.id, frame, fill, position: { x, y, z }, size: { width, depth } });
        }

        group.add(...batch.build(), labelGroup);
        root.add(group);

        return { id: floor.id, name: floor.name, index, y, group, labelGroup, picks, rooms: floor.rooms.map((room) => ({ ...room, box: roomBox(room, centre) })) };
    });

    // Paints a table's outline. `selected` wins over its own status.
    function paint(tableId, selected = false) {
        const table = tables.get(tableId);
        if (!table) return;

        const look = materials[selected ? 'selected' : table.status];
        table.frame.material = look.frame;
        table.fill.material = look.fill;
        table.frame.scale.y = selected ? 2.5 : 1;
    }

    function dispose() {
        root.traverse((child) => {
            child.geometry?.dispose();
            if (child.isSprite) {
                child.material.map?.dispose();
                child.material.dispose();
            } else if (child.isMesh && !Object.values(materials).some((look) => look.frame === child.material || look.fill === child.material) && child.material !== pickMaterial) {
                child.material.dispose();
            }
        });
        Object.values(materials).forEach((look) => { look.frame.dispose(); look.fill.dispose(); });
        pickMaterial.dispose();
    }

    return { root, floors, tables, paint, dispose };
}

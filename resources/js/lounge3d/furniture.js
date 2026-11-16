// Low-poly lounge furniture. Every function adds shapes to a Batch around the batch origin.
import { Euler } from 'three';
import { COLORS, mix } from './kit.js';

const UPHOLSTERY_BASE = '#1a1630';

// A sofa of the given length. `facing` is the side its seat opens to: 'z+', 'z-', 'x+' or 'x-'.
export function addSofa(batch, palette, length, depth, cx, cz, facing) {
    const alongX = facing.startsWith('z');
    const sign = facing.endsWith('+') ? 1 : -1;

    // (along, out) are sofa-local: along its length, and out from the backrest towards the seat.
    const part = (color, along, height, out, a, y, o) => {
        const [width, deep] = alongX ? [along, out] : [out, along];
        const [x, z] = alongX ? [cx + a, cz + o * sign] : [cx + o * sign, cz + a];
        batch.box(color, width, height, deep, x, y, z);
    };

    part(palette.body, length, 0.4, depth, 0, 0, 0);
    part(palette.body, length, 0.86, 0.2, 0, 0, -depth / 2 + 0.1);
    part(palette.body, 0.16, 0.62, depth, -length / 2 + 0.08, 0, 0);
    part(palette.body, 0.16, 0.62, depth, length / 2 - 0.08, 0, 0);

    const cushions = Math.max(1, Math.round((length - 0.32) / 0.75));
    const cushionWidth = (length - 0.32) / cushions;
    for (let i = 0; i < cushions; i++) {
        const offset = -length / 2 + 0.16 + cushionWidth * (i + 0.5);
        part(palette.cushion, cushionWidth - 0.05, 0.1, depth - 0.3, offset, 0.4, 0.07);
    }

    if (palette.trim) part(palette.trim, length, 0.04, depth + 0.02, 0, 0, 0);
}

export function addHookah(batch, x, y, z, random, scale = 1) {
    const glass = [COLORS.violet, COLORS.amber, '#5ed6c4', '#f28bb0'][Math.floor(random() * 4)];
    const s = scale;

    batch.cylinder(mix(glass, '#151525', 0.35), 0.07 * s, 0.13 * s, 0.2 * s, x, y, z, 7);
    batch.cylinder(mix(glass, '#151525', 0.35), 0.03 * s, 0.07 * s, 0.1 * s, x, y + 0.2 * s, z, 7);
    batch.cylinder(COLORS.metal, 0.018 * s, 0.018 * s, 0.56 * s, x, y + 0.3 * s, z, 5);
    batch.cylinder(COLORS.metal, 0.11 * s, 0.11 * s, 0.015 * s, x, y + 0.78 * s, z, 8);
    batch.cylinder('#7a4a35', 0.055 * s, 0.03 * s, 0.08 * s, x, y + 0.86 * s, z, 6);
    batch.cylinder(`glow:${COLORS.ember}`, 0.04 * s, 0.045 * s, 0.02 * s, x, y + 0.94 * s, z, 6);

    // The hose: one sloped piece from the stem down to a mouthpiece resting on the table.
    const angle = random() * Math.PI * 2;
    const reach = 0.3 * s;
    batch.cylinder('#2a2c3f', 0.012 * s, 0.012 * s, 0.6 * s,
        x + (Math.cos(angle) * reach) / 2, y + 0.1 * s, z + (Math.sin(angle) * reach) / 2, 4,
        new Euler(Math.sin(angle) * 0.5, 0, -Math.cos(angle) * 0.5));
    batch.cylinder(COLORS.gold, 0.015 * s, 0.015 * s, 0.1 * s, x + Math.cos(angle) * reach, y, z + Math.sin(angle) * reach, 4,
        new Euler(Math.PI / 2, 0, angle));
}

function addStool(batch, x, z, color) {
    batch.cylinder(COLORS.metal, 0.03, 0.12, 0.7, x, 0, z, 5);
    batch.cylinder(color, 0.2, 0.18, 0.08, x, 0.7, z, 8);
}

/**
 * One table with its seating, fitted into a footprint of `width` x `depth` metres.
 *
 *   up to 2 guests   a small round table and two armchairs (high table and stools in a bar)
 *   3 to 4 guests    a low table between two sofas
 *   5 to 9 guests    a U of three sofas
 *   10 and more      a large U with ottomans and two hookahs
 */
export function addTableSet(batch, { width, depth, capacity, roomColor, premium, highTable }, random) {
    // Build with the long side along x. The caller rotates the batch for upright footprints.
    const long = Math.max(width, depth);
    const short = Math.min(width, depth);

    const palette = premium
        ? { body: '#4a1a33', cushion: '#6d2748', trim: COLORS.gold }
        : { body: mix(roomColor, UPHOLSTERY_BASE, 0.74), cushion: mix(roomColor, UPHOLSTERY_BASE, 0.52), trim: null };
    const tableTop = premium ? '#2b1f2f' : COLORS.wood;

    if (highTable) {
        batch.cylinder(COLORS.metal, 0.04, 0.2, 1.02, 0, 0, 0, 6);
        batch.cylinder(COLORS.wood, 0.42, 0.42, 0.05, 0, 1.02, 0, 8);
        addStool(batch, -long / 2 + 0.35, 0, palette.cushion);
        addStool(batch, long / 2 - 0.35, 0, palette.cushion);
        addHookah(batch, 0, 1.07, 0, random, 0.9);
        return;
    }

    if (capacity <= 2) {
        const chair = Math.min(0.95, short * 0.42);
        batch.cylinder(COLORS.darkWood, 0.06, 0.2, 0.42, 0, 0, 0, 6);
        batch.cylinder(tableTop, 0.38, 0.38, 0.05, 0, 0.42, 0, 8);
        addSofa(batch, palette, chair, 0.7, -long / 2 + 0.38, 0, 'x+');
        addSofa(batch, palette, chair, 0.7, long / 2 - 0.38, 0, 'x-');
        addHookah(batch, 0, 0.47, 0, random);
        return;
    }

    const sofaDepth = Math.min(0.8, short * 0.3);

    if (capacity <= 4) {
        const tableDepth = Math.min(0.9, Math.max(0.4, short - sofaDepth * 2 - 0.3));
        const tableLength = Math.max(0.8, long * 0.5);

        batch.box(COLORS.darkWood, tableLength - 0.12, 0.36, tableDepth - 0.12, 0, 0, 0);
        batch.box(tableTop, tableLength, 0.05, tableDepth, 0, 0.36, 0);
        addSofa(batch, palette, long - 0.2, sofaDepth, 0, -short / 2 + sofaDepth / 2, 'z+');
        addSofa(batch, palette, long - 0.2, sofaDepth, 0, short / 2 - sofaDepth / 2, 'z-');
        addHookah(batch, 0, 0.41, 0, random);
        return;
    }

    // U shape: one sofa along the back, one at each end, open towards z+.
    const sideLength = Math.max(1, short - sofaDepth - 0.1);
    const sideCentre = -short / 2 + sofaDepth + 0.1 + sideLength / 2;
    const tableLength = Math.max(1, long - sofaDepth * 2 - 0.9);
    const tableDepth = Math.min(1.3, Math.max(0.5, short - sofaDepth - 0.9));
    const tableCentre = -short / 2 + sofaDepth + 0.35 + tableDepth / 2;

    addSofa(batch, palette, long - 0.1, sofaDepth, 0, -short / 2 + sofaDepth / 2, 'z+');
    addSofa(batch, palette, sideLength, sofaDepth, -long / 2 + sofaDepth / 2 + 0.05, sideCentre, 'x+');
    addSofa(batch, palette, sideLength, sofaDepth, long / 2 - sofaDepth / 2 - 0.05, sideCentre, 'x-');

    batch.box(COLORS.darkWood, tableLength - 0.14, 0.36, tableDepth - 0.14, 0, 0, tableCentre);
    if (premium) batch.box(COLORS.gold, tableLength + 0.06, 0.03, tableDepth + 0.06, 0, 0.33, tableCentre);
    batch.box(tableTop, tableLength, 0.05, tableDepth, 0, 0.36, tableCentre);

    if (capacity >= 10) {
        addHookah(batch, -tableLength / 4, 0.41, tableCentre, random, 1.1);
        addHookah(batch, tableLength / 4, 0.41, tableCentre, random, 1.1);
        batch.box(palette.cushion, 0.8, 0.4, 0.8, -tableLength / 4, 0, short / 2 - 0.5);
        batch.box(palette.cushion, 0.8, 0.4, 0.8, tableLength / 4, 0, short / 2 - 0.5);
    } else {
        addHookah(batch, 0, 0.41, tableCentre, random);
    }
}

// --- Room props ----------------------------------------------------------------------------

// A counter along the back wall of a bar, with a bottle shelf behind it and stools in front.
export function addBar(batch, room, random) {
    const length = room.width * 0.72;
    const back = room.z0 + 0.2;
    const counter = Math.min(room.z0 + 1.7, room.z0 + room.depth * 0.4);

    batch.box(COLORS.wall, length, 1.7, 0.3, room.cx, 0, back + 0.15);
    for (const [shelf, count] of [[0.95, 9], [1.4, 9]]) {
        batch.box(COLORS.darkWood, length, 0.04, 0.34, room.cx, shelf, back + 0.17);
        for (let i = 0; i < count; i++) {
            const color = [COLORS.amber, COLORS.violet, '#5ed6c4', '#f28bb0', COLORS.glass][Math.floor(random() * 5)];
            const x = room.cx - length / 2 + (length / count) * (i + 0.5) + (random() - 0.5) * 0.1;
            batch.cylinder(color, 0.03, 0.05, 0.22 + random() * 0.1, x, shelf + 0.04, back + 0.17, 5);
        }
    }

    batch.box(COLORS.darkWood, length, 1.05, 0.6, room.cx, 0, counter);
    batch.box(COLORS.wood, length + 0.16, 0.06, 0.78, room.cx, 1.05, counter);
    batch.box(`glow:${COLORS.amber}`, length, 0.05, 0.03, room.cx, 0.12, counter + 0.32);

    const stools = Math.max(2, Math.floor(length / 1.1));
    for (let i = 0; i < stools; i++) {
        addStool(batch, room.cx - length / 2 + (length / stools) * (i + 0.5), counter + 0.85, mix(COLORS.amber, UPHOLSTERY_BASE, 0.6));
    }
}

// A flight of steps on every floor but the top one, where a railing guards the stairwell.
export function addStairs(batch, room, isTopFloor) {
    if (isTopFloor) {
        batch.box('#05060b', room.width - 0.5, 0.03, room.depth - 0.5, room.cx, 0.04, room.cz);
        batch.box(COLORS.metal, room.width - 0.4, 0.05, 0.05, room.cx, 1, room.z0 + 0.25);
        batch.box(COLORS.metal, 0.05, 0.05, room.depth - 0.4, room.x0 + 0.25, 1, room.cz);
        return;
    }

    const steps = 9;
    const run = (room.width - 0.4) / steps;
    for (let i = 0; i < steps; i++) {
        batch.box(COLORS.wood, run, 0.2 * (i + 1), room.depth - 0.5, room.x0 + 0.2 + run * (i + 0.5), 0, room.cz);
    }
    batch.box(`glow:${COLORS.violet}`, room.width - 0.4, 0.03, 0.04, room.cx, 0.02, room.z1 - 0.22);
}

// A door frame on the street side and a mat inside it.
export function addEntrance(batch, room) {
    const doorWidth = Math.min(2.2, room.width * 0.5);

    batch.box(COLORS.metal, 0.14, 2.3, 0.14, room.cx - doorWidth / 2, 0, room.z1);
    batch.box(COLORS.metal, 0.14, 2.3, 0.14, room.cx + doorWidth / 2, 0, room.z1);
    batch.box(COLORS.metal, doorWidth + 0.14, 0.14, 0.14, room.cx, 2.3, room.z1);
    batch.box(`glow:${COLORS.violet}`, doorWidth - 0.1, 0.05, 0.05, room.cx, 2.22, room.z1 - 0.08);
    batch.box(mix(COLORS.violet, UPHOLSTERY_BASE, 0.7), doorWidth, 0.03, Math.min(1.3, room.depth * 0.5), room.cx, 0.04, room.cz + 0.2);
}

// A floor lamp with a warm globe, used in the corners of seating rooms.
export function addLamp(batch, x, z, color) {
    batch.cylinder(COLORS.metal, 0.02, 0.12, 1.5, x, 0, z, 5);
    batch.cylinder(`glow:${color}`, 0.14, 0.1, 0.22, x, 1.5, z, 6);
}

// A planter with a low-poly shrub.
export function addPlant(batch, x, z) {
    batch.cylinder('#2a2c3f', 0.22, 0.16, 0.4, x, 0, z, 6);
    batch.cylinder('#2f6b4f', 0.05, 0.36, 0.7, x, 0.4, z, 5);
    batch.cylinder('#3d8a63', 0.02, 0.24, 0.5, x, 0.85, z, 5);
}

// A wide screen on the back wall, for the cinema, karaoke and game rooms.
export function addScreen(batch, room, color) {
    const width = Math.min(room.width * 0.6, 7);

    batch.box(COLORS.wall, width + 0.3, 1.5, 0.12, room.cx, 0.4, room.z0 + 0.14);
    batch.box(`glow:${mix(color, '#ffffff', 0.35)}`, width, 1.2, 0.04, room.cx, 0.55, room.z0 + 0.22);
}

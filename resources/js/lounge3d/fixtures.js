// Low-poly models for the fixtures placed on the floor plan.
//
// A fixture stands at (x, y) on the plan with a footprint of width x depth metres. Rotation 0
// means its front faces down the plan (+z in the scene); rotation turns it clockwise on the plan.
// Every model is built around its own centre, front towards +z, and placed with batch.at().
import { Euler } from 'three';
import { addHookah, addPlant } from './furniture.js';
import { COLORS, mix } from './kit.js';

const DARK = '#1b1c2b';

export const LIGHT_COLORS = { amber: '#F5B860', violet: '#B9A5FF', rose: '#F28BB0', teal: '#5ED6C4', white: '#FFF1D6' };
const SCREEN = '#9fb4ff';

function screen(batch, width, height, y, z, colour = SCREEN) {
    batch.box(DARK, width + 0.08, height + 0.08, 0.06, 0, y - 0.04, z);
    batch.box(`glow:${mix(colour, '#ffffff', 0.25)}`, width, height, 0.015, 0, y, z + 0.035);
}

function lightColour(fixture) {
    return LIGHT_COLORS[fixture.settings?.color] ?? LIGHT_COLORS.amber;
}

const lights = {
    pendant_light(batch, fixture) {
        const colour = lightColour(fixture);
        batch.cylinder('#2a2c3f', 0.01, 0.01, 0.6, 0, 2.3, 0, 4);
        batch.cylinder('#1e2033', 0.06, 0.24, 0.22, 0, 2.08, 0, 8);
        batch.cylinder(`glow:${colour}`, 0.11, 0.08, 0.06, 0, 2.04, 0, 8);
    },

    floor_lamp(batch, fixture) {
        const colour = lightColour(fixture);
        batch.cylinder(COLORS.metal, 0.14, 0.16, 0.04, 0, 0, 0, 8);
        batch.cylinder(COLORS.metal, 0.02, 0.02, 1.45, 0, 0.04, 0, 5);
        batch.cylinder(`glow:${colour}`, 0.12, 0.2, 0.28, 0, 1.45, 0, 8);
    },

    neon_strip(batch, fixture) {
        const colour = lightColour(fixture);
        // A thin backing on the wall and a glowing tube in front of it, visible from above too.
        batch.box('#141524', fixture.width + 0.06, 0.1, 0.02, 0, 1.84, -fixture.depth / 2);
        batch.box(`glow:${colour}`, fixture.width, 0.07, 0.07, 0, 1.86, -fixture.depth / 2 + 0.05);
    },

    led_strip(batch, fixture) {
        batch.box(`glow:${lightColour(fixture)}`, fixture.width, 0.025, 0.04, 0, 0.02, 0);
    },
};

const builders = {
    game_station(batch, w, d, random) {
        batch.box(COLORS.darkWood, w, 0.45, d, 0, 0, 0);
        batch.box(`glow:${COLORS.violet}`, w * 0.9, 0.03, 0.02, 0, 0.08, d / 2);
        screen(batch, w * 0.85, w * 0.48, 0.55, -d / 2 + 0.12);
        // The console and two controllers.
        batch.box('#f2f1f7', 0.12, 0.32, 0.26, w * 0.32, 0.45, 0);
        batch.box('#1e2033', 0.12, 0.02, 0.26, w * 0.32, 0.6, 0);
        for (const side of [-1, 1]) {
            batch.box(random() > 0.5 ? '#f2f1f7' : '#2a2c3f', 0.16, 0.05, 0.1, side * w * 0.18, 0.45, d * 0.2);
        }
    },

    wall_tv(batch, w, d) {
        screen(batch, w, w * 0.56, 1.0, -d / 2 + 0.05);
    },

    projector_screen(batch, w, d) {
        batch.box(COLORS.metal, w + 0.2, 0.08, 0.1, 0, 2.15, -d / 2 + 0.05);
        batch.box(`glow:${mix(SCREEN, '#ffffff', 0.55)}`, w, w * 0.42, 0.02, 0, 2.1 - w * 0.42, -d / 2 + 0.06);
        for (const side of [-1, 1]) batch.box(DARK, 0.06, 2.2, 0.06, side * (w / 2 + 0.08), 0, -d / 2 + 0.05);
    },

    karaoke(batch, w, d) {
        // A screen on a stand, two speakers and a microphone in front.
        batch.cylinder(COLORS.metal, 0.03, 0.18, 0.9, 0, 0, -d / 2 + 0.15, 6);
        screen(batch, w * 0.55, w * 0.32, 0.9, -d / 2 + 0.15, '#f28bb0');
        for (const side of [-1, 1]) {
            batch.box(DARK, 0.4, 1.0, 0.35, side * (w / 2 - 0.2), 0, -d / 2 + 0.2);
            batch.cylinder('#2a2c3f', 0.12, 0.12, 0.03, side * (w / 2 - 0.2), 0.55, -d / 2 + 0.39, 8, new Euler(Math.PI / 2, 0, 0));
        }
        batch.cylinder(COLORS.metal, 0.015, 0.12, 1.35, 0, 0, d / 2 - 0.1, 5);
        batch.cylinder(`glow:${COLORS.violet}`, 0.04, 0.03, 0.12, 0, 1.35, d / 2 - 0.1, 6);
    },

    dj_booth(batch, w, d) {
        batch.box(DARK, w, 1.0, d, 0, 0, 0);
        batch.box(`glow:${COLORS.violet}`, w * 0.92, 0.05, 0.02, 0, 0.55, d / 2 + 0.005);
        batch.box(`glow:${COLORS.amber}`, w * 0.92, 0.05, 0.02, 0, 0.35, d / 2 + 0.005);
        batch.box('#2a2c3f', w * 0.9, 0.04, d * 0.8, 0, 1.0, 0);
        for (const side of [-1, 1]) {
            batch.cylinder('#0f0f18', 0.22, 0.22, 0.03, side * w * 0.26, 1.04, -0.05, 12);
            batch.cylinder(COLORS.metal, 0.03, 0.03, 0.04, side * w * 0.26, 1.06, -0.05, 6);
        }
        batch.box('#3a3d57', w * 0.18, 0.05, d * 0.5, 0, 1.04, 0);
    },

    speaker(batch, w, d) {
        batch.box(DARK, w * 0.9, 1.1, d * 0.8, 0, 0, 0);
        for (const [y, r] of [[0.75, w * 0.18], [0.35, w * 0.3]]) {
            batch.cylinder('#2a2c3f', r, r, 0.03, 0, y, d * 0.4, 10, new Euler(Math.PI / 2, 0, 0));
        }
    },

    hookah_station(batch, w, d, random) {
        batch.box(COLORS.wood, w, 0.9, d, 0, 0, 0);
        batch.box(COLORS.darkWood, w + 0.04, 0.04, d + 0.04, 0, 0.9, 0);
        const count = Math.max(2, Math.floor(w / 0.5));
        for (let i = 0; i < count; i++) addHookah(batch, -w / 2 + (w / count) * (i + 0.5), 0.94, 0, random, 0.8);
    },

    coat_rack(batch, w, d, random) {
        for (const side of [-1, 1]) batch.box(COLORS.metal, 0.05, 1.75, 0.05, side * (w / 2 - 0.05), 0, 0);
        batch.box(COLORS.metal, w, 0.04, 0.04, 0, 1.72, 0);
        const coats = Math.max(2, Math.floor(w / 0.3));
        for (let i = 0; i < coats; i++) {
            const colour = ['#3a2c2a', '#2c2f4a', '#4a1a33', '#1b3a33', '#5a4632'][Math.floor(random() * 5)];
            batch.box(colour, 0.08, 0.8 + random() * 0.25, d * 0.7, -w / 2 + 0.15 + (w - 0.3) * (i / Math.max(1, coats - 1)), 0.85, 0);
        }
    },

    plant(batch, w) {
        addPlant(batch, 0, 0);
        if (w > 0.9) addPlant(batch, w * 0.25, w * 0.15);
    },

    partition(batch, w, d) {
        const slats = Math.max(3, Math.round(w / 0.18));
        for (let i = 0; i < slats; i++) batch.box(COLORS.wood, 0.06, 1.8, d, -w / 2 + 0.03 + (w - 0.06) * (i / (slats - 1)), 0, 0);
        batch.box(COLORS.darkWood, w, 0.06, d + 0.02, 0, 1.8, 0);
    },

    curtain(batch, w, d, random) {
        const folds = Math.max(4, Math.round(w / 0.2));
        const colour = random() > 0.5 ? '#4a1a33' : '#2c2350';
        for (let i = 0; i < folds; i++) {
            batch.box(i % 2 ? colour : mix(colour, '#000000', 0.25), w / folds + 0.01, 2.4, 0.06, -w / 2 + (w / folds) * (i + 0.5), 0, (i % 2 ? 1 : -1) * d * 0.25);
        }
        batch.box(COLORS.metal, w + 0.1, 0.04, 0.04, 0, 2.42, 0);
    },

    aquarium(batch, w, d, random) {
        batch.box(COLORS.darkWood, w, 0.7, d, 0, 0, 0);
        batch.box('glass:#bfe6f2', w - 0.04, 0.65, d - 0.04, 0, 0.7, 0);
        batch.box('glow:#1e6f8f', w - 0.12, 0.5, d - 0.12, 0, 0.74, 0);
        for (let i = 0; i < 6; i++) {
            const colour = ['#f5b860', '#f28bb0', '#5ed6c4', '#b9a5ff'][Math.floor(random() * 4)];
            batch.box(`glow:${colour}`, 0.09, 0.04, 0.03, (random() - 0.5) * (w - 0.3), 0.85 + random() * 0.35, (random() - 0.5) * (d - 0.2));
        }
        batch.cylinder('#2f6b4f', 0.0, 0.06, 0.35, -w * 0.3, 0.74, 0, 4);
    },

    feature_wall(batch, w, d, random) {
        batch.box('#141524', w, 2.4, d, 0, 0, 0);
        // Orbit lines in neon, the site's motif, slightly different on every wall.
        const lines = 3 + Math.floor(random() * 3);
        for (let i = 0; i < lines; i++) {
            const colour = i % 2 ? COLORS.amber : COLORS.violet;
            const width = w * (0.4 + random() * 0.5);
            batch.box(`glow:${colour}`, width, 0.03, 0.02, (random() - 0.5) * (w - width), 0.4 + (2 * (i + 0.5)) / lines, d / 2 + 0.005);
        }
    },
};

// A bar counter follows its corner points: one straight counter per piece, stools on the
// guest side (to the right of the direction the corners run in).
function addBarCounter(batch, points, centre) {
    batch.at(0, 0, 0);
    for (let i = 0; i < points.length - 1; i++) {
        const [ax, ay] = points[i];
        const [bx, by] = points[i + 1];
        const length = Math.hypot(bx - ax, by - ay);
        if (length < 0.1) continue;

        const angle = -Math.atan2(by - ay, bx - ax);
        const mx = (ax + bx) / 2 - centre.x;
        const mz = (ay + by) / 2 - centre.z;
        // Corners overlap a little, so an L or U reads as one counter.
        const piece = length + (i < points.length - 2 ? 0.3 : 0);

        batch.at(mx, 0, mz, angle);
        batch.box(COLORS.darkWood, piece, 1.05, 0.55, 0, 0, 0);
        batch.box(COLORS.wood, piece + 0.08, 0.06, 0.7, 0, 1.05, 0.05);
        batch.box(`glow:${COLORS.amber}`, piece, 0.04, 0.03, 0, 0.12, 0.29);

        const stools = Math.max(1, Math.floor(length / 0.75));
        for (let s = 0; s < stools; s++) {
            const along = -length / 2 + (length / stools) * (s + 0.5);
            batch.cylinder(COLORS.metal, 0.03, 0.12, 0.7, along, 0, 0.75, 5);
            batch.cylinder(mix(COLORS.amber, '#1a1630', 0.6), 0.2, 0.18, 0.08, along, 0.7, 0.75, 8);
        }
    }
}

export function addFixture(batch, fixture, centre, random) {
    if (fixture.type === 'bar_counter' && Array.isArray(fixture.points) && fixture.points.length > 1) {
        addBarCounter(batch, fixture.points, centre);
        return;
    }

    batch.at(fixture.x - centre.x, 0.04, fixture.y - centre.z, (-fixture.rotation * Math.PI) / 180);

    if (lights[fixture.type]) {
        lights[fixture.type](batch, fixture);
        return;
    }

    builders[fixture.type]?.(batch, fixture.width, fixture.depth, random);
}

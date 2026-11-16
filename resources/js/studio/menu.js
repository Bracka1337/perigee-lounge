// Procedural product shots for menu items, in the same low-poly style as the lounge.
//
// What is drawn comes from the item's category and from keywords in its English name and
// description. A random generator seeded with the item name varies the details, so every
// item looks a little different, and the same item always renders the same way.
import { CatmullRomCurve3, Euler, Group, TorusGeometry, TubeGeometry, Vector3 } from 'three';
import { Batch, COLORS, mix, seededRandom } from '../lounge3d/kit.js';

const TINTS = [
    [/coffee|espresso|rum|cola|dark matter/, '#4a2c1a'],
    [/mint|cucumber|jasmine|green|melon|grape|elderflower|lime/, '#7ed6a0'],
    [/berr|blackcurrant|hibiscus|pomegranate|red|nebula/, '#9b3fb8'],
    [/mango|peach|apricot|orange|buckthorn|solar/, '#f59a3c'],
    [/citrus|lemon|sour|sparkling/, '#f2d14e'],
    [/whisky|honey|black tea|smoky|amber/, '#c9822e'],
];

// The liquid or flavour color: keywords in the name win over keywords in the description.
export function tintFor(item) {
    for (const text of [item.name.toLowerCase(), item.description.toLowerCase()]) {
        const match = TINTS.find(([pattern]) => pattern.test(text));
        if (match) return match[1];
    }
    return COLORS.amber;
}

// Which shapes an item gets. Exported so the choice can be read in one place.
export function shapeFor(item) {
    const name = item.name.toLowerCase();
    const category = item.category.toLowerCase();

    if (/table for/.test(name)) return 'feast';
    if (/hookah & tea|hookah and tea/.test(name)) return 'hookah-tea';
    if (/two cocktails/.test(name)) return 'two-cocktails';
    if (/hookah/.test(category)) return 'hookah';
    if (/espresso/.test(name)) return 'espresso';
    if (/coffee/.test(name)) return 'coffee';
    if (/tea/.test(name) || /tea/.test(category)) return 'teapot';
    if (/water/.test(name)) return 'bottle';
    if (/sour/.test(name)) return 'tumbler';
    if (/spritz|wine/.test(name)) return 'wine';
    if (/cocktail/.test(category)) return /gin|tonic/.test(name) ? 'highball' : 'coupe';
    if (/drink/.test(category)) return 'highball';
    if (/almond|olive|nuts/.test(name)) return 'bowl';
    if (/board|mezze/.test(name)) return 'board';
    if (/baklava|cake|dessert/.test(name)) return 'pastry';
    if (/cheese|plate/.test(name)) return 'cheese';
    return /bite|food|snack/.test(category) ? 'cheese' : 'highball';
}

// --- Shapes. Each one stands on y = 0 around (x, z). ------------------------------------------

function hookah(batch, x, z, tint, random, scale = 1) {
    const s = scale;
    const glass = mix(tint, '#1a1a2e', 0.25);

    batch.cylinder(COLORS.metal, 0.2 * s, 0.24 * s, 0.03 * s, x, 0, z, 10);
    batch.cylinder(`glass:${mix(tint, '#ffffff', 0.5)}`, 0.13 * s, 0.26 * s, 0.34 * s, x, 0.03 * s, z, 9);
    batch.cylinder(glass, 0.11 * s, 0.22 * s, 0.2 * s, x, 0.04 * s, z, 9);
    batch.cylinder(`glass:${mix(tint, '#ffffff', 0.5)}`, 0.06 * s, 0.13 * s, 0.14 * s, x, 0.37 * s, z, 8);
    batch.cylinder(COLORS.gold, 0.07 * s, 0.07 * s, 0.04 * s, x, 0.5 * s, z, 8);
    batch.cylinder(COLORS.metal, 0.028 * s, 0.035 * s, 0.75 * s, x, 0.54 * s, z, 6);
    batch.cylinder(COLORS.gold, 0.05 * s, 0.05 * s, 0.03 * s, x, 0.8 * s, z, 6);
    batch.cylinder(COLORS.metal, 0.2 * s, 0.17 * s, 0.025 * s, x, 1.29 * s, z, 10);
    batch.cylinder('#8a5236', 0.1 * s, 0.05 * s, 0.14 * s, x, 1.32 * s, z, 7);
    batch.cylinder(COLORS.metal, 0.105 * s, 0.105 * s, 0.015 * s, x, 1.46 * s, z, 7);
    batch.cylinder(`glow:${COLORS.ember}`, 0.045 * s, 0.05 * s, 0.03 * s, x - 0.03 * s, 1.475 * s, z, 5);
    batch.cylinder(`glow:${mix(COLORS.ember, '#ffd27a', 0.5)}`, 0.03 * s, 0.035 * s, 0.03 * s, x + 0.045 * s, 1.475 * s, z + 0.02 * s, 5);

    // Hose: leaves the stem, sags, and comes to rest on the table with a gold mouthpiece.
    const side = random() > 0.5 ? 1 : -1;
    const points = [[0.03, 0.6, 0], [0.3, 0.66, 0.06], [0.58, 0.34, 0.2], [0.72, 0.05, 0.38], [0.98, 0.03, 0.52]]
        .map(([dx, y, dz]) => new Vector3(x + side * dx * s, y * s, z + dz * s));
    batch.add(new TubeGeometry(new CatmullRomCurve3(points), 16, 0.02 * s, 5), mix(tint, '#20202c', 0.6), 0, 0, 0);
    batch.cylinder(COLORS.gold, 0.016 * s, 0.026 * s, 0.18 * s, x + side * 1.04 * s, 0.01, z + 0.55 * s, 5, new Euler(Math.PI / 2, 0, -side * 1.05));
}

function ice(batch, x, y, z, random, count, spread) {
    for (let i = 0; i < count; i++) {
        batch.box('glass:#e8f6ff', 0.075, 0.075, 0.075, x + (random() - 0.5) * spread, y + random() * 0.05, z + (random() - 0.5) * spread,
            new Euler(random(), random(), random()));
    }
}

function garnish(batch, x, y, z, random, tint) {
    const pick = random();
    if (pick < 0.45) {
        // A citrus wheel on the rim.
        batch.cylinder(mix(tint, '#f2d14e', 0.6), 0.08, 0.08, 0.015, x, y, z, 8, new Euler(Math.PI / 2 - 0.2, 0.4, 0));
    } else if (pick < 0.8) {
        // A sprig of mint.
        batch.cylinder('#3d8a63', 0.0, 0.05, 0.12, x, y, z, 4, new Euler(0.3, 0, 0.4));
        batch.cylinder('#56b07f', 0.0, 0.04, 0.1, x + 0.03, y + 0.01, z, 4, new Euler(-0.3, 0, -0.5));
    } else {
        // Two berries on a pick.
        batch.cylinder(COLORS.gold, 0.006, 0.006, 0.2, x, y - 0.04, z, 4, new Euler(0, 0, 0.5));
        batch.cylinder('#9b2f5f', 0.035, 0.035, 0.06, x - 0.02, y + 0.03, z, 6);
        batch.cylinder('#6d2748', 0.035, 0.035, 0.06, x + 0.04, y - 0.01, z, 6);
    }
}

function highball(batch, x, z, tint, random, height = 0.56) {
    batch.cylinder('glass:#cfeaf5', 0.15, 0.13, height, x, 0, z, 10);
    batch.cylinder(tint, 0.135, 0.118, height * 0.78, x, 0.02, z, 10);
    ice(batch, x, height * 0.74, z, random, 4, 0.14);
    batch.cylinder(COLORS.violet, 0.01, 0.01, height * 1.25, x + 0.05, 0.08, z, 5, new Euler(0, 0, -0.22));
    garnish(batch, x - 0.12, height + 0.02, z + 0.04, random, tint);
}

function tumbler(batch, x, z, tint, random) {
    batch.cylinder('glass:#cfeaf5', 0.2, 0.17, 0.27, x, 0, z, 9);
    batch.cylinder(tint, 0.185, 0.158, 0.15, x, 0.03, z, 9);
    batch.box('glass:#f3fbff', 0.16, 0.16, 0.16, x + 0.02, 0.1, z, new Euler(0.2, 0.6, 0.15));
    garnish(batch, x - 0.15, 0.28, z + 0.06, random, tint);
}

function coupe(batch, x, z, tint, random) {
    batch.cylinder('glass:#cfeaf5', 0.15, 0.16, 0.015, x, 0, z, 10);
    batch.cylinder('glass:#cfeaf5', 0.014, 0.014, 0.34, x, 0.015, z, 6);
    batch.cylinder('glass:#cfeaf5', 0.27, 0.02, 0.2, x, 0.35, z, 10);
    batch.cylinder(tint, 0.225, 0.03, 0.15, x, 0.37, z, 10);
    garnish(batch, x + 0.2, 0.56, z, random, tint);
}

function wineGlass(batch, x, z, tint, random) {
    batch.cylinder('glass:#cfeaf5', 0.14, 0.15, 0.015, x, 0, z, 10);
    batch.cylinder('glass:#cfeaf5', 0.014, 0.014, 0.26, x, 0.015, z, 6);
    batch.cylinder('glass:#cfeaf5', 0.17, 0.08, 0.14, x, 0.27, z, 10);
    batch.cylinder('glass:#cfeaf5', 0.15, 0.17, 0.22, x, 0.41, z, 10);
    batch.cylinder(tint, 0.155, 0.075, 0.12, x, 0.285, z, 10);
    batch.cylinder(tint, 0.15, 0.155, 0.1, x, 0.405, z, 10);
    ice(batch, x, 0.5, z, random, 3, 0.12);
    garnish(batch, x - 0.14, 0.64, z + 0.03, random, tint);
}

function bottle(batch, x, z) {
    batch.cylinder('glass:#bfe6f2', 0.1, 0.1, 0.46, x, 0, z, 9);
    batch.cylinder('glass:#bfe6f2', 0.04, 0.1, 0.14, x, 0.46, z, 9);
    batch.cylinder('glass:#bfe6f2', 0.04, 0.04, 0.1, x, 0.6, z, 8);
    batch.cylinder(COLORS.metal, 0.045, 0.045, 0.04, x, 0.7, z, 8);
    batch.cylinder('#e8ecff', 0.102, 0.102, 0.16, x, 0.16, z, 9);
}

function cup(batch, x, z, liquid, scale = 1, ceramic = '#e9e4f0') {
    const s = scale;
    batch.cylinder(ceramic, 0.22 * s, 0.17 * s, 0.025 * s, x, 0, z, 10);
    batch.cylinder(ceramic, 0.13 * s, 0.085 * s, 0.15 * s, x, 0.025 * s, z, 9);
    batch.cylinder(liquid, 0.115 * s, 0.115 * s, 0.01, x, 0.165 * s, z, 9);
    batch.add(new TorusGeometry(0.055 * s, 0.014 * s, 5, 8, Math.PI), ceramic, x + 0.135 * s, 0.1 * s, z, new Euler(0, 0, -Math.PI / 2));
}

function teapot(batch, x, z, tint, random) {
    const ceramic = random() > 0.5 ? '#2c2f4a' : '#3a2c2a';
    batch.cylinder(ceramic, 0.27, 0.33, 0.1, x, 0, z, 8);
    batch.cylinder(`glass:${mix(tint, '#ffffff', 0.35)}`, 0.3, 0.27, 0.26, x, 0.1, z, 8);
    batch.cylinder(tint, 0.27, 0.245, 0.17, x, 0.11, z, 8);
    batch.cylinder(ceramic, 0.18, 0.3, 0.07, x, 0.36, z, 8);
    batch.cylinder(COLORS.gold, 0.035, 0.05, 0.05, x, 0.43, z, 6);
    batch.cylinder(ceramic, 0.03, 0.055, 0.3, x - 0.36, 0.2, z, 6, new Euler(0, 0, 0.9));
    batch.add(new TorusGeometry(0.13, 0.022, 5, 8, Math.PI), ceramic, x + 0.3, 0.25, z, new Euler(0, 0, -Math.PI / 2));
}

function plate(batch, x, z, radius = 0.5) {
    batch.cylinder('#e9e4f0', radius, radius * 0.72, 0.03, x, 0, z, 12);
    batch.cylinder('#d7d1e2', radius * 0.72, radius * 0.72, 0.008, x, 0.03, z, 12);
}

function cheese(batch, x, z, random) {
    plate(batch, x, z, 0.56);
    for (const [dx, dz, color, turn] of [[-0.18, 0.02, '#f2d14e', 0.4], [0.12, -0.14, '#f6e7b0', 2.2], [0.1, 0.18, '#e9b24a', 4.1]]) {
        batch.cylinder(color, 0.16, 0.16, 0.12, x + dx, 0.04, z + dz, 3, new Euler(0, turn + random(), 0));
    }
    batch.cylinder(COLORS.amber, 0.07, 0.06, 0.09, x - 0.02, 0.04, z - 0.26, 7);
    for (let i = 0; i < 5; i++) {
        batch.cylinder('#7a4a35', 0.03, 0.04, 0.04, x - 0.3 + random() * 0.18, 0.04, z + 0.16 + random() * 0.16, 5);
    }
}

function board(batch, x, z, random) {
    batch.box(COLORS.wood, 1.25, 0.05, 0.7, x, 0, z);
    batch.box(mix(COLORS.wood, '#ffffff', 0.12), 1.15, 0.01, 0.6, x, 0.05, z);

    for (const [dx, dz, color] of [[-0.38, -0.12, '#e4c58f'], [-0.05, -0.16, '#5f7a3a'], [-0.36, 0.18, '#b8473d']]) {
        batch.cylinder('#2c2f4a', 0.13, 0.09, 0.08, x + dx, 0.06, z + dz, 8);
        batch.cylinder(color, 0.115, 0.115, 0.012, x + dx, 0.13, z + dz, 8);
    }
    for (let i = 0; i < 4; i++) {
        batch.cylinder('#e7c98a', 0.15, 0.15, 0.025, x + 0.2 + i * 0.09, 0.06 + i * 0.012, z + 0.02 + (random() - 0.5) * 0.1, 3, new Euler(0, i * 0.7 + random(), 0));
    }
    for (let i = 0; i < 6; i++) {
        batch.cylinder(i % 2 ? '#3f4a2a' : '#5f7a3a', 0.03, 0.03, 0.04, x - 0.1 + random() * 0.3, 0.06, z + 0.16 + random() * 0.1, 5);
    }
}

function bowl(batch, x, z, random, isOlives) {
    batch.cylinder('#2c2f4a', 0.4, 0.24, 0.22, x, 0, z, 10);
    batch.cylinder('#1c1e33', 0.37, 0.37, 0.01, x, 0.17, z, 10);
    for (let i = 0; i < 22; i++) {
        const angle = random() * Math.PI * 2;
        const reach = Math.sqrt(random()) * 0.3;
        const color = isOlives ? (random() > 0.5 ? '#5f7a3a' : '#33402a') : (random() > 0.3 ? '#a86b3c' : '#8a5230');
        batch.cylinder(color, 0.032, 0.02, isOlives ? 0.05 : 0.075, x + Math.cos(angle) * reach, 0.18 + random() * 0.04, z + Math.sin(angle) * reach, 5,
            new Euler(random() * 1.5, random() * 3, random() * 1.5));
    }
    if (isOlives) batch.cylinder('#56b07f', 0.0, 0.03, 0.2, x + 0.1, 0.24, z - 0.05, 4, new Euler(1.2, 0.4, 0));
}

function pastry(batch, x, z, random) {
    plate(batch, x, z, 0.54);
    for (const [dx, dz] of [[-0.2, -0.06], [0.04, -0.16], [0.24, 0.02], [-0.06, 0.16], [0.16, 0.24]]) {
        const turn = Math.PI / 4 + (random() - 0.5) * 0.5;
        batch.box('#b9772f', 0.17, 0.07, 0.17, x + dx, 0.04, z + dz, new Euler(0, turn, 0));
        batch.box('#e2a24a', 0.15, 0.015, 0.15, x + dx, 0.11, z + dz, new Euler(0, turn, 0));
        batch.cylinder('#6fae54', 0.025, 0.025, 0.012, x + dx, 0.125, z + dz, 5);
    }
}

// The cocktail glass for an item in a combo, picked by the seeded generator.
function anyCocktail(batch, x, z, tint, random) {
    [coupe, highball, tumbler, wineGlass][Math.floor(random() * 4)](batch, x, z, tint, random);
}

/**
 * @returns {{ group: Group, width: number, height: number }} the shot and how much room it needs
 */
export function buildMenuShot(item) {
    const random = seededRandom(`menu-${item.name}`);
    const tint = tintFor(item);
    const shape = shapeFor(item);
    const accent = random() > 0.5 ? COLORS.violet : COLORS.amber;
    const batch = new Batch();
    // How much room the subject needs. The camera moves in for small things.
    let width = 1.3;
    let height = 0.85;

    // The stage: a round table top and, behind it, the orbit ring of the site with its one dot.
    batch.at(0, 0, 0);
    batch.cylinder('#191a2b', 1.3, 1.22, 0.1, 0, -0.1, 0, 28);
    batch.cylinder(`glow:${accent}`, 1.315, 1.315, 0.016, 0, -0.07, 0, 28);

    const tilt = -0.25 + random() * 0.5;
    batch.add(new TorusGeometry(2.25, 0.009, 4, 72), '#3a3d57', 0, 1.05, -1.3, new Euler(0.35, 0, tilt));
    batch.add(new TorusGeometry(1.55, 0.007, 4, 60), '#2a2c41', 0, 1.05, -1.3, new Euler(0.35, 0, tilt));
    const dotAngle = random() * Math.PI * 2;
    batch.cylinder(`glow:${accent}`, 0.05, 0.05, 0.02, Math.cos(dotAngle) * 2.25 * Math.cos(tilt), 1.05 + Math.sin(dotAngle) * 2.1 * 0.94, -1.3, 8, new Euler(Math.PI / 2, 0, 0));
    for (let i = 0; i < 9; i++) {
        batch.box('glow:#c9cbdd', 0.014, 0.014, 0.014, (random() - 0.5) * 6, 0.3 + random() * 2.4, -2 - random() * 1.5);
    }

    // The item itself, turned a little differently for every item.
    batch.at(0, 0, 0.1, (random() - 0.5) * 0.9);

    switch (shape) {
        case 'hookah':
            hookah(batch, 0, 0, tint, random, 1.25);
            width = 2.4;
            height = 2.2;
            break;
        case 'hookah-tea':
            hookah(batch, -0.5, -0.1, tint, random, 1.15);
            teapot(batch, 0.55, 0.15, '#c9822e', random);
            cup(batch, 0.95, 0.6, '#c9822e', 0.8);
            width = 3;
            height = 2.1;
            break;
        case 'feast':
            hookah(batch, -0.85, -0.35, '#9b3fb8', random, 1.05);
            hookah(batch, 0.9, -0.4, '#7ed6a0', random, 1.05);
            board(batch, 0, 0.45, random);
            teapot(batch, 0.05, -0.45, '#c9822e', random);
            width = 3.5;
            height = 2;
            break;
        case 'two-cocktails':
            anyCocktail(batch, -0.4, 0, '#9b3fb8', random);
            anyCocktail(batch, 0.4, 0.05, '#f59a3c', random);
            width = 1.7;
            break;
        case 'teapot':
            teapot(batch, -0.2, -0.05, tint, random);
            cup(batch, 0.55, 0.35, tint, 0.85);
            width = 1.9;
            height = 0.7;
            break;
        case 'coffee':
            cup(batch, 0, 0, '#3b2416', 1.35);
            batch.cylinder('#7a8a55', 0.02, 0.035, 0.06, 0.34, 0.03, 0.2, 5, new Euler(1.2, 0.5, 0));
            width = 1.1;
            height = 0.5;
            break;
        case 'espresso':
            cup(batch, 0, 0, '#2a180e', 0.95, '#e9e4f0');
            batch.box('#b9772f', 0.12, 0.03, 0.07, 0.3, 0.025, 0.16, new Euler(0, 0.5, 0));
            width = 1;
            height = 0.45;
            break;
        case 'bottle':
            bottle(batch, -0.25, -0.05);
            tumbler(batch, 0.3, 0.2, '#dff3fb', random);
            width = 1.5;
            height = 0.95;
            break;
        case 'tumbler':
            tumbler(batch, 0, 0, tint, random);
            width = 1;
            height = 0.55;
            break;
        case 'wine':
            wineGlass(batch, 0, 0, tint, random);
            break;
        case 'coupe':
            coupe(batch, 0, 0, tint, random);
            break;
        case 'bowl':
            bowl(batch, 0, 0, random, /olive/.test(item.name.toLowerCase()));
            height = 0.55;
            break;
        case 'board':
            board(batch, 0, 0, random);
            width = 2;
            height = 0.5;
            break;
        case 'pastry':
            pastry(batch, 0, 0, random);
            width = 1.5;
            height = 0.5;
            break;
        case 'cheese':
            cheese(batch, 0, 0, random);
            width = 1.5;
            height = 0.5;
            break;
        default:
            highball(batch, 0, 0, tint, random, 0.5 + random() * 0.14);
    }

    const group = new Group();
    group.add(...batch.build());

    return { group, width, height, shape, tint };
}

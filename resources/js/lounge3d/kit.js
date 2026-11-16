// Shared building blocks for the low-poly lounge: units, palette, materials and a geometry batcher.
import {
    BoxGeometry,
    Color,
    CylinderGeometry,
    Matrix4,
    Mesh,
    MeshBasicMaterial,
    MeshPhongMaterial,
    Quaternion,
    Vector3,
} from 'three';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';

// The 2D plan is 1200 x 1050 reference pixels. One pixel is 2.5 cm, so the plan is 30 m x 26.25 m.
export const PLAN_WIDTH = 30;
export const PLAN_DEPTH = 26.25;
export const PX = 0.025;
export const STOREY = 3.4;

export const COLORS = {
    ground: '#07080f',
    slab: '#0d0e19',
    walkway: '#12131f',
    wall: '#1b1d2e',
    wood: '#3a2c2a',
    darkWood: '#241c1f',
    metal: '#8d90a8',
    gold: '#d9b25f',
    amber: '#f5b860',
    ember: '#ff7a3c',
    violet: '#b9a5ff',
    glass: '#9fd8e8',
    free: '#565a7c',
    pending: '#0ea5e9',
    active: '#10b981',
    inactive: '#2a2c3f',
    selected: '#b9a5ff',
};

// Percent of the plan -> metres, with the plan centred on the origin.
export const planX = (percent) => (percent / 100 - 0.5) * PLAN_WIDTH;
export const planZ = (percent) => (percent / 100 - 0.5) * PLAN_DEPTH;

export function mix(a, b, amount) {
    return `#${new Color(a).lerp(new Color(b), amount).getHexString()}`;
}

// Deterministic random numbers, so a scene looks the same every time it is built.
export function seededRandom(seed) {
    let state = 0;
    for (const char of String(seed)) state = (Math.imul(state, 31) + char.charCodeAt(0)) >>> 0;

    return () => {
        state = (state + 0x6d2b79f5) >>> 0;
        let t = state;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/**
 * Collects simple shapes by color and merges each color into one mesh, so a whole
 * floor costs a handful of draw calls. Colors prefixed with "glow:" do not react to light,
 * and colors prefixed with "glass:" are see-through.
 */
export class Batch {
    constructor() {
        this.groups = new Map();
        this.origin = new Matrix4();
    }

    // Everything added after this call is placed relative to the given position and rotation.
    at(x, y, z, rotationY = 0) {
        this.origin = new Matrix4().compose(
            new Vector3(x, y, z),
            new Quaternion().setFromAxisAngle(new Vector3(0, 1, 0), rotationY),
            new Vector3(1, 1, 1),
        );
        return this;
    }

    add(geometry, color, x, y, z, rotation = null) {
        const local = new Matrix4();
        if (rotation) local.makeRotationFromEuler(rotation);
        local.setPosition(x, y, z);

        geometry.applyMatrix4(this.origin.clone().multiply(local));
        if (!this.groups.has(color)) this.groups.set(color, []);
        this.groups.get(color).push(geometry.index ? geometry.toNonIndexed() : geometry);
        return this;
    }

    box(color, width, height, depth, x, y, z, rotation = null) {
        return this.add(new BoxGeometry(width, height, depth), color, x, y + height / 2, z, rotation);
    }

    cylinder(color, radiusTop, radiusBottom, height, x, y, z, sides = 8, rotation = null) {
        return this.add(new CylinderGeometry(radiusTop, radiusBottom, height, sides), color, x, y + height / 2, z, rotation);
    }

    build() {
        const meshes = [];

        for (const [color, geometries] of this.groups) {
            const [kind, hex] = color.includes(':') ? color.split(':') : ['solid', color];
            const material = kind === 'glow'
                ? new MeshBasicMaterial({ color: hex })
                : kind === 'glass'
                    ? new MeshPhongMaterial({ color: hex, flatShading: true, shininess: 90, transparent: true, opacity: 0.34, depthWrite: false })
                    : new MeshPhongMaterial({ color: hex, flatShading: true, shininess: 12, specular: '#1c1c2a' });

            const merged = mergeGeometries(geometries, false);
            geometries.forEach((geometry) => geometry.dispose());
            meshes.push(new Mesh(merged, material));
        }

        this.groups.clear();
        return meshes;
    }
}

export function disposeObject(object) {
    object.traverse((child) => {
        child.geometry?.dispose();
        const materials = Array.isArray(child.material) ? child.material : [child.material];
        materials.forEach((material) => {
            material?.map?.dispose();
            material?.dispose();
        });
    });
}

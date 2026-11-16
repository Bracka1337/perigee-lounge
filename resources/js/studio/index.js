// Render studio: draws one scene from the data the server put on the page, then sets
// window.__renderReady so the headless browser knows it can take the picture.
//
// Scenes: a room or a table of the lounge from a fixed camera angle, or a menu product shot.
// Nothing moves and all randomness is seeded, so a re-render gives the same image.
import { Color, DirectionalLight, HemisphereLight, PerspectiveCamera, PointLight, Scene, Vector3, WebGLRenderer } from 'three';
import { COLORS } from '../lounge3d/kit.js';
import { addLoungeLights, buildLounge } from '../lounge3d/scene.js';
import { buildMenuShot } from './menu.js';

function loungeScene(scene, camera, data) {
    const lounge = buildLounge(data.layout, { labels: false });
    scene.add(lounge.root);

    const table = data.scene === 'table' ? lounge.tables.get(data.focus) : null;
    const floor = lounge.floors.find((candidate) => (table
        ? candidate.id === table.floorId
        : candidate.rooms.some((room) => room.id === data.focus)));
    if (!floor) throw new Error(`Nothing to render for ${data.scene} ${data.focus}`);

    // Only the floor in the picture is drawn, so nothing from above blocks the view.
    lounge.floors.forEach((other) => { other.group.visible = other === floor; });
    addLoungeLights(scene, floor.y);

    let target;
    let distance;
    let direction;

    if (table) {
        lounge.paint(table.id, true);
        target = new Vector3(table.position.x, floor.y + 0.5, table.position.z);
        distance = Math.max(table.size.width, table.size.depth) * 1.15 + 4.4;
        direction = new Vector3(table.position.x > 0 ? -0.55 : 0.55, 0.62, 0.8);
    } else {
        const box = floor.rooms.find((room) => room.id === data.focus).box;
        target = new Vector3(box.cx, floor.y + 0.3, box.cz);
        distance = Math.max(box.width, box.depth) * 0.95 + 6.5;
        direction = new Vector3(box.cx > 0 ? -0.45 : 0.45, 0.8, 0.85);
    }

    // A warm lamp over the subject lifts it out of the dark room.
    const lamp = new PointLight('#ffd9a8', 70, 16, 1.5);
    lamp.position.copy(target).add(new Vector3(0, 3.4, 1.2));
    scene.add(lamp);

    camera.fov = 34;
    camera.position.copy(target).addScaledVector(direction.normalize(), distance);
    camera.lookAt(target);
}

function menuScene(scene, camera, data) {
    const shot = buildMenuShot(data.item);
    scene.add(shot.group);

    scene.add(new HemisphereLight('#aab0ff', '#15101f', 0.9));

    const key = new DirectionalLight('#ffe6c4', 1.7);
    key.position.set(-2.5, 4.5, 3.5);
    scene.add(key);

    const rim = new PointLight(COLORS.violet, 45, 12, 1.6);
    rim.position.set(2.4, 1.9, -1.8);
    const warm = new PointLight(COLORS.amber, 30, 12, 1.6);
    warm.position.set(-2.6, 1.3, 1.2);
    scene.add(rim, warm);

    // Pull back for wide or tall subjects so they always fit the 4:3 frame.
    const distance = Math.max(2.1, shot.width * 1.55, shot.height * 2.35);
    const target = new Vector3(0, shot.height * 0.44, 0);

    camera.fov = 30;
    camera.position.set(0, target.y + distance * 0.42, distance);
    camera.lookAt(target);
}

function render() {
    const data = JSON.parse(document.getElementById('studioData').textContent);
    const canvas = document.getElementById('studioCanvas');

    const renderer = new WebGLRenderer({ canvas, antialias: true, preserveDrawingBuffer: true });
    renderer.setPixelRatio(1);
    renderer.setSize(window.innerWidth, window.innerHeight, false);

    const scene = new Scene();
    scene.background = new Color(COLORS.ground);

    const camera = new PerspectiveCamera(34, window.innerWidth / window.innerHeight, 0.1, 300);

    if (data.scene === 'menu') menuScene(scene, camera, data);
    else loungeScene(scene, camera, data);

    camera.updateProjectionMatrix();
    renderer.render(scene, camera);

    // One more frame, so the picture is on screen before the browser is told to capture it.
    requestAnimationFrame(() => {
        renderer.render(scene, camera);
        window.__renderReady = true;
    });
}

try {
    render();
} catch (error) {
    window.__renderError = String(error?.message ?? error);
    throw error;
}

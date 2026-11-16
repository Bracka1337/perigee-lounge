// Interactive 3D view of the lounge: orbit and zoom, a floor cutaway, and tables that can be picked.
import {
    Color,
    Mesh,
    MeshBasicMaterial,
    PerspectiveCamera,
    PlaneGeometry,
    Raycaster,
    Scene,
    Vector2,
    Vector3,
    WebGLRenderer,
} from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { COLORS, PLAN_DEPTH, PLAN_WIDTH, STOREY } from './kit.js';
import { addLoungeLights, buildLounge } from './scene.js';

export function supportsWebGL() {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
}

/**
 * @param {HTMLElement} container  the viewer fills it and follows its size
 * @param {object} layout          see scene.js
 * @param {object} options         { floorId, selectedId, onSelect(tableId), canSelect(tableId) }
 */
export function createLoungeViewer(container, layout, options = {}) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canSelect = options.canSelect ?? ((id) => lounge.tables.get(id)?.status === 'free');

    const renderer = new WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.domElement.className = 'block h-full w-full touch-none';
    container.append(renderer.domElement);

    const scene = new Scene();
    scene.background = new Color(COLORS.ground);

    const camera = new PerspectiveCamera(36, 1, 0.5, 300);
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = false;
    controls.minDistance = 9;
    controls.maxDistance = 75;
    controls.maxPolarAngle = Math.PI * 0.44;
    controls.zoomSpeed = 0.9;

    const lights = addLoungeLights(scene);

    // Dark glass under the open floor: the floors below stay visible, but dimmed.
    const dimmer = new Mesh(
        new PlaneGeometry(PLAN_WIDTH * 4, PLAN_DEPTH * 4),
        new MeshBasicMaterial({ color: COLORS.ground, transparent: true, opacity: 0.72, depthWrite: false }),
    );
    dimmer.rotation.x = -Math.PI / 2;
    scene.add(dimmer);

    let lounge = buildLounge(layout);
    scene.add(lounge.root);

    let activeFloor = lounge.floors.find((floor) => String(floor.id) === String(options.floorId)) ?? lounge.floors[0];
    let selectedId = options.selectedId ?? null;
    let hoveredId = null;
    let frameRequest = 0;
    let tween = null;
    let disposed = false;

    const render = () => {
        frameRequest = 0;
        if (disposed) return;

        if (tween) {
            const progress = Math.min(1, (performance.now() - tween.start) / tween.duration);
            const eased = 1 - (1 - progress) ** 3;
            const y = tween.from + (tween.to - tween.from) * eased;
            const shift = y - controls.target.y;
            controls.target.y = y;
            camera.position.y += shift;
            if (progress >= 1) tween = null;
            else requestRender();
        }

        controls.update();
        renderer.render(scene, camera);
    };

    // Frames are drawn only when something changed: the scene is static otherwise.
    function requestRender() {
        if (!frameRequest && !disposed) frameRequest = requestAnimationFrame(render);
    }

    function applyFloor(animate) {
        for (const floor of lounge.floors) {
            floor.group.visible = floor.index <= activeFloor.index;
            floor.labelGroup.visible = floor === activeFloor;
        }

        dimmer.visible = activeFloor.index > 0;
        dimmer.position.y = activeFloor.y - 0.32;
        lights.forEach((light) => { if (light.userData.followsFloor) light.position.y = activeFloor.y + 4.2; });

        const to = activeFloor.y;
        if (animate && !reducedMotion) {
            tween = { from: controls.target.y, to, start: performance.now(), duration: 520 };
        } else {
            camera.position.y += to - controls.target.y;
            controls.target.y = to;
        }
        requestRender();
    }

    function frameCamera() {
        const aspect = container.clientWidth / Math.max(1, container.clientHeight);
        // Far enough to see the whole plan, a little further on narrow containers.
        const distance = 41 * Math.max(1, 1.5 / aspect);
        const direction = new Vector3(0, 0.82, 0.57).normalize();

        controls.target.set(0, activeFloor.y, 0.6);
        camera.position.copy(controls.target).addScaledVector(direction, distance);
    }

    function resize() {
        const width = container.clientWidth;
        const height = container.clientHeight;
        if (!width || !height) return;

        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        requestRender();
    }

    function repaint() {
        lounge.tables.forEach((table, id) => lounge.paint(id, id === selectedId));
        if (hoveredId !== null && hoveredId !== selectedId) lounge.tables.get(hoveredId)?.frame.scale.setY(2.5);
        requestRender();
    }

    // --- Picking -----------------------------------------------------------------------------
    const raycaster = new Raycaster();
    const pointer = new Vector2();
    let pressedAt = null;

    function tableAt(event) {
        const bounds = renderer.domElement.getBoundingClientRect();
        pointer.set(((event.clientX - bounds.left) / bounds.width) * 2 - 1, -((event.clientY - bounds.top) / bounds.height) * 2 + 1);
        raycaster.setFromCamera(pointer, camera);

        const hit = raycaster.intersectObjects(activeFloor.picks, false)[0];
        return hit ? hit.object.userData.tableId : null;
    }

    const onPointerDown = (event) => { pressedAt = { x: event.clientX, y: event.clientY }; };

    const onPointerUp = (event) => {
        // A drag orbits the camera. Only a press and release in one place picks a table.
        if (!pressedAt || Math.hypot(event.clientX - pressedAt.x, event.clientY - pressedAt.y) > 6) return;

        const id = tableAt(event);
        if (id !== null && canSelect(id)) options.onSelect?.(id);
    };

    const onPointerMove = (event) => {
        if (event.buttons !== 0) return;

        const id = tableAt(event);
        const next = id !== null && canSelect(id) ? id : null;
        if (next === hoveredId) return;

        hoveredId = next;
        renderer.domElement.style.cursor = next === null ? 'grab' : 'pointer';
        repaint();
    };

    renderer.domElement.addEventListener('pointerdown', onPointerDown);
    renderer.domElement.addEventListener('pointerup', onPointerUp);
    renderer.domElement.addEventListener('pointermove', onPointerMove);
    renderer.domElement.style.cursor = 'grab';
    controls.addEventListener('change', requestRender);

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);

    resize();
    frameCamera();
    applyFloor(false);
    repaint();

    return {
        setFloor(id) {
            const floor = lounge.floors.find((candidate) => String(candidate.id) === String(id));
            if (!floor || floor === activeFloor) return;
            activeFloor = floor;
            hoveredId = null;
            applyFloor(true);
        },

        setSelected(id) {
            selectedId = id;
            repaint();
        },

        // Replaces the whole lounge, for example while rooms are being edited.
        rebuild(nextLayout) {
            const floorId = activeFloor.id;
            scene.remove(lounge.root);
            lounge.dispose();

            lounge = buildLounge(nextLayout);
            scene.add(lounge.root);
            activeFloor = lounge.floors.find((floor) => String(floor.id) === String(floorId)) ?? lounge.floors[0];
            hoveredId = null;
            applyFloor(false);
            repaint();
        },

        dispose() {
            disposed = true;
            cancelAnimationFrame(frameRequest);
            resizeObserver.disconnect();
            renderer.domElement.removeEventListener('pointerdown', onPointerDown);
            renderer.domElement.removeEventListener('pointerup', onPointerUp);
            renderer.domElement.removeEventListener('pointermove', onPointerMove);
            controls.removeEventListener('change', requestRender);
            controls.dispose();
            lounge.dispose();
            dimmer.geometry.dispose();
            dimmer.material.dispose();
            renderer.dispose();
            renderer.domElement.remove();
        },
    };
}

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
import { COLORS, STOREY } from './kit.js';
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
 * @param {object} options         { floorId, selectedId, onSelect(tableId), canSelect(tableId),
 *                                   onSelectRoom(roomId), canSelectRoom(roomId), describeTable(tableId),
 *                                   describeRoom(roomId), onFloorChange(floorId), labels }
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
    controls.maxDistance = Math.max(75, Math.max(layout.frame.width, layout.frame.depth) * 3);
    controls.maxPolarAngle = Math.PI * 0.44;
    controls.zoomSpeed = 0.9;

    const lights = addLoungeLights(scene);
    let daylight = false;

    // Evening or day: a button in the corner of the view.
    const dayButton = document.createElement('button');
    dayButton.type = 'button';
    dayButton.className = 'lounge-daylight-button';
    dayButton.setAttribute('aria-pressed', 'false');
    const dayLabels = options.labels ?? { day: 'Day', evening: 'Evening' };
    const showDayButton = () => {
        dayButton.textContent = daylight ? `☾ ${dayLabels.evening}` : `☀ ${dayLabels.day}`;
        dayButton.setAttribute('aria-pressed', String(daylight));
    };
    showDayButton();
    dayButton.addEventListener('click', () => {
        daylight = !daylight;
        scene.background.set(daylight ? '#2b3550' : COLORS.ground);
        lights.setDaylight(daylight);
        showDayButton();
        requestRender();
    });
    container.append(dayButton);

    const text = { wholeFloor: 'Whole floor', ...(options.labels ?? {}) };

    // Floor switcher and "whole floor" inside the view, so nobody has to scroll to change floors.
    const bar = document.createElement('div');
    bar.className = 'lounge-viewer-bar';
    const floorButtons = layout.floors.length > 1 ? layout.floors.map((floor) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'lounge-viewer-button';
        button.dataset.floor = String(floor.id);
        button.textContent = floor.name;
        button.addEventListener('click', () => {
            api.setFloor(floor.id);
            options.onFloorChange?.(floor.id);
        });
        bar.append(button);
        return button;
    }) : [];
    const overviewButton = document.createElement('button');
    overviewButton.type = 'button';
    overviewButton.className = 'lounge-viewer-button';
    overviewButton.textContent = text.wholeFloor;
    overviewButton.addEventListener('click', () => overview());
    bar.append(overviewButton);
    container.append(bar);

    const tooltip = document.createElement('div');
    tooltip.className = 'lounge-tooltip';
    tooltip.hidden = true;
    tooltip.setAttribute('role', 'status');
    container.append(tooltip);

    // Dark glass under the open floor: the floors below stay visible, but dimmed.
    const dimmer = new Mesh(
        new PlaneGeometry(layout.frame.width * 4 + 40, layout.frame.depth * 4 + 40),
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
            controls.target.lerpVectors(tween.fromTarget, tween.toTarget, eased);
            camera.position.lerpVectors(tween.fromPosition, tween.toPosition, eased);
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
        lights.setSpots(activeFloor.lights, activeFloor.y);

        const target = controls.target.clone().setY(activeFloor.y);
        flyTo(target, camera.position.clone().add(target.clone().sub(controls.target)), animate);
        floorButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.floor === String(activeFloor.id))));
    }

    // Moves the camera to look at a point, keeping the current viewing angle.
    function flyTo(target, position, animate = true) {
        if (!animate || reducedMotion) {
            tween = null;
            controls.target.copy(target);
            camera.position.copy(position);
        } else {
            tween = { fromTarget: controls.target.clone(), toTarget: target, fromPosition: camera.position.clone(), toPosition: position, start: performance.now(), duration: 600 };
        }
        requestRender();
    }

    // Looks at a spot of the open floor from closer by, from the same side as now.
    function focusOn(x, z, size) {
        const target = new Vector3(x, activeFloor.y, z);
        const direction = camera.position.clone().sub(controls.target).normalize();
        const distance = clampDistance(Math.max(size * 1.6, 10));
        flyTo(target, target.clone().addScaledVector(direction, distance));
    }

    function clampDistance(distance) {
        return Math.min(controls.maxDistance, Math.max(controls.minDistance, distance));
    }

    // Back to the whole floor, as when the view opened.
    function overview() {
        const aspect = container.clientWidth / Math.max(1, container.clientHeight);
        const size = Math.max(layout.frame.width, layout.frame.depth * 1.15);
        const distance = size * 1.4 * Math.max(1, 1.5 / aspect) + 4;
        const direction = new Vector3(0, 0.82, 0.57).normalize();
        const target = new Vector3(0, activeFloor.y, layout.frame.depth * 0.02);
        flyTo(target, target.clone().addScaledVector(direction, distance));
    }

    function frameCamera() {
        const aspect = container.clientWidth / Math.max(1, container.clientHeight);
        // Far enough to see the whole building, a little further on narrow containers.
        const size = Math.max(layout.frame.width, layout.frame.depth * 1.15);
        const distance = size * 1.4 * Math.max(1, 1.5 / aspect) + 4;
        const direction = new Vector3(0, 0.82, 0.57).normalize();

        controls.target.set(0, activeFloor.y, layout.frame.depth * 0.02);
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

    function roomAt() {
        // Uses the ray of the last tableAt() call.
        const hit = raycaster.intersectObjects(activeFloor.roomPicks, false)[0];
        return hit ? hit.object.userData.roomId : null;
    }

    function showTooltip(event, message) {
        if (!message) {
            tooltip.hidden = true;
            return;
        }
        const bounds = container.getBoundingClientRect();
        tooltip.textContent = message;
        tooltip.hidden = false;
        tooltip.style.left = `${Math.min(event.clientX - bounds.left + 14, bounds.width - tooltip.offsetWidth - 8)}px`;
        tooltip.style.top = `${Math.max(8, event.clientY - bounds.top - tooltip.offsetHeight - 10)}px`;
    }

    const onPointerDown = (event) => { pressedAt = { x: event.clientX, y: event.clientY }; };

    const onPointerUp = (event) => {
        // A drag orbits the camera. Only a press and release in one place picks a table.
        if (!pressedAt || Math.hypot(event.clientX - pressedAt.x, event.clientY - pressedAt.y) > 6) return;

        const id = tableAt(event);
        if (id !== null) {
            const table = lounge.tables.get(id);
            if (table) focusOn(table.position.x, table.position.z, Math.max(table.size.width, table.size.depth) * 2.4);
            if (canSelect(id)) options.onSelect?.(id);
            return;
        }

        // A click on a room flies to it, and picks it when the whole room can be booked.
        const roomId = roomAt();
        const room = activeFloor.rooms.find((candidate) => candidate.id === roomId);
        if (room) {
            focusOn(room.box.cx, room.box.cz, Math.max(room.box.width, room.box.depth));
            if (options.canSelectRoom?.(roomId)) options.onSelectRoom?.(roomId);
        }
    };

    const onPointerMove = (event) => {
        if (event.buttons !== 0) return;

        const id = tableAt(event);
        const roomId = id === null ? roomAt() : null;
        showTooltip(event, id !== null ? (options.describeTable?.(id) ?? lounge.tables.get(id)?.name)
            : roomId !== null ? (options.describeRoom?.(roomId) ?? activeFloor.rooms.find((room) => room.id === roomId)?.name) : null);

        const next = id !== null && canSelect(id) ? id : null;
        const pointer = next !== null || (roomId !== null && options.canSelectRoom?.(roomId));
        renderer.domElement.style.cursor = pointer ? 'pointer' : 'grab';
        if (next === hoveredId) return;

        hoveredId = next;
        repaint();
    };

    const onPointerLeave = () => { tooltip.hidden = true; };
    const onKeyDown = (event) => { if (event.key === 'Escape') overview(); };
    renderer.domElement.addEventListener('pointerleave', onPointerLeave);
    container.addEventListener('keydown', onKeyDown);
    renderer.domElement.tabIndex = 0;
    renderer.domElement.setAttribute('aria-label', text.canvas ?? 'Lounge 3D view');
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

    const api = {
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
            renderer.domElement.removeEventListener('pointerleave', onPointerLeave);
            container.removeEventListener('keydown', onKeyDown);
            renderer.domElement.remove();
            dayButton.remove();
            bar.remove();
            tooltip.remove();
        },
    };

    return api;
}

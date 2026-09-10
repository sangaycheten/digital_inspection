/**
 * Roof-zone polygon editor on Google Maps.
 *
 * Replaces the Leaflet.draw editor in admin/master/buildings/index.blade.php.
 * google.maps.drawing.DrawingManager was removed from the Maps JS API
 * (deprecated Aug 2025, removed in v3.65.3b, Jun 2026), so drawing is
 * hand-rolled on google.maps.Polygon from the core `maps` library. That has no
 * third-party dependency and getPath().getArray() yields [lat, lng] pairs --
 * exactly the shape already persisted in buildings.roof_zones, so stored data
 * is byte-compatible with zones drawn under Leaflet.
 *
 * Editor state is keyed 'create' | 'edit<buildingId>', unchanged from before.
 *
 * Globals exposed for the inline onclick handlers in the Blade view:
 *   initZoneMap, setZoneColor, startDraw, finishDraw, cancelDraw,
 *   removeZone, clearAllZones, serializeZones, loadExistingZones, centerMap
 */
(function () {
    'use strict';

    const DEFAULT_LAT  = 27.4716;
    const DEFAULT_LNG  = 89.6386;
    const DEFAULT_ZOOM = 19;
    const FILL_OPACITY = 0.25;

    const zoneEditors = {};
    window.zoneEditors = zoneEditors; // the Blade view inspects this

    function getEditor(key) {
        if (!zoneEditors[key]) {
            zoneEditors[key] = {
                map: null,
                ready: null,
                zones: [],          // { name, color, polygon: [[lat,lng]...], shape }
                activeColor: '#ef4444',
                draw: null,         // in-progress drawing state
            };
        }
        return zoneEditors[key];
    }

    function polyOptions(color, editable) {
        return {
            strokeColor:   color,
            strokeWeight:  2,
            strokeOpacity: 1,
            fillColor:     color,
            fillOpacity:   FILL_OPACITY,
            editable:      !!editable,
            clickable:     false,
        };
    }

    /** Read a google.maps.Polygon back into the stored [[lat, lng], ...] format. */
    function pathToLatLngs(shape) {
        return shape.getPath().getArray().map(function (p) {
            return [p.lat(), p.lng()];
        });
    }

    /** Accept both [lat,lng] arrays and {lat,lng} objects (defensive, as before). */
    function toLatLngLiterals(polygon) {
        return polygon.map(function (p) {
            return Array.isArray(p) ? { lat: Number(p[0]), lng: Number(p[1]) } : { lat: Number(p.lat), lng: Number(p.lng) };
        });
    }

    // -- Map initialisation --
    function initZoneMap(key, mapElId, lat, lng) {
        const ed = getEditor(key);
        if (ed.ready) return ed.ready;

        const el = document.getElementById(mapElId);
        if (!el) return null;

        const hasCoords = lat && lng;
        const center = {
            lat: hasCoords ? Number(lat) : DEFAULT_LAT,
            lng: hasCoords ? Number(lng) : DEFAULT_LNG,
        };

        ed.ready = (async function () {
            const mapsLib = await google.maps.importLibrary('maps');

            // Google's own satellite imagery replaces the Esri World Imagery
            // tile layer, and the built-in mapTypeControl replaces the manual
            // Leaflet street/satellite layer switcher.
            const map = new mapsLib.Map(el, {
                center: center,
                zoom: hasCoords ? DEFAULT_ZOOM : 13,
                mapId:             (window.APP_MAPS && window.APP_MAPS.mapId) || undefined,
                mapTypeId:         (window.APP_MAPS && window.APP_MAPS.defaultMapTypeId) || 'hybrid',
                mapTypeControl:    true,
                streetViewControl: false,
                fullscreenControl: true,
                tilt: 0,
            });

            ed.map = map;

            // Rehydrate any zones queued before the map finished loading.
            if (ed._pendingZones) {
                const pending = ed._pendingZones;
                ed._pendingZones = null;
                loadExistingZones(key, pending);
            }
            return map;
        })();

        return ed.ready;
    }

    function centerMap(key, siteId) {
        const ed = getEditor(key);
        if (!ed.ready) return;
        const coords = (window.siteCoords || {})[siteId];
        if (!coords || !coords.lat || !coords.lng) return;
        Promise.resolve(ed.ready).then(function (map) {
            if (map) {
                map.setCenter({ lat: Number(coords.lat), lng: Number(coords.lng) });
                map.setZoom(DEFAULT_ZOOM);
            }
        });
    }

    // -- Colour selection --
    function setZoneColor(key, el) {
        const ed = getEditor(key);
        ed.activeColor = el.dataset.color;
        el.closest('.d-flex').querySelectorAll('.color-swatch').forEach(function (s) {
            s.classList.remove('active');
        });
        el.classList.add('active');

        // Recolour an in-progress polygon so the swatch change is visible.
        if (ed.draw && ed.draw.shape) {
            ed.draw.shape.setOptions(polyOptions(ed.activeColor, true));
        }
    }

    // -- Drawing --
    function setDrawingUi(key, active) {
        const scope = document.getElementById(key === 'create' ? 'createZoneWrap' : 'editZoneWrap' + key.replace('edit', ''));
        if (!scope) return;
        const drawBtn   = scope.querySelector('[data-role="draw-zone"]');
        const finishBtn = scope.querySelector('[data-role="finish-zone"]');
        const hint      = scope.querySelector('[data-role="draw-hint"]');
        if (drawBtn)   drawBtn.disabled = active;
        if (finishBtn) finishBtn.classList.toggle('d-none', !active);
        if (hint)      hint.classList.toggle('d-none', !active);
    }

    function startDraw(key) {
        const ed = getEditor(key);
        if (!ed.map) {
            alert('Please select a site first to load the map.');
            return;
        }
        if (ed.draw) return; // already drawing

        const map = ed.map;
        const color = ed.activeColor;

        const state = { points: [], shape: null, listeners: [] };
        ed.draw = state;

        // Double-click finishes the ring, so suppress the zoom it would trigger.
        map.setOptions({ disableDoubleClickZoom: true });
        setDrawingUi(key, true);

        function redraw() {
            if (state.shape) state.shape.setMap(null);
            state.shape = new google.maps.Polygon(
                Object.assign({ map: map, paths: state.points }, polyOptions(color, true))
            );
        }

        state.listeners.push(map.addListener('click', function (e) {
            state.points.push({ lat: e.latLng.lat(), lng: e.latLng.lng() });
            redraw();
        }));

        state.listeners.push(map.addListener('dblclick', function () {
            finishDraw(key);
        }));

        state.escHandler = function (e) {
            if (e.key === 'Escape') cancelDraw(key);
        };
        document.addEventListener('keydown', state.escHandler);
    }

    function teardownDraw(key) {
        const ed = getEditor(key);
        const state = ed.draw;
        if (!state) return null;

        state.listeners.forEach(function (l) { l.remove(); });
        if (state.escHandler) document.removeEventListener('keydown', state.escHandler);
        if (ed.map) ed.map.setOptions({ disableDoubleClickZoom: false });
        ed.draw = null;
        setDrawingUi(key, false);
        return state;
    }

    function getZoneNameEl(key) {
        if (key === 'create') return document.getElementById('createZoneName');
        return document.getElementById('editZoneName' + key.replace('edit', ''));
    }

    function finishDraw(key) {
        const ed = getEditor(key);
        if (!ed.draw) return;

        // A polygon needs at least three vertices.
        if (ed.draw.points.length < 3) {
            alert('A zone needs at least 3 points. Keep clicking on the map, or press Escape to cancel.');
            return;
        }

        const state = teardownDraw(key);
        const shape = state.shape;

        // Freeze the finished shape and read its vertices back as [lat, lng].
        shape.setOptions(polyOptions(ed.activeColor, false));

        const nameEl   = getZoneNameEl(key);
        const zoneName = nameEl ? nameEl.value.trim() : '';

        ed.zones.push({
            name:    zoneName || ('Zone ' + (ed.zones.length + 1)),
            color:   ed.activeColor,
            polygon: pathToLatLngs(shape),
            shape:   shape,
        });

        if (nameEl) nameEl.value = '';
        renderZoneList(key);
    }

    function cancelDraw(key) {
        const state = teardownDraw(key);
        if (state && state.shape) state.shape.setMap(null);
    }

    // -- Zone list --
    function renderZoneList(key) {
        const ed = getEditor(key);
        const listId = key === 'create' ? 'createZoneList' : 'editZoneList' + key.replace('edit', '');
        const listEl = document.getElementById(listId);
        if (!listEl) return;

        listEl.innerHTML = '';
        ed.zones.forEach(function (zone, i) {
            const badge = document.createElement('span');
            badge.className = 'zone-badge';
            badge.style.background = zone.color;

            const icon = document.createElement('i');
            icon.className = 'ri-map-2-line';
            badge.appendChild(icon);
            badge.appendChild(document.createTextNode(zone.name));

            const close = document.createElement('i');
            close.className = 'ri-close-line';
            close.addEventListener('click', function () { removeZone(key, i); });
            badge.appendChild(close);

            listEl.appendChild(badge);
        });
    }

    function removeZone(key, index) {
        const ed = getEditor(key);
        const zone = ed.zones[index];
        if (!zone) return;
        if (zone.shape) zone.shape.setMap(null);
        ed.zones.splice(index, 1);
        renderZoneList(key);
    }

    function clearAllZones(key) {
        const ed = getEditor(key);
        cancelDraw(key);
        ed.zones.forEach(function (z) { if (z.shape) z.shape.setMap(null); });
        ed.zones = [];
        renderZoneList(key);
    }

    // -- Rehydrate saved zones into the edit map --
    function loadExistingZones(key, zones) {
        const ed = getEditor(key);
        if (!zones || !zones.length) return;

        if (!ed.map) { ed._pendingZones = zones; return; }

        zones.forEach(function (zone) {
            if (!zone.polygon || !zone.polygon.length) return;
            const color = zone.color || '#3b82f6';
            const shape = new google.maps.Polygon(
                Object.assign({ map: ed.map, paths: toLatLngLiterals(zone.polygon) }, polyOptions(color, false))
            );
            ed.zones.push({ name: zone.name, color: color, polygon: zone.polygon, shape: shape });
        });
        renderZoneList(key);
    }

    // -- Serialise to the hidden input before submit --
    function serializeZones(key, inputId) {
        const ed = getEditor(key);
        const input = document.getElementById(inputId);
        if (!input) return;
        const data = ed.zones.map(function (z) {
            return { name: z.name, color: z.color, polygon: z.polygon };
        });
        input.value = data.length ? JSON.stringify(data) : '';
    }

    // Expose for the inline handlers in the Blade view.
    window.initZoneMap       = initZoneMap;
    window.centerMap         = centerMap;
    window.setZoneColor      = setZoneColor;
    window.startDraw         = startDraw;
    window.finishDraw        = finishDraw;
    window.cancelDraw        = cancelDraw;
    window.removeZone        = removeZone;
    window.clearAllZones     = clearAllZones;
    window.serializeZones    = serializeZones;
    window.loadExistingZones = loadExistingZones;
    window.renderZoneList    = renderZoneList;
    window.getZoneEditor     = getEditor;
})();

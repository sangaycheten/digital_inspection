/**
 * Shared Google Maps location picker.
 *
 * Replaces the Leaflet + Nominatim pickers previously duplicated in
 * admin/master/sites/index.blade.php and admin/master/clients/index.blade.php.
 *
 * Requires partials/google-maps.blade.php to have run first (sets window.APP_MAPS).
 *
 * Public API:
 *   GMapPicker.init(opts)  -> Promise<slot>
 *   GMapPicker.get(key)    -> slot | undefined   (sync; slot.ready is the promise)
 *   GMapPicker.placePinOn(key, lat, lng, skipReverseGeocode)
 *   GMapPicker.attachAutocomplete(inputEl)
 *
 * Timezone detection stays on Open-Meteo: Google's Time Zone API has no CORS
 * support and would need a server-side proxy for no functional gain.
 */
window.GMapPicker = (function () {
    'use strict';

    const DEFAULT_ZOOM  = 19;
    const FALLBACK_ZOOM = 6;

    // key (map container DOM id) -> { ready, map, placePin, setCenter }
    const registry = {};

    // -- Timezone (Open-Meteo, unchanged from the Leaflet implementation) --
    function fetchTimezone(lat, lng, inputEl, displayEl, loadingEl) {
        if (!inputEl || !displayEl) return;
        if (loadingEl) loadingEl.style.display = '';
        displayEl.className = 'badge bg-secondary-subtle text-secondary fs-12';

        fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lng + '&timezone=auto&forecast_days=0')
            .then(r => r.json())
            .then(data => {
                const tz = data.timezone || 'UTC';
                inputEl.value         = tz;
                displayEl.textContent = tz;
                displayEl.className   = 'badge bg-success-subtle text-success fs-12';
            })
            .catch(() => {
                displayEl.className = 'badge bg-warning-subtle text-warning fs-12';
            })
            .finally(() => { if (loadingEl) loadingEl.style.display = 'none'; });
    }

    // -- Reverse geocoding (coords -> address) via the Geocoding API --
    let geocoder = null;

    async function reverseGeocode(lat, lng, addressEl) {
        if (!addressEl) return;
        try {
            if (!geocoder) {
                const geoLib = await google.maps.importLibrary('geocoding');
                geocoder = new geoLib.Geocoder();
            }
            // NB: the Geocoder response is legacy snake_case, unlike the
            // camelCase Place class used by autocomplete below.
            const res = await geocoder.geocode({ location: { lat: Number(lat), lng: Number(lng) } });
            if (res && res.results && res.results.length) {
                addressEl.value = res.results[0].formatted_address;
            }
        } catch (e) {
            // Leave the address untouched if geocoding fails.
        }
    }

    // -- Map + pin --
    async function init(opts) {
        const key = opts.mapId;
        if (registry[key]) return registry[key].ready;

        const el = document.getElementById(key);
        if (!el) return null;

        const latEl = opts.latEl;
        const lngEl = opts.lngEl;

        const parsedLat = parseFloat(latEl && latEl.value);
        const parsedLng = parseFloat(lngEl && lngEl.value);
        const hasCoords = !isNaN(parsedLat) && !isNaN(parsedLng) && (parsedLat !== 0 || parsedLng !== 0);

        const fallback = opts.defaultCenter || { lat: 27.4716, lng: 89.6386 };
        const center   = hasCoords ? { lat: parsedLat, lng: parsedLng } : fallback;
        const zoom     = hasCoords ? DEFAULT_ZOOM : (opts.defaultZoom || FALLBACK_ZOOM);

        // Reserve the slot synchronously so a second modal-open cannot race a
        // duplicate map into the same container while importLibrary is pending.
        const slot = {};
        registry[key] = slot;

        slot.ready = (async function () {
            const mapsLib   = await google.maps.importLibrary('maps');
            const markerLib = await google.maps.importLibrary('marker');

            const map = new mapsLib.Map(el, {
                center: center,
                zoom: zoom,
                mapId:             (window.APP_MAPS && window.APP_MAPS.mapId) || undefined,
                mapTypeId:         (window.APP_MAPS && window.APP_MAPS.defaultMapTypeId) || 'hybrid',
                mapTypeControl:    true,
                streetViewControl: false,
                fullscreenControl: true,
                tilt: 0,
            });

            let marker = null;

            function ensureMarker(position) {
                if (marker) { marker.position = position; return marker; }
                marker = new markerLib.AdvancedMarkerElement({ map: map, position: position, gmpDraggable: true });
                marker.addListener('dragend', function (e) {
                    placePin(e.latLng.lat(), e.latLng.lng());
                });
                return marker;
            }

            function placePin(la, lo, skipReverseGeocode) {
                const position = { lat: Number(la), lng: Number(lo) };
                ensureMarker(position);
                map.setCenter(position);
                if (map.getZoom() < DEFAULT_ZOOM) map.setZoom(DEFAULT_ZOOM);

                slot.hasRealCenter = true;

                if (latEl) latEl.value = position.lat.toFixed(7);
                if (lngEl) lngEl.value = position.lng.toFixed(7);

                fetchTimezone(position.lat, position.lng, opts.tzInputEl, opts.tzDisplayEl, opts.tzLoadingEl);
                if (!skipReverseGeocode) reverseGeocode(position.lat, position.lng, opts.addressEl);
            }

            if (hasCoords) ensureMarker(center);

            // Only a user-placed or saved coordinate should bias autocomplete.
            // The fallback centre is arbitrary and would skew results.
            slot.hasRealCenter = hasCoords;

            map.addListener('click', function (e) {
                placePin(e.latLng.lat(), e.latLng.lng());
            });

            // Enter in either coordinate field jumps the pin there.
            [latEl, lngEl].forEach(function (field) {
                if (!field) return;
                field.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    const la = parseFloat(latEl.value);
                    const lo = parseFloat(lngEl.value);
                    if (!isNaN(la) && !isNaN(lo)) placePin(la, lo);
                });
            });

            slot.map      = map;
            slot.placePin = placePin;
            // Google Maps auto-resizes its container, but does not re-centre --
            // callers use this when re-showing a modal that already has a map.
            slot.setCenter = function () {
                const la = parseFloat(latEl && latEl.value);
                const lo = parseFloat(lngEl && lngEl.value);
                map.setCenter(!isNaN(la) && !isNaN(lo) ? { lat: la, lng: lo } : center);
            };
            return slot;
        })();

        return slot.ready;
    }

    function get(key) {
        return registry[key];
    }

    /** Place the pin on a map that may still be initialising. */
    function placePinOn(key, lat, lng, skipReverseGeocode) {
        const slot = registry[key];
        if (!slot) return;
        Promise.resolve(slot.ready).then(function (s) {
            if (s && s.placePin) s.placePin(lat, lng, skipReverseGeocode);
        });
    }

    // -- Address autocomplete (Places API New) --
    // Deliberately uses the AutocompleteSuggestion *data* API rather than the
    // PlaceAutocompleteElement widget, so the existing themed <ul> markup,
    // CSS and keyboard navigation carry over unchanged.
    function debounce(fn, delay) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    function hideSuggestions(ulEl) {
        ulEl.innerHTML = '';
        ulEl.style.display = 'none';
    }

    function attachAutocomplete(inputEl) {
        const ulEl = document.getElementById(inputEl.dataset.suggestions);
        const key  = inputEl.dataset.mapId;
        if (!ulEl) return;

        // data-region-codes="au" restricts suggestions to Australia.
        // Comma-separated CLDR region codes, max 15; omit for worldwide results.
        const regionCodes = (inputEl.dataset.regionCodes || '')
            .split(',')
            .map(function (c) { return c.trim().toLowerCase(); })
            .filter(Boolean);

        let sessionToken = null;
        let placesLib    = null;

        async function places() {
            if (!placesLib) placesLib = await google.maps.importLibrary('places');
            return placesLib;
        }

        function render(suggestions) {
            ulEl.innerHTML = '';

            const usable = suggestions.filter(function (s) { return s.placePrediction; });
            if (!usable.length) {
                const li = document.createElement('li');
                li.className   = 'suggestion-searching text-muted';
                li.textContent = 'No results found.';
                ulEl.appendChild(li);
                ulEl.style.display = 'block';
                return;
            }

            usable.forEach(function (s) {
                const prediction = s.placePrediction;
                // .text is a FormattableText object, not a plain string.
                const label = prediction.text.toString();

                const li = document.createElement('li');
                li.className = 'suggestion-item';
                li.innerHTML = '<i class="ri-map-pin-line me-2 text-primary"></i>';
                li.appendChild(document.createTextNode(label));

                li.addEventListener('mousedown', async function (e) {
                    e.preventDefault();
                    inputEl.value = label;
                    hideSuggestions(ulEl);
                    try {
                        const place = prediction.toPlace();
                        // fetchFields() closes the billing session for this token.
                        await place.fetchFields({ fields: ['location', 'formattedAddress'] });
                        if (place.formattedAddress) inputEl.value = place.formattedAddress;
                        if (place.location) {
                            placePinOn(key, place.location.lat(), place.location.lng(), true);
                        }
                    } catch (err) {
                        // keep the typed text; nothing else to do
                    } finally {
                        sessionToken = null; // a new session starts on the next keystroke
                    }
                });

                ulEl.appendChild(li);
            });
            ulEl.style.display = 'block';
        }

        const doSearch = debounce(async function () {
            const q = inputEl.value.trim();
            if (q.length < 3) { hideSuggestions(ulEl); return; }

            ulEl.innerHTML = '<li class="suggestion-searching"><i class="ri-loader-4-line me-1"></i>Searching...</li>';
            ulEl.style.display = 'block';

            try {
                const lib = await places();
                if (!sessionToken) sessionToken = new lib.AutocompleteSessionToken();

                const request = { input: q, sessionToken: sessionToken };

                if (regionCodes.length) request.includedRegionCodes = regionCodes;

                // Bias toward the map's current view, but only once it holds a
                // real coordinate -- biasing from the fallback centre would
                // fight an active region restriction.
                const slot = registry[key];
                if (slot && slot.map && slot.hasRealCenter) {
                    const c = slot.map.getCenter();
                    if (c) request.locationBias = { center: { lat: c.lat(), lng: c.lng() }, radius: 50000 };
                }

                const res = await lib.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
                render(res.suggestions || []);
            } catch (err) {
                hideSuggestions(ulEl);
            }
        }, 400);

        inputEl.addEventListener('input', doSearch);

        // Keyboard navigation inside the dropdown (unchanged behaviour).
        inputEl.addEventListener('keydown', function (e) {
            const items  = ulEl.querySelectorAll('.suggestion-item');
            const active = ulEl.querySelector('.suggestion-item.active');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!active) { items[0].classList.add('active'); }
                else {
                    active.classList.remove('active');
                    (active.nextElementSibling || items[0]).classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (active) {
                    active.classList.remove('active');
                    (active.previousElementSibling || items[items.length - 1]).classList.add('active');
                }
            } else if (e.key === 'Enter') {
                const sel = ulEl.querySelector('.suggestion-item.active');
                if (sel) { e.preventDefault(); sel.dispatchEvent(new MouseEvent('mousedown')); }
            } else if (e.key === 'Escape') {
                hideSuggestions(ulEl);
            }
        });

        inputEl.addEventListener('blur', function () {
            setTimeout(function () { hideSuggestions(ulEl); }, 150);
        });
    }

    return {
        init: init,
        get: get,
        placePinOn: placePinOn,
        attachAutocomplete: attachAutocomplete,
        registry: registry,
    };
})();

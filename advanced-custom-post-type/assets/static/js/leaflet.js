var $ = jQuery.noConflict();

const DEFAULT_LAT = 51.505;
const DEFAULT_LNG = -0.09;
const DEFAULT_CITY = 'London';
const DEFAULT_COUNTRY = 'United Kingdom';
const ADDRESS_MULTI_STRING_SEPARATOR = '___###___';

window.onload = function () {

    /**
     * ===================================================================
     * COMMON FUNCTIONS
     * ===================================================================
     */

    /**
     *
     * @param map
     * @param lat
     * @param lng
     */
    const panTo = (map, lat, lng) => {
        map.panTo(new L.LatLng(lat, lng));
    };

    /**
     * Check if map is initialized
     *
     * @param el
     * @return {boolean}
     */
    const mapIsInitialized = (el) => {

        if (typeof L === 'object') {
            var container = L.DomUtil.get(el);

            return container && container['_leaflet_id'] != null;
        }

        return false;
    };

    /**
     * Extract city from address object
     *
     * @param address
     * @return {*}
     */
    const extractCity = (address) => {

        if(address.city){
            return address.city;
        }

        if(address.town){
            return address.town;
        }

        return address.county;
    };

    /**
     * Extract country from address object
     *
     * @param address
     * @return {*}
     */
    const extractCountry = (address) => {

        if(address.country){
            return address.country;
        }

        return null;
    };

    /**
     *
     * @param address
     * @return {Promise<any>}
     */
    const fetchAddress = async(address) => {

        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${address}&format=json`,
            {
                headers: {
                    'User-Agent': 'advanced-custom-post-type'
                }
            }
        );

        return await response.json();
    };

    /**
     *
     * @param lat
     * @param lng
     * @return {Promise<any>}
     */
    const fetchCoordinates = async(lat, lng) => {

        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
            {
                headers: {
                    'User-Agent': 'advanced-custom-post-type'
                }
            }
        );

        return await response.json();
    };

    /**
     * Add search control to the map
     *
     * @param map
     * @return {*}
     */
    const addSearchControl = (map) => {
        const search = new GeoSearch.GeoSearchControl({
            provider: new GeoSearch.OpenStreetMapProvider(),
            style: 'bar',
            keepResult: false,
            searchLabel: 'Type the address or point a location on the map'
        });

        map.addControl(search);

        return search;
    };

    /**
     *
     * @param mapPreviewId
     * @param mapPreview
     * @param defaultLat
     * @param defaultLng
     * @return {*}
     */
    const initTheMap = (mapPreviewId, mapPreview, defaultLat, defaultLng) => {

        const map = L.map(mapPreviewId).setView([defaultLat, defaultLng], 17);
        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        mapPreview.classList.remove('loading');

        setInterval(function() {
            map.invalidateSize();
        }, 2000);

        return map;
    };

    /**
     *
     * @param mapPreview
     * @return {{searchBoxCity: string, searchInput: *, selections: *, lngInput: *, countryInput: *, searchBoxLng: string, cityInput: *, searchBoxLat: string, mapPreviewId: string | null, latInput: *, searchBoxId: string}}
     */
    const extractMapElements = (mapPreview) => {

        const mapPreviewId = mapPreview.getAttribute('id');
        const searchBoxId = mapPreviewId.slice(0, -4);
        const searchBoxLat = searchBoxId + "_lat";
        const searchBoxLng = searchBoxId + "_lng";
        const searchBoxCity = searchBoxId + "_city";
        const searchBoxCountry = searchBoxId + "_country";
        const selectionsId = searchBoxId + "_selections";

        const searchInput = document.getElementById(searchBoxId);
        const latInput = document.getElementById(searchBoxLat);
        const lngInput = document.getElementById(searchBoxLng);
        const cityInput = document.getElementById(searchBoxCity);
        const countryInput = document.getElementById(searchBoxCountry);
        const selections = document.getElementById(selectionsId);

        return {
            mapPreviewId,
            searchBoxId,
            searchBoxLat,
            searchBoxLng,
            searchBoxCity,
            searchInput,
            selections,
            latInput,
            lngInput,
            cityInput,
            countryInput
        };
    };

    /**
     * ===================================================================
     * RUN ADDRESS FIELD
     * ===================================================================
     */

    /**
     * Run the single address field
     */
    async function runSingleAddressField() {

        // check leaflet is initialized
        if (typeof L === 'object') {

            const mapPreviews = document.getElementsByClassName("acpt_map_preview");

            for (let i = 0; i < mapPreviews.length; i++) {

                const mapPreview = mapPreviews.item(i);
                const {
                    mapPreviewId,
                    searchInput,
                    latInput,
                    lngInput,
                    cityInput,
                    countryInput
                } = extractMapElements(mapPreview);

                let defaultLat = latInput.value ? latInput.value : DEFAULT_LAT;
                let defaultLng = lngInput.value ? lngInput.value : DEFAULT_LNG;
                let defaultCity = cityInput.value ? cityInput.value : DEFAULT_CITY;
                let defaultCountry = countryInput.value ? countryInput.value : DEFAULT_COUNTRY;

                // User is manually typing an address
                searchInput.addEventListener("input", function () {
                    latInput.value = '';
                    lngInput.value = '';
                    cityInput.value = '';
                    countryInput.value = '';
                });

                if (!mapIsInitialized(mapPreviewId)) {

                    // if there is a default value but not the coordinates, fetch fetch the coordinates from the address
                    if (searchInput.value && !latInput.value && !lngInput.value) {

                        const fAddress = await fetchAddress(searchInput.value);

                        if (fAddress.length > 0) {
                            defaultLat = fAddress[0].lat;
                            defaultLng = fAddress[0].lng;
                        }
                    }

                    const map = initTheMap(mapPreviewId, mapPreview, defaultLat, defaultLng);

                    let marker = null;
                    marker = L.marker([defaultLat, defaultLng]).addTo(map);
                    marker.bindPopup(searchInput.value).openPopup();

                    const search = addSearchControl(map);

                    /**
                     * Search handler
                     * @param result
                     */
                    async function searchEventHandler(result) {

                        if (marker !== null) {
                            map.removeLayer(marker);
                        }

                        const fCoordinates = await fetchCoordinates(result.location.y, result.location.x);

                        searchInput.value = (search.searchElement && search.searchElement.input) ? search.searchElement.input.value : result.location.label;
                        latInput.value = result.location.y;
                        lngInput.value = result.location.x;
                        cityInput.value = extractCity(fCoordinates.address);
                        countryInput.value = extractCountry(fCoordinates.address);

                        result.marker.bindPopup(searchInput.value).openPopup();
                    }

                    /**
                     * On click event handler
                     * @param event
                     */
                    async function onClickEventHandler(event) {
                        if (event.type === 'click') {

                            // remove any other marker first
                            let index = 0;
                            map.eachLayer(function (layer) {
                                if (index !== 0) {
                                    map.removeLayer(layer);
                                }

                                index++;
                            });

                            const coordinates = [event.latlng.lat, event.latlng.lng];
                            const fCoordinates = await fetchCoordinates(coordinates[0], coordinates[1]);

                            searchInput.value = fCoordinates.display_name;
                            latInput.value = coordinates[0];
                            lngInput.value = coordinates[1];
                            cityInput.value = extractCity(fCoordinates.address);
                            countryInput.value = extractCountry(fCoordinates.address);

                            if (marker !== null) {
                                map.removeLayer(marker);
                            }

                            marker = L.marker(coordinates).addTo(map);
                            marker.bindPopup(fCoordinates.display_name).openPopup();
                            panTo(map, event.latlng.lat, event.latlng.lng);
                        }
                    }

                    map.on('geosearch/showlocation', searchEventHandler);
                    map.on('click', onClickEventHandler);

                    /**
                     * Reset all items on the map
                     */
                    document.addEventListener("acpt-reset-map", function (e) {
                        if (e.detail.fieldId === mapPreviewId) {
                            console.log(`Resetting map ${mapPreviewId}...`);

                            // remove any other marker first
                            let index = 0;
                            map.eachLayer(function (layer) {
                                if (index !== 0) {
                                    map.removeLayer(layer);
                                }

                                index++;
                            });

                            panTo(map, DEFAULT_LAT, DEFAULT_LNG);
                            marker = L.marker([DEFAULT_LAT, DEFAULT_LNG]).addTo(map);
                        }
                    });
                }
            }
        }
    }

    /**
     * ===================================================================
     * RUN ADDRESS MULTI FIELD
     * ===================================================================
     */

    /**
     *
     * @param value
     * @param input
     */
    const addValueToAnInput = (value, input) => {

        const savedValues = input.value;
        const savedValuesArray = savedValues ? savedValues.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];

        savedValuesArray.push(value);

        input.value = savedValuesArray.join(ADDRESS_MULTI_STRING_SEPARATOR);
    };

    /**
     *
     * @param index
     * @param input
     */
    const deleteValueFromAnInput = (index, input) => {

        const savedValues = input.value;
        const savedValuesArray = savedValues ? savedValues.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];

        if(savedValuesArray[index]){
            savedValuesArray.splice(index, 1);
        }

        input.value = savedValuesArray.join(ADDRESS_MULTI_STRING_SEPARATOR);
    };

    /**
     * Add item to the map
     *
     * @param lat
     * @param lng
     * @param marker
     * @param map
     * @param selections
     * @param searchInput
     * @param latInput
     * @param lngInput
     * @param cityInput
     * @param countryInput
     *
     * @return {Promise<void>}
     */
    const addItemToMap = async (
        lat,
        lng,
        marker,
        map,
        selections,
        searchInput,
        latInput,
        lngInput,
        cityInput,
        countryInput
    ) => {

        const coordinates = [lat, lng];
        const fCoordinates = await fetchCoordinates(lat, lng);
        const city = extractCity(fCoordinates.address);
        const country = extractCountry(fCoordinates.address);

        addValueToAnInput(fCoordinates.display_name, searchInput);
        addValueToAnInput(lat, latInput);
        addValueToAnInput(lng, lngInput);
        addValueToAnInput(city, cityInput);
        addValueToAnInput(country, countryInput);

        // add item to map and fly to
        marker = L.marker(coordinates).addTo(map);
        marker.bindPopup(fCoordinates.display_name).openPopup();
        panTo(map, lat, lng);

        // add item to selection sidebar
        addItemToSelections(selections, fCoordinates.display_name, selections.children.length, lat, lng);
    };

    /**
     * Add item to selection
     * (used by addItemToMap)
     *
     * @param selections
     * @param display_name
     * @param index
     * @param lat
     * @param lng
     */
    const addItemToSelections = (selections, display_name, index, lat, lng) => {
        selections.innerHTML += `
            <div class="selection" data-index="${index}" data-lat="${lat}" data-lng="${lng}">
                <span class="acpt_map_multi_selection">
                    ${display_name}
                </span>
                <a class="acpt_map_delete_multi_selection button button-danger">-</a>
            </div>
        `;
    };

    /**
     * Run the multi address field
     */
    async function runMultiAddressField()
    {
        // check leaflet is initialized
        if (typeof L === 'object') {

            const mapPreviews = document.getElementsByClassName("acpt_map_multi_preview");

            for (let i = 0; i < mapPreviews.length; i++) {

                const mapPreview = mapPreviews.item(i);
                const {
                    mapPreviewId,
                    selections,
                    searchInput,
                    latInput,
                    lngInput,
                    cityInput,
                    countryInput
                } = extractMapElements(mapPreview);

                let defaultValues = searchInput.value ? searchInput.value.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];
                let defaultLatValues = latInput.value ? latInput.value.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];
                let defaultLngValues = lngInput.value ? lngInput.value.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];
                let defaultCityValues = cityInput.value ? cityInput.value.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];
                let defaultCountryValues = countryInput.value ? countryInput.value.split(ADDRESS_MULTI_STRING_SEPARATOR) : [];

                let defaultLat = defaultLatValues.length > 0 ? defaultLatValues[defaultLatValues.length - 1] : DEFAULT_LAT;
                let defaultLng = defaultLngValues.length > 0 ? defaultLngValues[defaultLngValues.length - 1] : DEFAULT_LNG;
                let defaultCity = defaultCityValues.length > 0 ? defaultCityValues[defaultCityValues.length - 1] : DEFAULT_CITY;
                let defaultCountry = defaultCountryValues.length > 0 ? defaultCountryValues[defaultCountryValues.length - 1] : DEFAULT_COUNTRY;

                if (!mapIsInitialized(mapPreviewId)) {

                    // init the map
                    const map = initTheMap(mapPreviewId, mapPreview, defaultLat, defaultLng);

                    let marker = null;

                    // add saved values
                    if(defaultValues.length > 0){
                        defaultValues.map((value, index) => {
                            marker = L.marker([defaultLatValues[index], defaultLngValues[index]]).addTo(map);
                            marker.bindPopup(value).openPopup();
                        });
                    }

                    // add search control input
                    const search = addSearchControl(map);

                    /**
                     * Add found address to the list
                     * @param result
                     * @return {Promise<void>}
                     */
                    async function searchEventHandler(result)
                    {
                        await addItemToMap(
                            result.location.y,
                            result.location.x,
                            marker,
                            map,
                            selections,
                            searchInput,
                            latInput,
                            lngInput,
                            cityInput,
                            countryInput
                        );
                    }

                    /**
                     * Add click address to the list
                     * @param event
                     * @return {Promise<void>}
                     */
                    async function onClickEventHandler(event)
                    {
                        await addItemToMap(
                            event.latlng.lat,
                            event.latlng.lng,
                            marker,
                            map,
                            selections,
                            searchInput,
                            latInput,
                            lngInput,
                            cityInput,
                            countryInput
                        );
                    }

                    map.on('geosearch/showlocation', searchEventHandler);
                    map.on('click', onClickEventHandler);

                    /**
                     * Click on selection items and fly to the corresponding point on the map
                     */
                    $('body').on('click', '.acpt_map_multi_selection', function(e) {
                        const $this = $(this);
                        const lat = $this.parent().data("lat");
                        const lng = $this.parent().data("lng");
                        const parentElement = $this.parent();
                        const list = $this.parent().parent();
                        const parentId = list.attr("id").replace("_selections", ""); // id_443775_selections
                        const mapId = map.boxZoom._container.id.replace("_map", ""); // id_443775_map

                        list.find(".selection").each((i, el) => {
                            el.classList.remove("active");
                        });

                        parentElement.addClass("active");

                        if(lat && lng && (parentId === mapId)){
                            map.panTo(new L.LatLng(lat, lng));
                            map.eachLayer(function (layer) {
                                if(layer._latlng && layer._latlng.lat === lat && layer._latlng.lng === lng){
                                    if(layer.isPopupOpen() === false){
                                        layer.bindPopup($this.text()).openPopup();
                                    }
                                }
                            });
                        }
                    });

                    /**
                     * Delete an item
                     */
                    $('body').on('click', '.acpt_map_delete_multi_selection', function(e) {
                        const $this = $(this);
                        const lat = $this.parent().data("lat");
                        const lng = $this.parent().data("lng");
                        const index = $this.parent().data("index");

                        map.eachLayer(function (layer) {

                            if(layer._latlng && layer._latlng.lat === lat && layer._latlng.lng === lng){

                                // remove from values
                                deleteValueFromAnInput(index, searchInput);
                                deleteValueFromAnInput(index, latInput);
                                deleteValueFromAnInput(index, lngInput);
                                deleteValueFromAnInput(index, cityInput);
                                deleteValueFromAnInput(index, countryInput);

                                // remove from map
                                map.removeLayer(layer);

                                // remove from sidebar
                                $this.parent().remove();
                            }
                        });
                    });
                }
            }
        }
    }

    /**
     * For address fields inside a repeater
     */
    document.addEventListener("acpt_grouped_element_added", (e) => {
        runSingleAddressField();
        runMultiAddressField();
    });

    /**
     * For address fields inside a flexible block
     */
    document.addEventListener("acpt_flexible_element_added", (e) => {
        runSingleAddressField();
        runMultiAddressField();
    });

    runSingleAddressField();
    runMultiAddressField();
};
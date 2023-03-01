import AjaxRequest from './ajax-request';

class MondialRelayFancyListAdapter
{
    constructor(wrapper, selectedPointWrapper, defaultUrl, autocompleteUrl, autocompleteWrapperSelector, searchResultsSelector) {
        this.wrapper = wrapper;
        this.selectedPointWrapper = selectedPointWrapper;
        this.defaultUrl = defaultUrl;
        this.autocompleteUrl = autocompleteUrl;
        this.autocompleteWrapperSelector = autocompleteWrapperSelector;
        this.searchResultsSelector = searchResultsSelector;
        this.map = null;
        this.markers = {};
        this.itemsPerPage = 6;
        this.currentPage = 1;
        this.currentPickupPointId = null;
        this.debounce = (function () {
            return function (func, delay = 300) {
                let timeout;

                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        func.apply(this, args);
                    }, delay);
                }
            }
        })();

        document.getElementById('modal-mondial-relay').addEventListener('click', function (event) {
            let listItem = this.getResultListItemFromClick(event.target);
            if (listItem) {
                let id = listItem.getAttribute('data-relay-point-id');
                this.highlightPickupPoint(listItem.parentNode, listItem.getAttribute('data-relay-point-id'));
                if ("undefined" !== typeof(this.markers[id])) {
                    this.currentPickupPointId = id;
                    this.map.setCenter(this.markers[id].getPosition());
                }

                return;
            }

            if (event.target.getAttribute('data-autocomplete-item')) {
                return this.onSelectAutocompleteItem(event.target);
            }
        }.bind(this));

        document.getElementById('modal-mondial-relay').addEventListener('keyup', function (event) {
            if (event.target.getAttribute('data-mr-autocomplete')) {
                this.debounce(function () {
                    this.autocomplete();
                }.bind(this), 100)();
            }
        }.bind(this));

        document.getElementById('mr-select-btn').addEventListener('click', function() {
          let el = document.querySelector('.relay-point-list-item.highlight');
          if (null !== el) {
            this.onSelectRelayPoint(el.getAttribute('data-relay-point-id'));
          }
        }.bind(this));
    }

    onSelectShippingMethod() {
        let request = new AjaxRequest(this.defaultUrl, 'GET');
        let selectedPointWrapper = document.getElementById(this.selectedPointWrapper);

        request.send().then(function (response) {
            let jsonResponse = JSON.parse(response);
            selectedPointWrapper.innerHTML = jsonResponse.current;

            this.currentPickupPointId = jsonResponse.currentPointId;
            document.querySelector('.smr-pickup-point-id').value = this.currentPickupPointId;

            this.wrapper.innerHTML = JSON.parse(response).address;
            this.wrapper.style.display = 'block';

            let event = new CustomEvent('relay_point_panel_ready');
            document.dispatchEvent(event);
        }.bind(this));
    }

    onUnselectShippingMethod() {
        this.wrapper.style.display = 'none';
        this.wrapper.innerHTML = '';
    }

    onShowSearchPanel() {
        document.querySelector('.pickup-points-map').style.display = 'block';

        let autocompleteWrapper = this.wrapper.querySelector(this.autocompleteWrapperSelector),
            autocompleteInput = autocompleteWrapper.querySelector('input[type="text"]');

        autocompleteInput.removeEventListener('blur', this.onBlurAutocompleteInput);
        autocompleteInput.addEventListener('blur', this.onBlurAutocompleteInput.bind(this));
    }

    onHideSearchPanel() {
        let autocompleteWrapper = this.wrapper.querySelector(this.autocompleteWrapperSelector),
            autocompleteInput = autocompleteWrapper.querySelector('input[type="text"]');

        autocompleteInput.removeEventListener('blur', this.onBlurAutocompleteInput);
    }

    onSearchStart() {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector);

        resultWrapper.querySelector('.pickup-points-no-results').style.display = 'none';
        resultWrapper.querySelector('.pickup-points-results-list').style.display = 'flex';
        resultWrapper.querySelector('.pickup-points-pagination').style.display = 'flex';
        resultWrapper.querySelector('.pickup-points-results-list').innerHTML = '';
        resultWrapper.querySelector('.pickup-points-pagination').innerHTML = '';
        this.markers = {};
    }

    onSearchResultsChange(results) {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector);
        this.currentPage = 1;
        this.markers = {};

        if (0 === results.length) {
            resultWrapper.querySelector('.pickup-points-results-list').innerHTML = '';
            resultWrapper.querySelector('.pickup-points-pagination').innerHTML = '';
            resultWrapper.querySelector('.pickup-points-results-list').style.display = 'none';
            resultWrapper.querySelector('.pickup-points-pagination').style.display = 'none';
            resultWrapper.querySelector('.pickup-points-no-results').style.display = 'block';

            return;
        }

        this.map = new google.maps.Map(document.querySelector('.pickup-points-map'), {
            center: { lat: -34.397, lng: 150.644 },
            zoom: 13,
            disableDefaultUI: true,
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_TOP,
            },
        });

        let resultsList = document.createElement('ul'),
            chooseLabel = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-choose-label'),
            mrLogo = this.getMarkerIcon(),
            mrLogoSelected = this.getSelectedMarkerIcon();

        for (let i = 0; i < results.length; i++) {
            let li = document.createElement('li'),
                card = document.createElement('div'),
                name = document.createElement('p'),
                address = document.createElement('p'),
                addressComplement = document.createElement('p'),
                businessHours = document.createElement('div'),
                table = document.createElement('table'),
                complement = [],
                liClasses = ['relay-point-list-item'];

            name.setAttribute('class', 'pickup-point-name');
            name.textContent = results[i].label;

            address.setAttribute('class', 'pickup-point-address');
            address.textContent = results[i].address;

            if (results[i].zipCode) {
                complement.push(results[i].zipCode);
            }

            if (results[i].city) {
                complement.push(results[i].city);
            }

            if (complement.length > 0) {
                addressComplement.setAttribute('class', 'pickup-point-address');
                addressComplement.textContent = complement.join(' ');
            }

            if (!this.pointIsOpen(results[i].businessHours)) {
                liClasses.push('close');
            }

            results[i].businessHours.forEach((item) => {
                this.generateBusinessHoursTable(table, item);
            });

            businessHours.appendChild(table);
            businessHours.classList.add('point-business-hours');

            card.setAttribute('class', 'pickup-point-card');
            card.appendChild(name);
            card.appendChild(address);
            if (complement.length > 0) {
                card.appendChild(addressComplement);
            }
            card.appendChild(businessHours);

            li.setAttribute('class', liClasses.join(' '));
            li.setAttribute('data-relay-point-id', results[i].id);
            li.appendChild(card);

            resultsList.appendChild(li);

            let marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(results[i].lat),
                    lng: parseFloat(results[i].lng),
                },
                title: results[i].label,
                data: {
                    id: results[i].id,
                    label: results[i].label,
                    address: results[i].address,
                    ctaLabel: chooseLabel
                },
                icon: results[i].id === this.currentPickupPointId ? mrLogoSelected : mrLogo,
            });
            this.markers[results[i].id] = marker;

            marker.addListener('click', function () {
                this.highlightPickupPoint(resultsList, results[i].id);
                marker.getMap().setCenter(marker.getPosition());

                let el = document.querySelector('.relay-point-list-item.highlight');
                $('#modal-mondial-relay .accordion').accordion('open', i);
                el.scrollIntoView();
            }.bind(this));
        }

        if (null !== this.currentPickupPointId) {
            this.highlightPickupPoint(resultsList, this.currentPickupPointId);
        }

        resultWrapper.querySelector('.pickup-points-results-list').appendChild(resultsList);

        this.setPagination();
        this.showMarkers();
        $('#modal-mondial-relay').modal('refresh');

        $('#modal-mondial-relay .accordion').accordion({
            selector: {
                title: '.pickup-point-card',
                trigger: '.pickup-point-card',
                content: '.point-business-hours'
            }
        });
    }

    showMarkers() {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector),
            resultsList = resultWrapper.querySelector('.pickup-points-results-list'),
            items = resultsList.querySelectorAll('.relay-point-list-item'),
            bounds = new google.maps.LatLngBounds(),
            count = 0;

        for (let i = 0; i < items.length; i++) {
            let id = items[i].getAttribute('data-relay-point-id'),
                marker = this.markers[id] || null;

            if (!this.isOffsetVisible(i) || !marker) {
                if (marker) {
                    marker.setMap(null);
                }

                continue;
            }

            marker.setMap(this.map);
            bounds.extend(marker.getPosition());
            count++;
        }

        if (count > 0) {
            this.map.fitBounds(bounds);
        }
    }

    setPagination() {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector),
            resultsList = resultWrapper.querySelector('.pickup-points-results-list'),
            paginationList = resultWrapper.querySelector('.pickup-points-pagination'),
            itemsCount = resultsList.querySelectorAll('.relay-point-list-item').length,
            pagesCount = Math.ceil(itemsCount / this.itemsPerPage);

        resultWrapper.classList.remove('no-pagination');
        paginationList.innerHTML = '';

        if (1 >= pagesCount) {
            resultWrapper.classList.add('no-pagination');

            return;
        }

        for (let i = 0; i < pagesCount; i++) {
            let page = i + 1,
                li = document.createElement('li'),
                btn = document.createElement('button'),
                pageLabel = document.createTextNode(page.toString());

            btn.setAttribute('type', 'button');
            btn.setAttribute('data-page', page.toString());
            btn.appendChild(pageLabel);
            li.appendChild(btn);

            paginationList.appendChild(li);

            btn.addEventListener('click', function () {
                return this.showPage(i + 1);
            }.bind(this));
        }

        this.showPage(1);
    }

    showPage(page) {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector),
            resultsList = resultWrapper.querySelector('.pickup-points-results-list'),
            paginationList = resultWrapper.querySelector('.pickup-points-pagination'),
            resultItems = resultsList.querySelectorAll('.relay-point-list-item'),
            pageItems = paginationList.querySelectorAll('li');

        this.currentPage = page;

        for (let i = 0; i < resultItems.length; i++) {
            if (this.isOffsetVisible(i)) {
                resultItems[i].style.display = 'list-item';
            } else {
                resultItems[i].style.display = 'none';
            }
        }

        for (let i = 0; i < pageItems.length; i++) {
            pageItems[i].classList.remove('current');

            if (i + 1 === page) {
                pageItems[i].classList.add('current');
            }
        }

        this.showMarkers();
    }

    isOffsetVisible(index) {
        let offset = (this.currentPage - 1) * this.itemsPerPage + 1;

        return index + 1 >= offset && index + 1 < offset + this.itemsPerPage;
    }

    highlightPickupPoint(list, id) {
        let items = list.querySelectorAll('li'),
            listItem = list.querySelector('li[data-relay-point-id="' + id + '"]'),
            wrapper = list,
            marker = this.markers[id];

        while (wrapper !== null && !wrapper.classList.contains('pickup-points-results-list')) {
            wrapper = wrapper.parentNode;
        }

        if (wrapper) {
            let target = 0;
            for (let i = 0; i < items.length; i++) {
                if (items[i] === listItem) {
                    break;
                }
                target += items[i].clientHeight;
            }

            wrapper.scrollTop = target;
        }

        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('highlight');
        }

        listItem.classList.add('highlight');

        if (null !== this.currentPickupPointId && "undefined" !== typeof(this.markers[this.currentPickupPointId])) {
            this.markers[this.currentPickupPointId].setIcon(this.getMarkerIcon());
        }

        this.currentPickupPointId = id;
        marker.setIcon(this.getSelectedMarkerIcon());
    }

    getResultListItemFromClick(target) {
        let item = target;

        while(item !== null) {
            if (item.classList && item.classList.contains('relay-point-list-item')) {
                return item;
            }

            item = item.parentNode;
        }

        return null;
    }

    onSelectRelayPoint(id) {
        let event = new CustomEvent('select_relay_point', { detail: id });
        document.dispatchEvent(event);
    }

    generateBusinessHoursTable(table, data) {
        let row = table.insertRow();
        let cell = row.insertCell();
        let text = document.createTextNode(data.label);

        cell.appendChild(text);

        if ("undefined" === typeof(data.slots) || 0 >= data.slots.length) {
            cell = row.insertCell();
            cell.appendChild(document.createTextNode(' - '));
            cell.colSpan = 2;

            return;
        }

        if ("undefined" === typeof(data.slots[1])) {
            let isAfternoonSlot = parseInt(data.slots[0].from.substring(0, 2)) >= 12;

            if (isAfternoonSlot) {
                cell = row.insertCell();
                cell.appendChild(document.createTextNode(' - '));
            }

            cell = row.insertCell();
            cell.appendChild(document.createTextNode(data.slots[0].from + ' - ' + data.slots[0].to));

            if (!isAfternoonSlot) {
                cell = row.insertCell();
                cell.appendChild(document.createTextNode(' - '));
            }

            return;
        }

        cell = row.insertCell();
        cell.appendChild(document.createTextNode(data.slots[0].from + ' - ' + data.slots[0].to));
        cell = row.insertCell();
        cell.appendChild(document.createTextNode(data.slots[1].from + ' - ' + data.slots[1].to));
    }

    pointIsOpen(businessHours) {
        let now = new Date();

        for (let i = 0; i < businessHours.length; i++) {
            if (now.getDay() !== businessHours[i].day) {
                continue;
            }

            for (let j = 0; j < businessHours[i].slots.length; j++) {
                let start = new Date(),
                    end = new Date(),
                    from = businessHours[i].slots[j].from,
                    to = businessHours[i].slots[j].to;

                start.setHours(parseInt(from.substring(0, 2)), parseInt(from.substring(from.length - 2, from.length)));
                end.setHours(parseInt(to.substring(0, 2)), parseInt(to.substring(to.length - 2, to.length)));

                if (now.getTime() > start.getTime() && now.getTime() < end.getTime()) {
                    return true;
                }
            }
        }

        return false;
    }

    getMarkerIcon() {
        return this.wrapper.querySelector(this.searchResultsSelector)
          .querySelector('.pickup-points-results-list')
          .getAttribute('data-marker');
    }

    getSelectedMarkerIcon() {
        return this.wrapper.querySelector(this.searchResultsSelector)
          .querySelector('.pickup-points-results-list')
          .getAttribute('data-marker-selected');
    }

    autocomplete() {
        let autocompleteWrapper = this.wrapper.querySelector(this.autocompleteWrapperSelector),
            queryInput = autocompleteWrapper.querySelector('input[type="text"]'),
            suggestionInput = autocompleteWrapper.querySelector('input[type="hidden"]'),
            resultsList = autocompleteWrapper.querySelector('ul'),
            request = new AjaxRequest(this.autocompleteUrl, 'GET', {
            query: queryInput.value,
        });

        resultsList.style.display = 'none';
        suggestionInput.value = '';

        request.send().then(function (response) {
            let places = JSON.parse(response);

            resultsList.innerHTML = '';

            for (let i = 0; i < places.length; i++) {
                let li = document.createElement('li')
                li.setAttribute('data-autocomplete-item', places[i].id);
                li.appendChild(document.createTextNode(places[i].label));
                resultsList.appendChild(li);
            }

            resultsList.style.display = 'block';
        });
    }

    onSelectAutocompleteItem(item) {
        let autocompleteWrapper = this.wrapper.querySelector(this.autocompleteWrapperSelector),
            resultsList = autocompleteWrapper.querySelector('ul'),
            queryInput = autocompleteWrapper.querySelector('input[type="text"]'),
            suggestionInput = autocompleteWrapper.querySelector('input[type="hidden"]'),
            query = item.innerText.trim();

        queryInput.value = query;
        suggestionInput.value = item.getAttribute('data-autocomplete-item');
        resultsList.style.display = 'none';
        resultsList.innerHTML = '';

        if (query) {
            document.dispatchEvent(new CustomEvent('search_pickup_points'));
        }
    }

    onBlurAutocompleteInput = function () {
        let autocompleteWrapper = this.wrapper.querySelector(this.autocompleteWrapperSelector),
            list = autocompleteWrapper.querySelector('ul');

        this.debounce(function () {
            list.style.display = 'none';
            list.innerHTML = '';
        }.bind(this), 300)();
    }

    setMapCenter(coordinates) {
        this.map.setCenter(coordinates);
    }
}

export default MondialRelayFancyListAdapter;

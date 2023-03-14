import AjaxRequest from './ajax-request';
import Gmap from './gmap-adapter';
import Osm from './osm-adapter';

export default class MondialRelay
{
    constructor(options) {
        options = options || {};
        this.shippingMethodForm = document.querySelector('form[name="sylius_checkout_select_shipping"]');
        this.modal = document.getElementById('modal-mondial-relay');
        this.itemsPerPage = options.itemsPerPage || 6;
        this.currentPage = 1;
        this.resultsSet = [];
        this.currentPickupPoint = null;
        this.mapProvider = this.modal.getAttribute('data-map-provider');
        this.mapAdapter = null;

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

        this.addEventListeners();
        this.fixCurrentPointPosition();

        if (this.isMondialRelayMethodSelected()) {
            this.showModal();
        }

        this.loadCurrentPoint();
    }

    addEventListeners() {
        $(this.modal).modal({
            onHidden: function () {
                this.clearModalContent();
            }.bind(this),
            onApprove: function () {
                this.setCheckoutPickupPoint();
            }.bind(this)
        });

        this.shippingMethodForm.addEventListener('change', function () {
            if (this.isMondialRelayMethodSelected()) {
                this.showModal();
                document.getElementById('current-pickup-point').style.display = 'block';
            } else {
                document.getElementById('current-pickup-point').style.display = 'none';
                this.shippingMethodForm.querySelector('input.smr-pickup-point-id').value = '';
            }
        }.bind(this));

        document.addEventListener('submit', function (event) {
            // Submit search form
            if (-1 !== [].indexOf.call(this.modal.querySelectorAll('form.search-pickup-point-form'), event.target)) {
                event.preventDefault();
                event.stopPropagation();
                this.search();
            }
        }.bind(this));

        document.addEventListener('click', function (event) {
            // Click to close modal
            if (-1 !== [].indexOf.call(this.modal.querySelectorAll('.close-modal'), event.target)) {
                $(this.modal).modal('hide');

                return;
            }

            // Click to open modal
            if (-1 !== [].indexOf.call(this.shippingMethodForm.querySelectorAll('[data-load-pickup-point-list]'), event.target)) {
                this.showModal();

                return;
            }

            // Change results list page
            if (-1 !== [].indexOf.call(this.modal.querySelectorAll('button[data-page]'), event.target)) {
                this.showPage(parseInt(event.target.getAttribute('data-page')));

                return;
            }

            // Geolocation
            if (-1 !== [].indexOf.call(this.modal.querySelectorAll('button[data-mr-geolocalisation]'), event.target)) {
                this.geolocation();

                return;
            }

            if (-1 !== [].indexOf.call(this.modal.querySelectorAll('[data-autocomplete-item]'), event.target)) {
                return this.selectAutocompleteItem(event.target);
            }

            // Select pickup point
            let item = event.target;

            while(item !== null) {
                if (item.classList && item.classList.contains('relay-point-list-item')) {
                    return this.selectPickupPoint(item.getAttribute('data-relay-point-id'));
                }

                item = item.parentNode;
            }
        }.bind(this));

        document.addEventListener('keyup', function (event) {
            if (event.target.getAttribute('data-mr-autocomplete')) {
                this.debounce(function () {
                    this.autocomplete();
                }.bind(this), 300)();
            }
        }.bind(this));
    }

    fixCurrentPointPosition() {
        let currentPointWrapper = document.getElementById('current-pickup-point');

        if (!currentPointWrapper) {
            return;
        }

        let itemParent = currentPointWrapper.closest('.item');

        if (itemParent) {
            itemParent.parentNode.appendChild(currentPointWrapper);
        }

        if (this.isMondialRelayMethodSelected()) {
            currentPointWrapper.style.display = 'block';
        }
    }

    isMondialRelayMethodSelected() {
        return this.shippingMethodForm.querySelector('input[type="radio"][name$="[method]"][data-mr="true"]:checked') !== null;
    }

    loadCurrentPoint() {
        let input = this.shippingMethodForm.querySelector('input.smr-pickup-point-id');

        if (input.value) {
            this.currentPickupPoint = input.value;
            this.setCheckoutPickupPoint();
        }
    }

    setCheckoutPickupPoint() {
        let input = this.shippingMethodForm.querySelector('input.smr-pickup-point-id'),
            request = new AjaxRequest(this.modal.getAttribute('data-find-url'), 'GET', {pickupPointId: this.currentPickupPoint}),
            currentPointWrapper = document.getElementById('current-pickup-point');

        input.value = this.currentPickupPoint;

        request.send().then(function (response) {
            currentPointWrapper.innerHTML = response;
            currentPointWrapper.style.display = 'block';
        }.bind(this));
    }

    showModal() {
        let request = new AjaxRequest(this.modal.getAttribute('data-load-url'), 'GET');

        request.send().then(function (rawResponse) {
            let response = JSON.parse(rawResponse);
            this.modal.querySelector('.content').innerHTML = response.form || '';

            if (!this.mapProvider) {
                this.modal.querySelector('.pickup-points-map').style.display = 'none';
                this.modal.querySelector('button[data-mr-geolocalisation]').style.display = 'none';
            }

            $(this.modal).modal('show');

            this.onSearchResultsUpdated(response.points || []);
        }.bind(this));
    }

    clearModalContent() {
        this.modal.querySelector('.content').innerHTML = '';

        if (null !== this.mapAdapter) {
            this.mapAdapter = null;
        }
    }

    getMapAdapter() {
        if (null === this.mapAdapter) {
            this.createMapAdapter();
        }

        return this.mapAdapter;
    }

    createMapAdapter() {
        let params = {
            el: this.modal.querySelector('.pickup-points-map'),
            icons: {
                markerDefault: this.modal.getAttribute('data-marker'),
                markerSelected: this.modal.getAttribute('data-marker-selected'),
            },
            translations: {
                choose: this.modal.getAttribute('data-choose-label'),
            },
            onSelectMarker: this.selectPickupPoint.bind(this),
        };

        if ('google' === this.mapProvider) {
            this.mapAdapter = new Gmap(params);
        } else if ('open_street_map' === this.mapProvider) {
            this.mapAdapter = new Osm(params);
        }
    }

    changeLocation(location) {
        let form = this.modal.querySelector('form.search-pickup-point-form'),
            locationField = form.querySelector('input[name$="\[zipCode\]"]');

        locationField.value = location;
        this.search();
    }

    geolocation() {
        if ("undefined" === typeof(navigator.geolocation) || !this.getMapAdapter()) {
            return;
        }

        let btn = this.modal.querySelector('button[data-mr-geolocalisation]');
        btn.setAttribute('disabled', 'disabled');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                let positionNormalized = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                this.getMapAdapter().setMapCenter(positionNormalized);
                this.getMapAdapter().getZipCodeByPosition(positionNormalized).then(
                    function (zipCode) {
                        btn.removeAttribute('disabled');
                        this.changeLocation(zipCode);
                    }.bind(this),
                    function () {
                        btn.removeAttribute('disabled');
                    }
                )
            }.bind(this),
            function () {
                btn.removeAttribute('disabled');
            }
        )
    }

    search() {
        let form = this.modal.querySelector('form.search-pickup-point-form');

        let request = new AjaxRequest(
                form.getAttribute('action'),
                form.getAttribute('method').toUpperCase(),
                new URLSearchParams(new FormData(form))
            );

        request.send().then(function (response) {
            this.onSearchResultsUpdated(JSON.parse(response));
        }.bind(this));
    }

    hideResultList() {
        let resultsListWrapper = this.modal.querySelector('.pickup-points-results-list'),
            paginationList = this.modal.querySelector('.pickup-points-pagination');

        resultsListWrapper.classList.add('no-pagination');
        resultsListWrapper.innerHTML = '';
        resultsListWrapper.style.display = 'none';
        paginationList.innerHTML = '';
        paginationList.style.display = 'none';
    }

    showResultList() {
        let resultsListWrapper = this.modal.querySelector('.pickup-points-results-list'),
            paginationList = this.modal.querySelector('.pickup-points-pagination');

        resultsListWrapper.style.display = 'block';
        paginationList.style.display = 'flex';
    }

    onSearchResultsUpdated(points) {
        this.resultsSet = points;
        this.currentPage = 1;

        let resultsListWrapper = this.modal.querySelector('.pickup-points-results-list'),
            paginationList = this.modal.querySelector('.pickup-points-pagination'),
            pagesCount = Math.ceil(this.resultsSet.length / this.itemsPerPage);

        paginationList.innerHTML = '';
        resultsListWrapper.classList.remove('no-pagination');

        if (0 === this.resultsSet.length) {
            if (this.getMapAdapter()) {
                this.getMapAdapter().clearMarkers();
            }
            this.hideResultList();
            this.modal.querySelector('.pickup-points-no-results').style.display = 'block';

            return;
        }

        this.showSearchResults();
        this.showResultList();
        this.modal.querySelector('.pickup-points-no-results').style.display = 'none';

        if (1 >= pagesCount) {
            resultsListWrapper.classList.add('no-pagination');

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

            if (this.currentPage === page) {
                li.classList.add('current');
            }

            li.appendChild(btn);
            paginationList.appendChild(li);
        }
    }

    getDisplayResultsSet() {
        let offset = (this.currentPage - 1) * this.itemsPerPage;

        return this.resultsSet.slice(offset, offset + this.itemsPerPage);
    }

    showSearchResults() {
        let resultsListWrapper = this.modal.querySelector('.pickup-points-results-list'),
            resultsList = document.createElement('ul');

        resultsListWrapper.innerHTML = '';

        this.getDisplayResultsSet().map(function (point) {
            resultsList.append(this.createPointCard(point));
        }.bind(this));

        resultsListWrapper.append(resultsList);
        if (this.getMapAdapter()) {
            this.getMapAdapter().updateMarkers(this.getDisplayResultsSet());
        }
    }

    showPage(page) {
        let paginationList = this.modal.querySelector('.pickup-points-pagination');

        this.currentPage = page;
        this.showSearchResults();

        paginationList.querySelector('li.current').classList.remove('current');
        paginationList.querySelector('button[data-page="' + page + '"]').parentNode.classList.add('current');
    }

    createPointCard(point) {
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
        name.textContent = point.label;

        address.setAttribute('class', 'pickup-point-address');
        address.textContent = point.address;

        if (point.zipCode) {
            complement.push(point.zipCode);
        }

        if (point.city) {
            complement.push(point.city);
        }

        if (complement.length > 0) {
            addressComplement.setAttribute('class', 'pickup-point-address');
            addressComplement.textContent = complement.join(' ');
        }

        if (!this.pointIsOpen(point.businessHours)) {
            liClasses.push('close');
        }

        point.businessHours.forEach((item) => {
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
        li.setAttribute('data-relay-point-id', point.id);
        li.appendChild(card);

        return li;
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

    selectPickupPoint(id) {
        let resultsListWrapper = this.modal.querySelector('.pickup-points-results-list'),
            highlighted = resultsListWrapper.querySelector('li.highlight'),
            item = resultsListWrapper.querySelector('li[data-relay-point-id="' + id + '"]');

        if (highlighted && highlighted.getAttribute('data-relay-point-id') !== id) {
            highlighted.classList.remove('highlight');
        }

        if (item.classList.contains('highlight')) {
            item.classList.remove('highlight');
        } else {
            item.classList.add('highlight');
        }

        if (this.getMapAdapter()) {
            this.getMapAdapter().selectMarker(id);
        }
        this.currentPickupPoint = id;
    }

    autocomplete() {
        let autocompleteWrapper = this.modal.querySelector('.search-pickup-point-autocomplete'),
            queryInput = autocompleteWrapper.querySelector('input[type="text"]'),
            suggestionInput = autocompleteWrapper.querySelector('input[type="hidden"]'),
            resultsList = autocompleteWrapper.querySelector('ul'),
            request = new AjaxRequest(this.modal.getAttribute('data-autocomplete-url'), 'GET', {
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

    selectAutocompleteItem(item) {
        let autocompleteWrapper = this.modal.querySelector('.search-pickup-point-autocomplete'),
            resultsList = autocompleteWrapper.querySelector('ul'),
            queryInput = autocompleteWrapper.querySelector('input[type="text"]'),
            suggestionInput = autocompleteWrapper.querySelector('input[type="hidden"]'),
            query = item.innerText.trim();

        queryInput.value = query;
        suggestionInput.value = item.getAttribute('data-autocomplete-item');
        resultsList.style.display = 'none';
        resultsList.innerHTML = '';

        if (query) {
            this.search();
        }
    }
}

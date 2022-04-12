class MondialRelayListAdapter
{
    constructor(wrapper, defaultUrl, searchResultsSelector) {
        this.wrapper = wrapper;
        this.defaultUrl = defaultUrl;
        this.searchResultsSelector = searchResultsSelector;
        this.map = null;
        this.markers = {};
        this.infoWindow = null;

        document.addEventListener('click', function (event) {
            if (-1 !== [].indexOf.call(document.querySelectorAll('button[data-select-relay-point]'), event.target)) {
                this.onSelectRelayPoint(event.target.getAttribute('data-select-relay-point'));
            }
            if (-1 !== [].indexOf.call(document.querySelectorAll('button[data-show-relay-point]'), event.target)) {
                this.onShowRelayPoint(event.target.getAttribute('data-show-relay-point'));
            }
        }.bind(this));
    }

    onSelectShippingMethod() {
        let request = new AjaxRequest(this.defaultUrl, 'GET');

        request.send().then(function (response) {
            this.wrapper.innerHTML = response;
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
    }

    onHideSearchPanel() {
        if (this.infoWindow) {
            this.infoWindow.close();
            this.infoWindow = null;
        }

        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector);
        resultWrapper.querySelector('.pickup-points-results-list').innerHTML = '';
        document.querySelector('.pickup-points-map').style.display = 'none';
    }

    onSearchStart() {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector);

        resultWrapper.querySelector('.pickup-points-no-results').style.display = 'none';
        resultWrapper.querySelector('.pickup-points-results-list').innerHTML = '';
        this.markers = {};
    }

    onSearchResultsChange(results) {
        let resultWrapper = this.wrapper.querySelector(this.searchResultsSelector);

        if (0 === results.length) {
            resultWrapper.querySelector('.pickup-points-results-list').innerHTML = '';
            resultWrapper.querySelector('.pickup-points-no-results').style.display = 'block';

            return;
        }

        if (null === this.map) {
            this.map = new google.maps.Map(document.querySelector('.pickup-points-map'), {
                center: { lat: -34.397, lng: 150.644 },
                zoom: 13,
                disableDefaultUI: true,
            });
        }

        let ul = document.createElement('ul'),
            bounds = new google.maps.LatLngBounds(),
            chooseLabel = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-choose-label');

        for (let i = 0; i < results.length; i++) {
            let li = document.createElement('li'),
                card = document.createElement('div'),
                actions = document.createElement('div'),
                name = document.createElement('p'),
                address = document.createElement('p'),
                selectBtn = document.createElement('button'),
                showBtn = document.createElement('button');

            name.setAttribute('class', 'pickup-point-name');
            name.textContent = results[i].label;

            address.setAttribute('class', 'pickup-point-address');
            address.textContent = results[i].address;

            card.setAttribute('class', 'pickup-point-card');
            card.appendChild(name);
            card.appendChild(address);

            selectBtn.setAttribute('type', 'button');
            selectBtn.setAttribute('data-select-relay-point', results[i].id);
            selectBtn.textContent = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-choose-label');

            showBtn.setAttribute('type', 'button');
            showBtn.setAttribute('data-show-relay-point', results[i].id);
            showBtn.textContent = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-show-label');

            actions.setAttribute('class', 'pickup-point-controls');

            actions.appendChild(selectBtn);
            actions.appendChild(showBtn);

            li.appendChild(card);
            li.appendChild(actions);

            ul.appendChild(li);

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
                }
            });
            marker.setMap(this.map);
            bounds.extend(marker.getPosition());
            this.markers[results[i].id] = marker;
        }

        resultWrapper.querySelector('.pickup-points-results-list').appendChild(ul);
        if (results.length > 1) {
            this.map.fitBounds(bounds);
        }
    }

    onSelectRelayPoint(id) {
        let event = new CustomEvent('select_relay_point', { detail: id });
        document.dispatchEvent(event);
    }

    onShowRelayPoint(id) {
        if ("undefined" === typeof(this.markers[id])) {
            return;
        }

        if (this.infoWindow) {
            this.infoWindow.close();
        }

        let map = this.map,
            marker = this.markers[id],
            content = '<div class="pickup-point-card">' +
                '<p class="pickup-point-name">' + marker.data.label + '</p>' +
                '<p class="pickup-point-address">' + marker.data.address + '</p>' +
                '<button data-select-relay-point="' + marker.data.id + '">' + marker.data.ctaLabel + '</button>' +
                '</div>';

        this.infoWindow = new google.maps.InfoWindow({content: content});
        this.infoWindow.open({anchor: marker, map, shouldFocus: false});

        this.infoWindow.addListener('closeclick', function () {
            this.infoWindow = null;
        }.bind(this));
    }
}

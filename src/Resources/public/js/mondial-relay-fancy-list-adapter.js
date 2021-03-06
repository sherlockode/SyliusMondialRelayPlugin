class MondialRelayFancyListAdapter
{
    constructor(wrapper, defaultUrl, searchResultsSelector) {
        this.wrapper = wrapper;
        this.defaultUrl = defaultUrl;
        this.searchResultsSelector = searchResultsSelector;
        this.map = null;
        this.markers = {};

        document.addEventListener('click', function (event) {
            if (-1 !== [].indexOf.call(document.querySelectorAll('button[data-select-relay-point]'), event.target)) {
                this.onSelectRelayPoint(event.target.getAttribute('data-select-relay-point'));
            }

            let listItem = this.getResultListItemFromClick(event.target);
            if (listItem) {
                let id = listItem.getAttribute('data-relay-point-id');
                this.highlightPickupPoint(listItem.parentNode, listItem.getAttribute('data-relay-point-id'));
                if ("undefined" !== typeof(this.markers[id])) {
                    this.map.setCenter(this.markers[id].getPosition());
                }
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
            chooseLabel = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-choose-label'),
            mrLogo = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-logo');

        for (let i = 0; i < results.length; i++) {
            let li = document.createElement('li'),
                card = document.createElement('div'),
                logo = document.createElement('img'),
                actions = document.createElement('div'),
                name = document.createElement('p'),
                address = document.createElement('p'),
                selectBtn = document.createElement('button');

            name.setAttribute('class', 'pickup-point-name');
            name.textContent = results[i].label;

            address.setAttribute('class', 'pickup-point-address');
            address.textContent = results[i].address;

            logo.setAttribute('src', mrLogo);

            card.setAttribute('class', 'pickup-point-card');
            card.appendChild(name);
            card.appendChild(address);

            selectBtn.setAttribute('type', 'button');
            selectBtn.setAttribute('data-select-relay-point', results[i].id);
            selectBtn.textContent = resultWrapper.querySelector('.pickup-points-results-list').getAttribute('data-choose-label');

            actions.setAttribute('class', 'pickup-point-controls');

            actions.appendChild(selectBtn);

            li.setAttribute('class', 'relay-point-list-item');
            li.setAttribute('data-relay-point-id', results[i].id);
            li.appendChild(logo);
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
                },
                icon: mrLogo,
            });
            marker.setMap(this.map);
            bounds.extend(marker.getPosition());
            this.markers[results[i].id] = marker;
            let cb = this.highlightPickupPoint;

            marker.addListener('click', function () {
                cb(ul, results[i].id);
                marker.getMap().setCenter(marker.getPosition());
            });
        }

        resultWrapper.querySelector('.pickup-points-results-list').appendChild(ul);
        if (results.length > 1) {
            this.map.fitBounds(bounds);
        }
    }

    highlightPickupPoint(list, id) {
        let items = list.querySelectorAll('li'),
            listItem = list.querySelector('li[data-relay-point-id="' + id + '"]'),
            wrapper = list;

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
}

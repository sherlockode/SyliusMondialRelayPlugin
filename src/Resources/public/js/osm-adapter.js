import AjaxRequest from './ajax-request';

export default class OsmAdapter
{
    constructor(options) {
        this.el = options.el;
        this.map = L.map(this.el.getAttribute('id')).setView([51.505, -0.09], 13);
        this.markers = [];
        this.icons = {
            markerSelected: L.icon({iconUrl: options.icons.markerSelected}),
            markerDefault: L.icon({iconUrl: options.icons.markerDefault}),
        };
        this.translations = {
            choose: options.translations.choose
        };
        this.onSelectMarker = options.onSelectMarker || function() {};

        this.createMap();
    }

    createMap() {
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(this.map);
    }

    updateMarkers(markers) {
        this.clearMarkers();

        let points = [];

        for (let i = 0; i < markers.length; i++) {
            points.push(L.latLng(parseFloat(markers[i].lat), parseFloat(markers[i].lng)));
            let marker = L.marker(
                [parseFloat(markers[i].lat), parseFloat(markers[i].lng)],
                {
                    title: markers[i].label,
                    icon: markers[i].selected ? this.icons.markerSelected : this.icons.markerDefault,
                    id: markers[i].id,
                }
            ).addTo(this.map).on('click', function () {
                this.onSelectMarker(marker.options.id);
            }.bind(this));

            this.markers.push(marker);
        }

        if (this.markers.length > 0) {
            this.map.fitBounds(L.latLngBounds(points));
        }
    }

    clearMarkers() {
        for (let i = 0; i < this.markers.length; i++) {
            this.markers[i].removeFrom(this.map);
        }

        this.markers = [];
    }

    selectMarker(id) {
        for (let i = 0; i < this.markers.length; i++) {
            if (id === this.markers[i].options.id) {
                this.markers[i].setIcon(this.icons.markerSelected);
            } else {
                this.markers[i].setIcon(this.icons.markerDefault);
            }
        }
    }

    setMapCenter(position) {
        this.map.setView(L.latLng(parseFloat(position.lat), parseFloat(position.lng)));
    }

    getZipCodeByPosition(position) {
        return new Promise(function (resolve, reject) {
            let request = new AjaxRequest('https://nominatim.openstreetmap.org/reverse', 'GET', {
                format: 'jsonv2',
                lat: position.lat,
                lon: position.lng
            });
            request.send().then(function (rawResponse) {
                let response = JSON.parse(rawResponse);

                if ("undefined" !== response.address.postcode) {
                    return resolve(response.address.postcode);
                }

                return reject();
            }, reject);
        });
    }
}

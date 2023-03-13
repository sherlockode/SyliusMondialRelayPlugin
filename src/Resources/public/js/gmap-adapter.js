export default class GMapAdapter
{
    constructor(options) {
        this.el = options.el;
        this.map = new google.maps.Map(this.el, {
            center: { lat: -34.397, lng: 150.644 },
            zoom: 13,
            disableDefaultUI: true,
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_TOP,
            },
        });
        this.markers = [];
        this.icons = {
            markerSelected: options.icons.markerSelected || null,
            markerDefault: options.icons.markerDefault || null,
        };
        this.translations = {
            choose: options.translations.choose
        };
        this.onSelectMarker = options.onSelectMarker || function() {};
    }

    updateMarkers(markers) {
        this.clearMarkers();

        let bounds = new google.maps.LatLngBounds();

        for (let i = 0; i < markers.length; i++) {
            let marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(markers[i].lat),
                    lng: parseFloat(markers[i].lng),
                },
                title: markers[i].label,
                data: {
                    id: markers[i].id,
                    label: markers[i].label,
                    address: markers[i].address,
                    ctaLabel: this.translations.choose,
                },
                icon: markers[i].selected ? this.icons.markerSelected : this.icons.markerDefault,
            });
            marker.setMap(this.map);
            bounds.extend(marker.getPosition());
            marker.addListener('click', function () {
                this.onSelectMarker(marker.data.id);
            }.bind(this));
            this.markers.push(marker);
        }

        if (this.markers.length > 0) {
            this.map.fitBounds(bounds);
        }
    }

    clearMarkers() {
        for (let i = 0; i < this.markers.length; i++) {
            this.markers[i].setMap(null);
        }

        this.markers = [];
    }

    selectMarker(id) {
        for (let i = 0; i < this.markers.length; i++) {
            if (id === this.markers[i].data.id) {
                this.markers[i].setIcon(this.icons.markerSelected);
            } else {
                this.markers[i].setIcon(this.icons.markerDefault);
            }
        }
    }

    setMapCenter(position) {
        this.map.setCenter(position);
    }

    getZipCodeByPosition(position) {
        return new Promise(function (resolve, reject) {
            let geocoder = new google.maps.Geocoder();

            geocoder.geocode({location: position}).then(
                function (response) {
                    for (let i = 0; i < response.results.length; i++) {
                        let item = response.results[i];
                        for (let j = 0; j < item.address_components.length; j++) {
                            if (-1 !== item.address_components[j].types.indexOf('postal_code')) {
                                return resolve(item.address_components[j].short_name);
                            }
                        }
                    }

                    return reject();
                }
            ).catch(reject);
        });
    }
}

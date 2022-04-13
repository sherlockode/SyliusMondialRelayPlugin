class MondialRelay
{
    constructor(options) {
        this.selectors = this.getOption(options, 'selectors', {
            form: 'form[name="sylius_checkout_select_shipping"]',
            relayPointInput: 'input.smr-pickup-point-id',
            wrapper: '.smr_wrapper',
            showListPanelBtn: 'button[data-load-pickup-point-list]',
            searchInput: 'input[data-search-pickup-points-input]',
            searchBtn: 'button[data-search-pickup-points-button]',
            searchResults: '.pickup-points-search-results',
        });
        this.urls = this.getOption(options, 'urls');

        if (null !== this.urls) {
            this.createAdapter(this.getOption(options, 'adapter', 'regular'));
            this.init();
        }
    }

    getOption(options, path, defaultValue) {
        if ("undefined" !== typeof(options[path])) {
            return options[path];
        }

        if ("undefined" !== typeof(defaultValue)) {
            return defaultValue;
        }

        return null;
    }

    init() {
        this.addEventListeners();

        if (this.getForm().querySelector('input[type="radio"][name$="[method]"][data-mr="true"]:checked') !== null) {
            this.onSelectMondialRelayShipping();
        }
    }

    createAdapter(adapterType) {
        if ("regular" === adapterType) {
            this.adapter = new MondialRelayListAdapter(
                this.getForm().querySelector(this.selectors.wrapper),
                this.urls.defaultUrl,
                this.selectors.searchResults
            );

            return;
        }

        if ("fancy" === adapterType) {
            this.adapter = new MondialRelayFancyListAdapter(
                this.getForm().querySelector(this.selectors.wrapper),
                this.urls.defaultUrl,
                this.selectors.searchResults
            );

            return;
        }

        throw 'Adapter of type "' + adapterType + '" is not supported';
    }

    addEventListeners() {
        let form = this.getForm(),
            inputs = form.querySelectorAll('input[type="radio"][name$="[method]"]');

        form.addEventListener('submit', function (event) {
            if (document.activeElement === document.querySelector(this.selectors.searchInput)) {
                event.preventDefault();
                event.stopPropagation();
                this.onSearch();
            }
        }.bind(this), true);

        for (let i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('change', function (event) {
                if (event.target.getAttribute('data-mr')) {
                    this.onSelectMondialRelayShipping();
                } else {
                    this.onUnselectMondialRelayShipping();
                }
            }.bind(this), false);
        }

        document.addEventListener('click', function (event) {
            // Click on "select relay point button"
            if (-1 !== [].indexOf.call(document.querySelectorAll(this.selectors.showListPanelBtn), event.target)) {
                this.onShowSearchPanel();
            }
            // Trigger relay points search
            if (-1 !== [].indexOf.call(document.querySelectorAll(this.selectors.searchBtn), event.target)) {
                this.onSearch();
            }
        }.bind(this), false);

        document.addEventListener('select_relay_point', function (event) {
            if (event.detail) {
                this.onSelectPoint(event.detail);
            }
        }.bind(this), false);

        document.addEventListener('relay_point_panel_ready', function () {
            this.onRelayPointPanelReady();
        }.bind(this), false);
    }

    onSelectMondialRelayShipping() {
        this.adapter.onSelectShippingMethod();
    }

    onUnselectMondialRelayShipping() {
        this.adapter.onUnselectShippingMethod();
        document.querySelector(this.selectors.relayPointInput).value = '';
    }

    onRelayPointPanelReady() {
        let input = document.querySelector(this.selectors.relayPointInput);

        if (!input.value) {
            this.onShowSearchPanel();
        }
    }

    onShowSearchPanel() {
        let wrapper = this.getWrapper(),
            searchPanel = wrapper.querySelector('.pickup-points-search'),
            currentPickupPoint = wrapper.querySelector('.current-pickup-point');

        currentPickupPoint.style.display = 'none';
        searchPanel.style.display = 'flex';

        this.adapter.onShowSearchPanel();
        this.onSearch();
    }

    onHideSearchPanel() {
        this.adapter.onHideSearchPanel();
    }

    onSearch() {
        let wrapper = this.getWrapper(),
            searchPanel = wrapper.querySelector('.pickup-points-search'),
            query = searchPanel.querySelector(this.selectors.searchInput).value,
            request = new AjaxRequest(this.urls.searchUrl, 'GET', {zipCode: query});

        this.adapter.onSearchStart();

        request.send().then(function (response) {
            this.adapter.onSearchResultsChange(JSON.parse(response));
        }.bind(this));
    }

    onSelectPoint(id) {
        let input = document.querySelector(this.selectors.relayPointInput),
            request = new AjaxRequest(this.urls.findUrl, 'GET', {pickupPointId: id}),
            wrapper = this.getWrapper(),
            searchPanel = wrapper.querySelector('.pickup-points-search'),
            currentPickupPoint = wrapper.querySelector('.current-pickup-point');

        this.onHideSearchPanel();

        input.value = id;
        searchPanel.style.display = 'none';
        searchPanel.querySelector(this.selectors.searchInput).value = '';

        request.send().then(function (response) {
            currentPickupPoint.innerHTML = response;
            currentPickupPoint.style.display = 'block';
        }.bind(this));
    }

    getForm() {
        return document.querySelector(this.selectors.form);
    }

    getWrapper() {
        return this.getForm().querySelector(this.selectors.wrapper);
    }
}

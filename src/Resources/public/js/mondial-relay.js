class MondialRelay
{
    constructor(options) {
        this.selectors = this.getOption(options, 'selectors', {
            form: 'form[name="sylius_checkout_select_shipping"]',
            selectedPointWrapper: 'current-pickup-point',
            relayPointInput: 'input.smr-pickup-point-id',
            wrapper: '.smr_wrapper',
            showListPanelBtn: 'button[data-load-pickup-point-list]',
            searchInput: 'input[data-search-pickup-points-input]',
            searchBtn: 'button[data-search-pickup-points-button]',
            searchResults: '.pickup-points-search-results',
            modal: document.getElementById('modal-mondial-relay'),
        });

        this.urls = this.getOption(options, 'urls');

        if (null !== this.urls) {
            this.createAdapter();
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

    createAdapter() {
        this.adapter = new MondialRelayFancyListAdapter(
            this.getForm().querySelector(this.selectors.wrapper),
            this.selectors.selectedPointWrapper,
            this.urls.defaultUrl,
            this.selectors.searchResults
        );
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

            if (true === inputs[i].hasAttribute('data-mr')) {
                document.querySelector("[for='"+inputs[i].id+"']").addEventListener('click', function(event) {
                  if (inputs[i].checked) {
                    this.onSelectMondialRelayShipping();
                  }
                }.bind(this));
            }
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
        document.getElementById(this.selectors.selectedPointWrapper).innerHTML = '';
    }

    onRelayPointPanelReady() {
        let input = document.querySelector(this.selectors.relayPointInput);

        if (!input.value) {
            this.onShowSearchPanel();
        }
    }

    onShowSearchPanel() {
        this.adapter.onShowSearchPanel();

        $('#modal-mondial-relay').modal('show');
        document.getElementById('modal-mondial-relay').querySelector('.pickup-points-search').style.display = 'flex';

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
            currentPointWrapper = document.getElementById('current-pickup-point');

        input.value = id;

        request.send().then(function (response) {
          currentPointWrapper.innerHTML = response;
          currentPointWrapper.style.display = 'block';
        }.bind(this));
    }

    getForm() {
        return document.querySelector(this.selectors.form);
    }

    getWrapper() {
        return document.querySelector(this.selectors.wrapper);
    }
}

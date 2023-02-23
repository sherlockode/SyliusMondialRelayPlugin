import AjaxRequest from './ajax-request';
import MondialRelayFancyListAdapter from './mondial-relay-fancy-list-adapter';

class MondialRelay
{
    constructor(options) {
        this.selectors = this.getOption(options, 'selectors', {
            form: 'form[name="sylius_checkout_select_shipping"]',
            selectedPointWrapper: 'current-pickup-point',
            relayPointInput: 'input.smr-pickup-point-id',
            wrapper: '.smr_wrapper',
            showListPanelBtn: 'button[data-load-pickup-point-list]',
            searchForm: '.pickup-points-input form.search-pickup-point-form',
            searchResults: '.pickup-points-search-results',
            modalId: 'modal-mondial-relay',
            closeModalBtn: '.close-modal'
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
        this.fixCurrentPointPosition();
        this.addEventListeners();

        if (this.getForm().querySelector('input[type="radio"][name$="[method]"][data-mr="true"]:checked') !== null) {
            this.onSelectMondialRelayShipping();
        }
    }

    createAdapter() {
        this.adapter = new MondialRelayFancyListAdapter(
            document.querySelector(this.selectors.wrapper),
            this.selectors.selectedPointWrapper,
            this.urls.defaultUrl,
            this.selectors.searchResults
        );
    }

    fixCurrentPointPosition() {
        let currentPointWrapper = document.getElementById(this.selectors.selectedPointWrapper);

        if (!currentPointWrapper) {
            return;
        }

        let itemParent = currentPointWrapper.closest('.item');

        if (itemParent) {
            itemParent.parentNode.appendChild(currentPointWrapper);
        }

        currentPointWrapper.style.display = 'block';
    }

    addEventListeners() {
        let inputs = this.getForm().querySelectorAll('input[type="radio"][name$="[method]"]');

        for (let i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('change', function (event) {
                if (event.target.getAttribute('data-mr')) {
                    this.onSelectMondialRelayShipping();
                } else {
                    this.onUnselectMondialRelayShipping();
                }
            }.bind(this), false);

            if (true === inputs[i].hasAttribute('data-mr')) {
                document.querySelector("[for='"+inputs[i].id+"']").addEventListener('click', function() {
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
            // Click to close modal
            if (-1 !== [].indexOf.call(document.querySelectorAll(this.selectors.closeModalBtn), event.target)) {
                this.onHideSearchPanel();
            }
        }.bind(this), false);

        document.addEventListener('submit', function (event) {
            // Trigger relay points search
            if (-1 !== [].indexOf.call(document.querySelectorAll(this.selectors.searchForm), event.target)) {
                event.preventDefault();
                event.stopPropagation();
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
        let modal = document.getElementById(this.selectors.modalId);

        if (modal) {
            $(modal).modal('show');
            modal.querySelector('.pickup-points-search').style.display = 'flex';

            this.onSearch();
        }
    }

    onHideSearchPanel() {
        let modal = document.getElementById(this.selectors.modalId);

        if (modal) {
            $(modal).modal('hide');
        }
    }

    onSearch() {
        let searchForm = this.getSearchForm(),
            request = new AjaxRequest(
                searchForm.getAttribute('action'),
                searchForm.getAttribute('method').toUpperCase(),
                new URLSearchParams(new FormData(searchForm))
            );

        this.adapter.onSearchStart();

        request.send().then(function (response) {
            this.adapter.onSearchResultsChange(JSON.parse(response));
        }.bind(this));
    }

    onSelectPoint(id) {
        let input = document.querySelector(this.selectors.relayPointInput),
            request = new AjaxRequest(this.urls.findUrl, 'GET', {pickupPointId: id}),
            currentPointWrapper = document.getElementById(this.selectors.selectedPointWrapper);

        input.value = id;

        request.send().then(function (response) {
          currentPointWrapper.innerHTML = response;
          currentPointWrapper.style.display = 'block';
        }.bind(this));
    }

    getForm() {
        return document.querySelector(this.selectors.form);
    }

    getSearchForm() {
        return document.querySelector(this.selectors.searchForm);
    }

    getWrapper() {
        return document.querySelector(this.selectors.wrapper);
    }
}

export default MondialRelay;

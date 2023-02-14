import AjaxRequest from './ajax-request';

let form = null;
let modal = null;

const openModal = function (url) {
  let request = new AjaxRequest(url, 'GET');

  request.send().then(function (response) {
    let parsed = JSON.parse(response);

    if (parsed.success) {
      let tmp = document.createElement('div');
      tmp.innerHTML = parsed.html.trim();
      document.querySelector('body').append(tmp.firstChild);
      form = document.querySelector('.mr-print-ticket-modal');
      modal = $(form)
        .modal({
          onHidden: function () {
            form.removeEventListener('submit', onSubmitTicketForm);
            form = null;
            modal = null;
          }
        });
      form.addEventListener('submit', onSubmitTicketForm);
      modal.modal('show');
    }
  });
};

const onSubmitTicketForm = function (event) {
  event.preventDefault();

  if (!form) {
    return;
  }

  let request = new AjaxRequest(
    form.getAttribute('action'),
    form.getAttribute('method').toUpperCase()
  );

  request.send(new URLSearchParams(new FormData(form))).then(
    function (response) {
      let parsed = JSON.parse(response);

      if (parsed.success && "undefined" !== typeof(parsed.target_url)) {
        document.location = parsed.target_url;

        return;
      }

      let parser = new DOMParser();
      const dom = parser.parseFromString(parsed.html, 'text/html');
      form.innerHTML = dom.querySelector('form').innerHTML;
    }
  );
};

window.addEventListener('load', function () {
  let buttons = document.querySelectorAll('.mr-print-ticket-btn');

  for (let i = 0; i < buttons.length; i++) {
    buttons[i].addEventListener('click', function (event) {
      event.preventDefault();
      openModal(this.getAttribute('href'));
    }, {once: false});
  }
});

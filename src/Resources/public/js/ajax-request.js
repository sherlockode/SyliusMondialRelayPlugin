class AjaxRequest
{
    constructor(url, method, params) {
        this.url = url;
        this.method = method;
        this.params = params;
    }

    send(payload) {
        const url = this.getRealUrl();
        const method = this.method;

        return new Promise(function (resolve, reject) {
            var request = new XMLHttpRequest();

            request.open(method, url, true);
            request.onload = function() {
                if (this.status >= 200 && this.status < 400) {
                    return resolve(this.response);
                } else {
                    return reject(this.status);
                }
            };

            request.onerror = function() {
                return reject();
            };

            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.send(payload);
        });
    }

    getRealUrl() {
        if ('GET' !== this.method) {
            return this.url;
        }

        return this.url + '?' + new URLSearchParams(this.params);
    }
}

export default AjaxRequest;

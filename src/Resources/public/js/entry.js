import '../css/style.css';

import '../images/marker.svg';
import '../images/marker-selected.svg';

import MondialRelay from './mondial-relay.js';

window.addEventListener('load', function () {
  'use strict';

  new MondialRelay(mondialRelayCfg);
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import './styles/portal-eosc.css';

// start the Stimulus application
import './bootstrap';

const $ = require('jquery');
global.$ = global.jQuery = $;

require('datatables.net');

import 'popper.js';

require('chart.js');
import Chart from 'chart.js';

import moment from 'moment';
global.moment = moment;

require('daterangepicker');
require('daterangepicker/daterangepicker.css');



import 'bootstrap/scss/bootstrap.scss';
import 'bootstrap';
require('bootstrap-icons/font/bootstrap-icons.css');


import 'select2'; // globally assign select2 fn to $ element
import 'select2/dist/css/select2.css'; // optional if you have css loader


$(document).ready(function() {

    $('.select2-enable').select2();
    $(".dataTable").dataTable();

})

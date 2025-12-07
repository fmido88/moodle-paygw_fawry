// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe module check_status
 *
 * @module     paygw_fawry/check_status
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import $ from 'jquery';
import * as reportSelectors from 'core_reportbuilder/local/selectors';

let successUrl;
let disabledTimeout;

const Selectors = {
    bulkCheck: 'button[data-action="check-status-bulk"]',
    reportWrapper: '[data-region="fawry-report-wrapper"]',
    checkbox: 'input[type="checkbox"][data-togglegroup="report-select-all"][data-toggle="slave"]',
    masterCheckbox: 'input[type="checkbox"][data-togglegroup="report-select-all"][data-toggle="master"]',
    checkedRows: '[data-togglegroup="report-select-all"][data-toggle="slave"]:checked',
};

/**
 * Check the order status
 * @param {Number} orderId
 * @returns {Promise<Object>}
 */
function checkStatus(orderId) {
    let request = Ajax.call([{
        methodname: 'paygw_fawry_check_status',
        args: {
            orderid: orderId
        }
    }]);
    return request[0];
}

/**
 * Instant check for the order status.
 * @param {Array<Number>|Number} orderIds
 * @returns {Promise<Object>}
 */
async function instantCheck(orderIds) {
    let ids = Array.isArray(orderIds) ? orderIds : [orderIds];
    let tobeRequested = ids.map(orderId => {
        return {
            methodname: 'paygw_fawry_instant_check',
            args: {
                orderid: orderId
            }
        };
    });
    let requests = Ajax.call(tobeRequested);
    let data = await Promise.all(requests);

    let indexedData = {};
    data.forEach((response, index) => {
        indexedData[ids[index]] = response;
    });

    // eslint-disable-next-line no-console
    console.log(await indexedData);
    return indexedData;
}
/**
 * Perform instance check for a specific order id.
 * @param {Number|Array<number>} orderIds
 */
async function instanceCheckForIds(orderIds) {
    orderIds = Array.isArray(orderIds) ? orderIds : [orderIds];
    let holders = {};
    for (const orderId of orderIds) {
        holders[orderId] = $('[data-purpose="status-response"][data-orderid="' + orderId + '"]');
        holders[orderId].html('Checking...');
    }

    let response = await instantCheck(orderIds);

    for (const orderId of orderIds) {
        let $this = $('[data-action="check-status"][data-orderid="' + orderId + '"]').filter('a, button');
        let statusCell = $this.closest('tr').find('[data-purpose="order-status"]');
        if (response[orderId].status) {
            statusCell.text(response[orderId].status);
        }

        if (response[orderId].msg) {
            let responseHtml = "<br><pre>" + JSON.stringify(response[orderId], null, '\t') + "</pre>";
            if (holders[orderId].length) {
                holders[orderId].html(responseHtml);
            } else if (statusCell.length) {
                let newHolder = $('<div data-purpose="status-response" data-orderid="' + orderId + '"></div>');
                statusCell.append(newHolder);
                newHolder.html(responseHtml);
                setTimeout(function() {
                    newHolder.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 15000);
            }
        }
    }
}
/**
 * Register the instance check button in the reports table.
 */
async function registerInstanceCheckButton() {
    let button = $('button[data-action="check-status"], a[data-action="check-status"]');
    button.on('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        let $this = $(this); // Save reference to button
        clearTimeout(disabledTimeout);
        $this.attr('disabled', true);

        let orderId = $this.data("orderid");
        if (orderId) {
            await instanceCheckForIds(orderId);
        }

        disabledTimeout = setTimeout(function() {
            $this.attr('disabled', false);
        }, 5000);
    });

    let bulkButton = $(Selectors.bulkCheck);
    bulkButton.on('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        let $this = $(this); // Save reference to button
        clearTimeout(disabledTimeout);
        $this.attr('disabled', true);

        let reportRegion = $(reportSelectors.regions.report);
        const selectedOrders = [...reportRegion.find(Selectors.checkedRows)];
        const ordersIds = selectedOrders.map(check => parseInt(check.value));
        await instanceCheckForIds(ordersIds);

        disabledTimeout = setTimeout(function() {
            $this.attr('disabled', false);
        }, 5000);
    });
}
/**
 * Register the check button in process page.
 * @param {Number} orderid
 */
async function registerNormalCheckButton(orderid) {
    let button = $('button[data-action="check-status"]');
    button.on('click', async function() {
        let $this = $(this); // Save reference to button

        if (orderid) {
            clearTimeout(disabledTimeout);
            $this.attr('disabled', true);
            await instantCheck(orderid);
            let response = await checkStatus(orderid);
            if (response.status === 'success') {
                window.location.href = successUrl;
            } else {
                disabledTimeout = setTimeout(function() {
                    $this.attr('disabled', false);
                }, 30000);
            }
        }
    });
}

export const init = (orderid = null, url = null) => {

    if (orderid) {
        successUrl = url;
        var interval = setInterval(async function() {
            let response = await checkStatus(orderid);
            if (response.status === 'success') {
                clearInterval(interval);
                window.location.href = successUrl;
            } else if (response.status === 'failed') {
                clearInterval(interval);
            }
            $('[data-purpose="status"]').text(response.status);
        }, 15000);
    }

    if (orderid) {
        registerNormalCheckButton();
    } else {
        registerInstanceCheckButton();
    }

};
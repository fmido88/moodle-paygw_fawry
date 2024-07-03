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

let orderId;
let successUrl;

/**
 * Check the order status
 * @returns {Promise<Object>}
 */
function checkStatus() {
    let request = Ajax.call([{
        methodname: 'paygw_fawry_check_status',
        args: {
            orderid: orderId
        }
    }]);
    return request[0];
}

export const init = (orderid, url) => {
    orderId = orderid;
    successUrl = url;
    var interval = setInterval(async() => {
        let response = await checkStatus();
        if (response.status === 'success') {
            clearInterval(interval);
            window.location.href = successUrl;
        } else if (response.status === 'failed') {
            clearInterval(interval);
        }
        $('[data-purpose="status"]').text(response.status);
    }, 15000);
};
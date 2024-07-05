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
import $ from 'jquery';

/**
 * TODO describe module callback
 *
 * @module     paygw_fawry/callback
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Event observer to auto copy the callback url.
 */
export const init = () => {
    $('#cpicon').on("click", function() {
        var copyText = document.getElementById('cburl').innerText;
        if (navigator && navigator.clipboard) {
            navigator.clipboard.writeText(copyText);
        } else {
            // eslint-disable-next-line no-alert
            prompt("Copy link, then click OK.", copyText);
        }
    });
};
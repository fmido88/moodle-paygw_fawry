<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     paygw_fawry
 * @category    string
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['callback'] = 'Callback url';
$string['callback_help'] = 'Copy this url and add it to your account in Fawry terminal dashboard.';

$string['gatewaydescription'] = 'Pay with Fawry reference code';
$string['gatewayname'] = 'Fawry';


$string['hashcode'] = 'Secret has code';
$string['hashcode_help'] = 'From fawry portal dashboard';


$string['merchantid'] = 'Merchent ID';
$string['merchantid_help'] = 'From fawry portal dashboard';


$string['no_phone'] = 'No phone number provided';


$string['payment_completed'] = 'Payment completed';
$string['payment_successful'] = 'Payment successful';
$string['phone'] = 'Mobile phone number';
$string['pluginname'] = 'Fawry Payment';
$string['pluginname_desc'] = 'Collect payment with Fawry payments by reference codes';
$string['privacy:metadata:paygw_fawry'] = 'User meta data sent to Fawary gateway on payments';
$string['privacy:metadata:paygw_fawry:email'] = 'The users email';
$string['privacy:metadata:paygw_fawry:fullname'] = 'The user\'s full name';
$string['privacy:metadata:paygw_fawry:phone'] = 'The mobile phone number available';
$string['privacy:metadata:paygw_fawry_orders'] = 'Database table to store orders data';
$string['privacy:metadata:paygw_fawry_orders:reference'] = 'Fawry reference number';
$string['privacy:metadata:paygw_fawry_orders:userid'] = 'The user\'s id';


$string['reference_bill_reference'] = 'Fawry reference key';
$string['reference_error'] = 'Error while trying to request reference code';
$string['reference_key'] = 'Reference key';
$string['reference_process_help'] = 'Head to the nearest Fawry terminal and request Fawry pay with this reference number';


$string['staging'] = 'Staging';


$string['unknown_error'] = 'Unknown error';
$string['validto'] = 'Valid to';
$string['refamount'] = 'Pay an amount of';

$string['localorderid'] = 'Local order id';
$string['itemid'] = 'Item id';
$string['paymentarea'] = 'Payment area';
$string['component'] = 'Component';
$string['paymentid'] = 'Payment id';
$string['amount'] = 'Amount';
$string['timemodified'] = 'Time modified';
$string['check_status'] = 'Check status';
$string['fawry:viewreport'] = 'View Fawry report';
$string['orders_report'] = 'Fawry order report';
$string['fawryreferences'] = 'My Fawry references';
$string['fawryreport'] = 'Fawry report';

$string['referenceinnav'] = 'Reference in navigation';
$string['referenceinnav_desc'] = 'Show a tab to redirect to reference numbers page in the navigation bar to show generated reference numbers.';
$string['checkcron'] = 'Check in cron task';
$string['checkcron_desc'] = 'If checked a cron task will run periodically to check the payments with Fawry (useful if the callback fails)';

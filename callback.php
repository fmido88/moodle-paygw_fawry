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
 * The callback process is ocurred at this page.
 *
 * @package     paygw_fawry
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use paygw_fawry\order;
use paygw_fawry\security;

// Does not require login in server side transaction process callback.
require_once(__DIR__ . '/../../../config.php');

global $DB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postdata = file_get_contents('php://input');
    $postdata = mb_convert_encoding($postdata, 'UTF-8', 'ISO-8859-1');
    $jsondata = json_decode($postdata, true);

    $refnumber   = $jsondata['referenceNumber'] ?? $jsondata['fawryRefNumber'] ?? '';
    $signature   = $jsondata['signature'] ?? $jsondata['messageSignature'] ?? '';
    $orderid     = $jsondata['merchantRefNumber'] ?? '';
    $payamount   = $jsondata['paymentAmount'] ?? ''; // Format number in check.
    $orderamount = $jsondata['orderAmount'] ?? ''; // Format number in check.
    $status      = $jsondata['orderStatus'] ?? ''; // New, PAID, CANCELED, DELIVERED, REFUNDED, EXPIRED.
    $method      = $jsondata['paymentMethod'] ?? '';
    $payrefnum   = $jsondata['paymentRefrenceNumber'] ?? '';

    // Strings needed to calculate hash code:
    // fawryRefNumber + merchantRefNum + Payment amount(in two decimal format 10.00)
    // +Order amount(in two decimal format 10.00)+Order Status + Payment method
    // + Payment reference number ( if exist as in case of notification for order creation this element will be empty) + secureKey.
    $strings = [
        'fawryRefNumber'  => $refnumber,
        'merchantRefNum'  => $orderid,
        'paymentAmount'   => format_float($payamount, 2),
        'orderAmount'     => format_float($orderamount, 2),
        'orderStatus'     => $status,
        'paymentMethod'   => $method,
        'referenceNumber' => $payrefnum,
    ];
    $string = implode('', $strings);
} else {
    die('METHOD "' . $_SERVER['REQUEST_METHOD'] . '" NOT ALLOWED');
}

$orderid = clean_param($orderid, PARAM_INT);

if (empty($orderid)) {
    die('ORDER ID NOT FOUND');
}

$order = new order($orderid);

$secure = new security($order);
if (!$secure->verify_signature_string($signature, $string)) {
    die('SIGNATURE NOT MATCH');
}

$status = strtolower($status);
if ($status == 'paid') {
    $order->payment_complete();
    die ("Order completed");
}

if ($status == 'new') {
    $order->update_status('pending');
} else {
    $order->update_status($status);
}

die("ORDER UPDATED");

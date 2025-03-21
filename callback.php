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
    // + Order amount(in two decimal format 10.00) + Order Status + Payment method
    // + Payment reference number ( if exist as in case of notification for order creation this element will be empty) + secureKey.
    // Cleaning all parameters as text here to for the concat string then clean it again for DB queries.
    $strings = [
        'fawryRefNumber'  => clean_param($refnumber, PARAM_TEXT),
        'merchantRefNum'  => clean_param($orderid, PARAM_TEXT),
        'paymentAmount'   => $payamount ? format_float($payamount, 2) : '',
        'orderAmount'     => $orderamount ? format_float($orderamount, 2) : '',
        'orderStatus'     => clean_param($status, PARAM_TEXT),
        'paymentMethod'   => clean_param($method, PARAM_TEXT),
        'referenceNumber' => clean_param($payrefnum, PARAM_TEXT),
    ];
    $string = implode('', $strings);

    // Cleaning all parameters.
    $tobecleaned = [
        'refnumber'   => PARAM_INT,
        'signature'   => PARAM_ALPHANUM,
        'orderid'     => PARAM_INT,
        'payamount'   => PARAM_FLOAT,
        'orderamount' => PARAM_FLOAT,
        'status'      => PARAM_ALPHA,
        'method'      => PARAM_TEXT,
        'payrefnum'   => PARAM_TEXT,
    ];

    foreach ($tobecleaned as $key => $type) {
        if (isset($$key)) {
            $$key = clean_param($$key, $type);
        }
    }

} else {
    die('METHOD "' . $_SERVER['REQUEST_METHOD'] . '" NOT ALLOWED');
}

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
    $reference = new paygw_fawry\reference($order);
    // For more confirmation request the status from Fawry.
    // Five tries to avoid requests errors.
    for ($i = 5; $i > 0; $i--) {
        $response = $reference->request_status();
        if (strtolower($response['status']) == 'paid') {
            $order->payment_complete();
            die ("Order completed");
        }
    }

    die("The status is not match from the requested one");
}

if ($status == 'new') {
    $order->update_status('pending');
} else {
    $order->update_status($status);
}

die("ORDER UPDATED");

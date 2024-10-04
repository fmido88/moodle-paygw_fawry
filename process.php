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
 * Plugin administration pages are defined here.
 *
 * @package     paygw_fawry
 * @category    admin
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_login(null, false);

global $DB, $USER;

$orderid     = optional_param('orderid', null, PARAM_INT);
$description = optional_param('description', '', PARAM_TEXT);

if (!empty($orderid)) {
    $order = new paygw_fawry\order($orderid);
    if ($order->get_status() == 'success') {
        $url = $order->get_redirect_url();
        redirect($url, get_string('payment_completed', 'paygw_fawry'), null, 'success');
    }
    $component = $order->get_component();
    $paymentarea = $order->get_paymentarea();
    $itemid = $order->get_itemid();
    $requester = new paygw_fawry\reference($order, $description);

} else {

    $component   = required_param('component', PARAM_TEXT);
    $paymentarea = required_param('paymentarea', PARAM_TEXT);
    $itemid      = required_param('itemid', PARAM_INT);
    $requester   = paygw_fawry\reference::make($component, $paymentarea, $itemid, $description);
    $order       = $requester->order;
}

$params = [
    'orderid'     => $order->get_id(),
    'description' => $description,
];

if ($phone = $requester->get_user_mobile()) {
    $params['phone'] = $phone;
}


$url = new moodle_url('/payment/gateway/fawry/process.php', $params);
$PAGE->set_context(context_system::instance());

$title = get_string('reference_key', 'paygw_fawry');
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('frontpage');

if (empty($phone)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('no_phone', 'paygw_fawry'), 'error');

    unset($params['phone']);
    $mform = new MoodleQuickForm('fawry-phone', 'get', $url->out_omit_querystring());

    $mform->addElement('text', 'phone', get_string('phone', 'paygw_fawry'));
    $mform->setType('phone', PARAM_TEXT);

    $mform->addElement('submit', 'submit', get_string('submit'));

    foreach ($params as $element => $value) {
        $mform->addElement('hidden', $element);
        $mform->setDefault($element, $value);
        if ($element == 'orderid') {
            $mform->setType($element, PARAM_INT);
        } else {
            $mform->setType($element, PARAM_TEXT);
        }
    }

    $mform->display();
    echo $OUTPUT->footer();
    exit;
}

// Requesting all data need to complete the payment using this method.
$refcode = $requester->request_reference();

echo $OUTPUT->header();

$templatedata = new stdClass;
if (is_object($refcode)) {
    $templatedata->reference = $refcode->reference;
    $templatedata->deadtime = $refcode->deadtime;
    $templatedata->amount = $refcode->amount;
    $templatedata->success = true;

    $jsparams = [
        'orderid' => $order->get_id(),
        'url' => $order->get_redirect_url()->out(false),
    ];
    $PAGE->requires->js_call_amd('paygw_fawry/check_status', 'init', $jsparams);

} else if (is_string($refcode)) {
    $templatedata->error = $refcode;
    $templatedata->success = false;
} else {
    $templatedata->error = get_string('unknown_error', 'paygw_fawry');
    $templatedata->success = false;
}

echo $OUTPUT->render_from_template('paygw_fawry/process', $templatedata);

echo $OUTPUT->footer();

<?php
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

namespace paygw_fawry;

/**
 * Class reference
 *
 * To perform a payment with reference number.
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reference extends requester {
    /**
     * The order instance
     * @var order
     */
    public $order;
    /**
     * Item description
     * @var string
     */
    public $description;
    /**
     * Construct
     * @param order|int $order
     * @param string $description
     */
    public function __construct($order, $description = '') {
        if (is_number($order)) {
            $this->order = new order($order);
        } else {
            $this->order = $order;
        }
        $config = $this->order->get_gateway_config();
        parent::__construct($config->merchantid, $config->hashcode);
        $this->description = $description;
    }

    /**
     * make reference class instance from component, payment area, and  itemid
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param string $description
     * @return reference
     */
    public static function make($component, $paymentarea, $itemid, $description = '') {
        $order = order::created_order($component, $paymentarea, $itemid);
        return new reference($order, $description);
    }

    /**
     * Format all data required for requests.
     * @return array
     */
    protected function format_data() {
        $user = $this->order->get_user();
        if (empty($this->description)) {
            $this->description = "Payment for order #" . $this->order->get_id();
        }

        $secure = new security($this->order);
        $data = [
            'merchantCode'      => $this->mcode,
            'merchantRefNum'    => $this->order->get_id(),
            'customerProfileId' => $this->order->get_userid(),
            'paymentMethod'     => 'PayAtFawry',
            'customerName'      => fullname($user),
            'customerMobile'    => $this->get_user_mobile(),
            'customerEmail'     => $user->email,
            'amount'            => $this->order->get_cost(),
            'paymentExpiry'     => (time() + DAYSECS) * 1000,
            'description'       => $this->description,
            'language'          => current_language(),
            'chargeItems' => [
                [
                    'itemId' => $this->order->get_itemid(),
                    'description' => $this->description,
                    'price' => $this->order->get_cost(),
                    'quantity' => 1,
                ],
            ],
            'signature' => $secure->make_payment_signature('PayAtFawry'),
        ];

        $callback = new \moodle_url('/payment/gateway/fawry/callback.php');

        $data['orderWebHookUrl'] = $callback->out(false);

        return $data;
    }
    /**
     * Request a reference code.
     * @return object|string|null
     */
    public function request_reference() {
        $staging = (bool)$this->order->get_gateway_config()->staging;
        if ($staging) {
            $url = 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/charge';
        } else {
            $url = 'https://www.atfawry.com/ECommerceWeb/Fawry/payments/charge';
        }
        $data = $this->format_data();
        $response = $this->request($data, $url);

        if (!empty($response) && $response->statusCode == 200
            && in_array(strtolower($response->orderStatus), ['paid', 'unpaid', 'new'])) {

            // ...referenceNumber (if exist) + merchantRefNum + paymentAmount (in two decimal places format 10.00)
            // + orderAmount (in two decimal places format 10.00) + orderStatus + paymentMethod
            // + fawryFees (if exist) (in two decimal places format 10.00))
            // + shippingFees (if exist) (in two decimal places format 10.00))
            // + authNumber (if exists) + customerMail (if exist) + customerMobile (if exist) + secureKey.
            $this->order->set_fawry_reference($response->referenceNumber);
            return (object)[
                'reference' => $response->referenceNumber,
                'deadtime'  => date('F j, Y, g:i A', ceil($response->expirationTime / 1000)),
                'amount'    => $response->paymentAmount,
            ];
        } else if (!empty($response->statusDescription)) {
            return $response->statusDescription;
        } else if (is_string($response)) {
            return $response;
        }
        return null;
    }

    /**
     * Get the user mobile from the submitted data or from stored
     * phone number.
     * @return string|null
     */
    public function get_user_mobile() {
        $phone = optional_param('phone', null, PARAM_ALPHANUM);
        if ($phone = utils::validate_phone_number($phone)) {
            return $phone;
        }
        if ($phone = $this->get_stored_phone()) {
            return $phone;
        }
        return null;
    }
    /**
     * Get stored phone number.
     * @return string|null
     */
    protected function get_stored_phone() {
        global $DB;
        $user = $this->order->get_user();
        if (!empty($user->phone1)) {
            if ($number = utils::validate_phone_number($user->phone1)) {
                return $number;
            }
        }
        if (!empty($user->phone2)) {
            if ($number = utils::validate_phone_number($user->phone2)) {
                return $number;
            }
        }
        $records = $DB->get_records('user_info_data', ['userid' => $user->id], '', 'data');
        foreach ($records as $record) {
            if ($number = utils::validate_phone_number($record->data)) {
                return $number;
            }
        }
        return null;
    }
    /**
     * Request order status
     * @return array
     */
    public function request_status() {
        $secure = new security($this->order);

        $staging = (bool)$this->order->get_gateway_config()->staging;
        if ($staging) {
            $url = 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/status/v2';
        } else {
            $url = 'https://www.atfawry.com/ECommerceWeb/Fawry/payments/status/v2';
        }

        $data = [
            'merchantCode'      => $this->mcode,
            'merchantRefNumber' => $this->order->get_id(),
            'signature'         => $secure->make_status_signature(),
        ];

        $response = $this->request($data, $url, 'get');
        if (!empty($response)) {
            if (is_string($response)) {
                return [
                    'status' => 'error',
                    'msg'    => $response,
                ];
            }

            if (!empty($response->statusCode) && $response->statusCode != 200) {
                return [
                    'status' => 'error',
                    'msg'    => $response->statusDescription,
                ];
            }

            if (!empty($response->orderStatus)) {
                return ['status' => $response->orderStatus];
            }

            return [
                'status' => 'error',
                'msg'    => json_decode($response),
            ];
        }

        return [
                'status' => 'error',
                'msg'    => get_string('unknown_error', 'paygw_fawry'),
        ];
    }
}

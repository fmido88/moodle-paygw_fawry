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
 * Class security
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class security {
    /**
     * The order instance
     * @var order
     */
    protected order $order;
    /**
     * Make security instance by order object
     * @param order $order
     */
    public function __construct($order) {
        $this->order = $order;
    }
    /**
     * Create a signature to be sent with the request.
     * @param string $paymentmethod
     * @return string
     */
    public function make_payment_signature($paymentmethod) {
        $config = $this->order->get_gateway_config();
        $string = $config->merchantid;
        $string .= $this->order->get_id();
        $string .= $this->order->get_userid();
        $string .= $paymentmethod;
        $string .= format_float($this->order->get_cost(), 2);
        $string .= $config->hashcode;
        return hash('sha256' , $string);
    }
    /**
     * Make status signature.
     * @return string
     */
    public function make_status_signature() {
        $config = $this->order->get_gateway_config();
        $string = $config->merchantid;
        $string .= $this->order->get_id();
        $string .= $config->hashcode;
        return hash('sha256' , $string);
    }

    /**
     * Verify reference signature.
     * @param string $signature
     * @param array $strings
     * @return bool
     */
    public function verify_signature($signature, $strings) {
        $str = '';
        foreach ($strings as $key => $string) {
            if (stripos('amount', $key) !== false
               || stripos('fees', $key) !== false) {
                $string = format_float($string, 2);
            }
            $str .= $string;
        }
        return $this->verify_signature_string($signature, $string);
    }
    /**
     * Verify signature from concatenated strings
     * @param string $signature
     * @param string $str
     * @return bool
     */
    public function verify_signature_string($signature, $str) {
        $config = $this->order->get_gateway_config();
        $string = $str . $config->hashcode;
        return $signature === hash('sha256' , $string);
    }
}

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

namespace paygw_fawry\ajax;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

use paygw_fawry\order;
/**
 * Class api
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api extends \external_api {
    /**
     * check_status_parameters
     * @return \external_function_parameters
     */
    public static function check_status_parameters() {
        return new \external_function_parameters([
                'orderid' => new \external_value(PARAM_INT, 'The local order id'),
            ]);
    }

    /**
     * Check the order status
     * @param int $orderid
     * @throws \moodle_exception
     * @return string[]
     */
    public static function check_status($orderid) {
        global $USER;
        require_login(null, false);
        $params = self::validate_parameters(self::check_status_parameters(), ['orderid' => $orderid]);
        $orderid = $params['orderid'];
        $order = new order($orderid);
        $userid = $order->get_userid();
        if ($userid != $USER->id) {
            throw new \moodle_exception('invalid user id');
        }
        return [
            'status' => $order->get_status(),
        ];
    }
    /**
     * The returned values of check_status()
     * @return \external_single_structure
     */
    public static function check_status_returns() {
        return new \external_single_structure([
            'status' => new \external_value(PARAM_ALPHA, 'The order status'),
        ]);
    }
}

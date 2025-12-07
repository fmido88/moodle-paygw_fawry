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

use core_payment\local\entities\payable;
use core_payment\helper;

/**
 * Class order
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class order {
    /**
     * The order id
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $itemid;
    /**
     * @var string
     */
    protected $component;
    /**
     * @var string
     */
    protected $paymentarea;
    /**
     * @var float
     */
    protected $rawcost;
    /**
     * @var float
     */
    protected $cost;
    /**
     * @var string
     */
    protected $currency;
    /**
     * The order status
     * @var string
     */
    protected $status;
    /**
     * @var int
     */
    public $timecreated;
    /**
     * @var int
     */
    protected $timemodified;
    /**
     * The payment id in the payments table.
     * @var int
     */
    protected $paymentid;
    /**
     * The fawry reference.
     * @var string
     */
    protected $reference;
    /**
     * @var payable
     */
    protected $payable;
    /**
     * @var \moodle_url
     */
    protected $successurl;
    /**
     * @var int
     */
    protected $userid;
    /**
     * The user object
     * @var \stdClass
     */
    protected $user;
    /**
     * The database table name
     */
    public const TABLENAME = 'paygw_fawry_orders';

    /**
     * Create a class to manage an order locally
     * @param int $orderid
     */
    public function __construct($orderid) {
        global $DB;
        $this->id = $orderid;

        $this->load_record_data();

        try {
            $this->payable = helper::get_payable($this->component, $this->paymentarea, $this->itemid);
        } catch (\Throwable $e) {
            if (!($e instanceof \dml_exception)) {
                throw $e;
            }
            $this->payable = null;
        }

        if (!empty($this->payable)) {
            $this->rawcost = $this->payable->get_amount();
            $this->currency = $this->payable->get_currency();
        } else if (!empty($this->paymentid)) {
            $conditions = [
                'id'          => $this->paymentid,
                'component'   => $this->component,
                'paymentarea' => $this->paymentarea,
                'itemid'      => $this->itemid,
                'gateway'     => 'fawry',
            ];
            $payment = $DB->get_record('payments', $conditions);
            if (!empty($payment)) {
                $this->rawcost = $payment->amount;
                $this->currency = $payment->currency;
            } else if (!empty($e)) {
                throw $e;
            }
        } else {
            if (!empty($e)) {
                throw $e;
            } else {
                throw new \moodle_exception('cannot find the order');
            }
        }

        $surcharge = helper::get_gateway_surcharge('fawry');
        $this->cost = helper::get_rounded_cost($this->rawcost, $this->currency, $surcharge);
    }

    /**
     * Load the record data.
     * @return void
     */
    protected function load_record_data() {
        global $DB;
        $record = $DB->get_record(self::TABLENAME, ['id' => $this->id], '*', MUST_EXIST);

        foreach ($record as $key => $value) {
            if (property_exists($this, $key) && !empty($value)) {
                $this->$key = $value;
            }
        }
    }
    /**
     * Set the fawry payment id
     * @param int $reference
     * @param bool $updaterecord
     */
    public function set_fawry_reference($reference, $updaterecord = true) {
        $this->reference = $reference;
        if ($updaterecord) {
            $this->update_record();
        }
    }

    /**
     * Set the payment id local one.
     * @param int $id
     * @param bool $updaterecord
     */
    public function set_paymentid($id, $updaterecord = true) {
        $this->paymentid = $id;
        if ($updaterecord) {
            $this->update_record();
        }
    }

    /**
     * Update the order status
     * @param string $status
     * @param bool $updaterecord
     */
    public function update_status($status, $updaterecord = true) {
        $this->status = \core_text::strtolower($status);
        if ($updaterecord) {
            $this->update_record();
        }
    }

    /**
     * Get the local (merchant) order id
     * which is the same as the table
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }
    /**
     * Get fawry orderid.
     * @return int|null
     */
    public function get_fawry_reference() {
        return $this->reference ?? null;
    }

    /**
     * Return the id of the user.
     * @return int
     */
    public function get_userid() {
        return $this->userid;
    }
    /**
     * Get the user object
     * @return \stdClass
     */
    public function get_user() {
        if (!empty($this->user)) {
            return $this->user;
        }

        $this->user = \core_user::get_user($this->userid);
        return $this->user;
    }
    /**
     * Get the currency of this transaction
     * @return string
     */
    public function get_currency() {
        return $this->currency;
    }

    /**
     * Get the cost after adding the surcharge
     * @return float
     */
    public function get_cost() {
        return $this->cost;
    }

    /**
     * Get the raw cost without surcharge.
     * @return float
     */
    public function get_raw_cost() {
        return $this->rawcost;
    }

    /**
     * Get the component
     * @return string
     */
    public function get_component() {
        return $this->component;
    }

    /**
     * Get the payment account id for this item
     * @return int
     */
    public function get_account_id() {
        return $this->payable->get_account_id();
    }

    /**
     * Get paymentarea
     * @return string
     */
    public function get_paymentarea() {
        return $this->paymentarea;
    }

    /**
     * Return the itemid
     * @return int
     */
    public function get_itemid() {
        return $this->itemid;
    }

    /**
     * Return the order status.
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Get the payment configurations
     * @return \stdClass
     */
    public function get_gateway_config() {
        $config = (object)helper::get_gateway_configuration($this->component,
                                                            $this->paymentarea,
                                                            $this->itemid,
                                                            'fawry');

        return $config;
    }

    /**
     * Get redirect url
     * @return \moodle_url
     */
    public function get_redirect_url() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        if (!empty($this->successurl)) {
            return $this->successurl;
        }
        // Find redirection.
        $url = new \moodle_url('/');
        // Method only exists in 3.11+.
        if (method_exists('\core_payment\helper', 'get_success_url')) {
            $url = helper::get_success_url($this->component, $this->paymentarea, $this->itemid);
        } else if (($this->component == 'enrol_fee' && $this->paymentarea == 'fee')
                || ($this->component == 'enrol_wallet' && $this->paymentarea == 'enrol')) {
            $enrol = explode('_', $this->component, 2)[1];
            $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => $enrol, 'id' => $this->itemid]);
            if (!empty($courseid)) {
                $url = course_get_url($courseid);
            }
        }

        $this->successurl = $url;
        return $this->successurl;
    }

    /**
     * Save the payment and process the order
     * This will automatically update the record.
     */
    public function payment_complete($checkrecord = false) {

        if ($this->status == 'success' && !empty($this->paymentid)) {
            return;
        }

        if ($checkrecord) {
            // Update any change in the status.
            $this->load_record_data();
        }

        $paymentid = helper::save_payment($this->get_account_id(),
                                $this->component,
                                $this->paymentarea,
                                $this->itemid,
                                $this->userid,
                                $this->rawcost,
                                $this->currency,
                                'fawry');

        $this->update_status('success', false);
        $this->set_paymentid($paymentid);

        helper::deliver_order($this->component,
                              $this->paymentarea,
                              $this->itemid,
                              $paymentid,
                              $this->userid);

        // Notify user.
        \core\notification::success(get_string('payment_successful', 'paygw_fawry'));
    }

    /**
     * Update the data base record.
     */
    protected function update_record() {
        global $DB;

        $this->timemodified = time();
        $record = [
            'id'           => $this->id,
            'status'       => $this->status,
            'timemodified' => time(),
        ];
        if ($paymentid = $this->paymentid ?? null) {
            $record['paymentid'] = $paymentid;
        }
        if ($reference = $this->get_fawry_reference()) {
            $record['reference'] = $reference;
        }
        $DB->update_record(self::TABLENAME, (object)$record);
    }
    /**
     * Create a new order.
     * @param string $component
     * @param string $paymentarea
     * @param string $itemid
     */
    public static function created_order($component, $paymentarea, $itemid) {
        global $USER, $DB;
        // Try to get an order with the same data in the last day.
        $data = [
            'itemid'       => $itemid,
            'component'    => $component,
            'paymentarea'  => $paymentarea,
            'userid'       => $USER->id,
            'status'       => 'new',
            'status2'      => 'unpaid',
            'status3'      => 'pending',
            'timetocheck'  => time() - DAYSECS,
        ];
        $select = "itemid = :itemid AND component = :component AND paymentarea = :paymentarea";
        $select .= " AND userid = :userid AND (status = :status OR status = :status2 OR status = :status3) AND timecreated >= :timetocheck";
        $records = $DB->get_records_select(self::TABLENAME, $select, $data, 'timecreated DESC', 'id', 0, 1);
        if (!empty($records)) {
            return new order(reset($records)->id);
        }

        // Create a new one.
        unset($data['timetocheck']);
        unset($data['status2'], $status['status3']);

        $data['timecreated'] = $data['timemodified'] = time();
        $orderid = $DB->insert_record(self::TABLENAME, (object)$data);
        return new order($orderid);
    }

    /**
     * Make instance of order management class by passing the fawry
     * payment id.
     * @param int $reference
     * @return order
     */
    public static function instance_form_fawry_paymentid($reference) {
        global $DB;
        $record = $DB->get_record(self::TABLENAME, ['reference' => $reference], 'id', MUST_EXIST);
        return new order($record->id);
    }
    /**
     * Get all orders
     * @param int $from
     * @param int $to
     * @param string|array|null $status
     * @return array[order]
     */
    public static function get_orders($from = 0, $to = 0, $status = null) {
        global $DB;
        $select = "1=1";
        $params = [];
        if ($from > 0) {
            $select .= " AND timecreated >= :fromtime";
            $params['fromtime'] = $from;
        }

        if ($to > 0) {
            $select .= " AND timecreated <= :totime";
            $params['totime'] = $to;
        }

        if (!empty($status)) {
            if (is_array($status)) {
                [$in, $inparams] = $DB->get_in_or_equal($status, SQL_PARAMS_NAMED, 'stat');
                $select .= " AND status $in";
                $params = array_merge($params, $inparams);
            } else {
                $select .= " AND status = :stat";
                $params['stat'] = $status;
            }
        }

        $records = $DB->get_records_select(self::TABLENAME, $select, $params, '', 'id');
        $orders = [];
        foreach ($records as $record) {
            try {
                $orders[$record->id] = new order($record->id);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $orders;
    }
}


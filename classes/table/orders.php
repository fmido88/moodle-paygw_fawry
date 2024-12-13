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

namespace paygw_fawry\table;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Orders reports.
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orders extends \table_sql {
    /**
     * Construct orders table
     * @param string|\moodle_url $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct('paymob_orders');

        $this->is_downloadable(true);
        $this->is_downloading(optional_param('download', "", PARAM_ALPHA), 'paymob_report_' . time());
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        $columns = [
            'id'           => get_string('localorderid', 'paygw_fawry'),
            'fullname'     => get_string('user'),
            'itemid'       => get_string('itemid', 'paygw_fawry'),
            'paymentarea'  => get_string('paymentarea', 'paygw_fawry'),
            'component'    => get_string('component', 'paygw_fawry'),
            'payment_id'   => get_string('paymentid', 'paygw_fawry'),
            'amount'       => get_string('amount', 'paygw_fawry'),
            'currency'     => get_string('currency'),
            'status'       => get_string('status'),
            'timecreated'  => get_string('timecreated'),
            'timemodified' => get_string('timemodified', 'paygw_fawry'),
        ];

        if (!$this->is_downloading()) {
            $columns['check'] = get_string('check_status', 'paygw_fawry');
            $this->no_sorting('check');
        }

        $this->define_baseurl($baseurl);
        $this->define_columns(array_keys($columns));
        $this->no_sorting('amount');
        $this->no_sorting('currency');

        $this->define_headers(array_values($columns));
        $this->set_our_sql();
    }

    /**
     * Set our proper sql.
     * @return void
     */
    public function set_our_sql() {
        global $USER;
        $ufieldsapi = \core_user\fields::for_name();
        $ufields = $ufieldsapi->get_sql('u')->selects;
        $fields = "ord.* $ufields";
        $from = "{paygw_fawry_orders} ord";
        $from .= " JOIN {user} u ON u.id = ord.userid";
        $where = '';
        $params = [];
        if (!has_capability('paygw/fawry:viewreport', \context_system::instance())) {
            $where .= "ord.userid = :userid";
            $params['userid'] = $USER->id;
        }
        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Override to add order object.
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @return void
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;
        parent::query_db($pagesize, $useinitialsbar);
        foreach ($this->rawdata as $key => $record) {
            try {
                $this->rawdata[$key]->order = new \paygw_fawry\order($record->id);
            } catch (\dml_missing_record_exception $e) {
                continue;
            }
        }
    }

    /**
     * Render the check button
     * @param \stdClass $row
     * @return string
     */
    public function col_check($row) {
        global $DB;
        if (in_array($row->status, ['paid', 'completed'])) {
            return $row->status;
        }
        $attributes = [
            'data-action'  => 'check-status',
            'data-orderid' => $row->id,
            'class'        => 'btn btn-secondary',
        ];
        return \html_writer::tag('button', get_string('check_status', 'paygw_fawry'), $attributes);
    }

    /**
     * Amount
     * @param object $row
     * @return string
     */
    public function col_amount($row) {
        if (empty($row->order)) {
            return '';
        }
        $order = $row->order;
        return $order->get_cost();
    }

    /**
     * Currency
     * @param object $row
     * @return string
     */
    public function col_currency($row) {
        if (empty($row->order)) {
            return '';
        }
        $order = $row->order;
        return $order->get_currency();
    }

    /**
     * Other columns
     * @param string $column
     * @param object $row
     * @return string
     */
    public function other_cols($column, $row) {
        if (in_array($column, ['timecreated', 'timemodified'])) {
            return userdate($row->$column);
        }

        return $row->$column;
    }
}

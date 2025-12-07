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

namespace paygw_fawry\task;

use paygw_fawry\order;

/**
 * Class check_pending
 *
 * @package    paygw_fawry
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_pending extends \core\task\scheduled_task {
    /**
     * Get the name of the task
     *
     * @return string
     */
    public function get_name() {
        return get_string('checkpendingtask', 'paygw_fawry');
    }

    /**
     * Execute the task
     */
    public function execute() {
        global $DB;
        mtrace('Fawry check pending orders task started...');

        $orders = order::get_orders(time() - 10 * DAYSECS, 0, ['pending', 'unpaid']);
        // Todo: remove glitched orders fix later.
        // [$in, $params] = $DB->get_in_or_equal(['paid', 'completed', 'success', 'new'], SQL_PARAMS_NAMED);
        // $glitched = $DB->get_records_select('paygw_fawry_orders', "status $in AND reference IS NULL", $params, '', 'id');

        // foreach ($glitched as $record) {
        //     if (isset($orders[$record->id])) {
        //         continue;
        //     }
        //     try {
        //         $orders[$record->id] = new order($record->id);
        //     } catch (\Throwable $e) {
        //         mtrace("Error loading glitched order ID {$record->id}: " . $e->getMessage());
        //         continue;
        //     }
        // }

        if (empty($orders)) {
            mtrace('No pending orders found.');
            return;
        }

        foreach ($orders as $order) {
            $id = $order->get_id();

            try {
                $reference = new \paygw_fawry\reference($order);
                $response = (object)$reference->request_status();
            } catch (\Throwable $e) {
                mtrace("Error checking order ID {$id}: " . $e->getMessage());
                continue;
            }

            if (($response->status ?? '') == 'error') {
                mtrace("Error checking order ID {$id}: " . ($response->message ?? 'Unknown error'));
                continue;
            }

            mtrace("Checked order ID {$id}: " . json_encode($response));
        }

        mtrace('Fawry check pending orders task completed.');
    }
}

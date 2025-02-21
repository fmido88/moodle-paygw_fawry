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
 * Class hook_callback
 *
 * @package    paygw_fawry
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callback {
    /**
     * Add fawry reference report to navigation menu.
     * @param \core\hook\navigation\primary_extend $hook
     * @return void
     */
    public static function add_reports_to_nav(\core\hook\navigation\primary_extend $hook) {
        global $DB, $USER;
        $enabled = (bool)get_config('paygw_fawry', 'paygw_fawry/referenceinnav');
        if (!$enabled) {
            return;
        }

        $view = $hook->get_primaryview();
        $isadmin = has_capability('paygw/fawry:viewreport', \context_system::instance());
        $hasview = clone $isadmin;
        if (!$hasview) {
            $hasview = $DB->record_exists('paygw_fawry_orders', ['userid' => $USER->id]);
        }

        if ($hasview) {
            $view->add(
                $isadmin ? get_string('fawryreport', 'paygw_fawry') : get_string('fawryreferences', 'paygw_fawry'),
                new \moodle_url('/payment/gateway/fawry/report.php'),
            );
        }
    }
}

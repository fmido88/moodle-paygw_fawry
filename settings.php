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
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('paygw_fawry_settings', '', get_string('pluginname_desc', 'paygw_fawry')));

    \core_payment\helper::add_common_gateway_settings($settings, 'paygw_fawry');
    $settings->add(new admin_setting_configcheckbox('paygw_fawry/referenceinnav',
                                                    get_string('referenceinnav', 'paygw_fawry'),
                                                    get_string('referenceinnav_desc', 'paygw_fawry'), 0));
    $settings->add(new admin_setting_configcheckbox('paygw_fawry/checkcron',
                                                    get_string('checkcron', 'paygw_fawry'),
                                                    get_string('checkcron_desc', 'paygw_fawry'), 0));
}

if (has_capability('paygw/fawry:viewreport', context_system::instance())) {
    $reporturl = new moodle_url("/payment/gateway/fawry/report.php");
    $report = new admin_externalpage('paygw_fawry_payment_report',
                                    get_string('orders_report', 'paygw_fawry'),
                                    $reporturl, 'paygw/fawry:viewreport');
    $ADMIN->add('reports', $report);
}

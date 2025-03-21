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

/**
 * TODO describe file report
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_login();

$url = new moodle_url('/payment/gateway/fawry/report.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$table = new \paygw_fawry\table\orders($url);

if (!$table->is_downloading()) {
    $title = get_string('orders_report', 'paygw_fawry');
    $PAGE->set_heading($title);
    $PAGE->set_title($title);

    $PAGE->requires->js_call_amd('paygw_fawry/check_status', 'init', []);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
}

$table->out(50, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}

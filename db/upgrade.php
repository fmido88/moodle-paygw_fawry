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
 * Upgrade steps for Fawry Payment
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    paygw_fawry
 * @category   upgrade
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_paygw_fawry_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2023071401) {

        // Rename field timemodified on table paygw_fawry_orders to timemodified.
        $table = new xmldb_table('paygw_fawry_orders');
        $field = new xmldb_field('timeupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'timecreated');

        // Launch rename field timemodified.
        $dbman->rename_field($table, $field, 'timemodified');

        // Fawry savepoint reached.
        upgrade_plugin_savepoint(true, 2023071401, 'paygw', 'fawry');
    }
    return true;
}

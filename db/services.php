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
 * External functions and service declaration for Fawry Payment
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    paygw_fawry
 * @category   webservice
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_fawry_check_status' => [
        'classname'   => 'paygw_fawry\ajax\api',
        'methodname'  => 'check_status',
        'description' => 'Check the status of the order',
        'type'        => 'read',
        'services'    => [],
        'ajax'        => true,
    ],
    'paygw_fawry_instant_check' => [
        'classname'   => 'paygw_fawry\ajax\api',
        'methodname'  => 'instant_check',
        'description' => 'Check the status of the order directly by inquiry from Fawry',
        'type'        => 'read',
        'services'    => [],
        'ajax'        => true,
    ],
];

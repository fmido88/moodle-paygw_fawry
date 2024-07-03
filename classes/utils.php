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
 * Class utils
 *
 * @package    paygw_fawry
 * @copyright  2024 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Validate an Egyptian phone number
     * @param string $number
     * @return null|string
     */
    public static function validate_phone_number($number) {
        if (empty($number)) {
            return null;
        }

        // Remove spaces and non-numeric characters.
        $number = preg_replace('/\D/', '', $number);

        // Remove leading international code if present.
        if (strpos($number, '002') === 0) {
            $number = substr($number, 3);
        } else if (strpos($number, '2') === 0) {
            $number = substr($number, 1);
        } else if (strlen($number) === 10 && strpos($number, '1') === 0) {
            $number = '0' . $number;
        }

        $code = substr($number, 0, 3);
        $acceptable = ['010', '012', '011', '015'];
        if (!in_array($code, $acceptable)) {
            return null;
        }

        if (strlen($number) === 11) {
            return $number;
        }

        return null;
    }
}

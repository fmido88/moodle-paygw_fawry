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
 * Contains class for fawry payment gateway.
 *
 * @package    paygw_fawry
 * @copyright  2023 Mo. Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_fawry;

/**
 * The gateway class for PayPal payment gateway.
 *
 * @package    paygw_fawry
 * @copyright  2023 Mo. Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {
    /**
     * Returns the list of currencies that the payment gateway supports.
     * return an array of the currency codes in the three-character ISO-4217 format
     * @return array<string>
     */
    public static function get_supported_currencies(): array {
        return ['EGP'];
    }

    /**
     * Configuration form for the gateway instance
     *
     * Use $form->get_mform() to access the \MoodleQuickForm instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('advcheckbox', 'staging', get_string('staging', 'paygw_fawry'), '', ['group' => 1], [false, true]);

        $mform->addElement('text', 'merchantid', get_string('merchantid', 'paygw_fawry'));
        $mform->setType('merchantid', PARAM_TEXT);
        $mform->addHelpButton('merchantid', 'merchantid', 'paygw_fawry');

        $mform->addElement('passwordunmask', 'hashcode', get_string('hashcode', 'paygw_fawry'));
        $mform->setType('hashcode', PARAM_TEXT);
        $mform->addHelpButton('hashcode', 'hashcode', 'paygw_fawry');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
                                                 \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled) {
            if (empty($data->merchantid)) {
                $errors['merchantid'] = get_string('required');
            }
            if (empty($data->hashcode)) {
                $errors['hashcode'] = get_string('required');
            }
            if (!empty($errors)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
        }
    }
}

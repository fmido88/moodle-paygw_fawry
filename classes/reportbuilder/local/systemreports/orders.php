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

namespace paygw_fawry\reportbuilder\local\systemreports;

use core\output\pix_icon;
use core\url;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\report\action;
use core_reportbuilder\system_report;
use paygw_fawry\reportbuilder\local\entities\order;
use stdClass;

/**
 * Class orders
 *
 * @package    paygw_fawry
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orders extends system_report {
    protected function can_view(): bool {
        return is_siteadmin();
    }
    protected function initialise(): void {
        $orderentity = new order();
        $orderalias = $orderentity->get_table_alias(\paygw_fawry\order::TABLENAME);
        $this->set_main_table(\paygw_fawry\order::TABLENAME, $orderalias);
        $this->add_entity($orderentity);

        $this->add_base_fields("{$orderalias}.id");

        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $userentity->add_join("JOIN {user} {$useralias} ON {$useralias}.id = {$orderalias}.userid");
        $this->add_entity($userentity);

        $columns = [
            'order:id',
            'user:fullnamewithlink',
            'order:itemid',
            'order:componentarea',
            'order:paymentid',
            'order:reference',
            'order:amount',
            'order:status',
            'order:timecreated',
            'order:timemodified',
        ];
        $this->add_columns_from_entities($columns);

        $filters1 = [
            'order:id',
            'order:itemid',
            'order:component_paymentarea',
            'order:paymentid',
            'order:reference',
            'order:status',
        ];
        $this->add_filters_from_entities($filters1);
        $userfilters = $userentity->get_filters();
        $userfilters = [
            'fullname',
            'firstname',
            'lastname',
            'username',
            'email',
            'phone1',
            'phone2',
        ];
        $identityfilters = $userentity->get_identity_filters($this->get_context());
        foreach ($identityfilters as $filter) {
            $userfilters[] = $filter->get_name();
        }

        $this->add_filters_from_entity($userentity->get_entity_name(), array_unique($userfilters));
        $filters2 = [
            'order:timecreated',
            'order:timemodified',
        ];
        $this->add_filters_from_entities($filters2);

        $this->set_checkbox_toggleall(static function(stdClass $row) {
            return [$row->id, $row->id];
        });

        $this->set_downloadable(true);

        $attributes = [
            'data-action'  => 'check-status',
            'data-orderid' => ':id',
        ];
        $action = new action(
            url: new url('#'),
            icon: new pix_icon('i/info', get_string('check_status', 'paygw_fawry')),
            attributes: $attributes
        );

        $this->add_action($action);
    }
}

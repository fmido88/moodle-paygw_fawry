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

namespace paygw_fawry\reportbuilder\local\entities;

use core\output\pix_icon;
use core_payment\helper;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\category;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\action;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

/**
 * Class order
 *
 * @package    paygw_fawry
 * @copyright  2025 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class order extends base {
    public static array $orders = [];
    public function get_default_entity_title(): lang_string {
        return new lang_string('order', 'paygw_fawry');
    }
    protected function get_default_tables(): array {
        return [\paygw_fawry\order::TABLENAME];
    }
    public function initialise(): self {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    public function get_all_filters(): array {
        $filters = [];
        $orderalias = $this->get_table_alias(\paygw_fawry\order::TABLENAME);

        $filters[] = new filter(
            number::class,
            'id',
            new lang_string('order_id', 'paygw_fawry'),
            $this->get_entity_name(),
            "{$orderalias}.id");

        $filters[] = new filter(
            text::class,
            'reference',
            new lang_string('reference', 'paygw_fawry'),
            $this->get_entity_name(),
            "{$orderalias}.reference");

        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('status', 'paygw_fawry'),
            $this->get_entity_name(),
            "{$orderalias}.status"
        ))->set_options(self::get_status_options());

        $filters[] = new filter(
            number::class,
            'itemid',
            new lang_string('itemid', 'paygw_fawry'),
            $this->get_entity_name(),
            "{$orderalias}.itemid");

        $filters[] = (new filter(
            select::class,
            'component_paymentarea',
            new lang_string('component_paymentarea', 'paygw_fawry'),
            $this->get_entity_name(),
            "CONCAT({$orderalias}.component, '-', {$orderalias}.paymentarea)"
        ))->set_options(self::get_components_options());

        $filters[] = new filter(
            number::class,
            'paymentid',
            new lang_string('paymentid', 'paygw_fawry'),
            $this->get_entity_name(),
            "{$orderalias}.paymentid"
        );

        $filters[] = new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated'),
            $this->get_entity_name(),
            "{$orderalias}.timecreated");

        $filters[] = new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'reportbuilder'),
            $this->get_entity_name(),
            "{$orderalias}.timemodified");

        return $filters;
    }
    public function get_all_columns(): array {
        $columns = [];
        $orderalias = $this->get_table_alias(\paygw_fawry\order::TABLENAME);
        $columns[] = (new column(
            'id',
            new lang_string('order_id', 'paygw_fawry'),
            $this->get_entity_name()))
            ->set_is_sortable(true)
            ->set_type(column::TYPE_INTEGER)
            ->add_joins($this->get_joins())
            ->add_field("{$orderalias}.id");

        $columns[] = (new column('itemid',
                    new lang_string('itemid', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_INTEGER)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.itemid");

        $columns[] = (new column('component',
                    new lang_string('resetcomponent'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_TEXT)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.component")
                    ->set_callback(function($value) {
                        return new lang_string('pluginname', $value);
                    });

        $columns[] = (new column('paymentarea',
                    new lang_string('paymentarea', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_TEXT)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.paymentarea");

        $columns[] = (new column('componentarea',
                    new lang_string('component_paymentarea', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(false)
                    ->set_type(column::TYPE_TEXT)
                    ->add_joins($this->get_joins())
                    ->add_fields("{$orderalias}.component, {$orderalias}.paymentarea")
                    ->set_callback(function($value, $row) {
                        return get_string('pluginname', $row->component) . " | " . $row->paymentarea;
                    });

        $columns[] = (new column('amount',
                    new lang_string('amount', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(false)
                    ->set_type(column::TYPE_FLOAT)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.id")
                    ->set_callback(function($value): string {
                        if ($order = self::get_order($value)) {
                            return helper::get_cost_as_string($order->get_cost(), $order->get_currency());
                        }
                        return '';
                    });

        $columns[] = (new column('paymentid'
                    , new lang_string('paymentid', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_INTEGER)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.paymentid");

        $columns[] = (new column('reference',
                    new lang_string('reference', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_INTEGER)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.reference");

        $columns[] = (new column('status',
                    new lang_string('status', 'paygw_fawry'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_TEXT)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.status")
                    ->add_attributes(['data-purpose' => 'order-status']);

        $columns[] = (new column('timecreated',
                    new lang_string('timecreated'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_TIMESTAMP)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.timecreated")
                    ->set_callback(function($value, $row) {
                        return format::userdate($value, $row);
                    });

        $columns[] = (new column('timemodified',
                    new lang_string('timemodified', 'reportbuilder'),
                    $this->get_entity_name()))
                    ->set_is_sortable(true)
                    ->set_type(column::TYPE_TIMESTAMP)
                    ->add_joins($this->get_joins())
                    ->add_field("{$orderalias}.timemodified")
                    ->set_callback(function($value, $row) {
                        return format::userdate($value, $row);
                    });

        return $columns;
    }

    public static function get_order(int $id): \paygw_fawry\order|false {
        if (!isset(self::$orders[$id])) {
            try {
                self::$orders[$id] = new \paygw_fawry\order($id);
            } catch (\Throwable $e) {
                self::$orders[$id] = false;
            }
        }
        return self::$orders[$id];
    }

    public static function get_status_options(): array {
        global $DB;
        return $DB->get_records_menu(\paygw_fawry\order::TABLENAME, [], 'status', 'DISTINCT(status) as stat, status');
    }

    public static function get_components_options(): array {
        global $DB;
        $fields = "DISTINCT(CONCAT(component, '-', paymentarea)) as combine, component, paymentarea";
        $records = $DB->get_records(\paygw_fawry\order::TABLENAME, null, '', $fields);
        $options = [];
        foreach ($records as $record) {
            $options[$record->combine] = get_string('pluginname', $record->component) . " | " . $record->paymentarea;
        }
        return $options;
    }
}

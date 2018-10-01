<?php

namespace Winged\Utils;

use Winged\Date\Date;

class Order
{

    /**
     * @var $results Model[] | array
     */
    public $results = null;
    public $fields = [];
    public $type = 'object';
    public $orders = [];

    public static function init($all)
    {
        if (is_array($all) && !empty($all) && !is_null($all)) {
            if (array_key_exists(0, $all)) {
                if (is_array($all[0])) {
                    return new Order($all);
                }
                if (is_object($all[0])) {
                    if (is_subclass_of($all[0], 'Winged\Model\Model')) {
                        return new Order($all);
                    }
                }
            }
        }
        return new Order(null);
    }

    public static function add($all)
    {
        return self::init($all);
    }

    public function __construct($results)
    {
        $this->results = $results;
        if (!is_null($this->results)) {
            if (is_array($this->results[0])) {
                $this->type = 'array';
                foreach ($this->results[0] as $key => $value) {
                    $this->fields[] = $key;
                }
            } else {
                $this->type = 'object';
                $this->fields = $this->results[0]->getTableFields();
            }
        }
    }

    public function orderByDesc($field)
    {
        return $this->orderBy($field, 'desc');
    }

    public function orderByAsc($field)
    {
        return $this->orderBy($field, 'asc');
    }

    private function orderBy($field, $direction)
    {
        $direction = strtolower(trim($direction));
        if ($direction !== 'asc' && $direction !== 'desc') {
            $direction = 'desc';
        }
        if (in_array($field, $this->fields)) {
            $this->orders[] = [$field, $direction];
        }
        return $this;
    }

    public function execute()
    {
        if ($this->results != null) {
            if ($this->type === 'array') {
                $this->executeArray();
            } else {
                $this->executeObject();
            }
            return $this->results;
        }
        return null;
    }

    private function executeArray()
    {

        $results = $this->results;
        $orders = $this->orders;
        $ordersCount = is_array($orders) ? count7($orders) : 0;
        $groups = [];
        if ($ordersCount === 0 || count7($results) === 1) {
            $this->results = $results;
            return;
        }
        while ($ordersCount > 0) {
            $remake_results = [];
            $order = array_shift($orders);
            list($field, $direction) = $order;
            $groupsCount = is_array($groups) ? count7($groups) : 0;
            if ($groupsCount > 0) {
                $merged = [];
                foreach ($groups as $key => $group) {
                    $remake_results = [];
                    $filter = [];
                    $new_group = [];
                    $grouped = 0;
                    foreach ($group as $key_z => $result) {
                        $filter[$key_z] = $result[$field];
                    }
                    if ($direction === 'asc') {
                        asort($filter);
                    } else {
                        arsort($filter);
                    }
                    foreach ($filter as $key_y => $value) {
                        $remake_results[] = $group[$key_y];
                    }
                    $begin = $remake_results[0][$field];
                    $new_group[$grouped] = [];
                    foreach ($remake_results as $remake_result) {
                        if ($remake_result[$field] != $begin) {
                            $begin = $remake_result[$field];
                            $grouped++;
                            if (!array_key_exists($grouped, $new_group)) {
                                $new_group[$grouped] = [];
                            }
                        }
                        $new_group[$grouped][] = $remake_result;
                    }
                    $merged = array_merge($merged, $new_group);
                }
                $groups = $merged;
            } else {
                $filter = [];
                foreach ($results as $key => $result) {
                    $filter[$key] = $result[$field];
                }
                if ($direction === 'asc') {
                    asort($filter);
                } else {
                    arsort($filter);
                }
                foreach ($filter as $key => $value) {
                    $remake_results[] = $results[$key];
                }
                $begin = $remake_results[0][$field];
                $grouped = 0;
                $groups[$grouped] = [];
                foreach ($remake_results as $remake_result) {
                    if ($remake_result[$field] != $begin) {
                        $begin = $remake_result[$field];
                        $grouped++;
                        if (!array_key_exists($grouped, $groups)) {
                            $groups[$grouped] = [];
                        }
                    }
                    $groups[$grouped][] = $remake_result;
                }
            }
            $ordersCount = is_array($orders) ? count7($orders) : 0;
        }
        $merged = [];
        foreach ($groups as $group) {
            $merged = array_merge($merged, $group);
        }
        $this->results = $merged;
    }

    private function executeObject()
    {
        $results = $this->results;
        $orders = $this->orders;
        $ordersCount = is_array($orders) ? count7($orders) : 0;
        $groups = [];
        if ($ordersCount === 0 || count7($results) === 1) {
            $this->results = $results;
            return;
        }
        while ($ordersCount > 0) {
            $remake_results = [];
            $order = array_shift($orders);
            list($field, $direction) = $order;
            $groupsCount = is_array($groups) ? count7($groups) : 0;
            if ($groupsCount > 0) {
                $merged = [];
                foreach ($groups as $key => $group) {
                    $remake_results = [];
                    $filter = [];
                    $new_group = [];
                    $grouped = 0;
                    foreach ($group as $key_z => $result) {
                        $value = $result->{$field};
                        if(is_object($value)){
                            switch (get_class($value)){
                                case 'Winged\Date\Date':
                                    /**
                                     * @var $value Date
                                     */
                                    $value = $value->timestamp();
                                    break;
                                default:
                                    break;
                            }
                        }
                        $filter[$key_z] = $value;
                    }
                    if ($direction === 'asc') {
                        asort($filter);
                    } else {
                        arsort($filter);
                    }
                    foreach ($filter as $key_y => $value) {
                        $remake_results[] = $group[$key_y];
                    }
                    $begin = $remake_results[0]->{$field};
                    $new_group[$grouped] = [];
                    foreach ($remake_results as $remake_result) {
                        if ($remake_result->{$field} != $begin) {
                            $begin = $remake_result->{$field};
                            $grouped++;
                            if (!array_key_exists($grouped, $new_group)) {
                                $new_group[$grouped] = [];
                            }
                        }
                        $new_group[$grouped][] = $remake_result;
                    }
                    $merged = array_merge($merged, $new_group);
                }
                $groups = $merged;
            } else {
                $filter = [];
                foreach ($results as $key => $result) {
                    $value = $result->{$field};
                    if(is_object($value)){
                        switch (get_class($value)){
                            case 'Winged\Date\Date':
                                /**
                                 * @var $value Date
                                 */
                                $value = $value->timestamp();
                                break;
                            default:
                                break;
                        }
                    }
                    $filter[$key] = $value;
                }
                if ($direction === 'asc') {
                    asort($filter);
                } else {
                    arsort($filter);
                }
                foreach ($filter as $key => $value) {
                    $remake_results[] = $results[$key];
                }
                $begin = $remake_results[0]->{$field};
                $grouped = 0;
                $groups[$grouped] = [];
                foreach ($remake_results as $remake_result) {
                    if ($remake_result->{$field} != $begin) {
                        $begin = $remake_result->{$field};
                        $grouped++;
                        if (!array_key_exists($grouped, $groups)) {
                            $groups[$grouped] = [];
                        }
                    }
                    $groups[$grouped][] = $remake_result;
                }
            }
            $ordersCount = is_array($orders) ? count7($orders) : 0;
        }
        $merged = [];
        foreach ($groups as $group) {
            $merged = array_merge($merged, $group);
        }
        $this->results = $merged;
    }
}
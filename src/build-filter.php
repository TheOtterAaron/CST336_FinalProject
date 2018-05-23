<?php

    function Filter()
    {
        $filter = array
        (
            "rules" => array(),
            "sortOn" => "BOOK.Title",
            "sortOrder" => 'ASC'
        );

        return $filter;
    }

    function SetFilterSort(&$filter, $field, $order)
    {
        $filter['sortOn'] = $field;
        $filter['sortOrder'] = $order;
    }

    function AddFilterRule(&$filter, $name, $field, $minValue, $maxValue = NULL)
    {
        $filter['rules'][] = array
        (
            "name" => $name,
            "field" => $field,
            "minValue" => $minValue,
            "maxValue" => $maxValue
        );
    }

?>
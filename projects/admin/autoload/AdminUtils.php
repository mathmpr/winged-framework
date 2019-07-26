<?php

/**
 * Class AdminUtils
 */
class AdminUtils
{
    /**
     * @param $primaryKeyName
     * @param $tableName
     * @param $entryTuple
     *
     * @return bool|string
     */
    public static function prepareMultipleUpdate($primaryKeyName, $tableName, $entryTuple)
    {
        $str = 'UPDATE ' . $tableName . ' SET ';
        if (is_array($entryTuple) && is_string($primaryKeyName)) {
            if (array_key_exists(0, $entryTuple)) {
                if (array_key_exists($primaryKeyName, $entryTuple[0])) {
                    $fields = [];
                    foreach ($entryTuple[0] as $key => $value) {
                        if ($key !== $primaryKeyName) {
                            $fields[] = $key;
                        }
                    }
                    $first_run = true;
                    $in_stmt = [];
                    foreach ($fields as $field_order => $field) {
                        if ($first_run) {
                            $str .= $field . ' = CASE ';
                        } else {
                            $str .= ', ' . $field . ' = CASE ';
                        }
                        foreach ($entryTuple as $key => $result) {
                            $value = $result[$field];
                            if (!intval($result[$field]) > 0 || !floatval($result[$field]) > 0) {
                                $value = '"' . $value . '"';
                            }
                            $str .= 'WHEN ' . $primaryKeyName . ' = ' . $result[$primaryKeyName] . ' THEN ' . $value . ' ';
                            if ($first_run) {
                                $in_stmt[] = $result[$primaryKeyName];
                            }
                        }
                        $first_run = false;
                        $str .= 'ELSE ' . $field . ' END';
                    }
                    $str .= ' WHERE ' . $primaryKeyName . ' IN (' . join(', ', $in_stmt) . ')';
                    return $str;
                }
            }
        }
        return false;
    }

}
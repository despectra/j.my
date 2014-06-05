<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 18.05.14
 * Time: 18:17
 */

class Utils {
    public static $_SALT_1 = "omnomnomYEhToP4>dfoseofijsdfjjjw";
    public static $_SALT_2 = "IUiun84N98sNiuU<<>>><,.!!@9j";

    public static function dbRowsToAssocArrays($dbHandler, $columns) {
        $data = array();
        while ($row = $dbHandler->fetchRow()) {
            $arrayRow = array();
            foreach($columns as $col) {
                $arrayRow[$col] = $row[$col];
            }
            array_push($data, $arrayRow);
        }
        return $data;
    }
} 
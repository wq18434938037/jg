<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/19 0019
 * Time: 16:33
 */

namespace App\HttpController;
use App\HttpController\Base\Base;


class Database extends Base
{
    function index()
    {
        // TODO: Implement index() method.
            $condifData = \tool::appConfig('MYSQL');
        $mysql_conn = mysqli_connect($condifData['host'], $condifData['user'], $condifData['password']);
        $database = $condifData['database'];
        $name = \tool::appConfig('app_name');
        mysqli_select_db($mysql_conn,$database);
        mysqli_query($mysql_conn,'SET NAMES utf8');
        $table_result = mysqli_query($mysql_conn,'show tables');
        // 取得所有的表名
        while ($row = mysqli_fetch_array($table_result)) {
            $tables [] ['TABLE_NAME'] = $row [0];
        }

        // 循环取得所有表的备注及表中列消息
        foreach ($tables as $k => $v) {
            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.TABLES ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
            $table_result = mysqli_query($mysql_conn,$sql);
            while ($t = mysqli_fetch_array($table_result)) {
                $tables [$k] ['TABLE_COMMENT'] = $t ['TABLE_COMMENT'];
            }

            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

            $fields = array();
            $field_result = mysqli_query($mysql_conn,$sql);
            while ($t = mysqli_fetch_array($field_result)) {
                $fields [] = $t;
            }
            $tables [$k] ['COLUMN'] = $fields;
        }
        mysqli_close($mysql_conn);

        $html = '';
        $header_index = '<div id="floatTips"><ul>';
// 循环所有表
        foreach ($tables as $k => $v) {
            $header_index .= '<li><a href="#' . $v ['TABLE_NAME'] . '" title="购买意向表">' . $v ['TABLE_NAME'] . '</a>(' . $v ['TABLE_COMMENT'] . ' )</li>';
            $html .= '<div style="page-break-before: always;">';
            $html .= '<h2><a name="' . $v ['TABLE_NAME'] . '"></a>' . $v ['TABLE_NAME'] . '' . $v ['TABLE_COMMENT'] . '</h2>';
            $html .= '<table class="print" width="100%"><tbody><tr><th width="50">字段名</th><th width="80">数据类型</th><th width="70">默认值</th> <th width="60">允许非空</th><th width="60">自动递增</th><th>备注</th></tr>';
            $html .= '';

            foreach ($v ['COLUMN'] as $f) {
                $html .= '<tr class="even"><td nowrap="nowrap">' . $f ['COLUMN_NAME'] . '</td>';
                $html .= '<td xml:lang="en" dir="ltr" nowrap="nowrap">' . $f ['COLUMN_TYPE'] . '</td>';
                $html .= '<td>&nbsp;' . $f ['COLUMN_DEFAULT'] . '</td>';
                $html .= '<td nowrap="nowrap">&nbsp;' . $f ['IS_NULLABLE'] . '</td>';
                $html .= '<td>' . ($f ['EXTRA'] == 'auto_increment' ? '是' : '&nbsp;') . '</td>';
                $html .= '<td>' . $f ['COLUMN_COMMENT'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></p>';
        }
        $header_index .= '</ul><span><a href="#top">返回顶部↑</a></span></div>';

        $this->renderTemplate([
            'name' => $name . ' 数据字典',
            'html' => $html,
            'header_index' => $header_index,
        ]);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/2
 * Time: 16:08
 */

namespace app\admin\service;
class ExportService
{
    //excel数据对象
    private static $objPHPExcel;
    //初始行下标数
    private static $hang = 1;
    //excel下标数组
    private static $index = array();
    //title标题所占单元格
    private static $excelArr = array();

    private static $lastCell = '';
    //开始sheet
    private static $sheetIndex = 0;

    //增长变量
    private static $i = 0;
    //header
    private static $header = array();
    /**
     * 下载excel文件
     * @param string $fileName
     * @param $objPHPExcel
     * @return bool
     */
    public static function exportExcel($fileName = '报表', $objPHPExcel)
    {
        $fileName = iconv('utf-8', 'gb2312', $fileName);
        //文件通过浏览器下载
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName.xls");
        header('Cache-Control: max-age=0');
        $objWriter = new  \PHPExcel_Writer_Excel5($objPHPExcel);//PHPExcel_Writer_Excel5 PHPExcel_Writer_Excel2007
        $objWriter->save('php://output');exit;
        $fileName = iconv('utf-8', 'gb2312', $fileName);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=$fileName.xlsx");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        return true;
    }

    //循环生成sheet表
    public static function allDept_name($data){
        foreach ($data as $key=>$datum) {
            self::getExportObjExcel($datum['data'],$key);
        }
        return self::$objPHPExcel;
    }

    /**
     * excel数据导出模板
     * @param array $data
     * @param string $sheet
     * @return \PHPExcel
     */
    public static function getExportObjExcel($data = array(),$sheet=null)
    {
        if (empty(self::$objPHPExcel)){
            vendor("PHPExcel.Classes.PHPExcel");
            self::$objPHPExcel = new \PHPExcel();;
            self::excelIndex();
        }
        self::$hang = 1;
        self::$lastCell = '';
        self::$i = 0;
        self::$header = array();
        self::$excelArr  = array();
        self::$objPHPExcel->createSheet(self::$sheetIndex);
        self::$objPHPExcel->setActiveSheetIndex(self::$sheetIndex);
        self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->setTitle($sheet);
        self::headerRowColToArrary($data['header']);
        self::getObjPHPExcelData(self::$header);
        if (!empty($data['list']))
            self::getObjPHPExcelData($data['list']);
        self::setObjPHPExcelStyle();
        self::$sheetIndex++;
        return self::$objPHPExcel;
    }

    /**
     * 获取数据单元格设置数据对象
     * @param array $data
     */
    public static function getObjPHPExcelData($data = array())
    {
        foreach ($data as $key => $value) {
            $lie = 0;
            foreach ($value as $index => $vdata) {
                //单元格取值键名
                if (is_array($vdata)){
                    $cellValue = $vdata['headerName'];
                    $row = empty($vdata['rowspan'])?1:$vdata['rowspan'];
                    $col = empty($vdata['colspan'])?1:$vdata['colspan'];
                }else{
                    $cellValue = $vdata;
                    if ($index == 'dept'){
                        $row = empty($value['dept_row'])?1:$value['dept_row'];
                        $col = empty($value['dept_col'])?1:$value['dept_col'];
                    }elseif ($index == 'base'){
                        $row = empty($value['base_row'])?1:$value['base_row'];
                        $col = empty($value['base_col'])?1:$value['base_col'];
                    }
                    elseif ($index=='dept_row' || $index=='dept_col' || $index=='base_row' || $index=='base_col'){
                        continue;
                    }else{
                        $row = 1;
                        $col = 1;
                    }
                }
                //合并项_前
                $first_str = self::getFirst(self::$hang, $lie);
                $first = self::getIndex($first_str);
                //合并项_后
                $second_str = self::getSecond($first_str, $row, $col);
                $second = self::getIndex($second_str);
                //合并单元格
                $merage[] = $first . ":" . $second;
                //单元格数据设置
                if (empty($cellValue)){
                    $cellValue = '';
                }
                $info[] = array(
                    $first, $cellValue
                );
                $lie++;
            }
            //行自增
            self::$hang++;
        }
        self::setExcelData($merage, $info);
    }

    //设置表格样式
    public function setObjPHPExcelStyle(){

        self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->getDefaultColumnDimension()->setWidth(16);
        self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->getDefaultRowDimension()->setRowHeight(15);
        if (!empty(self::$lastCell)){
            self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->getStyle('A1:'.self::$lastCell)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->getStyle('A1:'.self::$lastCell)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        self::$objPHPExcel->getDefaultStyle(self::$sheetIndex)->getFont()->setName('Times New Roman');
    }

    /**
     * 设置excel值与合并项
     * @param $merage
     * @param $info
     */
    public static function setExcelData($merage, $info)
    {
        //设置单元格值
        if (count($info)) {
            foreach ($info as $vdata) {
                self::$objPHPExcel->setActiveSheetIndex(self::$sheetIndex)->setCellValue($vdata[0], $vdata[1]);
                self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->getStyle($vdata[0])->getFont()->setSize(10);
            }
            self::$lastCell = $vdata[0];
        }
        //设置合并单元格值
        if (count($merage)) {
            foreach ($merage as $vdata) {
                self::$objPHPExcel->getActiveSheet(self::$sheetIndex)->mergeCells($vdata);
            }
        }
    }

    /**
     * 根据组合字符串换算成excel标准下标
     * @param string $indexStr
     * @return string
     */
    public static function getIndex($indexStr = '')
    {
        $arr = explode(':', $indexStr);
        return self::$index[$arr[0]] . $arr[1];
    }

    /**
     * 获取合并项的第二个参数,并填充已经使用过的单元格在excelArr中
     * @param $second
     * @param $row
     * @param $col
     * @return string
     */
    public static function getSecond($second, $row, $col)
    {
        $secondArr = explode(':', $second);
        if ($row > 1 || $col > 1) {
            for ($i = 0; $i < $row; $i++) {
                for ($j = 0; $j < $col; $j++) {
                    self::$excelArr[] = ($secondArr[0] + $j) . ':' . ($secondArr[1] + $i);
                }
            }
        }
        return end(self::$excelArr);
    }

    /**
     * 获取合并项的第一个参数
     * @param $hang
     * @param $lie
     * @return string
     */
    public static function getFirst($hang, $lie)
    {
        $first = $lie . ':' . $hang;
        if (!in_array($first, self::$excelArr)) {
            self::$excelArr[] = $first;
        } else {
            $lie++;
            $first = self::getFirst($hang, $lie);
        }
        return $first;
    }

    /**
     * excel下标排序
     * @param string $startColumn
     * @return array
     */
    public static function excelIndex($startColumn = 'A')
    {
        //Excel列下标顺序
        for ($i = 1; $i <= 255; $i++) {
            $a[] = $startColumn++;
        }
        self::$index = $a;
    }


    //跨行跨列的header格式转换为rowspan,colspan数组
    public static function headerRowColToArrary($head){
        foreach ($head as $key=>$item) {
            if($item['headerName']=="操作"){
                unset($head[$key]);
            }
            if (empty($item['headerName']))
                continue;
            self::$header[self::$i][] = array(
                'headerName' => $item['headerName'],
                'rowspan' => empty($item['rowspan']) ? 1 : $item['rowspan'],
                'colspan' => empty($item['colspan']) ? 1 : $item['colspan'],
            );
//            var_dump($item['children']);
            if (is_array($item['children'])){
                self::$i++;
                self::headerRowColToArrary($item['children']);
                self::$i--;
            }
        }
    }
}
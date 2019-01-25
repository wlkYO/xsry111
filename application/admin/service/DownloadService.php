<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 17:03
 */

namespace app\admin\service;

class DownloadService
{
    //excel数据对象
    private static $objPHPExcel;
    //初始行下标数
    private static $hang = 1;
    //excel下标数组
    private static $index = array();
    //title标题所占单元格
    private static $excelArr = array();
    private static $mergeArr = array();

    /**
     * 下载excel文件
     * @param string $fileName
     * @param $objPHPExcel
     * @return bool
     */
    public static function downLoadExcel($fileName = '测试表', $objPHPExcel)
    {
        $fileName = iconv('utf-8', 'gb2312', $fileName);
        //文件通过浏览器下载
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName.xls");
        header('Cache-Control: max-age=0');
        $objWriter = new  \PHPExcel_Writer_Excel5($objPHPExcel);//PHPExcel_Writer_Excel5 PHPExcel_Writer_Excel2007
        $objWriter->save('php://output');
        return true;
    }

    /**
     * excel数据导出模板
     * @param array $data
     * @return \PHPExcel
     */
    public static function getObjPHPExcel($data = array())
    {
        vendor("PHPExcel.Classes.PHPExcel");
        self::$objPHPExcel = new \PHPExcel();
        self::excelIndex();
        self::getObjPHPExcelTitle($data['head']);
        self::getObjPHPExcelData($data['data']);
        self::setObjPHPExcelStyle(count($data['data']), $data['head']);
        return self::$objPHPExcel;
    }

    /**
     * 标题头渲染
     * @param array $data
     * @param int $is_data
     */
    public static function getObjPHPExcelTitle($data = array())
    {
        foreach ($data as $key => $value) {
            $lie = 0;
            //数据值
            $row = 1;
            $col = 1;
            //单元格取值键名
            $cellValue = $value;
            //合并项_前
            $first_str = self::getFirst(self::$hang, $lie);
            $first = self::getIndex($first_str);
            //合并项_后
            $second_str = self::getSecond($first_str, $row, $col);
            $second = self::getIndex($second_str);
            //合并单元格
            $merage[] = $first . ":" . $second;
            self::$mergeArr[] = $first . ":" . $second;
            //单元格数据设置
            $info[] = array(
                $first, $cellValue
            );
            $lie++;
            //行自增
//            self::$hang++;
        }
        self::setExcelData($merage, $info);
    }

    public static function getObjPHPExcelData($data = array())
    {
        foreach ($data as $kdata => $vdata) {
            # 字符串下标转数字下标
            $vdata = self::arrConvet($vdata);
            $index = $kdata + 2;
            foreach ($vdata as $key => $val) {
                # phpexel数字转列字母值
                $columnIndex = \PHPExcel_Cell::stringFromColumnIndex($key);
                self::$objPHPExcel->setActiveSheetIndex()->setCellValue($columnIndex.$index,$val);
                # 给每个单元格设置淡蓝色边框
                $gezi = $columnIndex.$index;
                $styleThinBlackBorder = array(
                    'borders' => array (
                        'allborders' => array (
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                            //'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
                            'color' => array ('argb'=>'FF99CDFF'),          //设置border颜色
                        ),
                    ),
                );
                self::$objPHPExcel->getActiveSheet()->getStyle("$gezi:$gezi")->applyFromArray($styleThinBlackBorder);
            }
        }
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
                self::$objPHPExcel->setActiveSheetIndex()->setCellValue($vdata[0], $vdata[1]);
                self::$objPHPExcel->getActiveSheet()->getStyle($vdata[0])->getFont()->setSize(11);
                # 标题栏设置蓝色填充及边框
                $styleThinBlackBorder = array(
                    'borders' => array (
                        'allborders' => array (
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                            //'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
                            'color' => array ('argb'=>'FF99CDFF'),          //设置border颜色
                        ),
                    ),
                );
                $cells = $vdata[0].":".$vdata[0];
//                self::$objPHPExcel->getActiveSheet()->getStyle($cells)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
                # 设置边框
                self::$objPHPExcel->getActiveSheet()->getStyle($cells)->applyFromArray($styleThinBlackBorder);
                # 设置填充
                self::$objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                self::$objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->getStartColor()->setARGB('FF99CDFF');
            }
        }
        //设置合并单元格值
        if (count($merage)) {
            foreach ($merage as $vdata) {
                self::$objPHPExcel->getActiveSheet()->mergeCells($vdata);
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

    public static function arrConvet($arr)
    {
        unset($arr['id']);
        $arr = array_values($arr);
        return $arr;
    }

    public static function setObjPHPExcelStyle($num, $titleArr)
    {
        # 设置自动筛选
//        self::$objPHPExcel->getActiveSheet()->setAutoFilter("A2:AH1");//筛选根据范围设置
        # 冻结窗口设置
        self::$objPHPExcel->getActiveSheet()->freezePane('A2');
        # 设置文档的字体及大小
        self::$objPHPExcel->getDefaultStyle()->getFont()->setName( 'Calibra');
        self::$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        # 设置字体居中
        $lastIndex = $num + 1;
        self::$objPHPExcel->getActiveSheet()->getStyle('A1:E'.$lastIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
//        self::$objPHPExcel->setActiveSheetIndex()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        self::$objPHPExcel->setActiveSheetIndex()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        # 设置第一行字体加粗加颜色
//        self::$objPHPExcel->getActiveSheet()->getStyle( 'A4:AH4')->getFont()->setBold(true);
//        self::$objPHPExcel->getActiveSheet()->getStyle( 'A4:AH4')->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_BLUE);

        # 内容自适应
        foreach ($titleArr as $key => $val) {
            $lie = self::getIndex($key);
            self::$objPHPExcel->getActiveSheet()->getColumnDimension($lie)->setWidth(25);
        }
//        self::$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
//        self::$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        # 设置标题栏各种颜色填充，填充的样式和背景色
//        self::$objPHPExcel->getActiveSheet()->getStyle("A2")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
//        self::$objPHPExcel->getActiveSheet()->getStyle("A2")->getFill()->getStartColor()->setARGB('FF99CC00');
    }
}
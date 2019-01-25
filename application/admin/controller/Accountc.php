<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/10
 * Time: 16:08
 */

namespace app\admin\controller;
use app\api\service\ExcelCsvImportService;
use think\Loader;

//二三级部门核算主体对应关系维护---基础增删查改，导入导出
class Accountc
{
    public function test()
    {

    }

    public function importExcel(){
        //$zhizaobmCode = Model::changZhizaobm($zhizaobm, true);
        $importService = new ExcelCsvImportService();
        $option = $importService->getImportOption(1);
        //获取解析导入文件数据
        $data = $importService->reportImport($option);
        if(empty($data))
            $data = array();

        $saveData = $data['data'];
        $error = $data['error'];
        if (!empty($error)) {
            $filePath = $this->errorLog($error);
            $saveData = $this->seprateData($data['data'],$data['error']);
        }
//        $data = $importService->addNewColumn('zhizaobm',$zhizaobm,$data);
        //检查是否已保存订单日报
        $data = $importService->checkHistortDingdanrbConfirm($saveData,$option,$zhizaobm,$field);
        if (!is_array($data)){
            exit($data);
        }
        $validate = new DingdanrbValidate();
        if ($validate->batch()->check($data)) {
            $ret = DingdanrbLogic::save1($data,$field);
            return retmsg(0, array('data'=>$error,'downloadUrl'=>$filePath), "保存成功 $ret 行！");
        } else {
            return retmsg(-1, null, $validate->getError());
        }
        return json(array('resultcode' => 200, 'resultmsg' => '文件导入成功!'));
    }

}

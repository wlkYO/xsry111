<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/09
 * Time: 09:12
 */

namespace app\admin\service;
use think\Controller;
/**
 * 导入文件 键值对应数据库字段
 * Class KeyValueParamService
 * @package app\admin\service
 */
class KeyValueParamService extends Controller
{
    /**
     * 生产账务系统导入文件列表
     * @return array
     */
    public static function importTableList(){
        $arr = [
            //基础资料
            '核算主体对应关系',
            '科目代码基础表',
            '日月报科目对应关系',
            '利润科目结转对应表',
            '科目汇总表格式',
            '月报汇总格式',
            '损益部分',     //日报格式
            '库房资产',     //日报格式
            '支付调整', //?????

            //业务数据
            '费用明细账导入',
            '日常',           //工人工资表导入
            '月底',           //工人工资表导入
            '次月初',         //工人工资表导入
//            '管理人员工资表导入', 新增
            '基本工资及扣款格式',
            '管理人员补偿薪酬格式',

            '特殊项导入',
            '预提项目导入',
            '收支明细',     //手工库房导入
            '盘点数据',     //手工库房导入
            '成品库盘点导入',
            '在建工程导入'
        ];
        return $arr;
}

    /**
     * 通过表$table获取option设置 不包含表格的跨行跨列
     * @param $table
     * @return array
     */
    public static function getKeyValueOption($table)
    {
        //titleLine 标题行
        //firstLine 数据开始行
        //columnMap 标题对应数据库字段
        //tableName 数据库表名
        switch ($table) {
            case '应收账款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '客户姓名' => 'cname',
                        '期初欠款日期' => 'date',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '预收账款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '客户姓名' => 'cname',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '暂存款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '客户姓名' => 'cname',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '应付账款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '客户姓名' => 'cname',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '其他应收账款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '明细科目' => 'cname',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '其他应付账款期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '片区' => 'area',
                        '经营部' => 'dept',
                        '明细科目' => 'cname',
                        '业务类别' => 'yw_type',
                        '期初' => 'qichu',
                        '经手人' => 'handlers',
                    )
                );
                break;
            case '现金结存期初':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_qichu',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '现金结存期初' => 'qichu',
                    )
                );
                break;
            case '现金日记账':
                $option = array(
                    'titleLine' => 0,
                    'firstLine' => 1,
                    'tableName' => 'xjrjz_rijizhang',
                    'is_yewu' => 1,
                    'columnMap' => array(
                        '日期' => 'date',
                        '项目类别' => 'xm_type',
                        '一级明细' => 'yijimx',
                        '二级明细' => 'erjimx',
                        '摘要' => 'remark',
                        '业务类别' => 'yw_type',
                        '收入' => 'income',
                        '支出' => 'spending',
                        '余额' => 'balance',
                        '经手人' => 'handlers',
                    )
                );
                break;
            default:{
                return json_encode(array('resultcode' => -1, 'resultmsg' => '该文件的数据未做导入设置!'));
                exit;
            }
        }
        return $option;
    }
    //设置长表的下标val ID
    public function val_id_add(){
        if (!isset($this->val_id))
            $this->val_id = 1;
        return $this->val_id++;
    }
    //几个长表,包含跨行跨列的表格
    public function headerJson($table){

        if ($table == '支付调整'){
            $head = [
                ['headerName'=>"基地",'rowspan'=>3,'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','field'=>'base'],
                    ]],
                ]],
                ['headerName'=>"部门",'rowspan'=>3,'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','field'=>'dept'],
                    ]],
                ]],
                ['headerName'=>"核算主体",'rowspan'=>3,'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','field'=>'dept_name'],
                    ]],
                ]],
                ['headerName'=>"月报应付期初",'colspan'=>6,'children'=>[
                    ['headerName'=>'管理人员','colspan'=>4,'children'=>[
                        ['headerName'=>'基本薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'津贴薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'补偿薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'管理社保','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'工人','colspan'=>2,'children'=>[
                        ['headerName'=>'正常工资','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'工人社保','field'=>'val'.self::val_id_add()],
                    ]],
                ]],
                ['headerName'=>"工资支付",'colspan'=>6,'children'=>[
                    ['headerName'=>'管理人员','colspan'=>4,'children'=>[
                        ['headerName'=>'基本薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'津贴薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'补偿薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'管理社保','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'工人','colspan'=>2,'children'=>[
                        ['headerName'=>'正常工资','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'工人社保','field'=>'val'.self::val_id_add()],
                    ]],
                ]],
                ['headerName'=>"费用调整项",'colspan'=>12,'children'=>[
                    ['headerName'=>'合计调整损益','rowspan'=>2,'children'=>[
                        ['headerName'=>'','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'管理人员','colspan'=>4,'children'=>[
                        ['headerName'=>'基本薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'津贴薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'补偿薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'管理社保','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'工人','colspan'=>2,'children'=>[
                        ['headerName'=>'正常工资','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'工人社保','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'特殊费用补贴','colspan'=>2,'children'=>[
                        ['headerName'=>'会务费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'手机费','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'营业外收入','colspan'=>3,'children'=>[
                        ['headerName'=>'考勤','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'罚款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'赔偿','field'=>'val'.self::val_id_add()],
                    ]],
                ]],
                ['headerName'=>"工资表明细",'colspan'=>40,'children'=>[
                    ['headerName'=>'管理人员','colspan'=>36,'children'=>[
                        ['headerName'=>'基本薪酬','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'公积金津贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'全勤津贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'基础业务津贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'层级津贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'评估/谈判津贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'会务费补贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'手机费补贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'补贴','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'上月转入','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'提成','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'应发合计','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'应扣款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'借款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'水电气','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'物管费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'网费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'电话费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'党费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'考勤','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'其他1','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'个人所得税','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'补应扣款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'质量扣款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'赔偿','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'罚款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'意外险','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'工作服','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'服装暂存','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'退服装款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'部门公积金','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'住房公积金','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'实发工资','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'工伤扣款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'体检费','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'****','field'=>'val'.self::val_id_add()],

                    ]],
                    ['headerName'=>'工人','colspan'=>4,'children'=>[
                        ['headerName'=>'实发工资','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'应扣款','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'水电气','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'物管费','field'=>'val'.self::val_id_add()],
                    ]],
                ]],
                ['headerName'=>"工资发放信息",'colspan'=>8,'children'=>[
                    ['headerName'=>'管理人员','colspan'=>4,'children'=>[
                        ['headerName'=>'存卡金额1','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'存卡金额2','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'现金金额','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'暂存款','field'=>'val'.self::val_id_add()],
                    ]],
                    ['headerName'=>'工人','colspan'=>4,'children'=>[
                        ['headerName'=>'存卡金额1','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'存卡金额2','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'现金金额','field'=>'val'.self::val_id_add()],
                        ['headerName'=>'暂存款','field'=>'val'.self::val_id_add()],
                    ]],
                ]],
            ];
        }
        if($table == '预提项目导入'){
            $head = [
                ['headerName'=>"事业部预提费用",'colspan'=>36,'children'=>[
                    ['headerName'=>"基地",'children'=>[
                        ['headerName'=>'月报明细科目','colspan'=>4,'field'=>'base'],
                    ]],
                    ['headerName'=>"部门",'children'=>[
                        ['headerName'=>'','field'=>'dept'],
                    ]],
                    ['headerName'=>"月报主体",'children'=>[
                        ['headerName'=>'','field'=>'dept_name'],
                    ]],
                    ['headerName'=>"合计",'children'=>[
                        ['headerName'=>'','field'=>'total'],
                    ]],
                    ['headerName'=>"基本薪酬",'children'=>[
                        ['headerName'=>'基本薪酬','field'=>'base_pay'],
                    ]],
                    ['headerName'=>"津贴薪酬",'children'=>[
                        ['headerName'=>'津贴薪酬','field'=>'jintie_pay'],
                    ]],
                    ['headerName'=>"补偿薪酬",'children'=>[
                        ['headerName'=>'补偿薪酬','field'=>'buchang_pay'],
                    ]],
                    ['headerName'=>"管理人员社保",'children'=>[
                        ['headerName'=>'管理人员保障款','field'=>'shebao_pay'],
                    ]],
                    ['headerName'=>"年终奖",'children'=>[
                        ['headerName'=>'浮动薪酬','field'=>'year_award_pay'],
                    ]],
                    ['headerName'=>"计时工资",'children'=>[
                        ['headerName'=>'正常工资','field'=>'jishi_pay'],
                    ]],
                    ['headerName'=>"工人社保",'children'=>[
                        ['headerName'=>'一线人员保障款','field'=>'gongren_pay'],
                    ]],
                    ['headerName'=>"全勤奖及其他奖励",'children'=>[
                        ['headerName'=>'浮动薪酬','field'=>'quanqin_pay'],
                    ]],
                    ['headerName'=>"餐补",'children'=>[
                        ['headerName'=>'职工福利费','field'=>'canbu_pay'],
                    ]],
                    ['headerName'=>"电话费",'children'=>[
                        ['headerName'=>'通讯费','field'=>'tel_pay'],
                    ]],
                    ['headerName'=>"水费",'children'=>[
                        ['headerName'=>'水费','field'=>'shui_pay'],
                    ]],
                    ['headerName'=>"电费",'children'=>[
                        ['headerName'=>'电费','field'=>'dian_pay'],
                    ]],
                    ['headerName'=>"气费",'children'=>[
                        ['headerName'=>'气费','field'=>'qi_pay'],
                    ]],
                    ['headerName'=>"租赁费",'children'=>[
                        ['headerName'=>'库房\厂房租赁费','field'=>'zulin_pay'],
                    ]],
                    ['headerName'=>"无形资产摊销",'children'=>[
                        ['headerName'=>'无形资产摊销','field'=>'wuxing_pay'],
                    ]],
                    ['headerName'=>"内转折旧费",'children'=>[
                        ['headerName'=>'内转折旧费','field'=>'zhejiu_nz_pay'],
                    ]],
                    ['headerName'=>"不动产折旧费",'children'=>[
                        ['headerName'=>'不动产折旧费','field'=>'zhejiu_bdc_pay'],
                    ]],
                    ['headerName'=>"设备折旧费",'children'=>[
                        ['headerName'=>'设备折旧费','field'=>'zhejiu_sb_pay'],
                    ]],
                    ['headerName'=>"车辆(模具)折旧费",'children'=>[
                        ['headerName'=>'车辆(模具)折旧费','field'=>'zhejiu_cl_pay'],
                    ]],
                    ['headerName'=>"其他折旧费",'children'=>[
                        ['headerName'=>'其他折旧费','field'=>'zhejiu_qt_pay'],
                    ]],
                    ['headerName'=>"预提税费",'children'=>[
                        ['headerName'=>'营业税金及附加','field'=>'yutis_pay'],
                    ]],
                    ['headerName'=>"应收账款占用",'children'=>[
                        ['headerName'=>'应收账款占用','field'=>'yingshou_zy_pay'],
                    ]],
                    ['headerName'=>"商品占用",'children'=>[
                        ['headerName'=>'商品占用','field'=>'shangpin_zy_pay'],
                    ]],
                    ['headerName'=>"材料半成品占用",'children'=>[
                        ['headerName'=>'材料半成品占用','field'=>'cailiao_zy_pay'],
                    ]],
                    ['headerName'=>"其他资产占用",'children'=>[
                        ['headerName'=>'其他资产占用','field'=>'qita_zy_pay'],
                    ]],
                    ['headerName'=>"垃圾运费",'children'=>[
                        ['headerName'=>'其他经营费用','field'=>'laji_pay'],
                    ]],
                    ['headerName'=>"物流运费",'children'=>[
                        ['headerName'=>'其他运费','field'=>'wuliu_pay'],
                    ]],
                    ['headerName'=>"车油费",'children'=>[
                        ['headerName'=>'车油费','field'=>'cheyou_pay'],
                    ]],
                    ['headerName'=>"草垫费",'children'=>[
                        ['headerName'=>'草垫费','field'=>'caodian_pay'],
                    ]],
                    ['headerName'=>"品牌推广费",'children'=>[
                        ['headerName'=>'广告宣传费','field'=>'tuiguang_pay'],
                    ]],
                    ['headerName'=>"转销售维修费补贴",'children'=>[
                        ['headerName'=>'外部维修费','field'=>'xiaoshou_wx_pay'],
                    ]],
                    ['headerName'=>"销售价格补贴",'end'=>1,'children'=>[
                        ['headerName'=>'调拨价格补贴','end'=>1,'field'=>'xiaoshou_jg_pay'],
                    ]],
                ]]
            ];
        }
        if ($table == '损益部分'){
            $head = [
                ['headerName'=>"部门",'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','children'=>[
                            ['headerName'=>'','field'=>'dept'],
                        ]],
                    ]],
                ]],
                ['headerName'=>"项目",'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','children'=>[
                            ['headerName'=>'','field'=>'dept_name'],
                        ]],
                    ]],
                ]],
                ['headerName'=>"损益部分",'children'=>[
                    ['headerName'=>'内部加工收入','children'=>[
                        ['headerName'=>'','children'=>[
                            ['headerName'=>'','field'=>'val'.self::val_id_add()],
                        ]],
                    ]],
                ]],
            ];
        }
        if ($table == '库房资产部分'){
            $head = [
                ['headerName'=>"基地",'children'=>[
                    ['headerName'=>'','children'=>[
                        ['headerName'=>'','field'=>'base'],
                    ]],
                ]],
            ];
        }
        return  $head;
    }
}

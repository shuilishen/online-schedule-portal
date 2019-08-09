<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/7
 * Time: 9:50
 */

namespace app\paiban\controller;
use app\common\controller\myCommonController;
use app\index\model\Members;
use app\index\model\Orgs;
use app\index\model\Shifts;
use app\paiban\model\Paiban;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Output extends myCommonController
{
    /**
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $YM=$this->request->param('YM','');

        $inputFileName = './workflowTableModel/234model2.xlsx';
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName( 'Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

        $wday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        $startdate = strtotime($YM);
        $enddate = strtotime('+1 month', $startdate);
        $alphabet = ['A','B','C','D','E','F','G','H','I','J','K',
                        'L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                        'AA','AB','AC','AD','AE','AF','AG','AH','AI'
        ];

        $index = 4;
        while ($startdate < $enddate)
        {
            $worksheet->getCell($alphabet[$index].'1')->setValue(date("Y-m-d ", $startdate).$wday[date('w', $startdate)]);
            $worksheet->getCell($alphabet[$index].'2')->setValue('班别代码(Shift Code)');
            $index++;
            $startdate = strtotime("+1 day", $startdate);
        }

        $members = Members::order('oid', 'ASC')->select();

        $index = 3;
        foreach ($members as $member) {
            $startdate = strtotime($YM);

            $check = Paiban::where('date', '>=', date("Y-m-d", $startdate))
                ->where('date', '<', date("Y-m-d", $enddate))
                ->where('Mem_id', $member['id'])->order('date', 'ASC')->find();

            if($check)
            {
                $workflowOfMem = Paiban::where('date', '>=', date("Y-m-d", $startdate))
                    ->where('date', '<', date("Y-m-d", $enddate))
                    ->where('Mem_id', $member['id'])->order('date', 'ASC')->select();

                $wListOfMem = array();
                foreach ($workflowOfMem as $item) {
                    $shift = Shifts::get($item['Shi_id']);
                    $wListOfMem[$item['date']] = $shift['SC'];
                }

                $org = Orgs::get($member['oid']);

                $worksheet->getCell($alphabet[0].$index)->setValue($org['Org_C']);
                $worksheet->getCell($alphabet[1].$index)->setValue($org['Org_N']);
                $worksheet->getCell($alphabet[2].$index)->setValueExplicit($member['eid'], DataType::TYPE_STRING);
                $worksheet->getCell($alphabet[3].$index)->setValue($member['name']);

                $startdate = strtotime($YM);
                $dindex = 4;
                while ($startdate < $enddate)
                {
                    $dateString = date("Y-m-d", $startdate);

                    if(array_key_exists($dateString, $wListOfMem))
                        $worksheet->getCell($alphabet[$dindex].$index)->setValue(''.$wListOfMem[$dateString]);
                    else
                        $worksheet->getCell($alphabet[$dindex].$index)->setValue('OFF');

                    if(date('w', $startdate) == 0 || date('w', $startdate) == 6)
                    {
                        $worksheet->getStyle( $alphabet[$dindex].$index)->getFill()->setFillType(FILL::FILL_SOLID);
                        $worksheet->getStyle( $alphabet[$dindex].$index)->getFill()->getStartColor()->setARGB('FFFFDCDC');
                    }
                    $startdate = strtotime("+1 day", $startdate);
                    $dindex++;
                }
                $index++;
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        ob_end_clean();
        //application/vnd.openxmlformats-officedocument.spreadsheetml.sheet

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="workflowTable.xlsx"');
        header('Cache-Control: max-age=0');
        header ('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

//        $writer = new Xlsx($spreadsheet);
//        $writer->save('workflowTableOutput/workflowTable.xlsx');
//        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//        $writer->save('/workflowTableOutput/workflowTable.xlsx');
//        return json_encode($YM);
    }

}
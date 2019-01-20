<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14 0014
 * Time: 18:56
 */

namespace app\Admin\controller;


use app\admin\model\actDetail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Controller;
use think\Cookie;

class Index extends Controller
{
    //主页
    function index(){
        if(Cookie::get('user_name')==null){
            $this->redirect(url('login/Index/index'));
        }
        $dept = new actDetail();
        $list = $dept::paginate(10);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);

        return $this->fetch();
    }

    //添加活动
    function addActivity(){
        $user = Cookie::get('user_name');
        $file = request()->file('listFile');
        $actName = request()->post('actName');
        $actDetail = request()->post('actDetail');
        $actAddress = request()->post('actAddress');
        $actMaster = request()->post('actMaster');
        $startTime = request()->post('startTime');
        $endTime = request()->post('endTime');
        // 移动到框架应用根目录/Info/ 目录下

        $info = $file->move(ROOT_PATH ."/Info/");
        if($info){
            $file_loc = $info->getSaveName();
            $ext = $info->getExtension();
            $count = $this->count_line($ext,ROOT_PATH ."/Info/".$file_loc);
            $act = new actDetail();
            $act->data([
                'name'  =>  $actName,
                'startTime' =>  date("y-m-d h:i",strtotime($startTime)),
                'endTime' => date("y-m-d h:i",strtotime($endTime)),
                'location' => $actAddress,
                'detail' => $actDetail,
                'listLoc' => $file_loc,
                'user_name' => $user,
                'master' => $actMaster,
                'list_cnt' => $count
            ]);
            $isOk = $act->save();
            if($isOk){
                $returnData = array(
                    'Code' => 200);
                return json($returnData);
            }
            else{
                $returnData = array(
                    'Code' => 100);
                return json($returnData);
            }
        }else{
            $returnData = array(
                'Code' => 100);
            return json($returnData);
        }
    }

    //修改活动信息
    function modifyActivity(){
        $user = Cookie::get('user_name');
        $id = request()->post('actId');
        $file = request()->file('listFile');
        $actName = request()->post('actName');
        $actDetail = request()->post('actDetail');
        $actAddress = request()->post('actAddress');
        $actMaster = request()->post('actMaster');
        $startTime = request()->post('startTime');
        $endTime = request()->post('endTime');

        $act = new actDetail();

        // 移动到框架应用根目录/Info/ 目录下
        if($file){//若传了新文件，则删除原本文件，更新文件位置
            $info = $file->move(ROOT_PATH ."/Info/");
            $file_loc = $info->getSaveName();
            $ext = $info->getExtension();
            $count = $this->count_line($ext,ROOT_PATH ."/Info/".$file_loc);

            $old_file = actDetail::where('id',$id)->value('listLoc');
            unlink(ROOT_PATH ."/Info/".$old_file);
            $isOk = $act->save([
                'name'  =>  $actName,
                'startTime' =>  date("y-m-d h:i",strtotime($startTime)),
                'endTime' => date("y-m-d h:i",strtotime($endTime)),
                'location' => $actAddress,
                'detail' => $actDetail,
                'listLoc' => $file_loc,
                'user_name' => $user,
                'master' => $actMaster,
                'list_cnt' => $count
            ],['id' => $id]);

            if($isOk){
                $returnData = array(
                    'Code' => 200);
                return json($returnData);
            }
            else{
                $returnData = array(
                    'Code' => 100);
                return json($returnData);
            }
        }
        else{//若没传文件，则只更新其他字段
            $isOk = $act->save([
                'name'  =>  $actName,
                'startTime' =>  date("y-m-d h:i",strtotime($startTime)),
                'endTime' => date("y-m-d h:i",strtotime($endTime)),
                'location' => $actAddress,
                'detail' => $actDetail,
                'user_name' => $user,
                'master' => $actMaster,
            ],['id' => $id]);

            if($isOk){
                $returnData = array(
                    'Code' => 200);
                return json($returnData);
            }
            else{
                $returnData = array(
                    'Code' => 100);
                return json($returnData);
            }
        }
    }

    //删除活动信息
    function deleteActivity(){
        $id = request()->post('id');
        $old_file = actDetail::where('id',$id)->value('listLoc');
        unlink(ROOT_PATH ."Info/".$old_file);
        $isOk = actDetail::destroy($id);
        if($isOk){
            $returnData = array(
                'Code' => 200);
            return json($returnData);
        }
        else{
            $returnData = array(
                'Code' => 100);
            return json($returnData);
        }
    }
    //计算文件中行数
    function count_line($ext,$file){
        $reader = IOFactory::createReader(ucfirst($ext));
        $sheet = $reader->load($file)->getSheet(0);
        $rows = $sheet->getHighestRow();
        return $rows-1;
    }



}
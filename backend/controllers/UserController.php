<?php

namespace backend\controllers;
use backend\models\Menu;
use backend\models\PasswordForm;
use yii\data\Pagination;
use backend\models\User;

use Yii;

class UserController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        $action = Yii::$app->controller->module->requestedRoute;
        //var_dump($action);exit;

        if(\Yii::$app->user->can($action)){
            return true;
        }else{
            //throw new \yii\web\UnauthorizedHttpException('对不起，您现在还没获此操作的权限');
            echo '<div style="margin: 100px auto;text-align: center;background-color: #1ab394; color: #ffffff;width: 500px;height: 50px;line-height: 50px;border-radius: 5px;;"><h4>对不起，您现在还没获此操作的权限</h4></div>';

        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    //用户列表
    public function actionList()
    {
        Yii::$app->user->identity->username;
        $data = User::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '15']);
        $user = $data->joinWith('usergroup')->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('list',[
            'user'=>$user,
            'pages' => $pages
        ]);
    }
    public function actionUpdate(){
        $item_name = Yii::$app->request->get('item_name');
        $id = Yii::$app->request->get('id');
        $model = User::find()->joinWith('usergroup')->where(['id'=>$id])->one();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }

        $model1 = $this->findModel($id);
        if ($model1->load(Yii::$app->request->post()) && $model1->save()) {
            $post = Yii::$app->request->post();
            //var_dump($post);exit;
            $role = $auth->createRole($post['AuthAssignment']['item_name']);                //创建角色对象
            $user_id = $id;                                             //获取用户id，此处假设用户id=1
            $auth->revokeAll($user_id);
            $auth->assign($role, $user_id);                           //添加对应关系
            return $this->redirect(['user/update', 'id' => $model1->id]);
        }

        return $this->render('update',[
            'model' => $model,
            'item' => $item_one
        ]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
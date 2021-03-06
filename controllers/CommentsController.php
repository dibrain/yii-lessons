<?php

namespace app\controllers;

use Yii;
use app\models\Comments;
use app\models\CommentsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/**
 * CommentsController implements the CRUD actions for Comments model.
 */
class CommentsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Comments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CommentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        print_r($dataProvider->getModels());
        die();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionChart(){
        $searchModel = new CommentsSearch();
        $comments = Comments::find()->all(); 
        $labels = [];
        $data = [];
        foreach( $comments as $comment ){
            $labels[] = $comment->body;
            $data[] = $comment->getReplies()->count();
        }
        return $this->render('chart', [
            'searchModel' => $searchModel,
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function actionThread(){
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $threadId = $parents[0];
                $comments = Comments::find()->where(['thread_id'=>$threadId])->all(); 
                foreach( $comments as $comment ){
                    $out[] = ['id'=> $comment->id, 'name' => $comment->body];
                } 
                return Json::encode(['output'=>$out, 'selected'=>'']);
            }
        }
        echo Json::encode(['output'=>'', 'selected'=>'']);
    }

    /**
     * Displays a single Comments model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Comments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if( \Yii::$app->user->can('createComment') ){
            $model = new Comments();
            if ($model->load(Yii::$app->request->post()) ) {
                $model->user_id = Yii::$app->user->identity->id;
                $model->save();
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $model;
                
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $response = [
                'error' => true,
                'message' => 'you are not authorized to do this'
            ];
            return $response;
        }
        
    }

    /**
     * Updates an existing Comments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (\Yii::$app->user->can('updateComment', ['comment' => $model])) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }else{
            die('you are not allowed to do this˝');
        }
    }

    /**
     * Deletes an existing Comments model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionSomeAction($id) {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', [
                        'model' => $model
            ]);
        } 
    }
    /**
     * Finds the Comments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Comments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comments::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

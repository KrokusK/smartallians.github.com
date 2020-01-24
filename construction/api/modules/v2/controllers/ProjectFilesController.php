<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\ProjectFile;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API ProjectFile controller
 */
class ProjectFilesController extends Controller
{
    /**
     * Constants
     */

    const CHECK_RIGHTS_RBAC = false;  // Enable check rights by rbac model

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    /**
     * POST Method.
     * Save project document
     *
     * @return json
     */
    public function actionCreate()
    {
        //if (Yii::$app->request->isAjax) {

        $postParams = Yii::$app->getRequest()->post();

        if (is_array($postParams)) {
            // check user is a guest
            if (array_key_exists('token', $postParams)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($postParams['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            // Attribute names associated by request parameters
            // Request: multipart/form-data request
            $arrayDocumentFormAssoc = array ('document' => 'document');

            $modelDocument = new ProjectFile();

            //$modelDocument->imageFiles = UploadedFile::getInstances($modelDocument, 'imageFiles'); // Format form parameters: Document[imageFiles][]
            $modelDocument->imageFiles = UploadedFile::getInstancesByName($arrayDocumentFormAssoc['document']);
            if ($modelDocument->upload() && !empty($modelDocument->imageFiles)) { // save documents

                $DocumentResponse = array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Документы успешно сохранено(ы)', 'count' => count($modelDocument->imageFiles));
                $DocumentPath = [];
                foreach ($modelDocument->arrayWebFilename as $file) {
                    array_push($DocumentPath, '/uploads/document/'.$file);
                }
                array_push($DocumentResponse, ['document' => $DocumentPath]);

                return Json::encode($DocumentResponse);
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Файл(ы) документов не были переданы'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

}

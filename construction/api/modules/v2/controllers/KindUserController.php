<?php
namespace api\modules\v2\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use api\modules\v2\models\KindUser;
use api\modules\v2\models\UserRequestData;

/**
 * API KindUser controller
 */
class KindUserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'view' => ['get'],
                ],
                'actions' => [
                    'create' => ['post'],
                ],
                'actions' => [
                    'update' => ['put', 'patch'],
                ],
                'actions' => [
                    'delete' => ['delete'],
                ],
                'actions' => [
                    'delete-by-param' => ['delete'],
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
     * GET Method. KindUser table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(['admin']);
            // get request params
            $getParams = $modelUserRequestData->getRequestParams();
            // init model KindUser
            $modelKindUser = new KindUser();
            // Search data
            return $modelKindUser->getDataKindUser($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. KindUser table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(['admin']);
            // get request params
            $postParams = $modelUserRequestData->getRequestParams();
            // init model KindUser
            $modelKindUser = new KindUser();
            // Save object by params
            return $modelKindUser->addDataKindUser($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. KindUser table.
     * Update record by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(['admin']);
            // get request params
            $putParams = $modelUserRequestData->getRequestParams();
            // Get model KindUser by id
            $modelKindUser = new KindUser();
            $modelKindUserById = $modelKindUser->getDataKindUserById($putParams);
            // Update object by id
            return $modelKindUserById->updateDataKindUser($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. KindUser table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(['admin']);
            // get request params
            $delParams = $modelUserRequestData->getRequestParams();
            // Get model KindUser by id
            $modelKindUser = new KindUser();
            if ($modelKindUser->isNullIdInParams($delParams)) {
                // Delete object by other params
                return $modelKindUser->deleteDataKindUserByParams($delParams);
            } else {
                // Delete object by id
                $modelKindUserById = $modelKindUser->getDataKindUserById($delParams);
                return $modelKindUserById->deleteDataKindUserById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}

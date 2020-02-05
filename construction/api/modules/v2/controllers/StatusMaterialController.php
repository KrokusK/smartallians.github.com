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
use api\modules\v2\models\StatusMaterial;
use api\modules\v2\models\UserRequestData;

/**
 * API StatusMaterial controller
 */
class StatusMaterialController extends Controller
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
     * GET Method. StatusMaterial table.
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
            // init model StatusMaterial
            $modelStatusMaterial = new StatusMaterial();
            // Search data
            return $modelStatusMaterial->getDataStatusMaterial($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. StatusMaterial table.
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
            // init model StatusMaterial
            $modelStatusMaterial = new StatusMaterial();
            // Save object by params
            return $modelStatusMaterial->addDataStatusMaterial($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. StatusMaterial table.
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
            // Get model StatusMaterial by id
            $modelStatusMaterial = new StatusMaterial();
            $modelStatusMaterialById = $modelStatusMaterial->getDataStatusMaterialById($putParams);
            // Update object by id
            return $modelStatusMaterialById->updateDataStatusMaterial($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. StatusMaterial table.
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
            // Get model StatusMaterial by id
            $modelStatusMaterial = new StatusMaterial();
            if ($modelStatusMaterial->isNullIdInParams($delParams)) {
                // Delete object by other params
                return $modelStatusMaterial->deleteDataStatusMaterialByParams($delParams);
            } else {
                // Delete object by id
                $modelStatusMaterialById = $modelStatusMaterial->getDataStatusMaterialById($delParams);
                return $modelStatusMaterialById->deleteDataStatusMaterialById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}

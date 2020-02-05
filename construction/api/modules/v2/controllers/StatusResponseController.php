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
use api\modules\v2\models\StatusResponse;
use api\modules\v2\models\UserRequestData;

/**
 * API StatusResponse controller
 */
class StatusResponseController extends Controller
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
     * GET Method. StatusResponse table.
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
            // init model StatusResponse
            $modelStatusResponse = new StatusResponse();
            // Search data
            return $modelStatusResponse->getDataStatusResponse($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. StatusResponse table.
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
            // init model StatusResponse
            $modelStatusResponse = new StatusResponse();
            // Save object by params
            return $modelStatusResponse->addDataStatusResponse($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. StatusResponse table.
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
            // Get model StatusResponse by id
            $modelStatusResponse = new StatusResponse();
            $modelStatusResponseById = $modelStatusResponse->getDataStatusResponseById($putParams);
            // Update object by id
            return $modelStatusResponseById->updateDataStatusResponse($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. StatusResponse table.
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
            // Get model StatusResponse by id
            $modelStatusResponse = new StatusResponse();
            if ($modelStatusResponse->isNullIdInParams($delParams)) {
                // Delete object by other params
                return $modelStatusResponse->deleteDataStatusResponseByParams($delParams);
            } else {
                // Delete object by id
                $modelStatusResponseById = $modelStatusResponse->getDataStatusResponseById($delParams);
                return $modelStatusResponseById->deleteDataStatusResponseById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}

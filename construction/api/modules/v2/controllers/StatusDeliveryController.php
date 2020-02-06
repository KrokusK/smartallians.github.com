<?php
namespace api\modules\v2\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use api\modules\v2\models\StatusDelivery;
use api\modules\v2\models\UserRequestData;

/**
 * API StatusDelivery controller
 */
class StatusDeliveryController extends Controller
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
     * GET Method. StatusDelivery table.
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
            $modelUserRequestData->checkUserRightsByRole(['admin', 'customer', 'contractor', 'mediator']);
            // get request params
            $getParams = $modelUserRequestData->getRequestParams();
            // init model StatusDelivery
            $modelStatusDelivery = new StatusDelivery();
            // Search data
            return $modelStatusDelivery->getDataStatusDelivery($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. StatusDelivery table.
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
            // init model StatusDelivery
            $modelStatusDelivery = new StatusDelivery();
            // Save object by params
            return $modelStatusDelivery->addDataStatusDelivery($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. StatusDelivery table.
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
            // Get model StatusDelivery by id
            $modelStatusDelivery = new StatusDelivery();
            $modelStatusDeliveryById = $modelStatusDelivery->getDataStatusDeliveryById($putParams);
            // Update object by id
            return $modelStatusDeliveryById->updateDataStatusDelivery($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. StatusDelivery table.
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
            // Get model StatusDelivery by id
            $modelStatusDelivery = new StatusDelivery();
            if ($modelStatusDelivery->isNullIdInParams($delParams)) {
                // Delete object by other params
               return $modelStatusDelivery->deleteDataStatusDeliveryByParams($delParams);
            } else {
                // Delete object by id
                $modelStatusDeliveryById = $modelStatusDelivery->getDataStatusDeliveryById($delParams);
                return $modelStatusDeliveryById->deleteDataStatusDeliveryById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}

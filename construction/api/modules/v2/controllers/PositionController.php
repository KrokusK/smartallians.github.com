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
use api\modules\v2\models\Position;
use api\modules\v2\models\UserRequestData;

/**
 * API Position controller
 */
class PositionController extends Controller
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
     * GET Method. Position table.
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
            // get user roles
            $userRoles = $modelUserRequestData->getUserRoles();
            // get request params
            $getParams = $modelUserRequestData->getRequestParams();
            // init model Position
            $modelPosition = new Position();
            // Search data
            return $modelPosition->getDataPosition($getParams, $userRoles);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. Position table.
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
            // init model Position
            $modelPosition = new Position();
            // Save object by params
            return $modelPosition->addDataPosition($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. Position table.
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
            // get user roles
            $userRoles = $modelUserRequestData->getUserRoles();
            // get request params
            $putParams = $modelUserRequestData->getRequestParams();
            // Get model Position by id
            $modelPosition = new Position();
            $modelPositionById = $modelPosition->getDataPositionById($putParams, $userRoles);
            // Update object by id
            return $modelPositionById->updateDataPosition($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. Position table.
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
            // get user roles
            $userRoles = $modelUserRequestData->getUserRoles();
            // get request params
            $delParams = $modelUserRequestData->getRequestParams();
            // Get model Position by id
            $modelPosition = new Position();
            if ($modelPosition->isNullIdInParams($delParams)) {
                // Delete object by other params
                return $modelPosition->deleteDataPositionByParams($delParams, $userRoles);
            } else {
                // Delete object by id
                $modelPositionById = $modelPosition->getDataPositionById($delParams, $userRoles);
                return $modelPositionById->deleteDataPositionById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}




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
use api\modules\v2\models\Portfolio;
use api\modules\v2\models\UserRequestData;

/**
 * API Portfolio controller
 */
class PortfolioController extends Controller
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
     * GET Method. Portfolio table.
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
            // init model Portfolio
            $modelPortfolio = new Portfolio();
            // Search data
            return $modelPortfolio->getDataPortfolio($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * POST Method. Portfolio table.
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
            // init model Portfolio
            $modelPortfolio = new Portfolio();
            // Save object by params
            return $modelPortfolio->addDataPortfolio($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. Portfolio table.
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
            // Get model Portfolio by id
            $modelPortfolio = new Portfolio();
            $modelPortfolioById = $modelPortfolio->getDataPortfolioById($putParams);
            // Update object by id
            return $modelPortfolioById->updateDataPortfolio($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. Portfolio table.
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
            // Get model Portfolio by id
            $modelPortfolio = new Portfolio();
            if ($modelPortfolio->isNullIdInParams($delParams)) {
                // Delete object by other params
                return $modelPortfolio->deleteDataPortfolioByParams($delParams);
            } else {
                // Delete object by id
                $modelPortfolioById = $modelPortfolio->getDataPortfolioById($delParams);
                return $modelPortfolioById->deleteDataPortfolioById($delParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}

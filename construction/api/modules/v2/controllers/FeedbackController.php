<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Feedback;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API Feedback controller
 */
class FeedbackController extends Controller
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
     * GET Method. Feedback table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        $getParams = Yii::$app->getRequest()->get();

        // check user is a guest
        if (array_key_exists('token', $getParams)) {
            $userByToken = \Yii::$app->user->loginByAccessToken($getParams['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }
        } else {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // Get array with user Roles
        $userRole =[];
        $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
        foreach($userAssigned as $userAssign){
            array_push($userRole, $userAssign->roleName);
        }
        //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

        // Check rights
        // If user have create right that his allowed to other actions to the Feedback table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        }
        /*
        $flagRights = false;
        foreach(array('admin', 'customer', 'contractor', 'mediator') as $value) {
            if (in_array($value, $userRole)) {
                $flagRights = true;
            }
        }
        if (!$flagRights) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        */

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayFeedbackAssoc = array ('id' => 'id', 'profile_id' => 'profile_id', 'status_feedback_id' => 'status_feedback_id', 'content' => 'content');

            if (in_array('admin', $userRole)) {
                $query = Feedback::find();  // get all records
            } else {
                $query = Feedback::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Feedback();
            foreach ($arrayFeedbackAssoc as $nameFeedbackAssoc => $valueFeedbackAssoc) {
                if (array_key_exists($valueFeedbackAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameFeedbackAssoc)) {
                        $modelValidate->$nameFeedbackAssoc = $getParams[$arrayFeedbackAssoc[$nameFeedbackAssoc]];
                        if (!$modelValidate->validate($nameFeedbackAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameFeedbackAssoc => $getParams[$arrayFeedbackAssoc[$nameFeedbackAssoc]]]);
                    }
                }
            }

            $modelFeedback = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Feedback object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelFeedback));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = Feedback::find();  // get all records
            } else {
                $query = Feedback::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelFeedback = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Feedback object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelFeedback));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Feedback table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createMediator')) {
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
            $arrayFeedbackAssoc = array ('id' => 'id', 'profile_id' => 'profile_id', 'status_feedback_id' => 'status_feedback_id', 'content' => 'content');

            $modelFeedback = new Feedback();

            // fill in the properties in the Feedback object
            foreach ($arrayFeedbackAssoc as $nameFeedbackAssoc => $valueFeedbackAssoc) {
                if (array_key_exists($valueFeedbackAssoc, $bodyRaw)) {
                    if ($modelFeedback->hasAttribute($nameFeedbackAssoc)) {
                        if ($nameFeedbackAssoc != 'id') {
                            $modelFeedback->$nameFeedbackAssoc = $bodyRaw[$valueFeedbackAssoc];

                            if (!$modelFeedback->validate($nameFeedbackAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueFeedbackAssoc));
                        }
                    }
                }
            }

            if ($modelFeedback->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagFeedback = $modelFeedback->save(false); // insert into Feedback table

                    if ($flagFeedback == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть сохранен'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Отзыв успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelFeedback))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Отзыв успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Feedback table.
     * Update record by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию обновления'));
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
            $arrayFeedbackAssoc = array ('id' => 'id', 'profile_id' => 'profile_id', 'status_feedback_id' => 'status_feedback_id', 'content' => 'content');

            if (array_key_exists($arrayFeedbackAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayFeedbackAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryFeedback = Feedback::find()->where(['id' => $bodyRaw[$arrayFeedbackAssoc['id']]]);  // get all records
                } else {
                    $queryFeedback = Feedback::find()->where(['AND', ['id' => $bodyRaw[$arrayFeedbackAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelFeedback = $queryFeedback->one();

                if (empty($modelFeedback)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Отзыв по id'));
                }

                foreach ($arrayFeedbackAssoc as $nameFeedbackAssoc => $valueFeedbackAssoc) {
                    if (array_key_exists($valueFeedbackAssoc, $bodyRaw)) {
                        if ($modelFeedback->hasAttribute($nameFeedbackAssoc)) {
                            $modelFeedback->$nameFeedbackAssoc = $bodyRaw[$arrayFeedbackAssoc[$nameFeedbackAssoc]];
                            if (!$modelFeedback->validate($nameFeedbackAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));
                        }
                    }
                }

                // Save Feedback object
                if ($modelFeedback->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagFeedback = $modelFeedback->save(false); // update Feedback table

                        if ($flagFeedback) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Отзыв успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Feedback table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
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
            $arrayFeedbackAssoc = array ('id' => 'id', 'profile_id' => 'profile_id', 'status_feedback_id' => 'status_feedback_id', 'content' => 'content');

            if (array_key_exists($arrayFeedbackAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayFeedbackAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryFeedback = Feedback::find()->where(['id' => $bodyRaw[$arrayFeedbackAssoc['id']]]);  // get all records
                } else {
                    $queryFeedback = Feedback::find()->where(['AND', ['id' => $bodyRaw[$arrayFeedbackAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelFeedback = $queryFeedback->one();

                if (empty($modelFeedback)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Отзыв по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelFeedback)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Feedback table
                    $countFeedbackDelete = $modelFeedback->delete($modelFeedback->id);

                    if ($countFeedbackDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Отзыв успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Feedback table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
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
            $arrayFeedbackAssoc = array ('id' => 'id', 'profile_id' => 'profile_id', 'status_feedback_id' => 'status_feedback_id', 'content' => 'content');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryFeedback = Feedback::find();  // get all records
            } else {
                $queryFeedback = Feedback::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new Feedback();
            foreach ($arrayFeedbackAssoc as $nameFeedbackAssoc => $valueFeedbackAssoc) {
                if (array_key_exists($valueFeedbackAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameFeedbackAssoc)) {
                        $modelValidate->$nameFeedbackAssoc = $bodyRaw[$arrayFeedbackAssoc[$nameFeedbackAssoc]];
                        if (!$modelValidate->validate($nameFeedbackAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueFeedbackAssoc));

                        $queryFeedback->andWhere([$nameFeedbackAssoc => $bodyRaw[$arrayFeedbackAssoc[$nameFeedbackAssoc]]]);
                    }
                }

            }
            $modelsFeedback = $queryFeedback->all();

            if (!empty($modelsFeedback) && !empty($modelValidate)) {
                foreach ($modelsFeedback as $modelFeedback) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Feedback table.
                         $countFeedbackDelete = $modelFeedback->delete();

                        if ($countFeedbackDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отзыв не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Отзыв успешно удален'));
            }
        }
    }

}

<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Profile;
use api\modules\v2\models\Specialization;
use api\modules\v2\models\City;
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
 * API Profile controller
 */
class ProfileController extends Controller
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
     * GET Method. Profile, kind_user, type_job, city, specialization tables.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        //if (Yii::$app->request->isAjax) {

        $getParams = Yii::$app->getRequest()->get();

        // check user is a guest
        $userByToken = \Yii::$app->user->loginByAccessToken($getParams['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }
        $userRole = \Yii::$app->authManager->getRolesByUser($userByToken->id);

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = array ('user_id' => 'user_id', 'kind_user_id' => 'kind_user_id', 'type_job_id' => 'type_job_id', 'fio' => 'fio', 'firm_name' => 'firm_name', 'inn' => 'inn', 'site' => 'site', 'avatar' => 'avatar', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            //$arraySpecializationAssoc = array ('specialization_id' => 'specialization_id');
            //$arrayCityAssoc = array ('city_id' => 'city_id');

            // Search record by id in the database
            if (($userRole !== 'admin') && (intval($getParams[$arrayProfileAssoc['user_id']]) !== $userByToken->id)) { // check role is a Admin or user_id equal id user
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Доступ запрещен'));
            }

            $query = Profile::find()->Where(intval(['user_id' => $arrayProfileAssoc['user_id']]));
            $modelValidate = new Profile();
            foreach ($arrayProfileAssoc as $nameProfileAssoc => $valueProfileAssoc) {
                if (array_key_exists($valueProfileAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameProfileAssoc)) {
                        $modelValidate->$nameProfileAssoc = $getParams[$arrayProfileAssoc[$nameProfileAssoc]];
                        if (!$modelValidate->validate($nameProfileAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileAssoc));

                        $query->andWhere([$nameProfileAssoc => $getParams[$arrayProfileAssoc[$nameProfileAssoc]]]);
                    }
                }
            }
            /*$modelValidate = new Specialization();
            if (array_key_exists($arraySpecializationAssoc['specialization_id'], $getParams)) {
                //if ($modelValidate->hasAttribute('specialization_id')) {
                    $modelValidate->name = $getParams[$arraySpecializationAssoc['specialization_id']];
                    if (!$modelValidate->validate('name')) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$arraySpecializationAssoc['specialization_id']));

                    $query->andWhere([$nameProfileAssoc => $getParams[$arrayProfileAssoc[$nameProfileAssoc]]]);
                //}
            }*/

            $modelRequest = $query->orderBy('created_at')
                ->with('users','kindUser','specializations','typeJob','cities')
                ->asArray()
                ->all();

            // get properties from Request object and from links
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelRequest));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = Profile::find()->Where(['user_id' => $userByToken->id]);

            $modelRequest = $query->orderBy('created_at')
                ->with('users','kindUser','specializations','typeJob','cities')
                ->asArray()
                ->all();

            // get properties from Request object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelRequest));

            return Json::encode($RequestResponse);
        }
        //}
    }


    /**
     * POST Method. Request table.
     * Insert record
     *
     * @return json
     */
    public function actionCreate()
    {
        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            } else {
                \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            }

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            $modelRequest = new Request();

            // fill in the properties in the Request object
            foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                if (array_key_exists($valueRequestAssoc, $bodyRaw)) {
                    if ($modelRequest->hasAttribute($nameRequestAssoc)) {
                        if ($nameRequestAssoc != 'id' && $nameRequestAssoc != 'created_at' && $nameRequestAssoc != 'updated_at') {
                            $modelRequest->$nameRequestAssoc = $bodyRaw[$valueRequestAssoc];

                            if (!$modelRequest->validate($nameRequestAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelRequest->created_by = $userByToken->id;
                            $modelRequest->created_at = time();
                            $modelRequest->updated_at = time();
                        }
                    }
                }
            }

            // check parametr for the KindJob object
            foreach ($arrayKindJobAssoc as $nameKindJobAssoc => $valueKindJobAssoc) {
                if (array_key_exists($valueKindJobAssoc, $bodyRaw)) {
                    if ($nameKindJobAssoc == 'kind_job_id' && !is_array($bodyRaw[$valueKindJobAssoc])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре work_type ожидается массив'));
                }
            }

            if ($modelRequest->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagRequest = $modelRequest->save(false); // insert into request table

                    $flagRequestKindJob = true;
                    if ($flagRequest) {

                        // Save records into request_kind_job table
                        if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                            foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                                $modelRequestKindJob = new RequestKindJob();

                                // fill in the properties in the KindJob object
                                if ($modelRequestKindJob->hasAttribute('kind_job_id')) {
                                    $modelRequestKindJob->kind_job_id = $value;
                                }

                                if ($modelRequestKindJob->validate('kind_job_id')) {
                                    $modelRequestKindJob->request_id = $modelRequest->id;

                                    if (!$modelRequestKindJob->save(false)) $flagRequestKindJob = false; // insert into request_kind_job table
                                }
                            }
                        }
                    }

                    if ($flagRequest == true && $flagRequestKindJob == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelRequest))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Request table.
     * Update records by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            } else {
                \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            }

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            if (array_key_exists($arrayRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryRequest = Request::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayRequestAssoc['id']]], ['created_by'=> $userByToken->id]]);
                $modelRequest = $queryRequest->orderBy('created_at')->one();

                if (empty($modelRequest)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }

                if (!empty($modelRequest)) {
                    // fill in the properties in the Request object
                    foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                        if (array_key_exists($valueRequestAssoc, $bodyRaw)) {
                            if ($modelRequest->hasAttribute($nameRequestAssoc)) {
                                if ($nameRequestAssoc != 'id' && $nameRequestAssoc != 'created_at' && $nameRequestAssoc != 'updated_at') {
                                    $modelRequest->$nameRequestAssoc = $bodyRaw[$valueRequestAssoc];

                                    if (!$modelRequest->validate($nameRequestAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                                    $modelRequest->created_by = $userByToken->id;
                                    $modelRequest->updated_at = time();
                                }
                            }
                        }
                    }

                    // check parametr for the KindJob object
                    foreach ($arrayKindJobAssoc as $nameKindJobAssoc => $valueKindJobAssoc) {
                        if (array_key_exists($valueKindJobAssoc, $bodyRaw)) {
                            if ($nameKindJobAssoc == 'kind_job_id' && !is_array($bodyRaw[$valueKindJobAssoc])) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре work_type ожидается массив'));
                        }
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
            }

            if ($modelRequest->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagRequest = $modelRequest->save(false); // insert into request table

                    $flagRequestKindJob = true;
                    if ($flagRequest) {

                        // Save records into request_kind_job table
                        if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                            // delete old records from request_kind_job table
                            RequestKindJob::deleteAll(['request_id' => $modelRequest->id]);

                            foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                                $modelRequestKindJob = new RequestKindJob();

                                // fill in the properties in the KindJob object
                                if ($modelRequestKindJob->hasAttribute('kind_job_id')) {
                                    $modelRequestKindJob->kind_job_id = $value;
                                }

                                if ($modelRequestKindJob->validate('kind_job_id')) {
                                    $modelRequestKindJob->request_id = $modelRequest->id;

                                    if (!$modelRequestKindJob->save(false)) $flagRequestKindJob = false; // insert into request_kind_job table
                                }
                            }
                        }
                    }

                    if ($flagRequest == true && $flagRequestKindJob == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена (обновлена)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена (обновлена)'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена (обновлена)'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Request table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            } else {
                \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            }

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (array_key_exists($arrayRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryRequest = Request::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayRequestAssoc['id']]], ['created_by'=> $userByToken->id]]);
                $modelRequest = $queryRequest->orderBy('created_at')->one();

                if (empty($modelRequest)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelRequest)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete old records from request_kind_job table
                    //RequestKindJob::deleteAll(['request_id' => $modelRequest->id]);

                    // delete from request table.
                    // Because the foreign keys with cascade delete that if a record in the parent table (request table) is deleted, then the corresponding records in the child table will automatically be deleted.
                    $countRequestDelete = $modelRequest->delete($modelRequest->id);

                    if ($countRequestDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно удалена'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Request table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php

        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            } else {
                \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            }

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            // Search record by id in the database
            $queryRequest = Request::find()->Where(['created_by' => $userByToken->id]);
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
            $modelValidate = new Request();
            foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                if (array_key_exists($valueRequestAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameRequestAssoc)) {
                        $modelValidate->$nameRequestAssoc = $bodyRaw[$arrayRequestAssoc[$nameRequestAssoc]];
                        if (!$modelValidate->validate($nameRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueRequestAssoc));

                        $queryRequest->andWhere([$nameRequestAssoc => $bodyRaw[$arrayRequestAssoc[$nameRequestAssoc]]]);
                    }
                }

            }
            $modelsRequest = $queryRequest->all();

            if (!empty($modelsRequest) && !empty($modelValidate)) {
                foreach ($modelsRequest as $modelRequest) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete old records from request_kind_job table
                        //RequestKindJob::deleteAll(['request_id' => $modelRequest->id]);

                        // delete from request table.
                        // Because the foreign keys with cascade delete that if a record in the parent table (request table) is deleted, then the corresponding records in the child table will automatically be deleted.
                        $countRequestDelete = $modelRequest->delete();

                        if ($countRequestDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявки не могут быть удалены'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявки не могут быть удалены'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявки успешно удалены'));
            }
        }
    }

}

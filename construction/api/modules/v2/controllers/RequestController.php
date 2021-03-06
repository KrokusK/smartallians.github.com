<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Profile;
use api\modules\v2\models\ProfileRROD;
use api\modules\v2\models\Request;
use api\modules\v2\models\RequestKindJob;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API Request controller
 */
class RequestController extends Controller
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
            /*
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to
                    'Origin' => ['*'],
                    // Allow only POST and PUT methods
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    // Allow only headers 'X-Wsse'
                    //'Access-Control-Request-Headers' => ['X-Wsse'],
                    //'Access-Control-Request-Headers' => ['*'],
                    // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                    'Access-Control-Allow-Credentials' => true,
                    //
                    'Access-Control-Allow-Headers' => ['authorization', 'DNT', 'User-Agent', 'Keep-Alive', 'Content-Type', 'accept,orig'],
                    // Allow OPTIONS caching
                    //'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    //'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                    //'Access-Control-Expose-Headers' => [],
                ],
            ],
            */
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
            /*
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
            */
        ];
    }


    /**
     * GET Method. Request table.
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
        // If user have create right that his allowed to other actions to the Request table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
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
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayRequestKindJobAssoc = array ('kind_job_id' => 'kind_job_id');

            // Search record by id in the database
            if (in_array('admin', $userRole) || in_array('contractor', $userRole)) {
                $query = Request::find()->leftJoin('request_kind_job','request_kind_job.request_id = request.id');  // get all records
            } else {
                $query = Request::find()->leftJoin('request_kind_job','request_kind_job.request_id = request.id')->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Request();
            foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                if (array_key_exists($valueRequestAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameRequestAssoc)) {
                        if ($nameRequestAssoc == 'description') {
                            $modelValidate->$nameRequestAssoc = $getParams[$arrayRequestAssoc[$nameRequestAssoc]];
                            if (!$modelValidate->validate($nameRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $query->andWhere(['ilike', $nameRequestAssoc, $getParams[$arrayRequestAssoc[$nameRequestAssoc]]]);
                        } elseif ($nameRequestAssoc == 'budjet') {
                            $value = $getParams[$arrayRequestAssoc[$nameRequestAssoc]];

                            $pos_begin = strpos($value, '[') + 1;
                            $pos_end = strpos($value, ',');
                            $value_start = substr($value, $pos_begin, $pos_end-$pos_begin);
                            $value_start = (int) str_replace(' ', '', $value_start);
                            $modelValidate->$nameRequestAssoc = $value_start;
                            if (!$modelValidate->validate($nameRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc.' значение '.$value_start));

                            $pos_begin = strpos($value, ',') + 1;
                            $pos_end = strpos($value, ']');
                            $value_end = substr($value, $pos_begin, $pos_end-$pos_begin);
                            $value_end = (int) str_replace(' ', '', $value_end);
                            $modelValidate->$nameRequestAssoc = $value_end;
                            if (!$modelValidate->validate($nameRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc.' значение '.$value_end));

                            $query->andWhere(['between', $nameRequestAssoc, $value_start, $value_end]);
                        } else {
                            $modelValidate->$nameRequestAssoc = $getParams[$arrayRequestAssoc[$nameRequestAssoc]];
                            if (!$modelValidate->validate($nameRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $query->andWhere([$nameRequestAssoc => $getParams[$arrayRequestAssoc[$nameRequestAssoc]]]);
                        }
                    }
                }
            }
            $modelValidate = new RequestKindJob();
            foreach ($arrayRequestKindJobAssoc as $nameRequestKindJobAssoc => $valueRequestKindJobAssoc) {
                if (array_key_exists($valueRequestKindJobAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameRequestKindJobAssoc)) {
                        $modelValidate->$nameRequestKindJobAssoc = $getParams[$arrayRequestKindJobAssoc[$nameRequestKindJobAssoc]];
                        if (!$modelValidate->validate($nameRequestKindJobAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestKindJobAssoc));

                        $query->andWhere(['request_kind_job.'.$nameRequestKindJobAssoc => $getParams[$arrayRequestKindJobAssoc[$nameRequestKindJobAssoc]]]);
                    }
                }
            }

            $modelRequest = $query->orderBy('created_at')
                ->with('kindJob', 'materials', 'statusRequest', 'cities')
                ->asArray()
                ->all();

            $RequestResponse = [
                'method' => 'GET',
                'status' => 0,
                'type' => 'success'
            ];

            $arrayDataRequest = [];
            foreach ($modelRequest as $keyRequest => $valueRequest) {
                $listMaterials = '';
                foreach ($valueRequest['materials'] as $keyMaterial => $valueMaterial) {
                    if (!empty($listMaterials)) $listMaterials .= ', ';
                    $listMaterials .= $valueMaterial['name'];
                }

                $dataRequest = [
                    'id' => $valueRequest['id'],
                    'status_request' => $valueRequest['statusRequest']['name'],
                    //'status_request_id' => $valueRequest['status_request_id'],
                    'city' => $valueRequest['cities']['name'],
                    //'city_id' => $valueRequest['city_id'],
                    'address' => $valueRequest['address'],
                    'name' => $valueRequest['name'],
                    'description' => $valueRequest['description'],
                    'task' => $valueRequest['task'],
                    'budjet' => $valueRequest['budjet'],
                    'period' => $valueRequest['period'],
                    'date_begin' => $valueRequest['date_begin'],
                    'date_end' => $valueRequest['date_end'],
                    'created_at' => $valueRequest['created_at'],
                    'kindJob' => $valueRequest['kindJob'][0],
                    'materials' => $listMaterials
                ];

                array_push($arrayDataRequest, $dataRequest);
            }

            // get properties from Request object and from links
            array_push($RequestResponse, $arrayDataRequest);
            //array_push($RequestResponse, ArrayHelper::toArray($modelRequest));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole) || in_array('contractor', $userRole)) {
                $query = Request::find();  // get all records
            } else {
                $query = Request::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelRequest = $query->orderBy('created_at')
                ->with('kindJob', 'materials', 'statusRequest', 'cities')
                ->asArray()
                ->all();

            $RequestResponse = [
                'method' => 'GET',
                'status' => 0,
                'type' => 'success'
            ];

            $arrayDataRequest = [];
            foreach ($modelRequest as $keyRequest => $valueRequest) {
                $listMaterials = '';
                foreach ($valueRequest['materials'] as $keyMaterial => $valueMaterial) {
                    if (!empty($listMaterials)) $listMaterials .= ', ';
                    $listMaterials .= $valueMaterial['name'];
                }

                $dataRequest = [
                    'id' => $valueRequest['id'],
                    'status_request' => $valueRequest['statusRequest']['name'],
                    //'status_request_id' => $valueRequest['status_request_id'],
                    'city' => $valueRequest['cities']['name'],
                    //'city_id' => $valueRequest['city_id'],
                    'address' => $valueRequest['address'],
                    'name' => $valueRequest['name'],
                    'description' => $valueRequest['description'],
                    'task' => $valueRequest['task'],
                    'budjet' => $valueRequest['budjet'],
                    'period' => $valueRequest['period'],
                    'date_begin' => $valueRequest['date_begin'],
                    'date_end' => $valueRequest['date_end'],
                    'created_at' => $valueRequest['created_at'],
                    'kindJob' => $valueRequest['kindJob'][0],
                    'materials' => $listMaterials
                ];

                array_push($arrayDataRequest, $dataRequest);
            }

            // get properties from Request object
            array_push($RequestResponse, $arrayDataRequest);
            //$RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            //array_push($RequestResponse, ArrayHelper::toArray($modelRequest));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Request table.
     * Insert record
     *
     * @return json
     */
    public function actionCreate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer')) {
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
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
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

            // Search record by id user in the profile table
            $queryProfile = Profile::find()->where(['user_id' => $userByToken->id]);
            $modelProfile = $queryProfile->one();

            if (empty($modelProfile)) {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Профиль по user_id'));
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

                    if ($flagRequest && $flagRequestKindJob) {
                        // Save record into profile_rrod table
                        $modelRequest->link('profiles', $modelProfile);
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
    }


    /**
     * PUT, PATCH Method. Request table.
     * Update records by id parameter
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
            // If user have create right that his allowed to other actions to the Request table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer')) {
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
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            if (array_key_exists($arrayRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryRequest = Request::find()->where(['id' => $bodyRaw[$arrayRequestAssoc['id']]]);  // get all records
                } else {
                    $queryRequest = Request::find()->where(['AND', ['id' => $bodyRaw[$arrayRequestAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
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

            // Search record by id user in the profile table
            $queryProfile = Profile::find()->where(['user_id' => $userByToken->id]);
            $modelProfile = $queryProfile->one();

            if (empty($modelProfile)) {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Профиль по user_id'));
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

                            // delete old records from profile_rrod table
                            ProfileRROD::deleteAll(['request_id' => $modelRequest->id]);
                            // Save record into profile_rrod table
                            $modelRequest->link('profiles', $modelProfile);
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
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Request table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (array_key_exists($arrayRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryRequest = Request::find()->where(['id' => $bodyRaw[$arrayRequestAssoc['id']]]);  // get all records
                } else {
                    $queryRequest = Request::find()->where(['AND', ['id' => $bodyRaw[$arrayRequestAssoc['id']]], ['created_by'=> $userByToken->id]]);  // get records created by this user
                }
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

                    // delete old records from profile_rrod table
                    ProfileRROD::deleteAll(['request_id' => $modelRequest->id]);

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
    }

    /**
     * DELETE Method. Request table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Request table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryRequest = Request::find();  // get all records
            } else {
                $queryRequest = Request::find()->where(['created_by'=> $userByToken->id]);  // get records created by this user
            }
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

                        // delete old records from profile_rrod table
                        ProfileRROD::deleteAll(['request_id' => $modelRequest->id]);

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

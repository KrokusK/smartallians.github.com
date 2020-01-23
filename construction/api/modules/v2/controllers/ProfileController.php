<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Profile;
use api\modules\v2\models\Contractor;
use api\modules\v2\models\KindUser;
use api\modules\v2\models\TypeJob;
use api\modules\v2\models\User;
use api\modules\v2\models\City;
use api\modules\v2\models\Specialization;
use api\modules\v2\models\ProfileCity;
use api\modules\v2\models\ProfileSpecialization;
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
 * API Request controller
 */
class ProfileController extends Controller
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
     * GET Method. Profile table.
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
            $arrayProfileAssoc = array ('id' => 'id', 'user_id' => 'user_id', 'kind_user_id' => 'kind_user_id', 'type_job_id' => 'type_job_id', 'fio' => 'fio', 'firm_name' => 'firm_name', 'inn' => 'inn', 'site' => 'site', 'avatar' => 'avatar', 'about' => 'about');
            $arrayContractorAssoc = array ('experience' => 'experience', 'cost' => 'cost');
            $arrayProfileCityAssoc = array ('city_id' => 'city');
            $arrayProfileSpecializationAssoc = array ('specialization_id' => 'specialization');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $query = Profile::find()->leftJoin('contractor','contractor.profile_id = profile.id')->leftJoin('profile_city','profile_city.profile_id = profile.id')->leftJoin('profile_specialization','profile_specialization.profile_id = profile.id');  // get all records
            } else {
                $query = Profile::find()->leftJoin('contractor','contractor.profile_id = profile.id')->leftJoin('profile_city','profile_city.profile_id = profile.id')->leftJoin('profile_specialization','profile_specialization.profile_id = profile.id')->Where(['profile.created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Profile();
            foreach ($arrayProfileAssoc as $nameProfileAssoc => $valueProfileAssoc) {
                if (array_key_exists($valueProfileAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameProfileAssoc)) {
                        $modelValidate->$nameProfileAssoc = $getParams[$arrayProfileAssoc[$nameProfileAssoc]];
                        if (!$modelValidate->validate($nameProfileAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileAssoc));

                        if ($nameProfileAssoc == 'fio' || $nameProfileAssoc == 'firm_name' || $nameProfileAssoc == 'inn') {
                            $query->andWhere(['ilike', $nameProfileAssoc, $getParams[$arrayProfileAssoc[$nameProfileAssoc]]]);
                        } else {
                            $query->andWhere([$nameProfileAssoc => $getParams[$arrayProfileAssoc[$nameProfileAssoc]]]);
                        }
                    }
                }
            }
            $modelValidate = new Contractor();
            foreach ($arrayContractorAssoc as $nameContractorAssoc => $valueContractorAssoc) {
                if (array_key_exists($valueContractorAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameContractorAssoc)) {
                        $modelValidate->$nameContractorAssoc = $getParams[$arrayContractorAssoc[$nameContractorAssoc]];
                        if (!$modelValidate->validate($nameContractorAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueContractorAssoc));

                        $query->andWhere(['contractor.'.$nameContractorAssoc => $getParams[$arrayContractorAssoc[$nameContractorAssoc]]]);
                    }
                }
            }
            $modelValidate = new ProfileCity();
            foreach ($arrayProfileCityAssoc as $nameProfileCityAssoc => $valueProfileCityAssoc) {
                if (array_key_exists($valueProfileCityAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameProfileCityAssoc)) {
                        $modelValidate->$nameProfileCityAssoc = $getParams[$arrayProfileCityAssoc[$nameProfileCityAssoc]];
                        if (!$modelValidate->validate($nameProfileCityAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileCityAssoc));

                        $query->andWhere(['profile_city.'.$nameProfileCityAssoc => $getParams[$arrayProfileCityAssoc[$nameProfileCityAssoc]]]);
                    }
                }
            }
            $modelValidate = new ProfileSpecialization();
            foreach ($arrayProfileSpecializationAssoc as $nameProfileSpecializationAssoc => $valueProfileSpecializationAssoc) {
                if (array_key_exists($valueProfileSpecializationAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameProfileSpecializationAssoc)) {
                        $modelValidate->$nameProfileSpecializationAssoc = $getParams[$arrayProfileSpecializationAssoc[$nameProfileSpecializationAssoc]];
                        if (!$modelValidate->validate($nameProfileSpecializationAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileSpecializationAssoc));

                        $query->andWhere(['profile_specialization.'.$nameProfileSpecializationAssoc => $getParams[$arrayProfileSpecializationAssoc[$nameProfileSpecializationAssoc]]]);
                    }
                }
            }

            $modelProfile = $query->orderBy('created_at')
                ->with('contractors','cities','specializations')
                ->asArray()
                ->all();

            // get properties from Profile object and from links
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelProfile));
            //array_push($RequestResponse, var_dump($modelProfile));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = Profile::find();  // get all records
            } else {
                $query = Profile::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelProfile = $query->orderBy('created_at')
                ->with('contractors','cities','specializations')
                ->asArray()
                ->all();

            // get properties from Profile object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelProfile));

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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
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
            $arrayProfileAssoc = array ('id' => 'id', 'user_id' => 'user_id', 'kind_user_id' => 'kind_user_id', 'type_job_id' => 'type_job_id', 'fio' => 'fio', 'firm_name' => 'firm_name', 'inn' => 'inn', 'site' => 'site', 'avatar' => 'avatar', 'about' => 'about');
            $arrayContractorAssoc = array ('experience' => 'experience', 'cost' => 'cost', 'passport' => 'passport');

            $modelProfile = new Profile();

            // fill in the properties in the Profile object
            foreach ($arrayProfileAssoc as $nameProfileAssoc => $valueProfileAssoc) {
                if (array_key_exists($valueProfileAssoc, $bodyRaw)) {
                    if ($modelProfile->hasAttribute($nameProfileAssoc)) {
                        if ($nameProfileAssoc != 'id' && $nameProfileAssoc != 'created_at' && $nameProfileAssoc != 'updated_at') {
                            $modelProfile->$nameProfileAssoc = $bodyRaw[$valueProfileAssoc];

                            if (!$modelProfile->validate($nameProfileAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileAssoc));

                            $modelProfile->created_by = $userByToken->id;
                            $modelProfile->created_at = time();
                            $modelProfile->updated_at = time();
                        }
                    }
                }
            }

            $modelContractor = new Contractor();

            // fill in the properties in the Contractor object
            foreach ($arrayContractorAssoc as $nameContractorAssoc => $valueContractorAssoc) {
                if (array_key_exists($valueContractorAssoc, $bodyRaw)) {
                    if ($modelContractor->hasAttribute($nameContractorAssoc)) {
                        if ($nameContractorAssoc != 'id' && $nameContractorAssoc != 'profile_id') {
                            $modelContractor->$nameContractorAssoc = $bodyRaw[$valueContractorAssoc];

                            if (!$modelContractor->validate($nameContractorAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueContractorAssoc));

                            $modelContractor->created_by = $userByToken->id;
                        }
                    }
                }
            }

            // check parametr for the Specialization object
            $arrayModelSpecialization = [];
            if (array_key_exists('specializations', $bodyRaw)) {
                if (!is_array($bodyRaw['specializations'])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре specializations ожидается массив'));

                foreach ($bodyRaw['specializations'] as $key => $value) {
                    if (!preg_match("/^[0-9]*$/", $value)) {
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: specializations'));
                    }

                    $querySpecialization = Specialization::find()->where(['id' => $value]);
                    $modelSpecialization = $querySpecialization->one();
                    if (empty($modelSpecialization)) {
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Специализация по id'));
                    }
                    array_push($arrayModelSpecialization, $modelSpecialization);
                }
            }

            // check parametr for the City object
            $arrayModelCity = [];
            if (array_key_exists('cities', $bodyRaw)) {
                if (!is_array($bodyRaw['cities'])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре cities ожидается массив'));

                foreach ($bodyRaw['cities'] as $key => $value) {
                    if (!preg_match("/^[0-9]*$/", $value)) {
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: cities'));
                    }

                    $queryCity = City::find()->where(['id' => $value]);
                    $modelCity = $queryCity->one();
                    if (empty($modelCity)) {
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Город по id'));
                    }
                    array_push($arrayModelCity, $modelCity);
                }
            }

            if ($modelProfile->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagProfile = $modelProfile->save(false); // insert into profile table

                    if ($flagProfile) {

                        $modelContractor->profile_id = $modelProfile->id;
                        if ($modelContractor->validate()) {
                            $flagContractor = $modelContractor->save(false); // insert into contractor table
                        } else {
                            $flagContractor = false;
                        }

                        // Save records into profile_city table
                        if ($flagContractor) {
                            foreach ($arrayModelCity as $model) {
                                $modelProfile->link('cities', $model);
                            }

                            foreach ($arrayModelSpecialization as $model) {
                                $modelProfile->link('specializations', $model);
                            }
                        }
                    }

                    if ($flagProfile && $flagContractor) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть сохранен'));
                }

                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Профиль успешно сохранен'));
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
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
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
     * DELETE Method. Profile table.
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
            // If user have create right that his allowed to other actions to the Profile table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
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
            $arrayProfileAssoc = array ('id' => 'id', 'user_id' => 'user_id', 'kind_user_id' => 'kind_user_id', 'type_job_id' => 'type_job_id', 'fio' => 'fio', 'firm_name' => 'firm_name', 'inn' => 'inn', 'site' => 'site', 'avatar' => 'avatar', 'about' => 'about');

            if (array_key_exists($arrayProfileAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayProfileAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryProfile = Profile::find()->where(['id' => $bodyRaw[$arrayProfileAssoc['id']]]);  // get all records
                } else {
                    $queryProfile = Profile::find()->where(['AND', ['id' => $bodyRaw[$arrayProfileAssoc['id']]], ['created_by'=> $userByToken->id]]);  // get records created by this user
                }
                $modelProfile = $queryProfile->one();

                if (empty($modelProfile)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Профиль по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelProfile)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from profile table.
                    // Because the foreign keys with cascade delete that if a record in the parent table (profile table) is deleted, then the corresponding records in the child table will automatically be deleted (contractor, profile_city, profile_specialization).
                    $countProfileDelete = $modelProfile->delete($modelProfile->id);

                    // delete old records from profile_rrod table
                    ProfileRROD::deleteAll(['profile_id' => $modelProfile->id]);

                    if ($countProfileDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Профиль успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }

    /**
     * DELETE Method. Profile table.
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
            // If user have create right that his allowed to other actions to the Profile table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
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
            $arrayProfileAssoc = array ('id' => 'id', 'user_id' => 'user_id', 'kind_user_id' => 'kind_user_id', 'type_job_id' => 'type_job_id', 'fio' => 'fio', 'firm_name' => 'firm_name', 'inn' => 'inn', 'site' => 'site', 'avatar' => 'avatar', 'about' => 'about');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryProfile = Profile::find();  // get all records
            } else {
                $queryProfile = Profile::find()->where(['created_by'=> $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Profile();
            foreach ($arrayProfileAssoc as $nameProfileAssoc => $valueProfileAssoc) {
                if (array_key_exists($valueProfileAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameProfileAssoc)) {
                        $modelValidate->$nameProfileAssoc = $getParams[$arrayProfileAssoc[$nameProfileAssoc]];
                        if (!$modelValidate->validate($nameProfileAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileAssoc));

                        $query->andWhere([$nameProfileAssoc => $getParams[$arrayProfileAssoc[$nameProfileAssoc]]]);
                    }
                }
            }
            $modelsProfile = $queryProfile->all();

            if (!empty($modelsProfile) && !empty($modelValidate)) {
                foreach ($modelsProfile as $modelProfile) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from profile table.
                        // Because the foreign keys with cascade delete that if a record in the parent table (profile table) is deleted, then the corresponding records in the child table will automatically be deleted (contractor, profile_city, profile_specialization).
                        $countProfileDelete = $modelProfile->delete();

                        // delete old records from profile_rrod table
                        ProfileRROD::deleteAll(['profile_id' => $modelProfile->id]);

                        if ($countProfileDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Профиль успешно удален'));
            }
        }
    }

}

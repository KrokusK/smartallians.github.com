<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Profile;
use api\modules\v2\models\Contractor;
use api\modules\v2\models\City;
use api\modules\v2\models\Specialization;
use api\modules\v2\models\ProfileCity;
use api\modules\v2\models\ProfileSpecialization;
use api\modules\v2\models\UserRequestData;
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
    /*
    public function actionView()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByPermission(['createCustomer', 'createContractor']);
            // get user roles
            $userRoles = $modelUserRequestData->getUserRoles();
            // get request params
            $getParams = $modelUserRequestData->getRequestParams();
            // init model Profile
            $modelProfile = new Profile();
            // Search data
            return $modelProfile->getDataProfile($getParams, $userRoles);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
    */

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
        // If user have create right that his allowed to other actions to the Profile table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        }

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = [
                'id' => 'id',
                'user_id' => 'user_id',
                'kind_user_id' => 'kind_user_id',
                'type_job_id' => 'type_job_id',
                'fio' => 'fio',
                'firm_name' => 'firm_name',
                'inn' => 'inn',
                'site' => 'site',
                'avatar' => 'avatar',
                'about' => 'about',
                'last_name' => 'last_name',
                'first_name' => 'first_name',
                'middle_name' => 'middle_name'
            ];
            $arrayContractorAssoc = [
                'experience' => 'experience',
                'cost' => 'cost'
            ];
            $arrayProfileCityAssoc = ['city_id' => 'city'];
            $arrayProfileSpecializationAssoc = ['specialization_id' => 'specialization'];

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
     * POST Method. Profile table.
     * Insert records
     *
     * @return json
     */
    /*
    public function actionCreate()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByPermission(['createCustomer', 'createContractor']);
            // get request params
            $postParams = $modelUserRequestData->getRequestParams();
            // init model Profile
            $modelProfile = new Profile();
            // Save object by params
            return $modelProfile->addDataProfile($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
    */

    /**
     * POST Method. Profile table.
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

            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));


            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = [
                'id' => 'id',
                'user_id' => 'user_id',
                'kind_user_id' => 'kind_user_id',
                'type_job_id' => 'type_job_id',
                'fio' => 'fio',
                'firm_name' => 'firm_name',
                'inn' => 'inn',
                'site' => 'site',
                'avatar' => 'avatar',
                'about' => 'about',
                'last_name' => 'last_name',
                'first_name' => 'first_name',
                'middle_name' => 'middle_name'
            ];
            $arrayContractorAssoc = [
                'experience' => 'experience',
                'cost' => 'cost',
                'passport' => 'passport'
            ];

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
     * PUT, PATCH Method. Profile table.
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
            // If user have create right that his allowed to other actions to the Profile table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию обновления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = [
                'id' => 'id',
                'user_id' => 'user_id',
                'kind_user_id' => 'kind_user_id',
                'type_job_id' => 'type_job_id',
                'fio' => 'fio',
                'firm_name' => 'firm_name',
                'inn' => 'inn',
                'site' => 'site',
                'avatar' => 'avatar',
                'about' => 'about',
                'last_name' => 'last_name',
                'first_name' => 'first_name',
                'middle_name' => 'middle_name'
            ];
            $arrayContractorAssoc = [
                'experience' => 'experience',
                'cost' => 'cost',
                'passport' => 'passport'
            ];

            if (array_key_exists($arrayProfileAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayProfileAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
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

                if (!empty($modelProfile)) {
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
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Профиль по id'));
                }
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id Профиля'));
            }

            // Update in the database
            if ($modelProfile->validate() ) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagProfile = $modelProfile->save(false); // update into profile table

                    if ($flagProfile) {

                        // delete old records from contractor table
                        Contractor::deleteAll(['profile_id' => $modelProfile->id]);

                        $modelContractor->profile_id = $modelProfile->id;
                        if ($modelContractor->validate()) {
                            $flagContractor = $modelContractor->save(false); // update into contractor table
                        } else {
                            $flagContractor = false;
                        }

                        // Save records into profile_city, profile_specialization tables
                        if ($flagContractor) {
                            // delete old records from profile_city table
                            ProfileCity::deleteAll(['profile_id' => $modelProfile->id]);
                            // delete old records from profile_specialization table
                            ProfileSpecialization::deleteAll(['profile_id' => $modelProfile->id]);

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
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть сохранен (обновлен)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Профиль не может быть сохранен (обновлен)'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Профиль успешно сохранен (обновлен)'));
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
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = [
                'id' => 'id',
                'user_id' => 'user_id',
                'kind_user_id' => 'kind_user_id',
                'type_job_id' => 'type_job_id',
                'fio' => 'fio',
                'firm_name' => 'firm_name',
                'inn' => 'inn',
                'site' => 'site',
                'avatar' => 'avatar',
                'about' => 'about',
                'last_name' => 'last_name',
                'first_name' => 'first_name',
                'middle_name' => 'middle_name'
            ];

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
                    // Because the foreign keys with cascade delete that if a record in the parent table (profile table) is deleted, then the corresponding records in the child table will automatically be deleted (contractor, profile_city, profile_specialization, profile_rrod).
                    $countProfileDelete = $modelProfile->delete($modelProfile->id);

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
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayProfileAssoc = [
                'id' => 'id',
                'user_id' => 'user_id',
                'kind_user_id' => 'kind_user_id',
                'type_job_id' => 'type_job_id',
                'fio' => 'fio',
                'firm_name' => 'firm_name',
                'inn' => 'inn',
                'site' => 'site',
                'avatar' => 'avatar',
                'about' => 'about',
                'last_name' => 'last_name',
                'first_name' => 'first_name',
                'middle_name' => 'middle_name'
            ];

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryProfile = Profile::find();  // get all records
            } else {
                $queryProfile = Profile::find()->where(['created_by'=> $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Profile();
            foreach ($arrayProfileAssoc as $nameProfileAssoc => $valueProfileAssoc) {
                if (array_key_exists($valueProfileAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameProfileAssoc)) {
                        $modelValidate->$nameProfileAssoc = $bodyRaw[$arrayProfileAssoc[$nameProfileAssoc]];
                        if (!$modelValidate->validate($nameProfileAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueProfileAssoc));

                        $queryProfile->andWhere([$nameProfileAssoc => $bodyRaw[$arrayProfileAssoc[$nameProfileAssoc]]]);
                    }
                }
            }
            $modelsProfile = $queryProfile->all();

            if (!empty($modelsProfile) && !empty($modelValidate)) {
                foreach ($modelsProfile as $modelProfile) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from profile table.
                        // Because the foreign keys with cascade delete that if a record in the parent table (profile table) is deleted, then the corresponding records in the child table will automatically be deleted (contractor, profile_city, profile_specialization, profile_rrod).
                        $countProfileDelete = $modelProfile->delete();

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

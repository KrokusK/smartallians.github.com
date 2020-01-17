<?php
namespace api\modules\v2\controllers;

use api\common\models\User;
use api\modules\v2\models\Photo;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API Photo controller
 */
class PhotoController extends Controller
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
                    'update' => ['post', 'put', 'patch'],
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
     * GET Method. Photo table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        //if (Yii::$app->request->isAjax) {

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
        $flagRights = false;
        foreach(array('admin', 'customer', 'contractor', 'mediator') as $value) {
            if (in_array($value, $userRole)) {
                $flagRights = true;
            }
        }
        if (!$flagRights) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $query = Photo::find();
            } else {
                $query = Photo::find()->Where(['created_by' => $userByToken->id]);
            }
            $modelValidate = new Photo();
            foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                if (array_key_exists($valuePhotoAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($namePhotoAssoc)) {
                        $modelValidate->$namePhotoAssoc = $getParams[$arrayPhotoAssoc[$namePhotoAssoc]];
                        if (!$modelValidate->validate($namePhotoAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePhotoAssoc));

                        $query->andWhere([$namePhotoAssoc => $getParams[$arrayPhotoAssoc[$namePhotoAssoc]]]);
                    }
                }

            }

            $modelPhoto = $query->orderBy('id')
                //->offset($pagination->offset)
                //->limit($pagination->limit)
                ->with('requests','responses','positions')
                ->asArray()
                ->all();

            // get properties from Photo object and from links
            $PhotoResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));
            //array_push($PhotoResponse, var_dump($modelPhoto));

            return Json::encode($PhotoResponse);

        } else {
            // Search all records in the database
            $query = Photo::find()->Where(['created_by' => $userByToken->id]);

            $modelPhoto = $query->orderBy('id')
                ->with('requests','responses','positions')
                ->asArray()
                ->all();

            // get properties from Photo object
            $PhotoResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));

            return Json::encode($PhotoResponse);
        }
        //}
    }


    /**
     * POST Method. Photo table.
     * Insert record
     *
     * @return json
     */
    public function actionCreate()
    {
        //if (Yii::$app->request->isAjax) {

        $postParams = Yii::$app->getRequest()->post();

        if (is_array($postParams)) {
            // check user is a guest
            if (array_key_exists('token', $postParams)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($postParams['token']);
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
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            // Attribute names associated by request parameters
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');
            // Name form with data. Request: multipart/form-data request
            $arrayPhotoFormAssoc = array ('photos' => 'photos');

            $modelPhoto = new Photo();

            // fill in the properties in the Photo object
            //$modelPhoto->load(Yii::$app->request->post());
            foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                if (array_key_exists($valuePhotoAssoc, $postParams)) {
                    if ($modelPhoto->hasAttribute($namePhotoAssoc)) {
                        if ($namePhotoAssoc != 'id' && $namePhotoAssoc != 'path') {
                            $modelPhoto->$namePhotoAssoc = $postParams[$valuePhotoAssoc];

                            if (!$modelPhoto->validate($namePhotoAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePhotoAssoc));

                            $modelPhoto->created_by = $userByToken->id;
                        }
                    }
                }
            }

            //$modelPhoto->imageFiles = UploadedFile::getInstances($modelPhoto, 'imageFiles'); // Format form parameters: Photo[imageFiles][]
            $modelPhoto->imageFiles = UploadedFile::getInstancesByName($arrayPhotoFormAssoc['photos']);
            if ($modelPhoto->upload() && !empty($modelPhoto->imageFiles)) { // save photos
                // Insert each new Photo in database
                foreach ($modelPhoto->arrayWebFilename as $file) {
                    $transactionPhoto = \Yii::$app->db->beginTransaction();
                    try {
                        $modelPhotoFile = new Photo();

                        foreach ($modelPhoto as $key => $value) {
                            if ($modelPhoto->hasAttribute($key))
                                if ($key != 'id' && $key != 'path') {
                                    $modelPhotoFile->$key = $value;
                                }
                        }

                        $modelPhotoFile->path = '/uploads/photo/'.$file;

                        //$PhotoResponse = array('method' => 'POST', 'status' => 0, 'type' => 'test');
                        //array_push($PhotoResponse, ArrayHelper::toArray($modelPhotoFile));
                        //return Json::encode($PhotoResponse);

                        if ($modelPhotoFile->validate()) {
                            $flagPhoto = $modelPhotoFile->save(false); // insert
                        } else {
                            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                        }

                        if ($flagPhoto == true) {
                            $transactionPhoto->commit();
                        } else {
                            $transactionPhoto->rollBack();
                            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото /uploads/photo/'.$file.' не может быть сохранено'));
                        }
                    } catch (Exception $ex) {
                        $transactionPhoto->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото /uploads/photo/'.$file.' не может быть сохранено'));
                    }
                }

                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Фото успешно сохранено(ы)'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Файл(ы) фото не были переданы'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * POST Method. Photo table.
     * Update records by id parameter
     *
     * PUT, POST methods not working.
     * Class yii\web\MultipartFormDataParser
     * This parser provides the fallback for the 'multipart/form-data' processing on non POST requests, for example: the one with 'PUT' request method.
     * But photo files by UploadedFile::getInstancesByName('photos') can't be get.
     * (https://www.yiiframework.com/doc/api/2.0/yii-web-multipartformdataparser)
     *
     * @return json
     */
    public function actionUpdate()
    {
        //if (Yii::$app->request->isAjax) {

        $postParams = Yii::$app->getRequest()->post();

        if (is_array($postParams)) {
            // check user is a guest
            if (array_key_exists('token', $postParams)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($postParams['token']);
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
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            // Attribute names associated by request parameters
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');
            // Name form with data. Request: multipart/form-data request
            $arrayPhotoFormAssoc = array ('photos' => 'photos');

            if (array_key_exists($arrayPhotoAssoc['id'], $postParams)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/", $postParams[$arrayPhotoAssoc['id']])) {
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                //$modelPhoto = new Photo();

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryPhoto = Photo::find()->where(['id' => $postParams[$arrayPhotoAssoc['id']]]);
                } else {
                    $queryPhoto = Photo::find()->where(['AND', ['id' => $postParams[$arrayPhotoAssoc['id']]], ['created_by'=> $userByToken->id]]);
                }
                $modelPhoto = $queryPhoto->orderBy('id')->one();

                if (empty($modelPhoto)) {
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена запись по id'));
                }

                // fill in the properties in the Photo object
                //$modelPhoto->load(Yii::$app->request->post());
                foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                    if (array_key_exists($valuePhotoAssoc, $postParams)) {
                        if ($modelPhoto->hasAttribute($namePhotoAssoc)) {
                            if ($namePhotoAssoc != 'id' && $namePhotoAssoc != 'path') {
                                $modelPhoto->$namePhotoAssoc = $postParams[$valuePhotoAssoc];

                                if (!$modelPhoto->validate($namePhotoAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valuePhotoAssoc));

                                $modelPhoto->created_by = $userByToken->id;
                            }
                        }
                    }
                }

                //$modelPhoto->imageFiles = UploadedFile::getInstances($modelPhoto, 'imageFiles'); // Format form parameters: Photo[imageFiles][]
                $modelPhoto->imageFiles = UploadedFile::getInstancesByName($arrayPhotoFormAssoc['photos']);

                if ($modelPhoto->upload()) { // save photos
                    if (!empty($modelPhoto->imageFiles)) {
                        // Insert each new Photo in database
                        foreach ($modelPhoto->arrayWebFilename as $file) {
                            $transactionPhoto = \Yii::$app->db->beginTransaction();
                            try {
                                $modelPhotoFile = new Photo();

                                foreach ($modelPhoto as $key => $value) {
                                    if ($modelPhoto->hasAttribute($key))
                                        if ($key != 'id' && $key != 'path') {
                                            $modelPhotoFile->$key = $value;
                                        }
                                }

                                $modelPhotoFile->path = '/uploads/photo/'.$file;
                                $modelPhoto->path = '/uploads/photo/' . $file;

                                //$PhotoResponse = array('method' => 'POST', 'status' => 0, 'type' => 'test');
                                //array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));
                                //return Json::encode($PhotoResponse);

                                if ($modelPhotoFile->validate()) {
                                    $flagPhoto = $modelPhoto->save(false); // insert
                                } else {
                                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                                }

                                if ($flagPhoto == true) {
                                    $transactionPhoto->commit();
                                } else {
                                    $transactionPhoto->rollBack();
                                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото /uploads/photo/' . $file . ' не может быть сохранено'));
                                }
                            } catch (Exception $ex) {
                                $transactionPhoto->rollBack();
                                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото /uploads/photo/' . $file . ' не может быть сохранено'));
                            }
                        }
                    } else {
                        $transactionPhoto = \Yii::$app->db->beginTransaction();
                        try {
                            //$PhotoResponse = array('method' => 'POST', 'status' => 0, 'type' => 'test');
                            //array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));
                            //return Json::encode($PhotoResponse);

                            if ($modelPhoto->validate()) {
                                $flagPhoto = $modelPhoto->save(false); // insert
                            } else {
                                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                            }

                            if ($flagPhoto == true) {
                                $transactionPhoto->commit();
                            } else {
                                $transactionPhoto->rollBack();
                                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото не может быть сохранено'));
                            }
                        } catch (Exception $ex) {
                            $transactionPhoto->rollBack();
                            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Фото не может быть сохранено'));
                        }
                    }

                    return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Фото успешно сохранено(ы)'));
                } else {
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id фото'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * DELETE Method. Photo table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */

    public function actionDelete()
    {
        //if (Yii::$app->request->isAjax) {
        $postParams = Yii::$app->getRequest()->post();

        if (is_array($postParams)) {
            // check user is a guest
            if (array_key_exists('token', $postParams)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($postParams['token']);
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
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            // Attribute names associated by request parameters
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');

            if (array_key_exists($arrayPhotoAssoc['id'], $postParams)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/", $postParams[$arrayPhotoAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryPhoto = Photo::find()->where(['id' => $postParams[$arrayPhotoAssoc['id']]]);
                } else {
                    $queryPhoto = Photo::find()->where(['AND', ['id' => $postParams[$arrayPhotoAssoc['id']]], ['created_by'=> $userByToken->id]]);
                }
                $modelPhoto = $queryPhoto->orderBy('id')->one();

                if (empty($modelPhoto)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена запись по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelPhoto)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Photo table.
                    $countPhotoDelete = $modelPhoto->delete($modelPhoto->id);

                    if ($countPhotoDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удалено'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удалено'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Фото успешно удалено'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удалено'));
            }

        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * DELETE Method. Photo table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        //if (Yii::$app->request->isAjax) {
        $postParams = Yii::$app->getRequest()->post();

        if (is_array($postParams)) {
            // check user is a guest
            if (array_key_exists('token', $postParams)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($postParams['token']);
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
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            // Attribute names associated by request parameters
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryPhoto = Photo::find();
            } else {
                $queryPhoto = Photo::find()->where(['created_by'=> $userByToken->id]);
            }
            $modelValidate = new Photo();
            foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                if (array_key_exists($valuePhotoAssoc, $postParams)) {
                    if ($modelValidate->hasAttribute($namePhotoAssoc)) {
                        $modelValidate->$namePhotoAssoc = $postParams[$arrayPhotoAssoc[$namePhotoAssoc]];
                        if (!$modelValidate->validate($namePhotoAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valuePhotoAssoc));

                        $queryPhoto->andWhere([$namePhotoAssoc => $postParams[$arrayPhotoAssoc[$namePhotoAssoc]]]);
                    }
                }

            }
            $modelsPhoto = $queryPhoto->all();

            if (!empty($modelsPhoto) && !empty($modelValidate)) {
                foreach ($modelsPhoto as $modelPhoto) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete old records from Photo_kind_job table
                        //PhotoKindJob::deleteAll(['Photo_id' => $modelPhoto->id]);

                        // delete from Photo table.
                        // Because the foreign keys with cascade delete that if a record in the parent table (Photo table) is deleted, then the corresponding records in the child table will automatically be deleted.
                        $countPhotoDelete = $modelPhoto->delete();

                        if ($countPhotoDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удалено'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удалено'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Фото успешно удалено'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }

}

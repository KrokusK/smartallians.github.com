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
        $userByToken = User::findIdentityByAccessToken($getParams['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        if (is_array($getParams)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');

            // Search record by id in the database
            //$query = Photo::find()->Where(['created_by' => Yii::$app->user->getId()]);
            $query = Photo::find()->Where(['created_by' => $userByToken->id]);
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
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
            //$query = Photo::find()->Where(['created_by' => Yii::$app->user->getId()]);
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

        //$modelPhoto = new Photo();

        if (is_array($postParams)) {
        //if ($modelPhoto->load(Yii::$app->request->post())) {
            // check user is a guest
            $userByToken = User::findIdentityByAccessToken($postParams['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }


            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'position_id' => 'position_id', 'caption' => 'caption', 'description' => 'description', 'path' => 'path');

            $modelPhoto = new Photo();

            // fill in the properties in the Photo object
            /*
            foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                if (array_key_exists($valuePhotoAssoc, $postParams)) {
                    if ($modelPhoto->hasAttribute($namePhotoAssoc)) {
                        if ($namePhotoAssoc != 'id' && $namePhotoAssoc != 'created_at' && $namePhotoAssoc != 'updated_at') {
                            $modelPhoto->$namePhotoAssoc = $postParams[$valuePhotoAssoc];

                            if (!$modelPhoto->validate($namePhotoAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePhotoAssoc));

                            $modelPhoto->created_by = $userByToken->id;
                        }
                    }
                }
            }
            */

            //$modelPhoto->imageFiles = $postParams['imagefiles'];
            $modelPhoto->load(Yii::$app->request->post());

            //$modelPhoto->imageFiles = UploadedFile::getInstances($modelPhoto, 'imageFiles');
            $modelPhoto->imageFiles = static::getInstancesByName('imageFiles');
            if ($modelPhoto->upload()) { // save ad photos
            }

            $bodyRaw = Yii::$app->getRequest()->bodyParams;

            $PhotoResponse = array('method' => 'POST', 'status' => 0, 'type' => 'test');
            //array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));
            array_push($PhotoResponse, $bodyRaw);
            return Json::encode($PhotoResponse);

            if ($modelPhoto->validate()) {
            /*
                                        $transaction = \Yii::$app->db->beginTransaction();
                                        try {
                                            $flagPhoto = $modelPhoto->save(false); // insert into Photo table

                                            $flagPhotoKindJob = true;
                                            if ($flagPhoto) {

                                                // Save records into Photo_kind_job table
                                                if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                                                    foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                                                        $modelPhotoKindJob = new PhotoKindJob();

                                                        // fill in the properties in the KindJob object
                                                        if ($modelPhotoKindJob->hasAttribute('kind_job_id')) {
                                                            $modelPhotoKindJob->kind_job_id = $value;
                                                        }

                                                        if ($modelPhotoKindJob->validate('kind_job_id')) {
                                                            $modelPhotoKindJob->Photo_id = $modelPhoto->id;

                                                            if (!$modelPhotoKindJob->save(false)) $flagPhotoKindJob = false; // insert into Photo_kind_job table
                                                        }
                                                    }
                                                }
                                            }

                                            if ($flagPhoto == true && $flagPhotoKindJob == true) {
                                                $transaction->commit();
                                            } else {
                                                $transaction->rollBack();
                                                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                                            }
                                        } catch (Exception $ex) {
                                            $transaction->rollBack();
                                            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                                        }

                                        //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelPhoto))));
                                        return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена'));
                                    */
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }

        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Photo table.
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

        //$modelPhoto->setAttributes($bodyRaw);

        // check user is a guest
        $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // load attributes in Photo object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            if (array_key_exists($arrayPhotoAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayPhotoAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryPhoto = Photo::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayPhotoAssoc['id']]], ['created_by'=> $userByToken->id]]);
                $modelPhoto = $queryPhoto->orderBy('created_at')->one();

                if (!empty($modelPhoto)) {
                    // fill in the properties in the Photo object
                    foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                        if (array_key_exists($valuePhotoAssoc, $bodyRaw)) {
                            if ($modelPhoto->hasAttribute($namePhotoAssoc)) {
                                if ($namePhotoAssoc != 'id' && $namePhotoAssoc != 'created_at' && $namePhotoAssoc != 'updated_at') {
                                    $modelPhoto->$namePhotoAssoc = $bodyRaw[$valuePhotoAssoc];

                                    if (!$modelPhoto->validate($namePhotoAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePhotoAssoc));

                                    $modelPhoto->created_by = $userByToken->id;
                                    $modelPhoto->updated_at = time();
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

            if ($modelPhoto->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagPhoto = $modelPhoto->save(false); // insert into Photo table

                    $flagPhotoKindJob = true;
                    if ($flagPhoto) {

                        // Save records into Photo_kind_job table
                        if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                            // delete old records from Photo_kind_job table
                            PhotoKindJob::deleteAll(['Photo_id' => $modelPhoto->id]);

                            foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                                $modelPhotoKindJob = new PhotoKindJob();

                                // fill in the properties in the KindJob object
                                if ($modelPhotoKindJob->hasAttribute('kind_job_id')) {
                                    $modelPhotoKindJob->kind_job_id = $value;
                                }

                                if ($modelPhotoKindJob->validate('kind_job_id')) {
                                    $modelPhotoKindJob->Photo_id = $modelPhoto->id;

                                    if (!$modelPhotoKindJob->save(false)) $flagPhotoKindJob = false; // insert into Photo_kind_job table
                                }
                            }
                        }
                    }

                    if ($flagPhoto == true && $flagPhotoKindJob == true) {
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
     * DELETE Method. Photo table.
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

        //$modelPhoto->setAttributes($bodyRaw);

        // check user is a guest
        $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // load attributes in Photo object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (array_key_exists($arrayPhotoAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayPhotoAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryPhoto = Photo::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayPhotoAssoc['id']]], ['created_by'=> $userByToken->id]]);
                $modelPhoto = $queryPhoto->orderBy('created_at')->one();

                if (empty($modelPhoto)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelPhoto)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete old records from Photo_kind_job table
                    //PhotoKindJob::deleteAll(['Photo_id' => $modelPhoto->id]);

                    // delete from Photo table.
                    // Because the foreign keys with cascade delete that if a record in the parent table (Photo table) is deleted, then the corresponding records in the child table will automatically be deleted.
                    $countPhotoDelete = $modelPhoto->delete($modelPhoto->id);

                    if ($countPhotoDelete > 0) {
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
     * DELETE Method. Photo table.
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

        //$modelPhoto->setAttributes($bodyRaw);

        // check user is a guest
        $userByToken = User::findIdentityByAccessToken($bodyRaw['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // load attributes in Photo object
        // example: yiisoft/yii2/base/Model.php

        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPhotoAssoc = array('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            // Search record by id in the database
            $queryPhoto = Photo::find()->Where(['created_by' => $userByToken->id]);
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
            $modelValidate = new Photo();
            foreach ($arrayPhotoAssoc as $namePhotoAssoc => $valuePhotoAssoc) {
                if (array_key_exists($valuePhotoAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($namePhotoAssoc)) {
                        $modelValidate->$namePhotoAssoc = $bodyRaw[$arrayPhotoAssoc[$namePhotoAssoc]];
                        if (!$modelValidate->validate($namePhotoAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valuePhotoAssoc));

                        $queryPhoto->andWhere([$namePhotoAssoc => $bodyRaw[$arrayPhotoAssoc[$namePhotoAssoc]]]);
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

    /**
     *  getInstancesByName Method. Photo table.
     *  Get array by name attribute (Form parameter).
     *  Request header: Content-Type: multipart/form-data
     *  example: yii\web\UploadedFile:: getInstancesByName
     *
     * @return array
     */
    public static function getInstancesByName($name)
    {
        $files = self::loadFiles();
        if (isset($files[$name])) {
            return [new static($files[$name])];
        }
        $results = [];
        foreach ($files as $key => $file) {
            if (strpos($key, "{$name}[") === 0) {
                $results[] = new static($file);
            }
        }

        return $results;
    }

}

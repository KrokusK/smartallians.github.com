<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\httpclient\Client;
use common\models\User;

/**
 * Signup form
 */
class SignupAccount extends Model
{
    public $username;
    public $email;
    public $password;
    public $modelResponseMessage;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Пользователь с таким именем уже существет.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Пользователь с таким email уже существет.'],

            ['phone', 'trim'],
            [['phone'], 'match', 'pattern' => '/^\+7[0-9]{3}[0-9]{3}[0-9]{2}[0-9]{2}$/', 'message' => 'телефон  должно быть введен в формаие +71234567890', 'skipOnEmpty' => true],
            ['phone', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Пользователь с таким телефоном уже существет.'],
            ['phone', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Create a model
     */
    public function __construct()
    {
        // Set property
        $this->modelResponseMessage = new ResponseMessage();
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup($params)
    {
        if (!$this->validate()) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: не возможно сохранить пользователя');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
        
        $user = new User();
        $user->username = $params['email'];
        $user->email = $params['email'];
        $user->setPassword($params['password']);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $flagSave = $user->save();
            $flagEmail = $this->sendEmail($user);

            if ($flagSave && $flagEmail) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: не возможно сохранить пользователя');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: не возможно сохранить пользователя');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveDataMessage('Пользователь успешно сохранен. На почту выслано письмо с подтверждением');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }

    /**
     * Sends confirmation email to user
     * @params parametrs of email letter
     * @return bool whether the email was sent
     */
    public function sendEmailVerifyCode($params)
    {
        $flag = Yii::$app
            ->mailer
            ->compose()
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($params['email'])
            ->setSubject('Подтверждение почты: ' . Yii::$app->name)
            ->setTextBody('Пожалуйста, введите следующий код подтверждения ' . $params['key'] . ' на странице регистрации.')
            ->send();

        if ($flag) {
            $this->modelResponseMessage->saveDataMessage('Письмо с верификационным кодом отправлено на почту' . $params['email']);
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: не возможно отправить почту');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Sends confirmation sms to user
     * @params parametrs of sms
     * @return bool whether the sms was sent
     */
    public function sendPhoneVerifyCode($params)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('https://sms.ru/sms/send')
            ->setData([
                'api_id' => '20A77165-3182-6775-558D-623A2BC81EDB',
                'to' => $params['phone'],
                'msg' => $params['msg'],
                'json' => '1'
            ])
            ->send();
        $flag = $response->isOk;
        $statusSMS = $response->data['status'];

        if ($flag) {
            $this->modelResponseMessage->saveDataMessage('Письмо с верификационным кодом отправлено на телефон' . $params['phone']);
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: не возможно отправить sms');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}

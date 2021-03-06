<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_usuarios = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            [['username'], 'validarUsuario'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUsuarios();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function validarUsuario($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUsuarios();
            if ($user->token !== null) {
                $this->addError($attribute, 'El usuario aún no está validado.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUsuarios();
            if ($user->banned_at !== null) {
                if ((new \DateTime()) > \DateTime::createFromFormat('Y-m-d H:i:s', $user->banned_at)) {
                    $user->banned_at = null;
                    $user->save(false);
                } else {
                    Yii::$app->session->setFlash('error', 'El usuario está baneado.');
                    return false;
                }
            }
            return Yii::$app->user->login($this->getUsuarios(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]].
     *
     * @return User|null
     */
    public function getUsuarios()
    {
        if ($this->_usuarios === false) {
            $this->_usuarios = Usuarios::findByNombre($this->username);
        }

        return $this->_usuarios;
    }
}

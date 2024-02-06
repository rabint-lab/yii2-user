<?php

namespace rabint\user\behaviors;

use rabint\user\models\UserToken;
use yii\filters\auth\AuthMethod;

/**
 * HttpHeaderAuth is an action filter that supports HTTP authentication through HTTP Headers.
 *
 * You may use HttpHeaderAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'basicAuth' => [
 *             'class' => \yii\filters\auth\HttpHeaderAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * The default implementation of HttpHeaderAuth uses the [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]]
 * method of the `user` application component and passes the value of the `X-Api-Key` header. This implementation is used
 * for authenticating API clients.
 *
 */
class UserHttpHeaderAuth extends AuthMethod
{
    /**
     * @var string the HTTP header name
     */
    public $token_header = 'X-User-Api-Key';
    public $permannet_header = 'X-Access-Api-Key';
    /**
     * @var string a pattern to use to extract the HTTP authentication value
     */
    public $pattern;


    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->permannet_header);
        $tokenHeader = $request->getHeaders()->get($this->token_header);

        if ($authHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
            }

            $identity = $user->loginByAccessToken($authHeader, get_class($this));
            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }
            return $identity;
        } elseif ($tokenHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $tokenHeader, $matches)) {
                    $tokenHeader = $matches[1];
                } else {
                    return null;
                }
            }
            $token = UserToken::find()->byToken($tokenHeader)->notExpired()->one();

            if ($token === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            } else {
                /**
                 * renew token
                 */
                $token->renew();

                $identity = $user->loginByAccessToken($token->user->access_token, get_class($this));
                if ($identity === null) {
                    $this->challenge($response);
                    $this->handleFailure($response);
                }
                return $identity;
            }
        }

        return null;
    }
}

<?php
namespace Platform\Frontend\Models;

class Users extends \Phalcon\Mvc\Model {
    /**
     * Validations and business logic
     */
    public function validation() {
        $validator = new \Phalcon\Validation();

        $validator->add('email', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('password', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('role', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));

        $validator->add('email', new \Phalcon\Validation\Validator\StringLength([
            'min' => 4,
            'max' => 60,
            'messageMaximum' => 'Maximum length is 60 characters',
            'messageMinimum' => 'Minimum length is 4 characters'
        ]));

        $validator->add('password', new \Phalcon\Validation\Validator\StringLength([
            'min' => 12,
            'max' => 60,
            'messageMaximum' => 'Maximum length is 30 characters',
            'messageMinimum' => 'Minimum length is 12 characters'
        ]));

        $validator->add('email', new \Phalcon\Validation\Validator\Email([
            'message' => 'Please fill in your Email'
        ]));

        $validator->add('password_confirm', new \Phalcon\Validation\Validator\Confirmation([
            'message' => 'Passwords do not match',
            'with' => 'password'
        ]));

        $validator->add('email', new \Phalcon\Validation\Validator\Uniqueness([
            'message' => 'Email already used in the system',
            'model' => $this
        ]));

        /**
         * $validator->add('role', new \Phalcon\Validation\Validator\Regex([ //@todo @security @hack
        'pattern' => '/^(client|supplier)$/',
        'message' => 'This field is required'
        ]));
         */
        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize() {
        /*$this->hasOne('id', 'Broadcasters', 'id', NULL);
        $this->hasOne('id', 'Clients', 'id', NULL);
        $this->hasMany('id', 'Credits', 'user_id', NULL);*/
    }

}

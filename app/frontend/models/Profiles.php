<?php
namespace Platform\Frontend\Models;

class Profiles extends \Phalcon\Mvc\Model {
    /**
     * Validations and business logic
     */
    public function validation() {
        $validator = new \Phalcon\Validation();


        $validator->add('first_name', new \Phalcon\Validation\Validator\StringLength([
            'min' => 2,
            'max' => 20,
            'messageMaximum' => 'Maximum length is 20 characters',
            'messageMinimum' => 'Minimum length is 2 characters',
            'allowEmpty'=>true
        ]));

        $validator->add('first_name', new \Phalcon\Validation\Validator\Alpha([
            'message' => 'This field must contain letters only',
            'allowEmpty'=>true
        ]));

        $validator->add('last_name', new \Phalcon\Validation\Validator\StringLength([
            'min' => 2,
            'max' => 20,
            'messageMaximum' => 'Maximum length is 20 characters',
            'messageMinimum' => 'Minimum length is 2 characters',
            'allowEmpty'=>true
        ]));

        $validator->add('last_name', new \Phalcon\Validation\Validator\Alpha([
            'message' => 'This field must contain letters only',
            'allowEmpty'=>true
        ]));

        $validator->add('type', new \Phalcon\Validation\Validator\InclusionIn(array(
            'message' => 'Please select the right type',
            'domain' => array('on','off'),
            'allowEmpty'=>true
        )));

        return $this->validate($validator);

        /*$valid_states = array(
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA',
            'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM',
            'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA',
            'WV', 'WI', 'WY');

        if($this->type=='owner' || $this->type=='partner') {
            $validator->add('lawfirm_name', new \Phalcon\Validation\Validator\StringLength([
                'min' => 2,
                'max' => 25,
                'messageMaximum' => 'Maximum length is 25 characters',
                'messageMinimum' => 'Minimum length is 2 characters'
            ]));
            $validator->add('lawfirm_name', new \Phalcon\Validation\Validator\PresenceOf([
                'message' => 'This field is required'
            ]));
        }
        else {//must be a supplier..
            $validator->add('cur_city', new \Phalcon\Validation\Validator\StringLength([
                'min' => 2,
                'max' => 20,
                'messageMaximum' => 'Maximum length is 20 characters',
                'messageMinimum' => 'Minimum length is 2 characters'
            ]));
            $validator->add('cur_city', new \Phalcon\Validation\Validator\Alpha([
                'message' => 'This field must contain letters only'
            ]));
            $validator->add('cur_city', new \Phalcon\Validation\Validator\PresenceOf([
                'message' => 'This field is required'
            ]));
        }
        $validator->add('string_id', new \Phalcon\Validation\Validator\StringLength([
            'min' => 2,
            'max' => 20,
            'messageMaximum' => 'Maximum length is 20 characters',
            'messageMinimum' => 'Minimum length is 2 characters'
        ]));
        $validator->add('string_id', new \Phalcon\Validation\Validator\Alnum([
            'message' => 'This field must contain letters only'
        ]));
        $validator->add('string_id', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('first_name', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('middle_name', new \Phalcon\Validation\Validator\StringLength([
            'min' => 2,
            'max' => 20,
            'messageMaximum' => 'Maximum length is 20 characters',
            'messageMinimum' => 'Minimum length is 2 characters',
            'allowEmpty'=>true
        ]));
        $validator->add('middle_name', new \Phalcon\Validation\Validator\Alpha([
            'message' => 'This field must contain letters only',
            'allowEmpty'=>true
        ]));
        $validator->add('last_name', new \Phalcon\Validation\Validator\StringLength([
            'min' => 2,
            'max' => 20,
            'messageMaximum' => 'Maximum length is 20 characters',
            'messageMinimum' => 'Minimum length is 2 characters',
            'allowEmpty'=>true
        ]));
        $validator->add('last_name', new \Phalcon\Validation\Validator\Alpha([
            'message' => 'This field must contain letters only',
            'allowEmpty'=>true
        ]));
        $validator->add('last_name', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('cur_state', new \Phalcon\Validation\Validator\InclusionIn(array(
            'message' => 'Not a valid state',
            'domain' => $valid_states,
            'allowEmpty'=>true
        )));
        $validator->add('cur_state', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('adm_state', new \Phalcon\Validation\Validator\InclusionIn(array(
            'message' => 'Not a valid state',
            'domain' => $valid_states,
            'allowEmpty'=>true
        )));
        $validator->add('adm_state', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('adm_month_year', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));
        $validator->add('type', new \Phalcon\Validation\Validator\InclusionIn(array(
            'message' => 'Invalid type',
            'domain' => array('owner','partner','supplier'),
            'allowEmpty'=>true
        )));
        $validator->add('type', new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'This field is required'
        ]));*/


    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        /*$this->hasOne('id', 'Broadcasters', 'id', NULL);
        $this->hasOne('id', 'Clients', 'id', NULL);
        $this->hasMany('id', 'Credits', 'user_id', NULL);*/
    }

}

<?php

namespace EeObjects\Members;

use EeObjects\AbstractItem;
use EeObjects\Exceptions\Members\MemberException;
use ExpressionEngine\Model\Member\Member as MemberModel;
use ExpressionEngine\Service\Model\Model;
use ExpressionEngine\Service\Validation\ValidationAware;
use ExpressionEngine\Service\Validation\Validator;
use ExpressionEngine\Service\Validation\Result AS ValidateResult;

class Member extends AbstractItem
{
    /**
     * @var int
     */
    protected $member_id = 0;

    /**
     * @var int
     */
    protected $role_id = 0;

    /**
     * The Fields object
     * @var Fields
     */
    protected $fields = null;

    /**
     * The Validation Rules we run by default
     * @var string[]
     */
    protected $rules = [
        'email' => 'required|email',
        'username' => 'required',
        'screen_name' => 'required',
        'password' => 'required',
        'role_id' => 'isNaturalNoZero',
        'unique_id' => 'required|alphaNumeric',
        'crypt_key' => 'required|alphaNumeric',
        'ip_address' => 'required|ipAddress',
        'join_date' => 'required',
        'language' => 'required',
        'timezone' => 'required',
    ];

    /**
     * Uses the ChannelEntry object to initialize our own object
     * @param ChannelEntry $entry
     */
    protected function init(Model $item): void
    {
        parent::init($item);
        $this->member_id = $item->member_id;
    }

    /**
     * @return int
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param $role_id
     * @return $this
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * The Channel Entries Fields object
     * @return Fields
     */
    public function getFields(): Fields
    {
        return $this->fields;
    }

    /**
     * Returns the Specific field object
     * @param $field_name
     * @return mixed
     */
    public function getField($field_name)
    {
        $field = $this->getFields()->getField($field_name);
        if ($field instanceof AbstractField) {
            return $field->setMemberId($this->member_id);
        }
    }

    /**
     * @param Fields $fields
     * @return $this
     */
    public function setFields(Fields $fields): Member
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return bool
     * @throws MemberException
     */
    public function save(): bool
    {
        if ($this->member_id) {
            $return = $this->update();
        } else {
            $return = $this->create();
        }

        return $return;
    }

    /**
     * @return bool
     * @throws MemberException
     */
    protected function update(): bool
    {
        if (!$this->member_id) {
            throw new MemberException("Member ID isn't setup!");
        }

        foreach ($this->set_data as $field_name => $value) {
            $field = $this->getField($field_name);
            if ($field instanceof AbstractField) {
                $field_name = $field->getRawColName();
                $this->model->setRawProperty($field_name, $field->prepValueForStorage($value));
            } else {
                if (isset($this->data[$field_name])) {
                    $this->model->setRawProperty($field_name, $value);
                }
            }
        }

        $return = false;
        if ($this->model->save() instanceof MemberModel) {
            foreach ($this->set_data as $field_name => $value) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->save($value);
                }
            }

            $this->renew();
            $return = true;
        }

        return $return;
    }

    protected function create()
    {
        $this->setDefaults();
        $member_data = [];
        foreach ($this->set_data as $field_name => $value) {
            $field = $this->getField($field_name);
            if ($field instanceof AbstractField) {
                $field_name = $field->getRawColName();
                $member_data[$field_name] = $field->prepValueForStorage($value);
            } else {
                $member_data[$field_name] = $value;
            }
        }

        $member = ee('Model')->make('Member');
        $member->set($member_data);
        if ($member->save() instanceof MemberModel) {
            $this->member_id = $member->member_id;
            foreach ($this->set_data as $field_name => $value) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->save($value);
                }
            }

            $this->renew();

            return true;
        }

        throw new MemberException('Could not create Entry!');
    }

    /**
     * @return void
     */
    protected function setDefaults(): void
    {
        if (!$this->get('join_date')) {
            $this->set('join_date', ee()->localize->now);
        }

        if (!$this->get('role_id')) {
            $this->set('role_id', $this->getRoleId());
        }
    }

    /**
     * Resets the Member object for reuse
     * @throws MemberException
     */
    protected function renew(): void
    {
        if (!$this->member_id) {
            throw new MemberException('Cannot renew Member! Member ID is missing!');
        }

        $member = ee('Model')
            ->get('Member', $this->member_id)
            ->first();

        if (!$member instanceof MemberModel) {
            throw new MemberException('Cannot renew Member! Member Missing from database!');
        }

        $this->init($member);
    }

    /**
     * @return void
     */
    public function delete()
    {
        if ($this->model instanceof MemberModel) {
            $member_fields = $this->getFields()->allFields();
            foreach ($member_fields as $field_name => $field_details) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->delete();
                }
            }

            $this->model->delete();
            $this->model = null;
            $this->member_id = 0;
            $this->role_id = 0;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->data;
        if ($this->model instanceof MemberModel) {
            $data = $this->getFields()->translateFieldData($this->model);
        }

        return $data;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (isset($this->set_data[$key])) {
            return $this->set_data[$key]; //means was set() previously
        }

        //pull the data from the Field if we have one
        $field = $this->getField($key);
        if ($field instanceof AbstractField) {
            $field_key = $field->getRawColName();
            if (isset($this->data[$field_key])) {
                $default = $this->data[$field_key];
            }

            $this->set_data[$key] = $field->read($default);

            return $this->set_data[$key];
        }

        //default back to the converted channel data values
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param $value
     * @return AbstractItem
     */
    public function set(string $key, $value): AbstractItem
    {
        $field = $this->getField($key);
        if ($field instanceof AbstractField) {
            $this->set_data[$key] = $value;
        } elseif ($this->getFields()->isMValue($key)) {
            $this->set_data[$key] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        foreach($this->getFields()->allFields() As $field) {
            if ($field['field_required'] == 'y') {
                $this->rules[$field['field_name']] = 'required';
            }
        }

        return $this->rules;
    }
}

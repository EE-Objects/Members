<?php

namespace EeObjects\Members;

use EeObjects\Fields\AbstractFields;
use EeObjects\Str;
use ExpressionEngine\Service\Model\Model;

class Fields extends AbstractFields
{
    /**
     * The shortname for where the parent Field group lives
     * @var bool
     */
    protected $config_domain = 'member';

    /**
     * Contains a key/value store of fields
     * @var array
     */
    protected $member_fields = [];

    /**
     * The Members safe values we bypass
     * @var array
     */
    protected $m_fields = [
        'role_id',
        'username',
        'screen_name',
        'email',
        'signature',
        'avatar_filename',
        'total_entries',
        'total_comments',
        'last_entry_date',
        'password',
        'crypt_key',
        'unique_id',
        'ip_address',
        'join_date',
        'language',
        'timezone',
    ];

    /**
     * @param string $field_name
     * @return null
     */
    public function getField(string $field_name)
    {
        if ($this->fieldExists($field_name)) {
            $field = $this->member_fields[$field_name];

            return $this->getFieldType($field['field_id'], $field['field_type'], $field['field_settings'], $field['field_name']);
        }

        return null;
    }

    /**
     * @param $field_name
     * @return bool
     */
    public function fieldExists($field_name)
    {
        $fields = $this->getMemberFields();

        return isset($fields[$field_name]);
    }

    /**
     * @return array
     */
    protected function getMemberFields(): array
    {
        if (!$this->member_fields) {
            $member_fields = ee('Model')->get('MemberField');
            if ($member_fields) {
                $fieldsarr = [];
                foreach ($member_fields->all() as $field) {
                    $fieldsarr[$field->m_field_name] = [
                        'field_id' => $field->m_field_id,
                        'field_name' => $field->m_field_name,
                        'field_type' => $field->m_field_type,
                        'field_label' => $field->m_field_label,
                        'field_settings' => $field->m_field_settings,
                        'field_fmt' => $field->m_field_fmt,
                    ];
                }

                $this->member_fields = $fieldsarr;
            }
        }

        return $this->member_fields;
    }

    protected function getFieldType(int $field_id, string $field_type, array $field_settings, $field_name = false): ?AbstractField
    {
        $obj_name = Str::dash2ns($field_type);
        $obj = 'EeObjects\Members\Fields\\' . $obj_name;
        if (class_exists($obj)) {
            $class = new $obj();
            $class->setFieldId($field_id)
                ->setFieldName($field_name)
                ->setSettings($field_settings);

            return $class;
        }

        return null;
    }

    /**
     * @param Model $item
     * @return array
     */
    public function translateFieldData(Model $item): array
    {
        $data = $item->toArray();
        foreach ($this->getMemberFields() as $field) {
            $field_type = $this->getFieldType($field['field_id'], $field['field_type'], $field['field_settings'], $field['field_name']);
            $key = 'm_field_id_' . $field['field_id'];
            if ($field_type instanceof AbstractField) {
                $data[$field['field_name']] = $field_type->setMemberId($item->member_id)->read($data[$key]);
            } elseif (isset($data[$key])) {
                $data[$field['field_name']] = $data[$key];
            }
        }

        return $this->cleanUglyData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function cleanUglyData(array $data): array
    {
        //remove ugly keys
        foreach ($data as $key => $value) {
            if (strstr($key, 'm_field_id_') ||
                strstr($key, 'm_field_ft_') ||
                strstr($key, 'm_field_dt_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param int $field_id
     * @return AbstractField|null
     */
    public function getFieldById(int $field_id)
    {
        echo __FILE__ . ':' . __LINE__;
        exit;
        $field = ee('Model')->get('ChannelField', $field_id)->first();
        if ($field instanceof ChannelField) {
            return $this->getFieldType($field->field_id, $field->field_type, $field->field_settings, $field->field_name);
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function isMValue($key)
    {
        return in_array($key, $this->m_fields);
    }

    public function allFields(): array
    {
        return $this->getMemberFields();
    }
}

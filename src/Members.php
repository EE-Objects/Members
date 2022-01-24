<?php

namespace EeObjects;

use EeObjects\Members\Fields;
use ExpressionEngine\Model\Member\Member as MemberModel;

class Members
{
    /**
     * @var Fields
     */
    protected $fields = null;

    /**
     * Returns the Fields object
     * @return Fields
     */
    protected function getFields()
    {
        if (is_null($this->fields)) {
            $this->fields = new Fields();
        }

        return $this->fields;
    }

    /**
     * Returns the EE Member
     * @param false $member_id
     * @return Members\Member|null
     */
    public function getMember($member_id = false): ?Members\Member
    {
        $member = $member = ee('Model')->get('Member', $member_id)
            ->first();

        if ($member instanceof MemberModel) {
            return $this->buildMemberObj($member);
        }

        return null;
    }

    /**
     * @param MemberModel $member
     * @return Members\Member
     */
    protected function buildMemberObj(MemberModel $member)
    {
        $obj = new Members\Member($member);
        $obj->setFields($this->getFields());

        return $obj;
    }

    /**
     * @param $role_id
     * @return Members\Member
     */
    public function getBlankMember($role_id)
    {
        $obj = new Members\Member();
        $obj->setRoleId($role_id);

        return $obj->setFields($this->getFields());
    }
}

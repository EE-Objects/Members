<?php

namespace EeObjects\Services;

use EeObjects\Members;
use ExpressionEngine\Model\Role\Role as RoleModel;

class MembersService
{
    /**
     * The Members object
     * @var Members
     */
    protected $members = null;

    /**
     * Returns the Members object
     * @return Members
     */
    protected function members()
    {
        if (is_null($this->members)) {
            $this->members = new Members();
        }

        return $this->members;
    }

    /**
     * @param $member_id
     * @return Members\Member|null
     */
    public function getMember(int $member_id)
    {
        return $this->members()->getMember($member_id);
    }

    /**
     * @param int $group_id
     * @return mixed
     */
    public function getBlankMember(int $group_id)
    {
        return $this->members()->getBlankMember($group_id);
    }

    /**
     * @param string $short_name
     * @return RoleModel|null
     */
    public function getRoleByShortName(string $short_name): ?RoleModel
    {
        $role = ee('Model')->get('Role')
            ->filter('short_name', $short_name)
            ->first();

        if ($role instanceof RoleModel) {
            return $role;
        }

        return null;
    }
}

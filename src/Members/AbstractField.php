<?php

namespace EeObjects\Members;

use EeObjects\Fields\AbstractField as EeObjectsAbstractField;

abstract class AbstractField extends EeObjectsAbstractField
{
    /**
     * The Member this Field belongs to
     * @var int
     */
    protected $member_id = 0;

    /**
     * @param int $member_id
     * @return $this
     */
    public function setMemberId(int $member_id): AbstractField
    {
        $this->member_id = $member_id;

        return $this;
    }

    /**
     * Returns the channel data label for the field
     * @return string
     */
    public function getRawColName(): string
    {
        return 'm_field_id_' . $this->getId();
    }
}

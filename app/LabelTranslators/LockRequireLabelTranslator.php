<?php

namespace MemoGram\PanelKit\LabelTranslators;

use Rapid\Laplus\Label\LabelTranslator;

class LockRequireLabelTranslator extends LabelTranslator
{

    public function id()
    {
        return '#' . $this->value;
    }

    public function group()
    {
        $groups = config('panelkit::lock.groups', []);

        return array_key_exists($this->value, $groups) ? __($groups[$this->value]) : $this->value;
    }

    public function url()
    {
        return $this->value;
    }

    public function title()
    {
        return $this->value;
    }

    public function isFake()
    {
        return $this->yesNo;
    }

    public function cachePass()
    {
        return $this->value === null ? null : __('panelkit::lock.counter.seconds', ['value' => $this->value]);
    }

    public function acceptDelay()
    {
        return $this->value === null ? null : __('panelkit::lock.counter.seconds', ['value' => $this->value]);
    }

    public function memberLimitUntil()
    {
        return $this->value === null ? null : __('panelkit::lock.counter.members', ['value' => $this->value]);
    }

    public function expireAt()
    {
        return $this->asDateTime;
    }

}

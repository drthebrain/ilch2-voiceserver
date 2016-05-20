<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Boxes;

class Voiceserver extends \Ilch\Box
{
    public function render()
    {
        $this->getView()->set('voiceServer', json_decode($this->getConfig()->get('voice_server'), true));
    }
}

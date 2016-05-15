<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Controllers;

class Ajax extends \Ilch\Controller\Frontend
{
    public function refreshVoiceAction()
    {
        $this->getView()->set('voiceServer', json_decode($this->getConfig()->get('voice_server'), true));
    }
}

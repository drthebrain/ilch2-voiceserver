<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Controllers;

class Index extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $this->getLayout()->getHmenu()->add($this->getTranslator()->trans('menuVoiceServer'), ['action' => 'index']);

        $this->getView()->set('voiceServer', json_decode($this->getConfig()->get('voice_server'), true));
    }
}

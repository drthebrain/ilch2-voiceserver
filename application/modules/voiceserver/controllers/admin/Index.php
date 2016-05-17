<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Controllers\Admin;

class Index extends \Ilch\Controller\Admin
{
    public function init()
    {
        $this->getLayout()->addMenu
        (
            'menuVoiceServer',
            array
            (
                array
                (
                    'name' => 'settings',
                    'active' => true,
                    'icon' => 'fa fa-cogs',
                    'url' => $this->getLayout()->getUrl(array('controller' => 'index', 'action' => 'index'))
                )
            )
        );
    }

    public function indexAction()
    {
        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuVoiceServer'), array('action' => 'index'))
                ->add($this->getTranslator()->trans('settings'), array('action' => 'index'));

        if ($this->getRequest()->isPost()) {
            $voiceserver = $this->getRequest()->getPost('voiceServer');
            if (empty($voiceserver['IP'])) {
                $this->addMessage($this->getTranslator()->trans('missingIP'), 'danger');
            } elseif (empty($voiceserver['QPort'])) {
                $this->addMessage($this->getTranslator()->trans('missingQPort'), 'danger');
            } elseif (empty($voiceserver['CPort'])) {
                $this->addMessage($this->getTranslator()->trans('missingCPort'), 'danger');
            } else {
                $this->getConfig()->set('voice_server', json_encode($this->getRequest()->getPost('voiceServer')));
                $this->addMessage('saveSuccess');
            }
        }

        $this->getView()->set('voiceServer', json_decode($this->getConfig()->get('voice_server'), true));
    }

}

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
            
            switch ($voiceserver['Type']) {
                case 'TS3':
                    if (empty($voiceserver['IP'])) {
                        $message = $this->getTranslator()->trans('missingIP');
                    } elseif (empty($voiceserver['QPort'])) {
                        $message = $this->getTranslator()->trans('missingQPort');
                    } elseif (empty($voiceserver['Port'])) {
                        $message = $this->getTranslator()->trans('missingPort');
                    } 
                    break;
                case 'Mumble':
                    unset($voiceserver['IP']);
                    unset($voiceserver['Port']);                   
                    unset($voiceserver['QPort']);
                    unset($voiceserver['CIcons']);
                    break;
                case 'Ventrilo':
                    if (empty($voiceserver['IP'])) {
                        $message = $this->getTranslator()->trans('missingIP');
                    } elseif (empty($voiceserver['Port'])) {
                        $message = $this->getTranslator()->trans('missingPort');
                    }
                    unset($voiceserver['QPort']);
                    unset($voiceserver['CIcons']);                   
                    break;
                default:
                    $message = 'Error';
                    break;
            }                 
             
            if (isset($message)) {
                $this->addMessage($message, 'danger');
            } else {        
                $this->getConfig()->set('voice_server', json_encode($voiceserver));
                $this->addMessage('saveSuccess'); 
            }
        }

        $this->getView()->set('voiceServer', json_decode($this->getConfig()->get('voice_server'), true));
    }

}

<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Config;

class Config extends \Ilch\Config\Install
{
    public $config = 
        [
        'key' => 'voiceserver',
        'author' => 'Rümmler, Dirk',
        'icon_small' => 'voiceserver.png',
        'languages' => 
            [
            'de_DE' => 
                [
                'name' => 'Voiceserver',
                'description' => 'Hier kann der Voiceserver (TS3|Ventrilo|Mumble) verwaltet werden.',
                ],
            'en_EN' => 
                [
                'name' => 'Voiceserver',
                'description' => 'Here you can manage Voiceserver (TS3|Ventrilo|Mumble) from your Site.',
                ],
            ]
        ];

    public function install()
    {
        $databaseConfig = new \Ilch\Config\Database($this->db());
        $databaseConfig->set('voice_server', '{"Type":"TS3","IP":"1.1.1.1","QPort":"10011","Port":"9987","Refresh":"30"}');
    }

    public function uninstall()
    {
        $this->db()->queryMulti("DELETE FROM `[prefix]_config` WHERE `key` = 'voice_server'");
    }
}

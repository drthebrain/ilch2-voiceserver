<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Voiceserver\Config;

class Config extends \Ilch\Config\Install
{
    public $config = [
        'key' => 'voiceserver',
        'version' => '1.0',
        'author' => 'Rümmler, Dirk',
        'icon_small' => 'voiceserver.png',
        'link' => 'https://github.com/drthebrain/voiceserver',
        'languages' => [
            'de_DE' => [
                'name' => 'Voiceserver',
                'description' => 'Hier kann der Voiceserver (TS3|Ventrilo|Mumble) verwaltet werden.',
            ],
            'en_EN' => [
                'name' => 'Voiceserver',
                'description' => 'Here you can manage your voiceserver (TS3|Ventrilo|Mumble).',
            ],
        ],
        'boxes' => [
            'voiceserver' => [
                'de_DE' => [
                    'name' => 'Voiceserver'
                ],
                'en_EN' => [
                    'name' => 'Voiceserver'
                ]
            ]
        ],
        'ilchCore' => '2.0.0',
        'phpVersion' => '5.6'
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

    public function getUpdate($installedVersion)
    {

    }
}

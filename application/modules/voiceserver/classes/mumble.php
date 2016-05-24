<?php

/**
 * Class Mumble
 * @package    Voiceserver
 * @subpackage mumble.php
 * @author     D.Rümmler
 * @version    2016-05-20
 */

Mumble::$useNewAPI = version_compare(Ice_stringVersion(), '3.4', '>=');
if(Mumble::$useNewAPI) {
    //This is because of a bug in ICE. It's wont work if a method includes those files. This should happen in init ice if the bug is fixed in a later version
    require_once 'Ice.php';
    require_once 'Murmur.php';
}

class Mumble
{
    public static $useNewAPI;
    
    private $id;
    private $_serverDatas;
    private $_channelDatas;
    private $_userDatas;
    private $_userList;
    private $_channelList;
    
    public $imagePath;
    public $hideEmptyChannels;
    public $hideParentChannels;
    
    public function Mumble($host, $port)
    {
        $this->_host = $host;
        $this->_port = $port;
        
        $this->ice  = $this->init_ICE();
        
        $this->id = 1;
        $this->_serverDatas = array();
        $this->_channelDatas = array();
        $this->_userDatas = array();
        $this->_userList = array();
        $this->_channelList = array();    
        $this->imagePath = "/application/modules/voiceserver/static/img/mumble/";
        $this->hideEmptyChannels = false;
        $this->hideParentChannels = false;
    }
    
    /**
     * Create's a ICE object
     * @return ice object
     */
    private function init_ICE() 
    { 
        if(!self::$useNewAPI) {
            global $ICE;
            Ice_loadProfile();
            $base = $ICE->stringToProxy("Meta:tcp -h ".$this->_host." -p ".$this->_port);
            return $base->ice_checkedCast("::Murmur::Meta");
        } else {
            $ICE = Ice_initialize();
            return Murmur_MetaPrxHelper::checkedCast($ICE->stringToProxy("Meta:tcp -h ".$this->_host." -p ".$this->_port));
        }
    }
  
    public function limitToChannels() 
    {
        $this->_channelList = func_get_args();
    }
    
    private function toHTML($string) 
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }
    
    private function sortUsers($a, $b) 
    { 
        return strcasecmp($a->name, $b->name);
    } 
    
    private function sortChannels($a, $b) 
    { 
        return $a->position > $b->position;
    }
    
    private function setShowFlag($channelIds) 
    {
        if (!is_array($channelIds))
            $channelIds = array($channelIds);
        foreach ($channelIds as $cid) {
            if (isset($this->_channelDatas[$cid])) {
                $this->_channelDatas[$cid]->show = true;
                if (!$this->hideParentChannels && $this->_channelDatas[$cid]->parent != 0) {
                    $this->setShowFlag($this->_channelDatas[$cid]->parent);
                }
            }
        }
    }
    
    private function renderImages($images) 
    {
        $content = "";
        foreach ($images as $image)
            if (file_exists(realpath('') .  $this->imagePath . $image)) {
                $content .= '<img src="' . $this->imagePath . $image . '" alt="' . $image . '"/>';
            } 
        return $content;
    }
    
    private function buildLink($channels = false) {
        $link  = 'mumble://' . $this->_serverDatas['host'] . ':' . $this->_serverDatas['port'];
        if ($channels) 
            $link .= '/' . implode('/', $channels);
        $link .= '?version=' . $this->_serverDatas['version'];
        return $link;
    }
    
    /**
     * convert timestamp to user readable time
     * @param string $time
     * @param boolean $ms
     * @return string
     */
    private function time_convert($time, $ms = false) 
    {
        if ($ms) $time = $time / 1000;
        $day = floor($time/86400);
        $hours = floor(($time%86400)/3600);
        $minutes = floor(($time%3600)/60);
        $seconds = floor($time%60);

        if ($day>0) {
            $time = $day."d ".$hours."h ".$minutes."m ".$seconds."s";
        } elseif ($hours>0) {
            $time = $hours."h ".$minutes."m ".$seconds."s";
        } elseif ($minutes>0) {
            $time = $minutes."m ".$seconds."s";
        } else {
            $time = $seconds."s";
        }

        return $time;
    }
    
    private function update() 
    {
        try {
            $server = $this->ice->getServer($this->id);

            $defaultconf = $this->ice->getDefaultConf();
            $serverconf = $server->getAllConf();
            $this->_serverDatas = array_merge($defaultconf, $serverconf);
            
            $this->ice->getVersion($major, $minor, $patch, $text);
            $this->_serverDatas['version'] = $major . '.' . $minor . '.' . $patch;

            $tmpChannels = $server->getChannels();
            usort($tmpChannels, array($this, "sortChannels"));
            $hide = count($this->_channelList) > 0 || $this->hideEmptyChannels;
            foreach ($tmpChannels as $channel) {
                $channel->show = !$hide;
                $this->_channelDatas[$channel->id] = $channel;
            }

            $tmpUsers = $server->getUsers();
            usort($tmpUsers, array($this, "sortUsers"));
            foreach ($tmpUsers as $user) {
                if (!isset($this->_userDatas[$user->channel]))
                    $this->_userDatas[$user->channel] = array();
                $this->_userDatas[$user->channel][] = $user;
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * prepare User Data
     * @param int $channelId
     * @return array
     */
    private function prepareUsers($channelId) 
    {
        $users = array();
        if (isset($this->_userDatas[$channelId])) {
            foreach ($this->_userDatas[$channelId] as $user) {
                $name = $this->toHTML($user->name);
 
                $icon = "talking_off.png";
                if ($user->bytespersec > 0)
                    $icon = "talking_on.png";
                $icon = $this->renderImages(array($icon));

                $flags = array();
                if (!empty($user->comment))
                    $flags[] = "comment.png";
                if ($user->prioritySpeaker)
                    $flags[] = "priority_speaker.png";
                if ($user->recording)
                    $flags[] = "recording.png";
                if ($user->mute)
                    $flags[] = "muted_server.png";
                if ($user->deaf)
                    $flags[] = "deafened_server.png";
                if ($user->selfMute)
                    $flags[] = "muted_self.png";
                if ($user->selfDeaf)
                    $flags[] = "deafened_self.png";
                if ($user->userid > -1)                   
                    $flags[] = "authenticated.png";
                $flags = $this->renderImages($flags);

                $userdata = array (
                    'name'  => $name,
                    'icon'  => $icon,
                    'flags' => $flags
                );
                $userlistdata = array(
                    'name'    => $name,
                    'icon'    => $icon,
                    'channel' => $this->toHTML($this->_channelDatas[$user->channel]->name),
                    'uptime'  => $this->time_convert($user->onlinesecs),
                    'afk'     => $this->time_convert($user->idlesecs),
                );

                $users[] = $userdata;
                $this->_userList[] = $userlistdata;
            }
        }
        return $users;
    }
     
    /**
     * prepare Channel Data
     * @param int $channelId
     * @return array
     */
    private function prepareChannelTree($channelId, $cnames = array()) 
    {
        $tree = array();
        foreach ($this->_channelDatas as $channel) {
            if ($channel->parent == $channelId) {
                if ($channel->show) {
                    $name = $this->toHTML($channel->name);
                    $cnames[] = rawurlencode($channel->name);

                    $topic = $this->toHTML($channel->description);

                    $icons = array();
                    $icons[] = !count($channel->links)?"channel.png":"channel_linked";
                    $icons = $this->renderImages($icons);
                    
                    $tree[$channel->id] = array(
                        'link'  => $this->buildLink($cnames),
                        'name'  => $name,
                        'topic' => $topic,
                        'icon'  => $icons,
                        'flags' => '' 
                    );

                    if ($users = $this->prepareUsers($channel->id))
                        $tree[$channel->id] ['users'] = $users;

                    if ($children = $this->prepareChannelTree($channel->id, $cnames))
                        $tree[$channel->id] ['children'] = $children;
                }
                array_pop($cnames);
            }           
        }
        return $tree;
    }
    
    /**
     * get the Servertree
     * @return array
     */
    public function getChannelTree() 
    { 
        try {
            $this->update(); 

            if ($this->hideEmptyChannels && count($this->_channelList) > 0)
                $this->setShowFlag(array_intersect($this->_channelList, array_keys($this->_userDatas)));
            else if ($this->hideEmptyChannels)
                $this->setShowFlag(array_keys($this->_userDatas));
            else if (count($this->_channelList) > 0)
                $this->setShowFlag($this->_channelList);

            $root = array( 
                'link'  => $this->buildLink(),
                'name'  => $this->toHTML($this->_serverDatas['registername']),
                'icon'  => $this->renderImages(array("mumble.png")),        
            );
            $channels = $this->prepareChannelTree(0);

        } catch (Exception $e) {
            
        }
        return array('root' => $root, 'tree' => $channels);
    }
    
    /**
     * get the full ServerInformations
     * @return array
     */
    public function getFullServerInfo() 
    {
        
        $tree = $this->getChannelTree();

        $content = array (
            'name'       => $this->toHTML($this->_serverDatas['registername']),
            'uptime'     => $this->time_convert($this->ice->getUptime()),
            'channelson' => count($this->_channelDatas) - 1,
            'userson'    => count($this->_userList),
            'server'     => $this->_serverDatas['host'].':'.$this->_serverDatas['port'],
            'version'    => $this->_serverDatas['version'],
            'welcome'    => $this->_serverDatas['welcometext'],
            'userlist'   => $this->_userList,
            'root'       => $tree['root'],
            'tree'       => $tree['tree'],
        );
        return $content;
    }   
}

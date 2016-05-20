<?php

/**
 * Class Ventrilo 
 * @package    Voiceserver
 * @subpackage ventrilo.php
 * @author     D.Rümmler
 * @version    2016-05-20
 */

class Ventrilo
{
    private $_host;
    private $_port;
    
    private $_password;
    private $_infocode;
    private $_serverDatas;
    private $_channelDatas;
    private $_userDatas;
    private $_userList;
    private $_channelList;

    public $executable;
    public $imagePath;
    public $hideEmptyChannels;
    public $hideParentChannels;
    
    public function Ventrilo($host, $port) 
    {
        $this->_host = $host;
        $this->_port = $port;
        
        $this->_password = '';
        $this->_infocode = 2;
        $this->_serverDatas = array();
        $this->_channelDatas = array();
        $this->_userDatas = array();
        $this->_userList = array();
        $this->_channelList = array();
        $this->executable = __DIR__ . '/ventrilo_status';
        $this->imagePath = "/application/modules/voiceserver/static/img/ventrilo/";
        $this->hideEmptyChannels = false;
        $this->hideParentChannels = false;
    }
    
    public function limitToChannels() 
    {
        $this->_channelList = func_get_args();
    }
    
    private function toHTML($string) 
    {
        $string = utf8_encode(rawurldecode($string));
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }   
    
    private function sortUsers($a, $b) 
    { 
        return strcasecmp($a["name"], $b["name"]);
    } 
    
    private function setShowFlag($channelIds) 
    {
        if (!is_array($channelIds))
            $channelIds = array($channelIds);
        foreach ($channelIds as $cid) {
            if (isset($this->_channelDatas[$cid])) {
                $this->_channelDatas[$cid]["show"] = true;
                if (!$this->hideParentChannels && $this->_channelDatas[$cid]["pid"] != 0) {
                    $this->setShowFlag($this->_channelDatas[$cid]["pid"]);
                }
            }
        }
    }
    
    private function renderImages($images) 
    {
        $content = "";
        foreach ($images as $image)
            if (file_exists(__DIR__ . '/../static/img/ventrilo/' . $image)) {
                $content .= '<img src="' . $this->imagePath . $image . '" alt="' . $image . '"/>';
            } 
        return $content;
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
    
    private function parseLine($rawline) {
        $tempDatas = array();
        $itemarray = explode(':', $rawline, 2);        

        if ($itemarray[0] == 'CHANNEL' || $itemarray[0] == 'CLIENT') {
            $items = explode(',', trim($itemarray[1]));

            foreach ($items as $item) {
                $tmp = explode('=', $item);
                $tempDatas[strtolower($tmp[0])] = $tmp[1];
            }
            
            if ($itemarray[0] == 'CHANNEL')  
                $this->_channelList[] = $tempDatas; 
 
            if ($itemarray[0] == 'CLIENT') 
                $this->_userList[] = $tempDatas;    
                    
        } else {
            $this->_serverDatas[strtolower($itemarray[0])] = trim($itemarray[1]);
        }
    }
    
    private function queryServer() 
    {      
        $command = $this->executable;
        $command .= " -c" . $this->_infocode;
        $command .= " -t" . $this->_host;

        if (strlen($this->_port)) {
            $command .= ":" . $this->_port;

            if (strlen($this->_password))
                $command .= ":" . $this->_password;
        }
        
        $command = escapeshellcmd($command);
        
        $pipe = popen($command, "r");
        if ($pipe === false) {
            throw new Exception("PHP Unable to spawn shell.");           
        } 
       
        $count = 0;
        while( !feof( $pipe ) )
        {
            $rawline = fgets( $pipe, 1024 );

            if (strlen($rawline) == 0)
                    continue;
            
            $this->parseLine($rawline);
            $count ++;
        }
        pclose($pipe);
        
        if ($count == 0) {
            throw new Exception("PHP Unable to start external status process.");    
        }
    }
    
    private function update() 
    {
        $response = $this->queryServer();
        
        $tmpChannels = $this->_channelList;
        $this->_channelList = array();
        $hide = count($this->_channelList) > 0 || $this->hideEmptyChannels;
        foreach ($tmpChannels as $channel) {
            $channel["show"] = !$hide;
            $this->_channelDatas[$channel["cid"]] = $channel;
        }

        $tmpUsers = $this->_userList;
        $this->_userList = array();
        usort($tmpUsers, array($this, "sortUsers"));
        foreach ($tmpUsers as $user) {
            if (!isset($this->_userDatas[$user["cid"]]))
                $this->_userDatas[$user["cid"]] = array();
            $this->_userDatas[$user["cid"]][] = $user;
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
                if ($user["phan"] == 0) {
                    $name = $this->toHTML($user["name"]);

                    $icon = "user.png";
                    $icon = $this->renderImages(array($icon));

                    $flags = array();
                    if ($user["admin"]) 
                        $flags[] = "admin.png";
                    $flags = $this->renderImages($flags);

                    $userdata = array (
                        'name'  => $name,
                        'icon'  => $icon,
                        'flags' => $flags
                    );
                    $userlistdata = array(
                        'name'    => $name,
                        'icon'    => $icon,
                        'channel' => $this->toHTML($this->_channelDatas[$user['cid']]["name"]),
                        'uptime'  => $this->time_convert($user['sec'])
                    );

                    $users[] = $userdata;
                    $this->_userList[] = $userlistdata;
                }
            }
        }
        return $users;
    }
    
    /**
     * prepare Channel Data
     * @param int $channelId
     * @return array
     */
    private function prepareChannelTree($channelId) 
    {
        $tree = array();
        foreach ($this->_channelDatas as $channel) {
            if ($channel["pid"] == $channelId) {
                if ($channel["show"]) {
                    $name = $this->toHTML($channel["name"]);
                    $topic = isset($channel["comm"]) ? $this->toHTML($channel["comm"]) : '';

                        $icon = "channel.png";
                        $icon = $this->renderImages(array($icon));

                        $flags = array();
                        if ($channel["prot"] == 1)
                            $flags[] = "protect.png";
                        $flags = $this->renderImages($flags);

                        $tree[$channel["cid"]] = array(
                            'link'  => '', //"javascript:tsstatusconnect('" . $this->_javascriptName . "'," . $channel["cid"] . ")",
                            'name'  => $name,
                            'topic' => $topic,
                            'icon'  => $icon,
                            'flags' => $flags,
                        );

                        if ($users = $this->prepareUsers($channel["cid"]))
                            $tree[$channel["cid"]] ['users'] = $users;

                        if ($children = $this->prepareChannelTree($channel["cid"]))
                            $tree[$channel["cid"]] ['children'] = $children;
                }
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

//            $host = $this->_host;
//            $port = $this->_serverDatas["virtualserver_port"];
//            $this->_javascriptName = $javascriptName = preg_replace("#[^a-z-A-Z0-9]#", "-", $host . "-" . $port);

            $root = array(
                'input' => '', //$javascriptName,
                'value' => '', //$host . ":" . $port,
                'link'  => '', //"javascript:tsstatusconnect('" . $this->_javascriptName . "')",
                'name'  => $this->toHTML($this->_serverDatas['name']),
                'icon'  => $this->renderImages(array("ventrilo.png")),        
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
            'name'       => $this->toHTML($this->_serverDatas['name']),
            'platform'   => $this->_serverDatas['platform'],
            'uptime'     => $this->time_convert($this->_serverDatas['uptime']),
            'channelson' => $this->_serverDatas['channelcount'],
            'userson'    => $this->_serverDatas['clientcount'],
            'server'     => $this->_host.':'.$this->_port,
            'version'    => $this->_serverDatas['version'],
            'welcome'    => $this->_serverDatas['comment'],
            'userlist'   => $this->_userList,
            'root'       => $tree['root'],
            'tree'       => $tree['tree'],
        );
        return $content;
    }
}

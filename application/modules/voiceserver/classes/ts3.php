<?php

/**
 * Class TS3 based on TSStatus: Teamspeak 3 viewer for php5
 * @package    Voiceserver
 * @subpackage ts3.php
 * @author     D.Rümmler
 * @version 2016-05-14
 * 
 * TSStatus: Teamspeak 3 viewer for php5
 * @author Sebastien Gerard <seb@sebastien.me>
 * @see http://tsstatus.sebastien.me/
 * @version 2013-08-31
 */

class TS3
{
    private $_host;
    private $_queryPort;
    private $_serverDatas;
    private $_channelDatas;
    private $_userDatas;
    private $_userList;
    private $_serverGroupFlags;
    private $_channelGroupFlags;
    private $_login;
    private $_password;
    private $_channelList;
    private $_useCommand;
    private $_socket;
    public $imagePath;
    public $timeout;
    public $hideEmptyChannels;
    public $hideParentChannels;
    public $showIcons;

    public function __construct($host, $queryPort)
    {
        $this->_host = $host;
        $this->_queryPort = $queryPort;

        $this->_socket = null;
        $this->_serverDatas = [];
        $this->_channelDatas = [];
        $this->_userDatas = [];
        $this->_userList = [];
        $this->_serverGroupFlags = [];
        $this->_channelGroupFlags = [];
        $this->_login = false;
        $this->_password = false;
        $this->_channelList = [];
        $this->_useCommand = "use port=9987";
        $this->imagePath = "/application/modules/voiceserver/static/img/ts3/";
        $this->timeout = 2;
        $this->hideEmptyChannels = false;
        $this->hideParentChannels = false;
        $this->showIcons = false;
    }

    public function useServerId($serverId) 
    {
        $this->_useCommand = "use sid=$serverId";
    }

    public function useServerPort($serverPort) 
    {
        $this->_useCommand = "use port=$serverPort";
    }
    
    public function setLoginPassword($login, $password) 
    {
        $this->_login = $login;
        $this->_password = $password;
    }
    
    public function clearServerGroupFlags() 
    {
        $this->_serverGroupFlags = [];
    }

    public function setServerGroupFlag($serverGroupId, $image) 
    {
        $this->_serverGroupFlags[$serverGroupId] = $image;
    }

    public function clearChannelGroupFlags() 
    {
        $this->_channelGroupFlags = [];
    }

    public function setChannelGroupFlag($channelGroupId, $image) 
    {
        $this->_channelGroupFlags[$channelGroupId] = $image;
    }

    public function limitToChannels() 
    {
        $this->_channelList = func_get_args();
    }

    private function ts3decode($str, $reverse = false) 
    {
        $find = ['\\\\', "\/", "\s", "\p", "\a", "\b", "\f", "\n", "\r", "\t", "\v"];
        $rplc = [chr(92), chr(47), chr(32), chr(124), chr(7), chr(8), chr(12), chr(10), chr(3), chr(9), chr(11)];

        if (!$reverse)
            return str_replace($find, $rplc, $str);
        return str_replace($rplc, $find, $str);
    }

    private function toHTML($string) 
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }

    private function sortUsers($a, $b) 
    {
        if ($a["client_talk_power"] != $b["client_talk_power"])
            return $a["client_talk_power"] > $b["client_talk_power"] ? -1 : 1;
        return strcasecmp($a["client_nickname"], $b["client_nickname"]);
    }    

    private function parseLine($rawLine) 
    { 
        $datas = [];
        $rawItems = explode("|", $rawLine);
        foreach ($rawItems as $rawItem) {
            $rawDatas = explode(" ", $rawItem);
            $tempDatas = [];
            foreach ($rawDatas as $rawData) {
                $ar = explode("=", $rawData, 2);
                $tempDatas[$ar[0]] = isset($ar[1]) ? $this->ts3decode($ar[1]) : "";
            }
            $datas[] = $tempDatas;
        }
        return $datas;
    }

    private function setShowFlag($channelIds) 
    {
        if (!is_array($channelIds))
            $channelIds = [$channelIds];
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
        foreach ($images as $image) {
            if (file_exists(realpath('') .  $this->imagePath . $image)) {
                $content .= '<img src="' . $this->imagePath . $image . '" alt="' . $image . '"/>';
            } else {
                $content .= $this->renderIcon($image);
            }
        }
        return $content;
    }

    /**
     * download and render icon
     * @param string $id
     * @return string
     * @throws Exception
     */
    private function renderIcon($id) 
    { 
        $content = "";
        if ($id < 0) $id = $id+4294967296; 
        if ($id == "100" || $id == "200" || $id == "300" || $id == "500" || $id == "600") {
            $image = "group_" . $id . ".png";
            $content = '<img src="' . $this->imagePath . $image . '" alt="' . $image . '"/>';
        } else {
            if (!file_exists(realpath('') .  $this->imagePath . 'server/') 
                && !is_dir(realpath('') .  $this->imagePath . 'server/') 
                && !is_writable(realpath('') .  $this->imagePath . 'server/')) {
                    @mkdir(realpath('') .  $this->imagePath . 'server/', 0755, true);
            }
            $image = $id . '.png';
            $pfad = realpath('') .  $this->imagePath . 'server/' . $image;
            if (!file_exists($pfad) && $this->showIcons)  {
                $dl = $this->parseLine($this->sendCommand("ftinitdownload clientftfid=".rand(1,99)." name=\/icon_".$id." cid=0 cpw= seekpos=0"));
                $ft = @fsockopen($this->_host, $dl[0]['port'], $errnum, $errstr, 2);
                if ($ft) {
                    fputs($ft, $dl[0]['ftkey']);
                    $img = '';
                    while(!feof($ft)) {
                        $img .= fgets($ft, 4096);
                    }
                    $file = fopen($pfad,"w");
                    fwrite($file, $img);
                    fclose($file);
                }
            }
            if (file_exists($pfad) && $this->showIcons) {
                $content .= '<img src="' . $this->imagePath . 'server/' . $image . '" alt="' . $image . '"/>';
            }
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
        } elseif ($hours > 0) {
            $time = $hours."h ".$minutes."m ".$seconds."s";
        } elseif ($minutes > 0) {
            $time = $minutes."m ".$seconds."s";
        } else {
            $time = $seconds."s";
        }

        return $time;
    }

    private function sendCommand($cmd) 
    {
        fputs($this->_socket, "$cmd\n");
        $response = "";
        do {
            $response .= fread($this->_socket, 8096);
        } while (strpos($response, 'error id=') === false);
        if (strpos($response, "error id=0") === false) {
            throw new Exception("TS3 Server returned the following error: " . $this->ts3decode(trim($response)));
        }
        return $response;
    }

    private function queryServer() 
    {
        $this->_socket = @fsockopen($this->_host, $this->_queryPort, $errno, $errstr, $this->timeout);
        if ($this->_socket) {
            @socket_set_timeout($this->_socket, $this->timeout);
            $isTs3 = trim(fgets($this->_socket)) == "TS3";

            if (!$isTs3) {
                throw new Exception("Not a Teamspeak 3 server/bad query port");
            }

            if ($this->_login !== false) {
                $this->sendCommand("login client_login_name=" . $this->_login . " client_login_password=" . $this->_password);
            }

            $response = "";
            $response .= $this->sendCommand($this->_useCommand);
            $response .= $this->sendCommand("serverinfo");
            $response .= $this->sendCommand("channellist -topic -flags -voice -limits -icon");
            $response .= $this->sendCommand("clientlist -uid -times -away -voice -groups -info -icon");
            $response .= $this->sendCommand("servergrouplist");
            $response .= $this->sendCommand("channelgrouplist");

            return $response;
        } else {
            throw new Exception("Socket error: $errstr [$errno]");
        }
    }

    private function disconnect() 
    {
        if ($this->_socket) {
            @fputs($this->_socket, "quit\n");
            @fclose($this->_socket);
        }
    }

    private function update() 
    {
        require_once 'cache.class.php';
        $cacheKey = 'ts3';
        $cache = new Cache(['name' => $cacheKey, 'path' => APPLICATION_PATH.'/modules/voiceserver/cache/', 'extension' => '.cache']);

        // Delete expired cache and try to retrieve it after that.
        $cache->eraseExpired();
        $result = $cache->retrieve($cacheKey);

        if (!empty($result)) {
            $response = $result;
        } else {
            $response = $this->queryServer();
        }

        $lines = explode("error id=0 msg=ok\n\r", $response);
        if (count($lines) == 7) {
            $this->_serverDatas = $this->parseLine($lines[1]);
            $this->_serverDatas = $this->_serverDatas[0];

            $tmpChannels = $this->parseLine($lines[2]);
            $hide = count($this->_channelList) > 0 || $this->hideEmptyChannels;
            foreach ($tmpChannels as $channel) {
                $channel["show"] = !$hide;
                $this->_channelDatas[$channel["cid"]] = $channel;
            }

            $tmpUsers = $this->parseLine($lines[3]);
            usort($tmpUsers, [$this, "sortUsers"]);
            foreach ($tmpUsers as $user) {
                if ($user["client_type"] == 0) {
                    if (!isset($this->_userDatas[$user["cid"]]))
                        $this->_userDatas[$user["cid"]] = [];
                    $this->_userDatas[$user["cid"]][] = $user;
                }
            }

            $serverGroups = $this->parseLine($lines[4]); 
            foreach ($serverGroups as $sg) 
                if ($sg["iconid"] > 0) 
                    $this->setServerGroupFlag($sg["sgid"], 'group_' . $sg["iconid"] . '.png');

            $channelGroups = $this->parseLine($lines[5]); 
            foreach ($channelGroups as $cg) 
                if ($cg["iconid"] > 0)
                    $this->setChannelGroupFlag($cg["cgid"], 'group_' . $cg["iconid"] . '.png');

            // Store content in cache.
            $cache->store($cacheKey, $response, 30);
        } else {
            throw new Exception("Invalid server response");
        }
    }   
        
    /**
     * prepare User Data
     * @param int $channelId
     * @return array
     */
    private function prepareUsers($channelId) 
    {
        $users = [];
        if (isset($this->_userDatas[$channelId])) {
            foreach ($this->_userDatas[$channelId] as $user) {
                if ($user["client_type"] == 0) {
                    $name = $this->toHTML($user["client_nickname"]);

                    $icon = "16x16_player_off.png";
                    if ($user["client_away"] == 1)
                        $icon = "16x16_away.png";
                    else if ($user["client_flag_talking"] == 1)
                        $icon = "16x16_player_on.png";
                    else if ($user["client_output_hardware"] == 0)
                        $icon = "16x16_hardware_output_muted.png";
                    else if ($user["client_output_muted"] == 1)
                        $icon = "16x16_output_muted.png";
                    else if ($user["client_input_hardware"] == 0)
                        $icon = "16x16_hardware_input_muted.png";
                    else if ($user["client_input_muted"] == 1)
                        $icon = "16x16_input_muted.png";
                    $icon = $this->renderImages([$icon]);

                    $flags = [];
                    if (isset($this->_channelGroupFlags[$user["client_channel_group_id"]])) {
                        $flags[] = $this->_channelGroupFlags[$user["client_channel_group_id"]];
                    }

                    $serverGroups = explode(",", $user["client_servergroups"]);
                    foreach ($serverGroups as $serverGroup) {
                        if (isset($this->_serverGroupFlags[$serverGroup])) {
                            $flags[] = $this->_serverGroupFlags[$serverGroup];
                        }
                    }
                    $flags = $this->renderImages($flags);

                    $userdata =  [
                        'name'  => $name,
                        'icon'  => $icon,
                        'flags' => $flags
                    ];
                    $userlistdata = [
                        'name'    => $name,
                        'icon'    => $icon,
                        'channel' => $this->toHTML($this->_channelDatas[$user['cid']]["channel_name"]),
                        'uptime'  => $this->time_convert(time() - $user['client_lastconnected']),
                        'afk'     => $this->time_convert($user['client_idle_time'],true),
                    ];
                
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
        $tree = [];
        foreach ($this->_channelDatas as $channel) {
            if ($channel["pid"] == $channelId) {
                if ($channel["show"]) {
                    $name = $this->toHTML($channel["channel_name"]);
                    $topic = $this->toHTML($channel["channel_topic"]);

                    $icon = "16x16_channel_green.png";
                    if ($channel["channel_maxclients"] > -1 && ($channel["total_clients"] >= $channel["channel_maxclients"]))
                        $icon = "16x16_channel_red.png";
                    else if ($channel["channel_maxfamilyclients"] > -1 && ($channel["total_clients_family"] >= $channel["channel_maxfamilyclients"]))
                        $icon = "16x16_channel_red.png";
                    else if ($channel["channel_flag_password"] == 1)
                        $icon = "16x16_channel_yellow.png";
                    $icon = $this->renderImages([$icon]);

                    $flags = [];
                    if ($channel["channel_flag_default"] == 1)
                        $flags[] = '16x16_default.png';
                    if ($channel["channel_needed_talk_power"] > 0)
                        $flags[] = '16x16_moderated.png';
                    if ($channel["channel_flag_password"] == 1)
                        $flags[] = '16x16_register.png';
                    if ($channel["channel_icon_id"] != 0)
                        $flags[] = $channel["channel_icon_id"];
                    $flags = $this->renderImages($flags);

                    $tree[$channel["cid"]] = [
                        'link'  => "ts3server://" . $this->_host . "?port=" . $this->_serverDatas["virtualserver_port"] . "&cid=" . $channel["cid"], 
                        'name'  => $name,
                        'topic' => $topic,
                        'icon'  => $icon,
                        'flags' => $flags,
                    ];

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
     * @return array|string
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

            $root = [
                'link'  => "ts3server://" . $this->_host . "?port=" . $this->_serverDatas["virtualserver_port"],
                'name'  => $this->toHTML($this->_serverDatas['virtualserver_name']),
                'icon'  => $this->renderImages(["ts3.png"]),        
            ];
            $channels = $this->prepareChannelTree(0);

            $this->disconnect();
        } catch (Exception $e) {
            $this->disconnect();
            return 'offline';
        }

        return ['root' => $root, 'tree' => $channels];
    }

    /**
     * get the full ServerInformations
     * @return array
     * @throws Exception
     */
    public function getFullServerInfo() 
    {
        $tree = $this->getChannelTree();

        if (is_array($tree)) {
            $content =  [
                'name'       => $this->toHTML($this->_serverDatas['virtualserver_name']),
                'platform'   => $this->_serverDatas['virtualserver_platform'],
                'uptime'     => $this->time_convert($this->_serverDatas['virtualserver_uptime']),
                'channelson' => $this->_serverDatas['virtualserver_channelsonline'],
                'userson'    => count($this->_userDatas),
                'server'     => $this->_host.':'.$this->_serverDatas["virtualserver_port"],
                'version'    => $this->_serverDatas['virtualserver_version'],
                'image'      => $this->_serverDatas['virtualserver_hostbanner_gfx_url'],
                'welcome'    => $this->_serverDatas['virtualserver_welcomemessage'],
                'userlist'   => $this->_userList,
                'root'       => $tree['root'],
                'tree'       => $tree['tree'],
            ];
        } else {
            $content = 'offline';
        }

        return $content;
    }
}

<legend><?=$this->getTrans('menuVoiceServer') ?></legend>

<?php $voiceServer = $this->get('voiceServer'); ?>

<?php function getVoiceserverView($items) { ?>
    <?php foreach ($items as $item): ?>
        <div class="voiceSrvItem">
            <a href="<?=$item['link'] ?>" title="<?=$item['topic'] ?>" >
                <?=$item['icon'] . $item['name'] ?>
                <?php if (isset($item['flags'])): ?>
                    <div class="voiceSrvFlags"><?=$item['flags'] ?></div>
                <?php endif; ?>
                <?php if (isset($item['users'])): ?>
                    <?php foreach ($item['users'] as $user): ?>
                        <div class="voiceSrvItem">
                        <?=$user['icon'] . $user['name'] ?>
                        <?php if (isset($user['flags'])): ?>
                            <div class="voiceSrvFlags"><?=$user['flags'] ?></div>
                        <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>          
            </a>
            <?php if (isset($item['children'])) {
                getVoiceserverView($item['children']); 
            } ?>
        </div>
    <?php endforeach; ?>
<?php }; ?>

<?php
switch ($voiceServer['Type']) {
    case 'TS3':
        require_once("./application/modules/voiceserver/classes/ts3.php");

        $ts3viewer = new TS3($voiceServer['IP'], $voiceServer['QPort']);
        $ts3viewer->useServerPort($voiceServer['Port']);
        $ts3viewer->hideEmptyChannels = isset($voiceServer['HideEmpty'])?$voiceServer['HideEmpty']:false;
        $ts3viewer->showIcons = isset($voiceServer['CIcons'])?$voiceServer['CIcons']:false;

        $datas = $ts3viewer->getFullServerInfo(); 
        break;
    case 'Mumble':
        require_once("./application/modules/voiceserver/classes/mumble.php");

        $mumbleviewer = new Mumble('127.0.0.1','6502');
        $mumbleviewer->hideEmptyChannels = isset($voiceServer['HideEmpty'])?$voiceServer['HideEmpty']:false;
        
        $datas = $mumbleviewer->getFullServerInfo();
        break;
    case 'Ventrilo':
        require_once("./application/modules/voiceserver/classes/ventrilo.php");

        $ventriloviewer = new Ventrilo($voiceServer['IP'], $voiceServer['Port']);
        $ventriloviewer->hideEmptyChannels = isset($voiceServer['HideEmpty'])?$voiceServer['HideEmpty']:false;
        
        $datas = $ventriloviewer->getFullServerInfo(); 
        break;
    default:
        break;
}
?>

<link href="<?=$this->getModuleUrl('static/css/voiceserver.css') ?>" rel="stylesheet">

<?php if (is_array($datas)): ?>
    <table class="table table-striped table-hover table-responsive">
        <tbody>
            <tr>
                <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableName') ?>:</td>
                <td align="center"><?=$datas['name'] ?></td>
            </tr>
            <?php if (key_exists('platform', $datas)): ?>
                <tr>
                    <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableOS') ?>:</td>
                    <td align="center"><?=$datas['platform'] ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableUptime') ?>:</td>
                <td align="center"><?=$datas['uptime'] ?></td>
            </tr>
            <tr>
                <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableChannels') ?>:</td>
                <td align="center"><?=$datas['channelson'] ?></td>
            </tr>
            <tr>
                <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableUser') ?>:</td>
                <td align="center"><?=$datas['userson'] ?></td>
            </tr>
        </tbody>
    </table>

    <table class="table table-striped table-hover table-responsive hidden-xs">
        <thead>
            <tr>
                <th><?=$this->getTrans('tableUser') ?></th>
                <th><?=$this->getTrans('tableChannel') ?></th>
                <th><?=$this->getTrans('tableLoggedin') ?></th>
                <?php if ($voiceServer['Type']!='Ventrilo'): ?>
                    <th><?=$this->getTrans('tableAfK') ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datas['userlist'] as $user): ?>
                <tr>
                    <td><?=$user['icon'] . ' ' . $user['name'] ?></td>
                    <td><?=$user['channel'] ?></td>
                    <td><?=$user['uptime'] ?></td>
                    <?php if ($voiceServer['Type']!='Ventrilo'): ?>
                        <td><?=$user['afk'] ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<table class="table table-striped table-hover table-responsive">
    <tbody>
        <?php if (is_array($datas)): ?>
        <tr>
            <td>
                <div class="voiceSrv">
                    <div class="voiceSrvItem voiceSrvServer">
                        <a href="<?=$datas['root']['link'] ?>" >
                            <?=$datas['root']['icon'] . $datas['root']['name'] ?>
                        </a>
                        <?php getVoiceserverView($datas['tree']); ?>
                    </div>
                </div>                
            </td>
            <td class="col-sm-6 hidden-xs">
                <table>
                    <tbody>
                        <tr>
                            <td><b>Server:</b></td>
                        </tr>
                        <tr>
                            <td><?=$datas['name'] ?><br><br></td>
                        </tr>
                        <tr>
                            <td><b>Server IP:</b></td>
                        </tr>
                        <tr>
                            <td><?=$datas['server'] ?><br><br></td>
                        </tr>
                        <tr>
                            <td><b>Version:</b></td>
                        </tr>
                        <tr>
                            <td><?=$datas['version'] ?><br><br></td>
                        </tr>
                        <?php if (!empty($datas['image'])): ?>
                            <tr>
                                <td><img src="<?=$datas['image'] ?>" /></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><b>Welcome Message:</b></td>
                        </tr>
                        <tr>
                            <td><?=$this->getHtmlFromBBCode($datas['welcome']) ?><br><br></td>
                        </tr>
                    </tbody>
                </table>                
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

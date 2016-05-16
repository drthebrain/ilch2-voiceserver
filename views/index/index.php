<legend><?=$this->getTrans('menuVoiceServer') ?></legend>

<?php $voiceServer = $this->get('voiceServer'); ?>

<?php function getVoiceserverView($items) { ?>
    <?php foreach ($items as $item): ?>
        <div class="voiceSrvItem">
            <a href="<?=$item['link'] ?>" title="<?=$item['topic'] ?>" >
                <?=$item['icon'] . $item['name'] ?>
                <div class="voiceSrvFlags"><?=$item['flags'] ?></div>
                <?php if (isset($item['users'])): ?>
                    <?php foreach ($item['users'] as $user): ?>
                        <div class="voiceSrvItem">
                        <?=$user['icon'] . $user['name'] ?>
                        <div class="voiceSrvFlags"><?=$user['flags'] ?></div>
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
if ($voiceServer['Type'] == 'TS3') {   
    require_once("./application/modules/voiceserver/classes/ts3viewer.php");

    $ts3viewer = new TS3Viewer($voiceServer['IP'], $voiceServer['QPort']);
    $ts3viewer->useServerPort($voiceServer['CPort']);
    
    $datas = $ts3viewer->getFullServerInfo(); 
}

//if ($voiceServer['Type'] == 'Mumble') {
//       require_once("./application/modules/voiceserver/classes/mumbleviewer.php");
//
//       $mumbleviewer = new MumbleViewer();
//       $test = $mumbleviewer->parse_response(NULL);
//}
?>

<link href="<?=$this->getModuleUrl('static/css/voiceserver.css') ?>" rel="stylesheet">
<script src="<?=$this->getModuleUrl('static/js/tsstatus.js') ?>" type="text/javascript"></script>

<table class="table table-striped table-hover table-responsive">
    <tbody>
        <tr>
            <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableName') ?>:</td>
            <td align="center"><?=$datas['name'] ?></td>
        </tr>
        <tr>
            <td class="col-sm-6 hidden-xs"><?=$this->getTrans('tableOS') ?>:</td>
            <td align="center"><?=$datas['platform'] ?></td>
        </tr>
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
            <th><?=$this->getTrans('tableAfK') ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach ($datas['userlist'] as $user): ?>
            <tr>
                <td><?=$user['icon'] . ' ' . $user['name'] ?></td>
                <td><?=$user['channel'] ?></td>
                <td><?=$user['uptime'] ?></td>
                <td><?=$user['afk'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="table table-striped table-hover table-responsive">
    <tbody>
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
                            <td><?=$voiceServer['IP'].':'.$voiceServer['CPort'] ?><br><br></td>
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
    </tbody>
</table>

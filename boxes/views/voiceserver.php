<?php $voiceServer = $this->get('voiceServer'); ?>

<?php function getVoiceserverBoxView($items) { ?>
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
                getVoiceserverBoxView($item['children']); 
            } ?>
        </div>
    <?php endforeach; ?>
<?php }; ?>

<?php
if ($voiceServer['Type'] == 'TS3') {   
    require_once("./application/modules/voiceserver/classes/ts3viewer.php");

    $ts3viewer = new TS3Viewer($voiceServer['IP'], $voiceServer['QPort']);
    $ts3viewer->useServerPort($voiceServer['CPort']);

    $datas = $ts3viewer->getChannelTree(); 
}

//if ($voiceServer['Type'] == 'Mumble') {
//       require_once("./application/modules/voiceserver/classes/mumbleviewer.php");
//
//       $mumbleviewer = new MumbleViewer();
//       $test = $mumbleviewer->parse_response(NULL);
//}
?>

<link href="<?=$this->getBaseUrl('application/modules/voiceserver/static/css/voiceserver.css') ?>" rel="stylesheet">
<script src="<?=$this->getBaseUrl('application/modules/voiceserver/static/js/tsstatus.js') ?>" type="text/javascript"></script>

<div id="voiceserver">
    <div class="voiceSrv">
        <input type="hidden" id="tsstatus-<?=$datas['root']['input'] ?>-hostport" value="<?=$datas['root']['value'] ?>" />
        <div class="voiceSrvItem voiceSrvServer">
            <a href="' . $datas['root']['link'] . '">
                <?=$datas['root']['icon'] . $datas['root']['name'] ?>
            </a>
            <?php getVoiceserverBoxView($datas['tree']); ?>
        </div>
    </div>
</div>

<?php if ($voiceServer['Refresh'] >= 30): ?>
<script>
    $(document).ready(function() {
        window.setInterval(function(){
            refreshVoice();
            function refreshVoice() {
                $('#voiceserver').load('<?=$this->getUrl(array('module' => 'voiceserver', 'controller' => 'ajax','action' => 'refreshVoice')); ?>');
            };
        }, <?=$voiceServer['Refresh']*1000 ?> );
    });
</script>
<?php endif; ?>

<?php $voiceServer = $this->get('voiceServer'); ?>

<?php function getVoiceserverBoxView($items) { ?>
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
                getVoiceserverBoxView($item['children']); 
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
        $ts3viewer->showIcons = $voiceServer['CIcons'];

        $datas = $ts3viewer->getFullServerInfo(); 
        break;
//    case 'Mumble':
//        require_once("./application/modules/voiceserver/classes/mumbleviewer.php");
//
//        $mumbleviewer = new MumbleViewer();
//        $test = $mumbleviewer->parse_response(NULL);
//        break;
    case 'Ventrilo':
        require_once("./application/modules/voiceserver/classes/ventrilo.php");

        $ventriloviewer = new Ventrilo($voiceServer['IP'], $voiceServer['QPort']);
        
        $datas = $ventriloviewer->getFullServerInfo(); 
        break;
    default:
        break;
}
?>

<link href="<?=$this->getBaseUrl('application/modules/voiceserver/static/css/voiceserver.css') ?>" rel="stylesheet">
<script src="<?=$this->getBaseUrl('application/modules/voiceserver/static/js/tsstatus.js') ?>" type="text/javascript"></script>

<div id="voiceserver">
    <div class="voiceSrv">
        <input type="hidden" id="tsstatus-<?=$datas['root']['input'] ?>-hostport" value="<?=$datas['root']['value'] ?>" />
        <div class="voiceSrvItem voiceSrvServer">
            <a href="<?=$datas['root']['link'] ?>">
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

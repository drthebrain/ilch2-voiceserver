<?php
$voiceServer = $this->get('voiceServer');

if (!function_exists('getView')) {
    function getView($items) {

        foreach ($items as $item) {

            echo '<div class="voiceSrvItem">';
            echo '<a href="' . $item['link'] . '" title="' . $item['topic'] . '">';
            echo $item['icon'] . $item['name'];
            echo '<div class="voiceSrvFlags">' . $item['flags'] . '</div>';
            if (isset($item['users'])) {
                foreach ($item['users'] as $user) {
                    echo '<div class="voiceSrvItem">';
                    echo $user['icon'] . $user['name'];
                    echo '<div class="voiceSrvFlags">' . $user['flags'] . '</div>';
                    echo '</div>';                               
                }
            }          
            echo '</a>';
            if (isset($item['children'])) {
                getView($item['children']); 
            }
            echo '</div>';

        }
    }
}

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
        <div class="voiceSrvItem voiceSrvServer">
            <?php
                echo '<a href="' . $datas['root']['link'] . '">';
                echo $datas['root']['icon'] . $datas['root']['name'];
                echo '</a>';
                getView($datas['tree']); 
            ?>
        </div>
    </div>
</div>
    
<script>
    $(document).ready(function() {
        window.setInterval(function(){
            refreshVoice();
            function refreshVoice() {
                $('#voiceserver').load('<?=$this->getUrl('voiceserver/ajax/refreshVoice'); ?>');
            };
        }, <?=$voiceServer['Refresh']*1000 ?> );
    });
</script>

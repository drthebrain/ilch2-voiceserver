<legend><?=$this->getTrans('settings') ?></legend>

<form class="form-horizontal" method="POST" action="">
    <?=$this->getTokenField() ?>
    <? $voiceServer = $this->get('voiceServer'); ?>

    <div class="form-group">
        <label for="voiceServer[Type]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerType') ?>:
        </label>
        <div class="col-lg-6">
            <select class="form-control" name="voiceServer[Type]">
                <option <?php if ($voiceServer['Type'] == 'TS3') { echo 'selected="selected"'; } ?> value="TS3">Teamspeak 3</option>
                <option <?php if ($voiceServer['Type'] == 'Mumble') { echo 'selected="selected"'; } ?> value="Mumble">Mumble</option>
                <option <?php if ($voiceServer['Type'] == 'Ventrilo') { echo 'selected="selected"'; } ?> value="Ventrilo">Ventrilo</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[IP]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerIP') ?>:
        </label>
        <div class="col-lg-6">
            <input class="form-control"
                   name="voiceServer[IP]"
                   type="text"                  
                   pattern="((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}"
                   value="<?=isset($voiceServer['IP']) ? $this->escape($voiceServer['IP']) : '' ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[Port]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerPort') ?>:
        </label>
        <div class="col-lg-6">
            <input class="form-control"
                   name="voiceServer[Port]"
                   type="number"
                   min="0" max="65535"
                   value="<?=isset($voiceServer['Port']) ? $this->escape($voiceServer['Port']) : '' ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[QPort]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerQPort') ?>:
        </label>
        <div class="col-lg-6">
            <input class="form-control"
                   name="voiceServer[QPort]"
                   type="number"
                   min="0" max="65535"
                   value="<?=isset($voiceServer['QPort']) ? $this->escape($voiceServer['QPort']) : '' ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[CIcons]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerCIcons') ?>:
        </label>
        <div class="col-lg-2">
            <div class="flipswitch">  
                <input type="radio" class="flipswitch-input" name="voiceServer[CIcons]" value="1" id="custom-icons-yes" <?php if (isset($voiceServer['CIcons']) && $voiceServer['CIcons'] == '1') { echo 'checked="checked"'; } ?> />  
                <label for="custom-icons-yes" class="flipswitch-label flipswitch-label-on"><?=$this->getTrans('yes') ?></label>  
                <input type="radio" class="flipswitch-input" name="voiceServer[CIcons]" value="0" id="custom-icons-no" <?php if ((isset($voiceServer['CIcons']) && $voiceServer['CIcons'] == '0') || !isset($voiceServer['CIcons'])) { echo 'checked="checked"'; } ?> />  
                <label for="custom-icons-no" class="flipswitch-label flipswitch-label-off"><?=$this->getTrans('no') ?></label>  
                <span class="flipswitch-selection"></span>  
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[HideEmpty]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerHideEmpty') ?>:
        </label>
        <div class="col-lg-2">
            <div class="flipswitch">  
                <input type="radio" class="flipswitch-input" name="voiceServer[HideEmpty]" value="1" id="hide-empty-yes" <?php if (isset($voiceServer['HideEmpty']) && $voiceServer['HideEmpty'] == '1') { echo 'checked="checked"'; } ?> />  
                <label for="hide-empty-yes" class="flipswitch-label flipswitch-label-on"><?=$this->getTrans('yes') ?></label>  
                <input type="radio" class="flipswitch-input" name="voiceServer[HideEmpty]" value="0" id="hide-empty-no" <?php if ((isset($voiceServer['HideEmpty']) && $voiceServer['HideEmpty'] == '0') || !isset($voiceServer['HideEmpty'])) { echo 'checked="checked"'; } ?> />  
                <label for="hide-empty-no" class="flipswitch-label flipswitch-label-off"><?=$this->getTrans('no') ?></label>  
                <span class="flipswitch-selection"></span>  
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[Refresh]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerRefresh') ?>:
        </label>
        <div class="col-lg-6">
            <input class="form-control"
                   name="voiceServer[Refresh]"
                   type="number"
                   min="0" step="30"
                   value="<?=isset($voiceServer['Refresh']) ? $this->escape($voiceServer['Refresh']) : '30' ?>" />
        </div>
    </div>
    
    <?=$this->getSaveBar() ?>
</form>

<script>
    function switchType() {
        switch($('select[name="voiceServer[Type]"]').val()) {
            case 'Mumble':
                $('[name="voiceServer[IP]"], [name="voiceServer[Port]"], [name="voiceServer[QPort]"], [name="voiceServer[CIcons]"]').closest('.form-group').hide();
                break;
            case 'Ventrilo':
                $('[name="voiceServer[IP]"], [name="voiceServer[Port]"]').closest('.form-group').show();
                $('[name="voiceServer[QPort]"], [name="voiceServer[CIcons]"]').closest('.form-group').hide();
                break;
            default:
                $('[name="voiceServer[IP]"], [name="voiceServer[Port]"], [name="voiceServer[QPort]"], [name="voiceServer[CIcons]"]').closest('.form-group').show();           
        }
    }
    switchType();
    $('select[name="voiceServer[Type]"]').change(switchType);
</script>
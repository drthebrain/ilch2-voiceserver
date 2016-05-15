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
                <!--option <?php if ($voiceServer['Type'] == 'Mumble') { echo 'selected="selected"'; } ?> value="Mumble">Mumble</option-->
                <!--option <?php if ($voiceServer['Type'] == 'Ventrilo') { echo 'selected="selected"'; } ?> value="Ventrilo">Ventrilo</option-->
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
                   value="<?=$this->escape($voiceServer['IP']) ?>" />
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
                   value="<?=$this->escape($voiceServer['QPort']) ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="voiceServer[CPort]" class="col-lg-2 control-label">
            <?=$this->getTrans('voiceServerCPort') ?>:
        </label>
        <div class="col-lg-6">
            <input class="form-control"
                   name="voiceServer[CPort]"
                   type="number"
                   min="0" max="65535"
                   value="<?=$this->escape($voiceServer['CPort']) ?>" />
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
                   value="<?=$this->escape($voiceServer['Refresh']) ?>" />
        </div>
    </div>
    
    <?=$this->getSaveBar() ?>
</form>
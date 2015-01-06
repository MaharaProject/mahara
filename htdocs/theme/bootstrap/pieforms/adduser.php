<?php
echo $form_tag;
?>

<div id="adduser-t">
    <div class="step step1" id="step1">
        <h3><?php echo get_string('usercreationmethod', 'admin'); ?></h3>
        <p class="step-descript"><?php echo get_string('howdoyouwanttocreatethisuser', 'admin'); ?></p>
        <div class="choice"><input type="radio" name="createmethod" class="ic"<?php if (!isset($_POST['createmethod']) || $_POST['createmethod'] == 'scratch') { ?> checked="checked"<?php } ?> id="createfromscratch" value="scratch"> <label for="createfromscratch"><?php echo get_string('createnewuserfromscratch', 'admin'); ?></label></div>
        <table>
<?php foreach (array('firstname', 'lastname', 'email') as $field) { ?>
            <tr>
                <th><?php echo $elements[$field]['labelhtml']; ?></th>
                <td><?php echo $elements[$field]['html']; ?></td>
            </tr>
<?php if ($elements[$field]['error']) { ?>
            <tr>
                <td class="errmsg" colspan="2"><?php echo $elements[$field]['error']; ?></td>
            </tr>
<?php } ?>
<?php } ?>
        </table>
        <div id="or"><?php echo get_string('Or...', 'admin'); ?></div>
        <div class="choice"><input type="radio" name="createmethod" class="ic"<?php if (isset($_POST['createmethod']) && $_POST['createmethod'] == 'leap2a') { ?> checked="checked"<?php } ?> id="uploadleap" value="leap2a"> <label for="uploadleap"><?php echo get_string('uploadleap2afile', 'admin'); ?></label> <?php echo get_help_icon('core', 'admin', 'adduser', 'leap2afile'); ?></div>
        <?php echo $elements['leap2afile']['html']; ?>
<?php if ($elements['leap2afile']['error']) { ?>
        <div class="errmsg"><?php echo $elements['leap2afile']['error']; ?></div>
<?php } ?>
    </div>
    <div class="filler">&raquo;</div>
    <div class="step step2">
        <h3><?php echo get_string('basicdetails', 'admin'); ?></h3>
        <p class="step-descript"><?php echo get_string('basicinformationforthisuser', 'admin'); ?></p>
        <table>
<?php foreach(array('username', 'password', 'staff', 'admin', 'authinstance', 'quota', 'institutionadmin') as $field) { ?>
            <tr>
                <th><?php echo $elements[$field]['labelhtml']; ?></th>
                <td><?php echo $elements[$field]['html']; ?></td>
            </tr>
<?php if (isset($elements[$field]['error'])) { ?>
            <tr>
                <td class="errmsg" colspan="2"><?php echo $elements[$field]['error']; ?></td>
            </tr>
<?php } ?>
<?php } ?>
        </table>
    </div>
    <div class="filler">&raquo;</div>
    <div class="step step3">
        <h3><?php echo get_string('create', 'admin'); ?></h3>
        <p class="step-descript"><?php echo get_string('clickthebuttontocreatetheuser', 'admin'); ?></p>
        <div class="center"><?php echo $elements['submit']['html']; ?></div>
        <div id="step3info"><?php echo get_string('userwillreceiveemailandhastochangepassword', 'admin'); ?></div>
    </div>
</div>
<div id="adduseroptions">
<?php

// Render account preferences with a renderer (inside this template :D)
$accountprefs = (object) expected_account_preferences();
$accountprefs = array_keys(general_account_prefs_form_elements($accountprefs));
$fieldset_elements = array();
foreach ($accountprefs as $p) {
    $fieldset_elements[] = $elements[$p];
}

$accountoptions_fieldset = array(
    'type' => 'fieldset',
    'legend' => get_string('accountoptionsdesc', 'account'),
    'collapsible' => true,
    'collapsed' => true,
    'elements' => $fieldset_elements,
);

$this->include_plugin('renderer', $this->data['renderer']);
$this->include_plugin('element', 'fieldset');
$this->build_element_html($accountoptions_fieldset);

echo pieform_render_element($this, $accountoptions_fieldset);

echo $hidden_elements;
echo '</form>';
?>
</div>

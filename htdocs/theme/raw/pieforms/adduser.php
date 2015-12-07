<?php
echo $form_tag;
?>

    <div class="panel-items panel-items-no-margin">
        <div class="step step1 panel panel-default first" id="step1">
            <h3 class="panel-heading"><?php echo get_string('usercreationmethod', 'admin'); ?></h3>
            <div class="panel-body">
                <div class="choice">
                    <input type="radio" name="createmethod" class="ic"<?php if (!isset($_POST['createmethod']) || $_POST['createmethod'] == 'scratch') { ?> checked="checked"<?php } ?> id="createfromscratch" value="scratch">
                    <label for="createfromscratch"><?php echo get_string('createnewuserfromscratch', 'admin'); ?></label>
                </div>

                <?php foreach (array('firstname', 'lastname', 'email') as $field) { ?>
                <div class="form-group">
                    <label><?php echo $elements[$field]['labelhtml']; ?></label>
                    <?php echo $elements[$field]['html']; ?>

                    <?php if ($elements[$field]['error']) { ?>
                        <p class="text-danger"><?php echo $elements[$field]['error']; ?></p>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <div class="option-alt panel-body">
                <span class="option-alt-divider" id="or">
                    <?php echo get_string('Or', 'admin'); ?>
                </span>

                <div class="choice">
                    <input type="radio" name="createmethod" class="ic"<?php if (isset($_POST['createmethod']) && $_POST['createmethod'] == 'leap2a') { ?> checked="checked"<?php } ?> id="uploadleap" value="leap2a"> <label for="uploadleap"><?php echo get_string('uploadleap2afile', 'admin'); ?></label> <?php echo get_help_icon('core', 'admin', 'adduser', 'leap2afile'); ?>
                </div>
                <?php echo $elements['leap2afile']['html']; ?>
                <?php if ($elements['leap2afile']['error']) { ?>
                    <div class="errmsg"><?php echo $elements['leap2afile']['error']; ?></div>
                <?php } ?>
            </div>
        </div>


        <div class="step step2 panel panel-default">
            <h3 class="panel-heading"><?php echo get_string('basicdetails', 'admin'); ?></h3>
            <div class="panel-body">
                <?php foreach (array('username', 'password', 'staff', 'admin', 'authinstance', 'quota', 'institutionadmin') as $field) { ?>
                    <?php if (isset($elements[$field]['type'])) { ?>
                        <div class="form-group <?php echo $elements[$field]['type']; ?>">
                            <?php echo $elements[$field]['labelhtml']; ?>

                            <?php echo $elements[$field]['html']; ?>
                            <?php if (isset($elements[$field]['error'])) { ?>
                                 <p class="text-danger"><?php echo $elements[$field]['error']; ?></p>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <div class="step step3 panel panel-default">
            <h3 class="panel-heading"><?php echo get_string('create', 'admin'); ?></h3>
            <div class="panel-body">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-link" data-toggle="modal-docked" data-target="#general-account-options">
                    <span class="icon icon-cog left text-default" role="presentation" aria-hidden="true"></span>
                    <?php echo get_string('accountoptionsdesc', 'account'); ?>
                </button>
                <div class="form-group">
                    <?php echo $elements['submit']['html']; ?>
                </div>
                <div class="metadata form-group html">
                    <?php echo get_string('userwillreceiveemailandhastochangepassword', 'admin'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-docked modal-docked-right modal-shown closed" id="general-account-options" tabindex="-1" role="dialog" aria-labelledby="#general-account-options-label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                  <div class="modal-header">
                      <button class="deletebutton close" name="action_removeblockinstance_id_80" data-dismiss="modal-docked" aria-label="Close">
                          <span class="times">&times;</span>
                          <span class="sr-only">Close</span>
                      </button>
                    <h4 class="modal-title blockinstance-header  text-inline general-account-options-title" id="general-account-options-label"><?php echo get_string('accountoptionsdesc', 'account'); ?></h4>
                  </div>

                   <div class="modal-body">
                    <?php

                    // Render account preferences with a renderer (inside this template :D)
                    $accountprefs = (object) expected_account_preferences();
                    $accountprefs = array_keys(general_account_prefs_form_elements($accountprefs));
                    $fieldset_elements = array();
                    foreach ($accountprefs as $p) {
                    $fieldset_elements[] = $elements[$p];
                    }

                    $accountoptions_fieldset = array(
                        'name' => 'generalaccountoptions',
                        'type' => 'fieldset',
                        'elements' => $fieldset_elements,
                    );

                    $this->include_plugin('renderer', $this->data['renderer']);
                    $this->include_plugin('element', 'fieldset');
                    $this->build_element_html($accountoptions_fieldset);

                    echo pieform_render_element($this, $accountoptions_fieldset);

                    echo $hidden_elements;

                    ?>
                    </div>
            </div>
        </div>
    </div>
</form>

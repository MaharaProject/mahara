<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Penny Leach <penny@catalyst.net.nz> 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'myresume');
define('SUBMENUITEM', 'myresume');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require('artefact.php');

// load up all the artefacts this user already has....
$coverletter = null;
$personalinformation = null;
$contactinformation = null;
$interest = null;
try {
    $coverletter = artefact_instance_from_type('coverletter');
}
catch (Exception $e) { }
try {
    $personalinformation = artefact_instance_from_type('personalinformation');
}
catch (Exception $e) { }
try {
    $contactinformation = artefact_instance_from_type('contactinformation');
}
catch (Exception $e) { 
    $contactinformation = ArtefactTypeContactinformation::setup_new($USER->get('id'));
}
try {
    $interest = artefact_instance_from_type('interest');
}
catch (Exception $e) { }

$form = array(
    'name'        => 'resumemainform',
    'jsform'      => true,
    'plugintype'  => 'artefact',
    'pluginname'  => 'account',
    'plugintype'  => 'artefact',
    'pluginname'  => 'resume',
    'jsform'      => true,
    'method'      => 'post',
    'elements'    => array(
        'coverletterfs' => array(
            'type' => 'fieldset',
            'legend' => get_string('coverletter', 'artefact.resume'),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => array(
                'coverletter' => array(
                    'type'  => 'wysiwyg',
                    'cols'  => 70,
                    'rows'  => 10,
                    'defaultvalue' => ((!empty($coverletter)) ? $coverletter->get('title') : null),
                ),
            )
        ),
        'interestsfs' => array(
            'type' => 'fieldset',
            'legend' => get_string('interest', 'artefact.resume'),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => array(
                'interest' => array(
                    'type' => 'wysiwyg',
                    'defaultvalue' => ((!empty($interest)) ? $interest->get('title') : null),
                    'cols'  => 70,
                    'rows'  => 10,
                ),
            ),
        ),
        'contactinformationfs' => array(
            'type' => 'fieldset',
            'legend' => get_string('contactinformation', 'artefact.resume'),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => array(
                'contactinformation' => array(
                    'type' => 'html',
                    'value' => $contactinformation->get_html(), 
                ),
            ),
        ),
        'personalinformation' => array(
            'type'        => 'fieldset',
            'legend'      => get_string('personalinformation', 'artefact.resume'),
            'collapsible' => true,
            'collapsed'   => true,
            'elements'    => array(
               'dateofbirth' => array(
                    'type'       => 'calendar',
                    'caloptions' => array(
                        'showsTime'      => false,
                        'ifFormat'       => '%Y/%m/%d'
                    ),
                    'defaultvalue' => ((!empty($personalinformation)) 
                        ? strtotime($personalinformation->get_composite('dateofbirth')) : null),
                    'title' => get_string('dateofbirth', 'artefact.resume'),
                ),
                'placeofbirth' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation)) 
                        ? $personalinformation->get_composite('placeofbirth') : null),
                    'title' => get_string('placeofbirth', 'artefact.resume'),
                ),  
                'citizenship' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('citizenship') : null),
                    'title' => get_string('citizenship', 'artefact.resume'),
                ),
                'visastatus' => array(
                    'type' => 'text', 
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('visastatus') : null),
                    'title' => get_string('visastatus', 'artefact.resume'),
                ),
                'gender' => array(
                    'type' => 'radio', 
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('gender') : null),
                    'options' => array(
                        'female' => get_string('female', 'artefact.resume'),
                        'male'   => get_string('male', 'artefact.resume'),
                    ),
                    'title' => get_string('gender', 'artefact.resume'),
                ),
                'maritalstatus' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('maritalstatus') :  null),
                    'title' => get_string('maritalstatus', 'artefact.resume'),
                ),
            ),
        ),
        'save' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    )
);

$cancelstr = get_string('cancel');
$addstr = get_string('add');
$editstr = get_string('edit');
$delstr = get_string('delete');
$confirmdelstr = get_string('compositedeleteconfirm', 'artefact.resume');
$confirmeditprofilestr = get_string('confirmeditprofile', 'artefact.resume');

$wwwroot = get_config('wwwroot');

$mainform = pieform($form);
$smarty = smarty(array('tablerenderer'));

$smarty->assign('mainform', $mainform);
$inlinejs = <<<EOF
var tableRenderers = {};

function toggleCompositeForm(type) {
    var elemName = ''; 
    elemName = type + 'form';
    if (hasElementClass(elemName, 'hiddenStructure')) {
        removeElementClass(elemName, 'hiddenStructure');
        $('add' + type + 'button').innerHTML = '{$cancelstr}';
    }
    else {
        $('add' + type + 'button').innerHTML = '{$addstr}';
        addElementClass(elemName, 'hiddenStructure'); 
    }
}

function compositeSaveCallback(name, data) {
    key = name.substr(3);
    tableRenderers[key].doupdate(); 
    toggleCompositeForm(key);
    $('add' + key).reset();
}

function deleteComposite(type, id, artefact) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'artefact': artefact},
            'GET', 
            function(data) {
                tableRenderers[type].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}

function editprofilebutton() {
    if (confirm('{$confirmeditprofilestr}')) {
        document.location='{$wwwroot}artefact/internal/';
    }
    return false;
}

EOF;
$inlinejs .= ArtefactTypeResumeComposite::get_showhide_composite_js();

$compositeforms = array();
foreach (ArtefactTypeResumeComposite::get_composite_artefact_types() as $compositetype) {
    $inlinejs .= <<<EOF
tableRenderers.{$compositetype} = new TableRenderer(
    '{$compositetype}list',
    'composite.json.php',
    [
EOF;
    $inlinejs .= call_static_method(generate_artefact_class_name($compositetype), 'get_tablerenderer_js');
    $inlinejs .= <<<EOF

        function (r) {
            return TD(null, A({'href': 'editcomposite.php?id=' + r.id + '&artefact=' + r.artefact}, '{$editstr}'));
        },
        function (r, d) {
           var link = A({'href': ''}, '{$delstr}');
            connect(link, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id, r.artefact);
            });
            return TD(null, link);
        }
    ]
);

tableRenderers.{$compositetype}.type = '{$compositetype}';
tableRenderers.{$compositetype}.statevars.push('type');
tableRenderers.{$compositetype}.emptycontent = '';
tableRenderers.{$compositetype}.updateOnLoad();

EOF;
    $elements = call_static_method(generate_artefact_class_name($compositetype), 'get_addform_elements');
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('save'),
    );
    $elements['compositetype'] = array(
        'type' => 'hidden',
        'value' => $compositetype,
    );
    $cform = array(
        'name' => 'add' . $compositetype,
        'plugintype' => 'artefact',
        'pluginname' => 'resume',
        'elements' => $elements, 
        'jsform' => true,
        'successcallback' => 'compositeform_submit',
        'jssuccesscallback' => 'compositeSaveCallback',
    );
    $compositeforms[$compositetype] = pieform($cform);
} // end composite loop

$smarty->assign('compositeforms', $compositeforms);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->display('artefact:resume:index.tpl');

function resumemainform_submit(Pieform $form, $values) {
    global $coverletter, $personalinformation, $interest, $USER;

    $userid = $USER->get('id');
    $errors = array();

    try {
        if (empty($coverletter) && !empty($values['coverletter'])) {
            $coverletter = new ArtefactTypeCoverletter(0, array( 
                'owner' => $userid, 
                'title' => $values['coverletter']
            ));
            $coverletter->commit();
        }
        else if (!empty($coverletter)) {
            $coverletter->set('description', $values['coverletter']);
            $coverletter->commit();
        }
    }
    catch (Exception $e) {
        $errors['coverletter'] = true;
    }
        
    try {
        if (empty($personalinformation)) {
            $personalinformation = new ArtefactTypePersonalinformation(0, array(
                'owner' => $userid,
                'title' => get_string('personalinformation', 'artefact.resume'),
            ));
        }
        foreach (array_keys(ArtefactTypePersonalInformation::get_composite_fields()) as $field) {
            $personalinformation->set_composite($field, $values[$field]);
        }
        $personalinformation->commit();
    }
    catch (Exception $e) {
        $errors['personalinformation'] = true;
    }

    try {
        if (empty($interest) && !empty($values['interest'])) {
            $interest = new ArtefactTypeInterest(0, array( 
                'owner' => $userid, 
                'title' => $values['interest']
            ));
            $interest->commit();
        }
        else if (!empty($interest)) {
            $interest->set('description', $values['interest']);
            $interest->commit();
        }
    }
    catch (Exception $e) {
        $errors['interest'] = true;
    }   

    if (empty($errors)) {
        $form->json_reply(PIEFORM_OK, get_string('resumesaved','artefact.resume'));
    }   
    else {
        $message = '';
        foreach (array_keys($errors) as $key) {
            $message .= get_string('resumesavefailed', 'artefact.resume')."\n";
        }
        $form->json_reply(PIEFORM_ERR, $message);
    }
}

?>

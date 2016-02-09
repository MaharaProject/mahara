<?php
defined('INTERNAL') || die();

function xmldb_blocktype_openbadgedisplayer_upgrade($oldversion = 0) {

    if ($oldversion < 2015062301) {
        $blocks = get_records_array('block_instance', 'blocktype', 'openbadgedisplayer');

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $configdata = unserialize($block->configdata);

                if (isset($configdata['badgegroup'])) {
                    // Append source to legacy values
                    if (is_string($configdata['badgegroup'])) {
                        $configdata['badgegroup'] = 'backpack:' . $configdata['badgegroup'];
                    }

                    else if (is_array($configdata['badgegroup'])) {
                        foreach ($configdata['badgegroup'] as &$group) {
                            $group = str_replace('https://openbadgepassport.com/', 'passport', $group);
                            $group = str_replace('https://backpack.openbadges.org/', 'backpack', $group);
                        }
                    }

                    $block->configdata = serialize($configdata);

                    update_record('block_instance', $block, 'id');
                }
            }
        }
    }

    return true;
}
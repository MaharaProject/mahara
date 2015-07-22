# sql script for deleting the plugin from the database, mainly intended
# for developing/testing scenarios when you want to remove/reinstall the
# script without reinstalling the complete database

# delete the old plugin installed as artefact
drop table IF EXISTS  `artefact_multirecipient_userrelation`;
drop table IF EXISTS  `artefact_multirecipient_notification`;

DELETE FROM `artefact_cron` WHERE plugin='multirecipientnotification';
DELETE FROM `artefact_event_subscription` WHERE plugin='multirecipientnotification';
DELETE FROM `artefact_config` WHERE `plugin`='multirecipientnotification';
DELETE FROM `artefact_installed_type` WHERE plugin='multirecipientnotification';
DELETE FROM `artefact_installed` WHERE name='multirecipientnotification';

# delete the new script installed as module
drop table IF EXISTS  `module_multirecipient_userrelation`;
drop table IF EXISTS  `module_multirecipient_notification`;

DELETE FROM `module_cron` WHERE plugin='multirecipientnotification';
DELETE FROM `module_event_subscription` WHERE plugin='multirecipientnotification';
DELETE FROM `module_config` WHERE `plugin`='multirecipientnotification';
DELETE FROM `module_installed_type` WHERE plugin='multirecipientnotification';
DELETE FROM `module_installed` WHERE name='multirecipientnotification';

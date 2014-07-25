# sql script for deleting the plugin from the database, mainly intended
# for developing/testing scenarios when you want to remove/reinstall the
# script without reinstalling the complete database

drop table IF EXISTS  `multirecipientnotification_internal_recipients`;
drop table IF EXISTS  `multirecipientnotification_internal_activity`;

DELETE FROM `artefact_cron` WHERE plugin='notificationoutbox';
DELETE FROM `artefact_event_subscription` WHERE plugin='notificationoutbox';
DELETE FROM `artefact_config` WHERE `plugin`='notificationoutbox';
delete from `artefact_installed_type` where plugin='notificationoutbox';
delete from `artefact_installed` where name='notificationoutbox';

drop table IF EXISTS  `artefact_multirecipient_userrelation`;
drop table IF EXISTS  `artefact_multirecipient_notification`;

DELETE FROM `artefact_cron` WHERE plugin='multirecipientnotification';
DELETE FROM `artefact_event_subscription` WHERE plugin='multirecipientnotification';
DELETE FROM `artefact_config` WHERE `plugin`='multirecipientnotification';
delete from `artefact_installed_type` where plugin='multirecipientnotification';
delete from `artefact_installed` where name='multirecipientnotification';


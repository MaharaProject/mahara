#!/bin/sh

psql mahara-trunk -c "delete from artefact_internal_profile_email where owner = $1;"
psql mahara-trunk -c "delete from artefact where owner = $1;"
psql mahara-trunk -c "delete from usr_activity_preference where usr = $1;"
psql mahara-trunk -c "delete from usr where id = $1;"

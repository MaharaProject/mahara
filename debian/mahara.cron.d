#
# Mahara implements its own cron to cater for people who need to run it in
# situations where normal unixy cron isn't available.
#
* * * * *   www-data     if [ -r /usr/share/mahara/lib/cron.php ] ; then /usr/bin/php5 /usr/share/mahara/lib/cron.php >>/var/log/mahara/cron.log 2>&1 ; fi

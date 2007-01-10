#
# Mahara implements its own cron to cater for people who need to run it in
# situations where normal unixy cron isn't available.
#
* * * * *   www-data     /usr/bin/php5 /var/www/mahara/lib/cron.php

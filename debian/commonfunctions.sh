function restart_apache {
        if (apachectl configtest >/dev/null 2>&1); then
            echo "Gracefully restarting apache"
            apachectl graceful
        else
            echo "apache configuration error ... not restarting"
            echo ""
            apachectl configtest
            echo ""
        fi
}

function restart_apache2 {
        if (apache2ctl configtest >/dev/null 2>&1); then
            echo "Gracefully restarting apache2"
            apache2ctl graceful
        else
            echo "apache2 configuration error ... not restarting"
            echo ""
            apache2ctl configtest
            echo ""
        fi
}

function set_config {
    perl -i -e '$field=shift;$value=shift if (scalar(@ARGV)>1);$value||="";while (<>) { s/(?:\s*\/\/\s*)?(\$cfg->$field.*=\s*).*/$1'\''$value'\'';/; print; }' $1 $2 /etc/mahara/config.debconf.php
}

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

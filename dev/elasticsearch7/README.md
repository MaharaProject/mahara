# Elasticsearch 7 for Mahara development

If you are wanting to use Elasticsearch 7.x in your development environment these instructions should get you started.  These only get the local cluster up and running.  See the Mahara documentation for configuration there.


> This README assumes you are familiar with [Docker](https://www.docker.com/).
> If the contents here do not work please check [the guide](https://www.elastic.co/guide/en/kibana/current/docker.html) for the latest version.
> All commands are run from the directory this file is in.

Much of this is lifted from [here](https://www.elastic.co/guide/en/elasticsearch/reference/current/configuring-tls-docker.html). These instructions have been tested with [my](https://wiki.mahara.org/wiki/User:Gold) environment and extra notes added for the issues I ran into.

## First run
### .env
Start by creating the local `.env` file. This will be updated as we set things up.

```
cp example.env .env
```

### vm.max_map_count
We have found that the elasticsearch containers shut themselves down without this system edit. This has been consistent across multiple machines with varying configurations. Feel free to skip it to test your machine, but odds are looking like this is a standard required edit for these containers.

`sudo sysctl -w vm.max_map_count=262144`

If you do test this on your local environment, and the containers do error out, check for this in the machine logs.

`es01 | bootstrap check failure [1] of [1]: max virtual memory areas vm.max_map_count [65530] is too low, increase to at least [262144]`

### Create certs
We need to create the certs for internal secure comms between the containers. This only needs to be run once.

```
docker-compose -f create-certs.yml run --rm create_certs
```
You may see the following error:
> Error: Failed to download metadata for repo 'appstream': Cannot prepare internal mirrorlist: No URLs in mirrorlist

This does not appear to have any impact on this step.

### Initial start
Start the cluster

```
docker-compose -f elastic-docker-tls.yml up -d
```

At this point, Kibana cannot connect to the Elasticsearch cluster. We need to set up that users password. Wait for this to return the cli to you. It takes a while, but it will exit back to the cli.

### Setup passwords
Run the elasticsearch-setup-passwords tool to generate passwords for all built-in users, including the `kibana_system` user. Update your `.env` file with the `kibana_system` user password. Also keep your own record of these passwords.

```
docker exec es01 /bin/bash -c "bin/elasticsearch-setup-passwords \
auto --batch --url https://es01:9200"
```

### Prepare Kibana

Ensure the `kibana_system` user password in the `.env` file is set to the password just generated.

After updating the password for the `kibana_system` user restart the containers.

    docker-compose stop

    docker-compose -f elastic-docker-tls.yml up -d

Kibana will take some time to start up.

We're ready.  Open Kibana here: [https://localhost:5601](https://localhost:5601)

You will be challenged with a login.  It is the `elastic` user from the earlier generated passwords.

### Create the 'mahara' user
Once Kibana has started login as the `elastic` user and create a new user for Mahara.

In Kibana open *Stack Management > Security:Users* and click Create User.

For development I just created one user and gave that `user` the superuser role. For reasons that are hopefully obvious this should not be done in a production environment.

> If you will be using this cluster for Behat testing create this user with the username 'mahara' and the password 'ThisIsThePassword'.  Behat has these values hardcoded into its config at the moment.

This username/password is what is added to the `config.php` file for the following (see "Configuration for Mahara" in the next section.):
```php
$cfg->plugin_search_elasticsearch7_username
$cfg->plugin_search_elasticsearch7_password
$cfg->plugin_search_elasticsearch7_indexingusername
$cfg->plugin_search_elasticsearch7_indexingpassword
```

## General usage

### Configuration for Mahara

Add the following to your `htdocs/config.php`:
```php
$cfg->plugin_search_elasticsearch7_host = 'localhost';
$cfg->plugin_search_elasticsearch7_port = '9200';
$cfg->plugin_search_elasticsearch7_scheme = 'https';
$cfg->plugin_search_elasticsearch7_types = 'usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,event_log';
$cfg->plugin_search_elasticsearch7_username = 'mahara';
$cfg->plugin_search_elasticsearch7_password = 'ThisIsThePassword';
$cfg->plugin_search_elasticsearch7_indexingusername = 'mahara';
$cfg->plugin_search_elasticsearch7_indexingpassword = 'ThisIsThePassword';
$cfg->plugin_search_elasticsearch7_ignoressl = true;
```

### Stand up the cluster

This will stand up the cluster and let it run in the background returning the shell to you.

```
docker-compose -f elastic-docker-tls.yml up -d
```

### Stop the cluster

```
docker-compose stop
```

### Monitor logs in the cluster

This will connect to the cluster and pipe all logs to the shell.  The `-f` holds the connection open and outputs real-time logging. Log lines are prefixed with the container that the entry is from and, if your shell supports it, these are colour coded.

```
docker-compose -f elastic-docker-tls.yml logs -f
```

### Tear down the cluster

If you are finished with the development task you wanted the cluster for this will tear it down and clean up the containers.

```
# Clean up containers and volumes.
docker-compose -f create-certs.yml -f elastic-docker-tls.yml down -v
# Clean up images.
docker rmi $(docker images docker.elastic.co/elasticsearch/elasticsearch -q)
docker rmi $(docker images docker.elastic.co/kibana/kibana -q)
```

This will remove the images, containers and volumes created for this cluster freeing up the associated disc space. Standing the cluster up again after this will require you start at **First Run** again.
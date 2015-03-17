DeployBundle
=================

[![Total Downloads](https://poser.pugx.org/hpatoio/deploy-bundle/downloads.png)](https://packagist.org/packages/hpatoio/deploy-bundle)
[![Latest Stable Version](https://poser.pugx.org/hpatoio/deploy-bundle/v/stable.png)](https://packagist.org/packages/hpatoio/deploy-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b4556cd7-652f-4a58-9126-eb2c1abd6c89/mini.png)](https://insight.sensiolabs.com/projects/b4556cd7-652f-4a58-9126-eb2c1abd6c89)
[![Project Status](http://stillmaintained.com/hpatoio/DeployBundle.png)](http://stillmaintained.com/hpatoio/DeployBundle)

## Installation
Run the command:

```bash
$ composer require hpatoio/deploy-bundle ~1.5
```

**N.B.** This project follow [semantic versioning](http://semver.org/). Latest stable branch is `1.5`.


### Enable the bundle in your project
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Hpatoio\DeployBundle\DeployBundle(),
        // ...
    );
}
```
## Configuration
Configuration is all about defining environments. You can define as many environments as you want, the only mandatory value is `host`. The deploy is made via rsync so default value are used if none are specified.
Remember that to get the configuration reference for this bundle you can run:
```bash
app/console config:dump-reference DeployBundle
```

Configuration example:
```yaml
# app/config/config.yml
deploy:
  prod:
    rsync-options: '-azC --force --delete --progress -h --checksum'
    host: my.destination.env
    dir: /path/to/project/root
    user: root
    port: 22
    timeout: 120 # Connection timeout in seconds. 0 for no timeout.
  uat:
    host: 192.168.1.10
    user: root2
    dir: /path/to/project/root
    port: 22022
    post_deploy_operations: 
        - app/console cache:clear --env=prod
        - app/console assets:install --env=prod
        - app/console assetic:dump --env=prod    
```

Most of the keys don't need explanation except:

#### post_deploy_operations
You can add a list of command you want run on the remote server after the deploy. In the configuration above you can see the common command you run after a deploy (clear the cache, publish assets etc)
These commands are run as a shell command on the remote server. So you can enter whichever shell command you want (cp, rm etc)

Please don't confuse Symfony environment with deploy environment. As you can see in the configuration above we run `post_deploy_operations` for Symfony environment `prod` on deploy environment `uat`

#### rsync-options
If you add the key `rsync-options` to your environment you will override the default options used for rsync. So the system is using:

* "-azC --force --delete --progress -h --checksum" if nothing is specified
* the value for the key `rsync-options` if specified it in `config.yml` for the target environment
* the value of the command line option `--rsync-options`

### Rsync Exclude
Create a `rsync_exclude.txt` file under `app/config` to exclude files from deploy. [here](https://github.com/hpatoio/DeployBundle/blob/master/.rsync_exclude.txt.dist) a good starting point.

You can also create a per-environment rsync_exclude. Just create a file in `app/config` with name `rsync_exclude_{env}.txt`. For more details you can read here #3 and here #7

## Force vendor syncronization
Usually `vendor` dir is excluded from rsync. If you need tou sync it you can add `--force-vendor`. (see later for an example)

## Use
Deployment is easy: 
```shell
php app/console project:deploy --go prod
```
Feel a bit unsure ? Simulate the deploy
```shell
php app/console project:deploy prod
```
Need to update vendor ? Use the option --force-vendor (Usually vendor is excluded from rsync)
```shell
php app/console project:deploy --go --force-vendor prod
```
Custom parameters for rsync
```shell
php app/console project:deploy --rsync-options="-azChdl" prod
```
License
-------------
DeployBundle is licensed under the CC-BY-SA-3.0 - see [here](http://www.spdx.org/licenses/CC-BY-SA-3.0) for details

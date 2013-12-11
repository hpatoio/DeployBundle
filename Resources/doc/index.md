## Installation

Add the bundle to your `composer.json`

```json
{
    ...
    "require": {
        ...
        "hpatoio/deploy-bundle": "1.*"
    }
}
```
Now tell composer to download the bundle by running the command:
```bash
$ php composer.phar update hpatoio/deploy-bundle
```
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
    host: 123.45.67.89 // or the hostname
    dir: /path/to/project/root
    user: root
    port: 22
    timeout: 120 # Connection timeout in seconds. 0 for no timeout.
    post_deploy_operations: 
        - app/console cache:clear
        - app/console assets:install
        - app/console assetic:dump
  uat:
    host: 127.0.0.1 // or the hostname
    user: root2
    dir: /path/to/project/root
    port: 22022
```

Most of the keys don't need explanation except:

#### post_deploy_operations | New in version 1.3
You can add a list of command you want run on the remote server after the deploy. In the configuration above you can see the common command you run after a deploy (clear the cache, publish assets etc)
These commands are run as a shell command on the remote server. So you can enter whichever shell command you want (cp, rm etc)
#### rsync-options | New in version 1.1
If you add the key `rsync-options` to your environment you will override the default options used for rsync. So the system is using:

* "-azC --force --delete --progress -h --checksum" if nothing is specified
* the value for the key `rsync-options` if specified it in `config.yml` for the target environment
* the value of the command line option `--rsync-options`

### Force vendor syncronization | New in version 1.3
Now you can force vendor dir syncronization simply adding `--force-vendor` when running the command. (see later for an example)

### Rsync Exclude
Create a `rsync_exclude.txt` file under `app/config` to exclude files from deploy. If you need a starting template you can get one [here](http://bit.ly/rsehdbsf2)
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
## Feedbacks
For any feedback open an issue here on Github or comment here http://www.iliveinperego.com/2012/03/symfony2-deploy-like-symfony-1-4/    
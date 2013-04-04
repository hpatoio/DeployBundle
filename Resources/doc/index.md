Brings Symfony 1.4 project:deploy command to Symfony2.

## Installation 2.0.*

###  Add DeployBundle to your deps

    [HpatoioDeployBundle]
      git=git://github.com/hpatoio/DeployBundle.git
      target=/bundles/Hpatoio/DeployBundle
      
### Add DeployBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Hpatoio\DeployBundle\DeployBundle(),
            // ...
        );
    }
    
### Register the namespace in autoload.php

    'Hpatoio'     => __DIR__.'/../vendor/bundles',
    
run 

    bin/vendors install
    
## Installation 2.1.* and 2.2.*

###  Add DeployBundle to your composer.json

     "hpatoio/deploy-bundle": "the-version-you-want"

For a complete list of available version have a look here: https://packagist.org/packages/hpatoio/deploy-bundle
      
### Add DeployBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Hpatoio\DeployBundle\DeployBundle(),
            // ...
        );
    }
    
run 

    composer update

### Configure

    # app/config/config.yml
    deploy:
      prod:
        rsync-options: '-azC --force --delete --progress -h --checksum'
        host: 127.0.0.1 // or the hostname
        user: root
        dir: /path/to/dir
        port: 22
        post_deploy_operations: 
            - app/console cache:clear
            - app/console assets:install
            - app/console assetic:dump
      uat:
        host: 127.0.0.1 // or the hostname
        user: root2
        dir: /path/to/dir
        port: 22

### Post deploy operations | New in version 1.3

You can add a list of command you want run on the remote server after the deploy. In the configuration above you can see the common command you run after a deploy (clear the cache, publish assets etc)
These commands are run as a shell command on the remote server. So you can enter whichever shell command you want (cp, rm etc)

### Force vendor syncronization | New in version 1.3

Now you can force vendor dir syncronization simply adding --force-vendor when running the command. (see later for an example)

### Rsync Options | New in version 1.1

If you add the key rsync-options to your environment you will override the default options used for rsync. So the system is using:

* "-azC --force --delete --progress -h --checksum" if nothing is specified
* the value for the key rsync-options if specified in config.yml for the target environment
* the value of the command line option --rsync-options

    
### Rsync Exclude

Create a rsync_exclude.txt file under app/config to exclude files in your deployments. If you need a starting template you can get one in Resources/template_rsync_exclude.txt

### Use

Deployment is easy: 

    php app/console project:deploy --go prod

Feel a bit unsure ? Simulate the deploy

    php app/console project:deploy prod
    
Need to update vendor ? Use the option --force-vendor (Usually vendor is excluded from rsync)

    php app/console project:deploy --go --force-vendor prod


    
Custom parameters for rsync

    php app/console project:deploy --rsync-options="-azChdl" prod

## Feedbacks

For any feedback open an issue here on Github or comment here http://www.iliveinperego.com/2012/03/symfony2-deploy-like-symfony-1-4/    


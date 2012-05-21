Provide the symfony 1.4 command line to deploy your app on a server.

## Installation

### Add DeployBundle to your deps

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

### Configure

    # app/config/config.yml
    deploy:
      prod:
        host: 127.0.0.1 // or the hostname
        user: root
        dir: /path/to/dir
        port: 22
      stage:
        host: 127.0.0.1 // or the hostname
        user: root2
        dir: /path/to/dir
        port: 22
    
### Rsync Exclude

Create a rsync_exclude.txt file under app/config to exclude files in your deployments.

### Use

Deployment is easy: 

    php app/console project:deploy --go prod

Simulate deployment

    php app/console project:deploy prod
    
Custom parameters for rsync (default -azC --force --delete --progress -h) 

    php app/console project:deploy --rsync-options="-azChdl" prod
    
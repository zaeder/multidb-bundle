# multidb-bundle
A symfony 4 bundle with a local database and dynamic dist database

Use a local database for application configuration and a separate database per client

## Install

    composer require zaeder/multidb-bundle 

## Create the entities

You must create 3 entities :

* For local database : 
    * Server implements Zaeder\MultiDbBundle\Entity\ServerInterface having for repository Zaeder\MultiDbBundle\Repository\ServerRepository or an extended class
    * User implements Zaeder\MultiDbBundle\Entity\LocalUserInterface having for repository Zaeder\MultiDbBundle\Repository\LocalUserRepository or an extended class
* For dist database : 
    * User implements Zaeder\MultiDbBundle\Entity\DistUserInterface having for repository Zaeder\MultiDbBundle\Repository\DistUserRepository or an extended class

We recommend to separate your entities and repositories in to folders Local and Dist

## Configure

You need to define two connections and entity manager in doctrine configuration. We recommend using the connection for the client database by default, it will be easier for the autowiring. 
Use the same configuration as the local connection as default, it will be reconfigured on login

Add to your configuration :

    multi_db:
      local:
        connection: localConnectionName
        entityManager: localEntityManagerName
        tablePrefix: '' #optional
        serverEntity: pathToServerEntity
        userEntity: pathToLocalUserEntity
      dist:
        connection: distConnectionName
        entityManager: distEntityManagerName
        tablePrefix: '' #optional
        userEntity: pathToDistUserEntity
      passwordKey: enterAnHash #better to define in .env or parameters.yml
      loginRedirect:
        - {role: 'roleName', route: 'routeName'} # Define higher role first
        
Add the bundle definition in config/bundles.php :

    Zaeder\MultiDbBundle\MultiDbBundle::class => ['all' => true],
    
Edit the config/packages/security.yaml :

     security:
       encoders:
         pathToLocalUserEntity:
           algorithm: bcrypt
       providers:
         users:
           entity: pathToLocalUserEntity
           property: 'username'
           manager_name: 'localEntityManagerName'
       firewalls:
         [...]
         local:
           pattern: ^/
           anonymous: true
           providers: users
           guard:
             authenticators:
               - Zaeder\MultiDbBundle\Security\Authentication\LoginFormAuthenticator
           logout:
             path: logout
       role_hierarchy:
         ROLE_ADMIN: [ROLE_ALLOWED_TO_SWITCH] #ROLE ADMIN is the the users without client database

## The login form

The login form need the fields serverKey, username, password and _csrf_token

## Donate

[Click here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2NWVQEGTFWBBE&source=url)

Or use the QR Code

![Paypal donation](https://lh5.googleusercontent.com/6Qq24ElNySo18R1gpAQUl8wewCEiK6-1lPifjxJaJUIjeOJ-dpJSb660McuAmSgysH6kAXk4lyXVvt4MUF-2=w1920-h888)

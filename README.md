Spitfire
========

A specialized crawler that exposes RESTful APIs to its content. 
Also a famous beer. Also a plane from the WWII.

Install
-------

Spitfire depends on PHP >= 5.3.5 and PEAR, if you are on Ubuntu, the given 
command line should install everything you need.

    sudo apt-get update
    sudo apt-get install apache2 mysql-server php5 libapache2-mod-php5 php5-pear

The following PHP dependencies should be installed via PEAR, below figures 
a list of the comand lines required to install everything.

    sudo pear channel-discover respect.li/pear
    sudo pear install Respect/Loader-0.2.0
    sudo pear install Respect/Rest-0.4.1
    sudo pear install Respect/Config-0.4.1
    sudo pear install Respect/Relational-0.4.4
    sudo pear install Respect/Validation-0.4.4

API
---

/sp/sao-paulo/legislativo/sessoes-plenarias/2012/04/12

/sp/sao-paulo/legislativo/partidos/PT
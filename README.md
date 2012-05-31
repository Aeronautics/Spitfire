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
    sudo pear install Respect/Data-0.1.5
    sudo pear install Respect/Relational-0.4.4
    sudo pear install Respect/Validation-0.4.4

If you like to user composer just run.

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

The database isntructions can be found in the [repository](https://github.com/Aeronautics/Spitfire/blob/master/doc/spitfire.sql)
while in development. Please, **be aware** that everything in this database 
(while not in this repository) can be changed! The best option is to use 
[MySQL Workbench](http://dev.mysql.com/downloads/workbench/) and use the 
```doc/spitifire.mwb``` to syncronise the database.

API
---

Only GET.

* /legislativo/partidos_[ .js, .xml, .json ]_
* /legislativo/TSE/partidos/___{PARTIDO}____[ .js | .xml | .json ]_
* /legislativo/TSE/partidos/___{PARTIDO}___/politicos_[ .js | .xml | .json ]_

Estrutura sugerida
------------------

* União
    * Executivo
    * Legislativo
        * Parlamentares
            * Câmara dos Deputados
            * Senado Federal
            * União dos dois acima (Congresso Nacional)
    * Judiciário
        * STJ (paira sobre estrutura do judiciario)
        * STF (paira sobre estrutura do judiciario)
        * TSE (registro nacional de partido)
* Estado
    * Executivo
    * Legislativo
        * Parlamentares
            * Deputados Estaduais
            * Assembléia Legislativa
    * Judiciário
        * Tribunais de Justiça
        * TRE - Tribunais Regionais Eleitorais
* Munícipio
    * Executivo
    * Legislativo
        * Parlamentares
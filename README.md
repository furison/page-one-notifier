PageOne Notifier
===============

Provides [PageOne](https://www.pageone.co.uk/) integration for Symfony Notifier.

DSN example
-----------

```
PAGE_ONE_DSN=page-one://USERNAME:PASSWORD@default?from=FROM
```

where:
 - `USERNAME` is your API Username
 - `PASSWORD` is your API password
 - `FROM` is the sender name

See your account info at https://www.pageone.co.uk/login

Installation
------------
 - Add directory `Notifier\PageOne` in your src directory
 - Clone this repository
 - Add the following config in services:
 ```YAML
  services:
    App\Notifier\PageOne\PageOneTransportFactory:
        parent: 'notifier.transport_factory.abstract'
            tags: ['texter.transport_factory']
 ```
 - Add the following in the `notifier.yaml` file:
 ```YAML
  framework:
    notifier:
        texter_transports:
            page-one: '%env(PAGE_ONE_DSN)%'
 ```
 - Use it as described here:  https://symfony.com/doc/current/notifier.html#creating-sending-notifications but use `['sms']` as the type
 - Profit!

Resources
---------

 * [REST API documentation](https://www.pageone.co.uk/developers/api-library/rest/)

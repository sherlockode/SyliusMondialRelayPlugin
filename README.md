# Sylius Mondial Relay plugin

This plugin enables Mondial Relay shipping method on your Sylius website.

## Installation

Install the plugin with composer:

```bash
$ composer require sherlockode/sylius-mondial-relay-plugin
```

Complete the configuration:

```yaml
# config/packages/sherlockode_sylius_mondial_relay.yaml

sherlockode_sylius_mondial_relay:
    wsdl: The mondial relay WSDL
    merchant_id: Your merchant ID
    private_key: Your private key
    google_map_api_key: '%env(GOOGLE_MAP_API_KEY)%'
    enable_ticket_printing: true
```

```dotenv
#.env

GOOGLE_MAP_API_KEY=xxxxxxxxxxxx
```

Import routing:

```yaml
# config/routes.yaml

sherlockode_sylius_mondial_relay_plugin:
    resource: "@SherlockodeSyliusMondialRelayPlugin/Resources/config/routing.xml"
```

In your Shipment entity, import the `PickupPointTrait`:

```php
<?php

// App/Entity/Shipping/Shipment.php

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping as ORM;
use Sherlockode\SyliusMondialRelayPlugin\Model\PickupPointTrait;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shipment")
 */
class Shipment extends BaseShipment
{
    use PickupPointTrait;
}
```

Don't forget to make a migration or a d:s:u after that

Update your webpack configuration to add entries both in shop config and admin config:
```js
// Shop config
Encore
  // ...
  .addEntry('sherlockode-mondial-relay', './vendor/sherlockode/sylius-mondial-relay-plugin/src/Resources/public/js/entry.js')

// Admin config
Encore
  // ...
  .addEntry('sherlockode-mondial-relay', './SyliusMondialRelayPlugin/src/Resources/public/js/admin.js')
```

To finish, don't forget to publish assets:

```bash
$ php bin/console assets:install
```

## Configuration

Now you only have to create a new shipping method. 
For the Shipping charges option, select "Mondial Relay"

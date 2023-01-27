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

Update your template to include the pickup point field

```twig
{# @SyliusShopBundle/Checkout/SelectShipping/_shipment.html.twig #}

<div class="ui segment">
    <div class="ui dividing header">{{ 'sylius.ui.shipment'|trans }} #{{ loop.index }}</div>
    <div class="ui fluid stackable items" {{ sylius_test_html_attribute('shipments') }}>
        {{ form_errors(form.method) }}

        {% for key, choice_form in form.method %}
            {% set fee = form.method.vars.shipping_costs[choice_form.vars.value] %}
            {% set method = form.method.vars.choices[key].data %}
            {% include '@SyliusShop/Checkout/SelectShipping/_choice.html.twig' with {'form': choice_form, 'method': method, 'fee': fee} %}
            {# Include the pickup point field here #}
            {% if choice_form.vars.attr["data-mr"] is defined %}
                {% include '@SherlockodeSyliusMondialRelayPlugin/Checkout/_pickup_point_form_widget.html.twig' %}
                <div id="current-pickup-point"></div>
            {% endif %}
        {% else %}
            {% include '@SyliusShop/Checkout/SelectShipping/_unavailable.html.twig' %}
        {% endfor %}
    </div>
</div>

{{ include ('@SherlockodeSyliusMondialRelayPlugin/Checkout/_modal.html.twig') }}
```

To finish, don't forget to publish assets:

```bash
$ php bin/console assets:install
```

## Configuration

Now you only have to create a new shipping method. 
For the Shipping charges option, select "Mondial Relay"

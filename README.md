Composite container factory
===========================

This package provides a factory creating a composite container that automatically detects compatible containers using container-discovery and Puli.

Usage
-----

```php
use TheCodingMachine\CompositeContainer\CompositeContainerFactory;

$compositeContainer = CompositeContainerFactory::get();
```

The `get` method will:

- initialize Puli
- create a composite container
- using Puli, discover all containers that are compatible with [container-discovery](https://github.com/thecodingmachine/container-discovery)
- instantiate all the containers and add them to the composite container
- finally, add Puli instances to the composite container

So the composite container contains all entries of aggregated containers.

The Composite container is of course compatible with container-interop, so you can use `get` and `has` method to fetch services.

**Important**: The `CompositeContainerFactory::get()` method should be called **only once** in your script. Two successive calls will NOT return the same instance.

Puli entries
------------

Those 4 entries will be added to the composite container:

- `puli.factory`: The Puli factory
- `puli.repository`: The Puli repository
- `puli.discovery`: The Puli discovery component
- `puli.asset_url_generator`: The Puli URL generator


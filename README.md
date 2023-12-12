# MageWire Backend module for Magento 2

**This module supplies Magewire compatibility for the Magento Admin Panel. It is built on top of the Magewire core (with RequireJS support).**

**WARNING: This is current in development. It might work for you, it might not. If you want to collaborate, you are welcome to join.**

### Installation
First install the composer package from this repository:
```bash
composer config repositories.magewire-backend vcs git@github.com:michielgerritsen/magewire-backend.git
composer require magewirephp/magewire-backend:@dev
```

Note that the current package also requires a Pull Request to be merged into the Magewire core (to reuse all of the current view files). To integrate this PR temporarily in your setup, we can use a composer patch:


```bash
composer require cweagans/composer-patches
```

Next, in your own root `composer.json` file, make sure to include the Pull Request:
```json
{
	"extra": {
	 "patches": {
      "magewirephp/magewire": {
         "Compatibility with backend": "https://patch-diff.githubusercontent.com/raw/magewirephp/magewire/pull/139.patch"
      }
    }
	}
}
```

Update composer:
```bash
composer update magewirephp/magewire
```

Afterwards, enable all of the relevant modules:
```bash
bin/magento module:enable Magewirephp_Magewire Magewirephp_MagewireRequireJs Magewirephp_MagewireBackend
```

Please note that Magewire will only be loaded, if there actually is a component making use of it. However, you should be able to inspect the loading of various JS files with the word `magewire`.

### Proof of concept
Create a custom XML layout to add a `block` somewhere in the admin. Add an argument `magewire` referring to a custom Magewire component class.

You could also use the following example which displays an input with `Hello World` on the dashboard page:
```bash
composer config repositories.magewire-backend vcs git@github.com:yireo-training/YireoTraining_MageWireBackendHelloWorld.git
composer require yireo-training/magento2-magewire-backend-hello-world:@dev
bin/magento module:enable YireoTraining_MageWireBackendHelloWorld
```

Other examples:
- [Yireo_MageWireBackendConfigSearch](https://github.com/yireo/Yireo_MageWireBackendConfigSearch)

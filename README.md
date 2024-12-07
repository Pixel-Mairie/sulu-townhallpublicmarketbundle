<center>

<img src="src/Resources/documentation/logo.svg" width="250">

# Public market for Town Hall Bundle

![GitHub release (with filter)](https://img.shields.io/github/v/release/Pixel-Mairie/sulu-townhallpublicmarketbundle) 
[![Dependency](https://img.shields.io/badge/sulu-2.6-cca000.svg)](https://sulu.io/)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=Pixel-Mairie_sulu-townhallpublicmarketbundle&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=Pixel-Mairie_sulu-townhallpublicmarketbundle)

</center>

## 📝 Presentation

This bundle for the Sulu CMS manages public contracts for town halls.

## ✅ Features

* Public market
* List of entities (via smart content)
* Activity log
* Trash

## 🚀 Installation
### Install the bundle

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your
project:

```bash
composer require pixelmairie/sulu-townhallpublicmarketbundle
```

### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Pixel\TownHallPublicMarketBundle\TownHallPublicMarketBundle::class => ['all' => true],
 ];
 ```

### Update schema
```shell script
bin/console do:sch:up --force
```

## Bundle Config

Define the Admin Api Route in `routes_admin.yaml`
```yaml
townhall.publics_markets_api:
  type: rest
  prefix: /admin/api
  resource: pixel_townhall.publics_markets_route_controller
  name_prefix: townhall.
``` 

## 👍 Use
### Add/Edit
Go to the "Town hall" section in the administration interface. Then, click on "Public market".
To add, simply click on "Add". Fill the fields that are needed for your use.

Here is the list of the fields:
* Title (mandatory)
* URL (mandatory and filled automatically according to the title)
* Published at (filled manually)
* Status (mandatory)
* Description (mandatory)
* List of documents

Once you finished, click on "Save".

The public market you added is not visible on the website yet. In order to do that, click on "Activate?". It should be now visible for visitors.

To edit, simply click on the pencil at the left of the entity you wish to edit.

The edit form has a preview where you can see all your changes being updated live.

### Status
A public market **must** have a status. This status allows you to determine at which step is your public market.

To create status:
* You **must** create a root category which **must** have its key named "publics_markets"
* Then, under this root category, you create all the categories you need

### Remove/Restore

There are two ways to remove a public market:
* Check every public market you want to remove and then click on "Delete"
* Go to the detail of a public market (see the "Add/Edit" section) and click on "Delete".

In both cases, the public market will be put in the trash.

To access the trash, go to the "Settings" and click on "Trash".
To restore a public market, click on the clock at the left. Confirm the restore. You will be redirected to the detail of the public market you restored.

To remove permanently a public market, check all the public markets you want to remove and click on "Delete".

## 🤝 Contributing

You can contribute to this bundle. The only thing you must do is respect the coding standard we implement.
You can find them in the `ecs.php` file.

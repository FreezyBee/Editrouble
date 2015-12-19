Quickstart
==========


Installation
------------

The best way to install FreezyBee/Editrouble is using  [Composer](http://getcomposer.org/):

```sh
$ composer require freezy-bee/editrouble
```

With Nette `2.3` and newer, you can enable the extension using your neon config.

```yml
extensions:
	editrouble: FreezyBee\Editrouble\DI\EditroubleExtension
```

Minimal configuration
------------------

```yml
editrouble:
    storage: doctrine # [doctrine, ndb, dibi]
    webPaths:
        js: 'pathTo medium-editor.min.jss'
        css: 'pathTo medium-editor.min.css'
```

Example
-------

```php

class BasePresenter extends Presenter
{
    use FreezyBee\Editrouble\Control\EditroubleTrait

    ...
}
```

```smarty
<head>
    <!-- used for toolbar layout -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
</head>

<body>
    <div n:editrouble="namespace_item-key">
    </div>
    
    <p n:editrouble="superNamespace_itemKey">
    </p>

    {control editrouble}
</body>
```
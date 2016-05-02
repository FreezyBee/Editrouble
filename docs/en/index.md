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
    storage: doctrine # [doctrine, dibi]
    webPaths:
        js: 'pathTo editrouble.jss'
        css: 'pathTo editrouble.css'
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
<body>
    <div n:editrouble="namespace_item-key">
    </div>
    
    <p n:editrouble="superNamespace_itemKey">
    </p>

    {control editrouble}
</body>
```
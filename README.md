# Laravel Haml

<!-- [![Packagist](https://img.shields.io/packagist/v/bkwld/laravel-haml.svg)](https://packagist.org/packages/bkwld/laravel-haml) -->

A small package that adds support for compiling Haml templates to Laravel via [MtHaml](https://github.com/arnaud-lb/MtHaml).  
<!-- Both vanilla php and [Blade syntax](http://laravel.com/docs/5.0/templates#blade-templating) is supported within the Haml. -->



## Installation

1. Add it to your composer.json (`"fkrfld/laravel-haml": "3.0"`) and do a composer install.
2. Add the service provider to your app.php config file providers: `'Fkrfld\LaravelHaml\ServiceProvider'`.



## Configuration

You can set [MtHaml](https://github.com/arnaud-lb/MtHaml) environment, options, and filters manually.  To do so:

Publish the config file with `php artisan vendor:publish` and edit it at /config/haml.php.  

For instance, to turn off auto-escaping:

	'mthaml' => array(
		'environment' => 'php',
		'options' => array(
			'enable_escaper' => false,
		),
		'filters' => array(),
	), 



## Usage

Laravel-Haml registers the ".haml", ".haml.php", ".haml.blade", and ".haml.blade.php" extension with Laravel and forwards compile requests on to MtHaml.  It compiles your Haml templates in the same way as Blade templates; the compiled template is put in app/storage/views.  Thus, you don't suffer compile times on every page load.

In other words, just put your Haml files in the regular views directory and name them like "whatever.haml".  You reference them in Laravel like normal: 

* **Laravel 5** : `view('home.whatever')` for `resources/views/home/whatever.haml.blade`

The Haml view files can work side-by-side with regular PHP views.  To use Blade templating within your Haml, just name the files with ".haml.blade" extensions.

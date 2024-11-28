<?php

namespace Fkrfld\LaravelHaml;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use MtHaml\Environment;

class ServiceProvider extends IlluminateServiceProvider
{
	/**
	 * Get the major Laravel version number
	 *
	 * @return integer 
	 */
	public function version() {
		$app = $this->app;
		return intval($app->version());
	}
    /**
     * Register services.
     */
    public function register(): void
    {
		// Version specific `registering`
		if ($this->app->version() == 5) $this->registerLaravel5();
        
		// Determine the cache dir
		$cache_dir = storage_path($this->app->version() == 5 ? '/framework/views' : '/views');

		// Bind the package-configred MtHaml instance
		$this->app->singleton('laravel-haml.mthaml', function($app) {
			$config = $this->getConfig();
			return new Environment($config['mthaml']['environment'], 
				$config['mthaml']['options'], 
				$config['mthaml']['filters']);
		});

		// Bind the Haml compiler
		$this->app->singleton('Fkrfld\LaravelHaml\HamlCompiler', function($app) use ($cache_dir) {
			return new HamlCompiler($app['laravel-haml.mthaml'], $app['files'], $cache_dir);
		});

		// Bind the Haml Blade compiler
		$this->app->singleton('Fkrfld\LaravelHaml\HamlBladeCompiler', function($app) use ($cache_dir) {
			return new HamlBladeCompiler($app['laravel-haml.mthaml'], $app['files'], $cache_dir);
		});

    }

	/**
	 * Register specific logic for Laravel 5. Merges package config with user config
	 * 
	 * @return void
	 */
	public function registerLaravel5() {
		$this->mergeConfigFrom(__DIR__.'../config/config.php', 'laravel-haml');
	}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        
		// Version specific booting
		switch($this->version()) {
			// case 4: $this->bootLaravel4(); break;
			case 5: $this->bootLaravel5(); break;
			case 6: $this->bootLaravel5(); break;
			default: $this->bootLaravel5(); 
			// default: throw new Exception('Unsupported Laravel version');
		}

		// Register compilers
		$this->registerHamlCompiler();
		$this->registerHamlBladeCompiler();

		$customDirectives = $this->app['blade.compiler']->getCustomDirectives();
		foreach ($customDirectives as $name => $closure) {
			$this->app['Fkrfld\LaravelHaml\HamlBladeCompiler']->directive($name, $closure);
		}	
    }

	/**
	 * Boot specific logic for Laravel 4. Tells Laravel about the package for auto 
	 * namespacing of config files
	 * 
	 * @return void
	 */
	// public function bootLaravel4() {
	// 	$this->package('fkrfld/laravel-haml');
	// }
    
	/**
	 * Boot specific logic for Laravel 5. Registers the config file for publishing 
	 * to app directory
	 * 
	 * @return void
	 */
	public function bootLaravel5() {
		$this->publishes([
			__DIR__.'/../config/config.php' => config_path('haml.php')
		], 'laravel-haml');
	}

	/**
	 * Register the regular haml compiler
	 *
	 * @return void
	 */
	public function registerHamlCompiler() {

		// Add resolver
		$this->app['view.engine.resolver']->register('haml', function() {
			return new CompilerEngine($this->app['Fkrfld\LaravelHaml\HamlCompiler']);
		});

		// Add extensions
		$this->app['view']->addExtension('haml', 'haml');
		$this->app['view']->addExtension('haml.php', 'haml');
	}

	/**
	 * Register the blade compiler compiler
	 *
	 * @return void
	 */
	public function registerHamlBladeCompiler() {

		// Add resolver
		$this->app['view.engine.resolver']->register('haml.blade', function() {
			return new CompilerEngine($this->app['Fkrfld\LaravelHaml\HamlBladeCompiler']);
		});

		// Add extensions
		$this->app['view']->addExtension('haml.blade', 'haml.blade');
		$this->app['view']->addExtension('haml.blade.php', 'haml.blade');
	}

	/**
	 * Get the configuration, which is keyed differently in L5 vs l4
	 *
	 * @return array 
	 */
	public function getConfig() {
        $configFiles = [
            4 => 'laravel-haml::config',
            5 => 'laravel-haml',
            6 => 'haml',
            7 => 'haml',
            8 => 'haml',
            9 => 'haml',
            10 => 'haml',
            11 => 'haml'
        ];

		$key = $configFiles[$this->version()];
		return $this->app->make('config')->get($key);
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array(
			'Fkrfld\LaravelHaml\HamlCompiler', 
			'Fkrfld\LaravelHaml\HamlBladeCompiler',
			'laravel-haml.mthaml',
		);
	}
}

# Logstash log stream for CakePHP#


## Requirements ##

* CakePHP 3.x
* PHP 5.3+
* Composer

## Installation ##

The only installation method supported by this plugin is by using composer. Just add this to your composer.json configuration:

	{
	  "require" : {
		"lorenzo/cakephp-logstash": "master"
	  }
	}

### Enable plugin

You need to enable the plugin your `app/Config/bootstrap.php` file:

    CakePlugin::load('Logstash');

Finally add a new logging stream in the same file:

	Log::config('logstash', [
        'engine' => 'Logstash',
        'levels' => ['notice', 'info', 'debug', 'warning', 'error', 'critical', 'alert', 'emergency'],
        'host' => 'tcp://127.0.0.1', // Set it to the real host works with udp too
        'port' => 2020, // Set it to the actual port
        'timeout' => 5 // Connection timeout
    ]);

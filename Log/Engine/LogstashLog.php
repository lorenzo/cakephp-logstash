<?php

App::uses('BaseLog', 'Log/Engine');

/**
 * A Log stream that will write messages directly to logstash
 * in json format
 *
 */
class LogstashLog extends BaseLog {

/**
 * Configuration used in this logger engine
 *
 * @var array
 */
	protected $_config = array(
		'host' => null,
		'port' => null
	);

/**
 * The resource for connecting to the logstash server
 *
 * @var resource
 */
	protected $_handle;

/**
 * Encodes a message and logs it directly to logstash
 *
 * @param string $type
 * @param string $message
 * @return void
 */
	public function write($type, $message) {
		if (is_string($message)) {
			$message = array('message' => $message);
		}

		$message['_type'] = $type;
		$message = json_encode($message);

		if ($this->_write($message) === false) {
			$this->_close();
			$this->_write($message);
		}
	}

/**
 * Configures this logger stream
 *
 * @param array $config
 * @return array
 */
	public function config($config = array()) {
		if (!isset($config['timeout'])) {
			$config['timeout'] = 5;
		}
		return parent::config($config);
	}

/**
 * Opens a connection to logstash
 *
 * @return resource
 */
	protected function _open($host, $port, $timeout) {
		$handle = pfsockopen($host, $port, $errNo, $errSt, $timeout);
	}

/**
 * Writes a message to logstash
 *
 * @param string message
 * @return boolean false if there is no connection to logstash
 */
	protected function _write($message) {
		if (!$this->_handle) {
			$this->_handle = $this->_open($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
			if ($this->_handle === false) {
				throw new SocketException('Could not connect to logstash');
			}
		}
		return @fwrite($this->_handle, $message);
	}

/**
 * Closes the connection to logstash
 *
 * @return void
 */
	protected function _close() {
		@fclose($this->_handle);
		$this->_handle = null;
	}

/**
 * Flushes the buffer handle before destroying this object
 *
 * @return void
 */
	public function __destruct() {
		if ($this->_handle) {
			@fflush($this->_handle);
		}
	}

}

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
		'port' => null,
		'timeout' => 5
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
		if (!$this->_handle) {
			$this->_open();
		}

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
 * Opens a connection to logstash
 *
 * @return void
 */
	protected function _open() {
		$this->_handle = pfsockopen(
			$this->_config['host'],
			$this->_config['port'],
			$errNo,
			$errSt,
			$this->_config['timeout']
		);

		if (!empty($errSt)) {
			throw new CakeException($errSt);
		}
	}

/**
 * Writes a message to logstash
 *
 * @param string message
 * @return boolean false if there is no connection to logstash
 */
	protected function _write($message) {
		if (!$this->_handle) {
			$this->_open();
		}
		return fwrite($message);
	}

/**
 * Closes the connection to logstash
 *
 * @return void
 */
	protected function _close() {
		fclose($this->_handle);
		$this->_handle = null;
	}

/**
 * Flushes the buffer handle before destroying this object
 *
 * @return void
 */
	public function __destruct() {
		if ($this->_handle) {
			fflush($this->_handle);
		}
	}

}

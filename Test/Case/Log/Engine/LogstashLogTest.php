<?php

App::uses('LogstashLog', 'Logstash.Log/Engine');

class LogstashLogTest extends CakeTestCase {

/**
 * Tests writing a string message
 *
 * @return void
 */
	public function testWriteStringSuccess() {
		$config = array(
			'host' => 'tcp://0.0.0.0',
			'port' => 2020
		);
		$engine = $this->getMock('LogstashLog', array('_open'), array($config));
		$file = tmpfile();
		$engine->expects($this->once())->method('_open')
			->with('tcp://0.0.0.0', 2020, 5)
			->will($this->returnValue($file));

		$engine->write('debug', 'This is a message');
		$expected = json_encode(array(
			'@timestamp' => gmdate('c'),
			'@type' => 'debug',
			'@message' => 'This is a message'
		));
		fseek($file, 0);
		$this->assertEquals($expected, fgets($file));
	}

/**
 * Tests writing an array
 *
 * @return void
 */
	public function testWriteArraySuccess() {
		$config = array(
			'host' => 'tcp://0.0.0.0',
			'port' => 2020
		);
		$engine = $this->getMock('LogstashLog', array('_open'), array($config));
		$file = tmpfile();
		$engine->expects($this->once())->method('_open')
			->with('tcp://0.0.0.0', 2020, 5)
			->will($this->returnValue($file));

		$engine->write('debug', array('key' => 'value'));
		$expected = json_encode(array(
			'@timestamp' => gmdate('c'),
			'@type' => 'debug',
			'@fields' => array('key' => 'value')
		));
		fseek($file, 0);
		$this->assertEquals($expected, fgets($file));
	}

/**
 * Test writing when connection is lost
 *
 * @return void
 */
	public function testWriteLostConnection() {
		$config = array(
			'host' => 'tcp://0.0.0.0',
			'port' => 2020
		);
		$engine = $this->getMock('LogstashLog', array('_open'), array($config));
		$file = tmpfile();
		$file2 = tmpfile();
		$engine->expects($this->at(0))->method('_open')
			->with('tcp://0.0.0.0', 2020, 5)
			->will($this->returnValue($file));

		$engine->expects($this->at(1))->method('_open')
			->with('tcp://0.0.0.0', 2020, 5)
			->will($this->returnValue($file2));

		fclose($file);
		$engine->write('debug', array('key' => 'value'));
		$expected = json_encode(array(
			'@timestamp' => gmdate('c'),
			'@type' => 'debug',
			'@fields' => array('key' => 'value')
		));
		fseek($file2, 0);
		$this->assertEquals($expected, fgets($file2));
	}

/**
 * Tests error when connecting
 *
 * @expectedException SocketException
 * @return void
 */
	public function testCreateConnectionError() {
		$config = array(
			'host' => 'tcp://0.0.0.0',
			'port' => 2020
		);
		$engine = $this->getMock('LogstashLog', array('_open'), array($config));
		$file = tmpfile();
		$engine->expects($this->once())->method('_open')
			->with('tcp://0.0.0.0', 2020, 5)
			->will($this->returnValue(false));

		$engine->write('debug', 'This is a message');
	}

}

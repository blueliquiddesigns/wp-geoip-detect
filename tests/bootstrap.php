<?php

//define('WP_DEBUG', true);

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../geoip-detect.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

ini_set('error_reporting', ini_get('error_reporting') | E_USER_NOTICE);


define('GEOIP_DETECT_TEST_DB_FILENAME', dirname(__FILE__) . '/' . GEOIP_DETECT_DATA_FILENAME);
define('GEOIP_DETECT_TEST_IP', '88.64.140.3');
define('GEOIP_DETECT_TEST_IP_SERIVCE_PROVIDER', 'https://raw.githubusercontent.com/yellowtree/wp-geoip-detect/master/tests/html/ipv4.txt');

function geoip_detect_test_set_test_database()
{
	return GEOIP_DETECT_TEST_DB_FILENAME;
}

function geoip_detect_get_external_ip_adress_test_set_test_ip($ip) {
	return GEOIP_DETECT_TEST_IP;
}

class WP_UnitTestCase_GeoIP_Detect extends WP_UnitTestCase
{
	public function setUp() {
		// Use Test File
		add_filter('geoip_detect_get_abs_db_filename', 'geoip_detect_test_set_test_database', 101);		
	}
	
	public function tearDown() {
		remove_filter('geoip_detect_get_abs_db_filename', 'geoip_detect_test_set_test_database', 101);
	}
	
	public function testDatabaseLocation() {
		$filename = geoip_detect_get_abs_db_filename();
		$this->assertEquals(GEOIP_DETECT_TEST_DB_FILENAME, $filename, 'Database path is incorrect. Maybe parent::setUp() has not been called.');
	}
	
	protected function assertValidGeoIPRecord($record, $ip)
	{
		$assert_text = 'When looking up info for IP ' . $ip . ':';
		$this->assertInstanceOf('geoiprecord', $record, $assert_text);
		$this->assertInternalType('string', $record->country_code, $assert_text);
		$this->assertEquals(2, strlen($record->country_code), $assert_text);
		$this->assertEquals(3, strlen($record->country_code3), $assert_text);
		$this->assertEquals(2, strlen($record->continent_code), $assert_text);
		
		$properties = array('country_code', 'country_code3', 'country_name', 'latitude', 'longitude', 'continent_code');

		foreach ($properties as $name) {
			$this->assertObjectHasAttribute($name, $record);
		}
	}
	
	
/**
 * 
 * Enter description here ...
 * @param GeoIp2\Model\City $record
 * @param int $ip
 */
	protected function assertValidGeoIP2Record($record, $ip)
	{
		$assert_text = 'When looking up info for IP "' . $ip . '":';
		$this->assertInstanceOf('GeoIp2\Model\City', $record, $assert_text);
		$this->assertSame(false, $record->isEmpty);	
		
		$this->assertInternalType('string', $record->country->isoCode, $assert_text);
		$this->assertEquals(2, strlen($record->country->isoCode), $assert_text);
		$this->assertEquals(2, strlen($record->continent->code), $assert_text);
		$this->assertInternalType('array', $record->country->names, $assert_text);
	}
	
	protected function assertAtLeastTheseProperties($expected, $actual) {
		$checkObject = new stdClass;
		foreach ($expected as $name => $value) {
			$this->assertObjectHasAttribute($name, $actual);
			
			$checkObject->$name = $actual->$name;
		}
		
		$this->assertEquals($expected, $checkObject);
	}
} 
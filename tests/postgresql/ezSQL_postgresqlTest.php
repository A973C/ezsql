<?php
require_once('ez_sql_loader.php');

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * Test class for ezSQL_postgresql.
 * Generated by PHPUnit
 *
 * Needs database tear up to run test, that creates database and a user with
 * appropriate rights.
 * Run database tear down after tests to get rid of the database and the user.
 *
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @name    ezSQL_postgresql_tear_up
 * @uses    postgresql_test_db_tear_up.sql
 * @uses    postgresql_test_db_tear_down.sql
 * @package ezSQL
 * @subpackage Tests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */
class ezSQL_postgresqlTest extends TestCase {

    /**
     * constant string user name 
     */
    const TEST_DB_USER = 'ez_test';
    
    /**
     * constant string password 
     */
    const TEST_DB_PASSWORD = 'ezTest';
    
    /**
     * constant database name 
     */
    const TEST_DB_NAME = 'ez_test';
    
    /**
     * constant database host
     */
    const TEST_DB_HOST = 'localhost';
    
    /**
     * constant database port 
     */
    const TEST_DB_PORT = '5432';
    
    /**
     * @var ezSQL_postgresql
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
              'The PostgreSQL Lib is not available.'
            );
        }
        $this->object = new ezSQL_postgresql();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_postgresql::quick_connect
     */
    public function testQuick_connect() {
        $this->assertTrue($this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testQuick_connect

    /**
     * @covers ezSQL_postgresql::connect
     * 
     */
    public function testConnect() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testConnect

    /**
     * @covers ezSQL_postgresql::select
     */
    public function testSelect() {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        
        $this->assertTrue($this->object->select(self::TEST_DB_NAME));
    } // testSelect

    /**
     * @covers ezSQL_postgresql::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_postgresql::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('NOW()', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_postgresql::showTables
     */
    public function testShowTables() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $result = $this->object->showTables();
        
        $this->assertEquals('SELECT table_name FROM information_schema.tables WHERE table_schema = \'' . self::TEST_DB_NAME . '\' AND table_type=\'BASE TABLE\'', $result);
    } // testShowTables

    /**
     * @covers ezSQL_postgresql::descTable
     */
    public function testDescTable() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));
        
        $this->assertEquals(
                "SELECT ordinal_position, column_name, data_type, column_default, is_nullable, character_maximum_length, numeric_precision FROM information_schema.columns WHERE table_name = 'unit_test' AND table_schema='" . self::TEST_DB_NAME . "' ORDER BY ordinal_position",
                $this->object->descTable('unit_test')
        );
        
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testDescTable

    /**
     * @covers ezSQL_postgresql::showDatabases
     */
    public function testShowDatabases() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertEquals(
                "SELECT datname FROM pg_database WHERE datname NOT IN ('template0', 'template1') ORDER BY 1",
                $this->object->showDatabases()
        );
    } // testShowDatabases

    /**
     * @covers ezSQL_postgresql::query
     */
    public function testQuery() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));
        
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testQuery
    
    /**
     * @covers ezSQLcore::insert
     */
    public function testInsert()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);        
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->assertEquals($this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 2' )), 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
        
    /**
     * @covers ezSQLcore::update
     */
    public function testUpdate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);        
        $this->object->query('CREATE TABLE unit_test2(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test2', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->object->insert('unit_test2', array('id'=>'2', 'test_key'=>'test 2' ));
        $this->object->insert('unit_test2', array('id'=>'3', 'test_key'=>'test 3' ));
        $unit_test['test_key'] = 'testing';
        $where['id'] = '1';
        $this->assertEquals($this->object->update('unit_test2', $unit_test, $where), 1);
        $where['test_key'] = 'test 3';
        $where['id'] = '3';
        $this->assertEquals($this->object->update('unit_test2', $unit_test, $where), 1);
        $where['id'] = '2';
        $this->assertEquals($this->object->update('unit_test2', $unit_test, $where), 0);
        $where['test_key'] = 'test 2';
        $this->assertEquals($this->object->update('unit_test2', $unit_test, $where), 1);
    }
    
    /**
     * @covers ezSQLcore::delete
     */
    public function testDelete()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);        
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $unit_test['id'] = '1';
        $unit_test['test_key'] = 'test 1';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = '2';
        $unit_test['test_key'] = 'test 2';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = '3';
        $unit_test['test_key'] = 'test 3';
        $this->object->insert('unit_test', $unit_test );
        $where['id'] = '1';
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        $where['test_key'] = 'test 3';
        $where['id'] = '3';
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        $where['test_key'] = 'test 2';
        $this->assertEquals($this->object->delete('unit_test', $where), 0);
        $where['id'] = '2';
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
    }  
       
    /**
     * @covers ezSQLcore::showing
     */
    public function testShowing()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);        
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'testing 3' ));
        
        $result = $this->object->showing('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        
        $where['test_key'] = 'testing 2';
        $result = $this->object->showing('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $result = $this->object->showing('unit_test', 'test_key', array( 'id'=>'3' ));
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }
    }    
    
    /**
     * @covers ezSQL_postgresql::disconnect
     */
    public function testDisconnect() {
        $this->object->disconnect();
        
        $this->assertFalse($this->object->isConnected());
    } // testDisconnect

    /**
     * @covers ezSQL_postgresql::getDBHost
     */
    public function testGetDBHost() {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getDBHost());
    } // testGetDBHost

    /**
     * @covers ezSQL_postgresql::getPort
     */
    public function testGetPort() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        
        $this->assertEquals(self::TEST_DB_PORT, $this->object->getPort());
    } // testGetPort

} // ezSQL_postgresqlTest
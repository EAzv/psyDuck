<?php

use PHPUnit\Framework\TestCase;

/**
*
* @requires PHP 5.6
* @author Eduardo Azevedo eduh.azvdo@gmail.com
* 
* @coversDefaultClass psyDuck
*/
class psyDuckTest extends TestCase
{

	const DS = DIRECTORY_SEPARATOR;
	const FOLDER = __DIR__ . self::DS . 'storage';

	protected static $psy;
	protected static $test_content;


	public function __construct()
	{
		parent::__construct();

		// default file container for tests
		self::$test_content = self::FOLDER . self::DS . 'test_content.jsonl';
		if (file_exists(self::$test_content))
			unlink(self::$test_content);

		// defines the class
		self::$psy = new psyDuck\psyDuck();
		self::$psy->setContainer(self::FOLDER);
	}

	public function __destruct()
	{
		if (is_callable('parent::__destruct'))
			parent::__destruct();

		if (file_exists(self::$test_content))
			unlink(self::$test_content);

		if (is_dir(self::FOLDER))
			rmdir(self::FOLDER);
	}

	public function tearDown()
	{
		//sleep(1);
	}

	/**
	 * @covers in
	 */
	public function testIn()
	{
		// default container for tests
		self::$psy->in('test_content');

		// check if was created properly
		$this->assertFileExists( self::$test_content );
	}

	/**
	 * @covers insert
	 * @depends testIn
	 */
	public function testInsert()
	{
		//$this->testIn();

		// array to insert
		$to_insert = $this->generateArrayList(0,1);
		
		// file content expected to be like this
		$expected_content = $this->generateJsonList(0,1);
		
		// do insertion
		self::$psy->insert($to_insert);
		//die(print_r($to_insert, true));
		//die(print_r($expected_content, true));
		
		// Assert
		$this->assertStringEqualsFile( self::$test_content, $expected_content);
	}

	/**
	 * @covers insert
	 * @depends testInsert
	 */
	public function testInsertMultlines()
	{
		//$this->testInsert(); // prepare

		// array to insert
		$to_insert = $this->generateArrayList(1);

		// file content expected to be like this
		$expected_content  = $this->generateJsonList(0);
		
		// do insertion
		self::$psy->insert($to_insert, true);
		//die(print_r($to_insert, true));
		//die(print_r($expected_content, true));

		// Assert
		$this->assertStringEqualsFile( self::$test_content, $expected_content);
	}

	/**
	 * @covers get
	 * @depends testInsertMultlines
	 */
	public function testGet()
	{
		//$this->testInsertMultlines(); // prepare
		
		$expected_result_entire	= array( 'Segundo', 'chave'=>'valor2', 'numero'=>2016 );
		$expected_result_null = false;
		$expected_result_modified = array( 'nome'=>'Primeiro', 'chave'=>'valor1.0', 'numero'=>'2015', 'extra'=>date('d.m.Y') );

		//shold return the entire data
		$result_entire = self::$psy->get(function($data){
				if ($data['chave'] == 'valor2')	return $data;
			});
		
		//shold not find any data, and return false
		$result_null = self::$psy->get(function($data){
				if ($data['chave'] == 'inexistente')	return true;
			});

		// should find, modify and retur the data
		$result_modified = self::$psy->get(function($data){
				if ($data['chave'] == 'valor1'):
					$data['nome'] = $data[0];	unset($data[0]);
					$data['chave'] .= '.0';
					$data['numero'] = strval($data['numero']);
					$data['extra'] = date('d.m.Y');
					return $data;
				endif;
			});

		// expect the data entire, without modifications
		$this->assertEquals( $expected_result_entire, $result_entire );
		
		// expect false, cause didn't find anything
		$this->assertEquals( $expected_result_null, $result_null );

		// expect with modifications
		$this->assertEquals( $expected_result_modified, $result_modified );
	}

	/**
	 * @covers find, each and fetch
	 * @depends testInsertMultlines
	 */
	public function testFind()
	{
		//$this->testInsertMultlines(); // prepare

		##########################################################
		## tests the result as it is, without modificatios
		$expected_result_entire = $this->generateArrayList(0);
		$result_entire = array();
		foreach (self::$psy->find() as $value)
			$result_entire[] = $value;

		##########################################################
		## tests the result with modificatios
		$expected_result_modified = $this->generateArrayList(0);
		for ($i=0; $i < count($expected_result_modified); $i++)
			$expected_result_modified[$i]['chave'] .= ' conta:'.$i;
		$result_modified = array();
		$generator_modified = self::$psy->find(function($data){
				static $counter = -1; $counter++;
				$data['chave'] .= ' conta:'.$counter;
				return $data;
			});
		foreach ($generator_modified as $value)
			$result_modified[] = $value;

		##########################################################
		## tests a false result
		$expected_result_null = array();
		$result_null = array();
		$generator_null = self::$psy->find(function($data){
				if($data['chave'] == 'inexistente') return true;
			});
		foreach ($generator_null as $value)
			$result_null[] = $value;

		##########################################################
		## first test of filtered shearch
		$expected_result_res1 = $this->generateArrayList(3,9); // return values bettwen 3 and 9
		$result_res1 = array();
		$generator_res1 = self::$psy->find(function($data){
			// get the last value, who is a number
				if( substr( $data['chave'], 5) > 3 
				&&  substr( $data['chave'], 5) <= 9 ) return true;
			});
		foreach ($generator_res1 as $value)
			$result_res1[] = $value;

		##########################################################
		## Second test of filtered shearch
		#	                                                             jump the 4º index
		$expected_result_res2 = array_merge( $this->generateArrayList(0,3), $this->generateArrayList(4) );
		$result_res2 = array();
		$generator_res2 = self::$psy->find(function($data){
				if( $data['chave'] != 'valor4' ) return true;
			});
		foreach ($generator_res2 as $value)
			$result_res2[] = $value;

		##########################################################
		
		// expect the entire data, without modifications
		$this->assertEquals( $expected_result_entire, $result_entire);

		// expect data with modifications
		$this->assertEquals( $expected_result_modified, $result_modified);

		// expect a empty array as result
		$this->assertEquals( $expected_result_null, $result_null );

		// expect filtered result
		$this->assertEquals( $expected_result_res1, $result_res1 );
		$this->assertEquals( $expected_result_res2, $result_res2 );

	}

	/**
	 * @covers node
	 * @depends testInsertMultlines
	 */
	public function testNode()
	{
		//$this->testIn(); // prepare
		
		$expected_result_entire = $this->generateArrayList(0);
		$result_entire = self::$psy->node();

		$expected_result_modified = $this->generateArrayList(0);
		for ($i=0; $i < count($expected_result_modified); $i++)
			$expected_result_modified[$i]['chave'] = $i;
		$result_modified = self::$psy->node(function($data){
				static $count = -1; $count++;
				$data['chave'] = $count;
				return $data;
			});

		// expect the entire data, without modifications
		$this->assertEquals( $expected_result_entire, $result_entire);

		// expect data with modifications
		$this->assertEquals( $expected_result_modified, $result_modified);

	}

	/**
	 * @covers delete
	 * @depends testInsertMultlines
	 */
	public function testDelete()
	{
		//$this->testIn(); // prepare

		# delete only one line                                       jump the 3º index
		$expected_result_res1 = array_merge( $this->generateArrayList(0,2), $this->generateArrayList(3) );
		self::$psy->delete(function($data){
				if($data[0] == 'Terceiro') return true;
			});
		$result_res1 = self::$psy->node();

		# delete all
		$expected_result_res2 = array();
		self::$psy->delete(function($data){
				return true;
			});
		$result_res2 = self::$psy->node();

		$this->assertEquals( $expected_result_res1, $result_res1 );
		$this->assertEquals( $expected_result_res2, $result_res2 );

		// restore
		$this->testInsert(); // prepare
		$this->testInsertMultlines(); // prepare
	}

	/**
	 * @covers update
	 * @depends testDelete
	 */
	public function testUpdate()
	{
		//$this->testIn(); // prepare
		
		$expected_result_res1 = $this->generateArrayList(0);
		for ($i=0; $i < count($expected_result_res1); $i++)
			$expected_result_res1[$i]['chave'] = base64_encode($expected_result_res1[$i]['chave']);
		self::$psy->update(function($data){
				$data['chave'] = base64_encode($data['chave']);
				return $data;
			});
		$result_res1 = self::$psy->node();

		$this->assertEquals( $expected_result_res1, $result_res1);

		// restore
		self::$psy->delete(function(){ return true; });
		$this->testInsert(); // prepare
		$this->testInsertMultlines(); // prepare
	}

	/**
	 * @covers count
	 * @depends testInsertMultlines
	 */
	public function testCount()
	{
		//$this->testIn(); // prepare

		self::$psy->delete(function(){ return true; }); // prepare
		self::$psy->insert( $this->generateArrayList(0,5), true);

		$expected_result_res1 = 5;
		$result_res1 = self::$psy->count();

		$this->assertEquals( $expected_result_res1, $result_res1);

		// restore
		self::$psy->delete(function(){ return true; });
		$this->testInsert(); // prepare
		$this->testInsertMultlines(); // prepare
	}






######################################################################################
	
	const ORDINAL = [ 'Primeiro', 'Segundo', 'Terceiro', 'Quarto', 'Quinto', 'Sexto', 'Sétimo', 'Oitavo', 'Nono', 'Décimo', 'Décimo primeiro', 'Décimo segundo', 'Técimo terceiro', 'Décimo quarto', 'Décimo quinto' ];

	private function generateArrayList (int $first=0, int $last=15, bool $curby=false):array
	{
		$r_array = array();
		for ( $i = $first+1; $i <= $last; $i++)
			$r_array[] = array(self::ORDINAL[$i-1], 'chave'=>'valor'.($i), 'numero'=>2015+($i-1));
		return (count($r_array)>1 || $curby==true) ? $r_array : $r_array[0];
	}

	private function generateJsonList (int $first=0, int $last=15):string
	{
		$to_return = ""; $array_list = $this->generateArrayList($first, $last, true);
		foreach ($array_list as $_index)
			$to_return .= json_encode($_index).PHP_EOL;
		return $to_return;
	}
}

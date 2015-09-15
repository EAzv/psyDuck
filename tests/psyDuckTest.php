<?php

/**
*
* @requires PHP 5.6
* @author Eduardo Azevedo eduh.azvdo@gmail.com
* 
* @coversDefaultClass psyDuck
*/
class psyDuckTest extends PHPUnit_Framework_TestCase
{

	const DS = DIRECTORY_SEPARATOR;
	const FOLDER = __DIR__ . self::DS . 'storage';
	protected static $psy;
	protected $folder = self::FOLDER;


	public function test_create()
	{	
		// defines the class
		self::$psy = new psyDuck();
	}

	/**
	 * @covers setContainer
	 * @depends test_create
	 */
	public function test_setContainer()
	{
		self::$psy->setContainer(self::FOLDER);
	}

	/**
	 * @covers in
	 * @depends test_setContainer
	 */
	public function test_in()
	{
		// default container for tests
		self::$psy->in('test_content');
		// default file container for tests
		$this->test_content = self::FOLDER . self::DS . 'test_content.json';
		// check if was created properly
		$this->assertFileExists( $this->test_content );
	}

	/**
	 * @covers insert
	 * @depends test_in
	 */
	public function test_insert()
	{
		$this->test_in();

		// array to insert
		$to_insert = $this->generateArrayToinsert(0,1);
		
		// file content expected to be like this
		$expected_content = $this->generateContentToCompare(0,1);
		
		// do insertion
		self::$psy->insert($to_insert);
		
		// Assert
		$this->assertStringEqualsFile( $this->test_content, $expected_content);
	}

	/**
	 * @covers insert
	 * @depends test_insert
	 */
	public function test_insert_multlines()
	{
		$this->test_in(); // prepare

		// array to insert
		$to_insert = $this->generateArrayToinsert(1);

		// file content expected to be like this
		$expected_content  = $this->generateContentToCompare(0);
		
		// do insertion
		self::$psy->insert($to_insert, true);

		// Assert
		$this->assertStringEqualsFile( $this->test_content, $expected_content);
	}

	/**
	 * @covers insert
	 * @depends test_insert_multlines
	 */
	public function test_get()
	{
		$this->test_in(); // prepare
		
		$expected_result_entire	= array( 'Segundo', 'chave'=>'valor2', 'numero'=>2016 );
		$expected_result_null = false;
		$expected_result_modified = array( 'nome'=>'Primeiro', 'chave'=>'valor1.0', 'numero'=>'2015', 'extra'=>date('d.m.Y') );

		//shold return the entire data
		$result_entire = self::$psy->get(function($data){
				if ($data['chave'] == 'valor2')	return true;
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
	 * @covers find
	 * @depends test_insert_multlines
	 */
	public function test_find()
	{
		$this->test_in(); // prepare

		##########################################################
		## tests the result as it is, without modificatios
		$expected_result_entire = $this->generateArrayToinsert(0);
		$result_entire = array();
		foreach (self::$psy->find() as $value)
			$result_entire[] = $value;

		##########################################################
		## tests the result with modificatios
		$expected_result_modified = $this->generateArrayToinsert(0);
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
		$expected_result_res1 = $this->generateArrayToinsert(3,9); // return values bettwen 3 and 9
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
		$expected_result_res2 = array_merge( $this->generateArrayToinsert(0,3), $this->generateArrayToinsert(4) );

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
	 * function drop not yet implemented in psyDuck class, 
	 *    this is jus a prototype
	 * @depends test_create
	 */
	public function test_drop()
	{
		if (file_exists( self::FOLDER . self::DS . 'test_content.json')) {
			unlink( self::FOLDER . self::DS . 'test_content.json');
		}
	}


	######################################################################################
	
	const ORDINAL = [ 'Primeiro', 'Segundo', 'Terceiro', 'Quarto', 'Quinto', 'Sexto', 'Sétimo', 'Oitavo', 'Nono', 'Décimo', 'Décimo primeiro', 'Décimo segundo', 'Técimo terceiro', 'Décimo quarto', 'Décimo quinto' ];

	protected function generateArrayToinsert ($first=0, $last=15)
	{
		$r_array = array();

		for ( $i = $first+1; $i <= $last; $i++):
			$r_array[] = array( self::ORDINAL[$i-1], 'chave'=>'valor'.($i), 'numero'=>2015+($i-1) );
		endfor;

		if (count($r_array) > 1)
			return $r_array;
		else
			return $r_array[0];
	}

	protected function generateContentToCompare ($first=0, $last=15)
	{
		$to_return = "";
		for ( $i = $first+1; $i <= $last; $i++):
			$to_return .= '{"0":"'. str_replace('é', '\u00e9', self::ORDINAL[$i-1]) .'","chave":"valor'.$i.'","numero":'.(2015+($i-1)).'}'.PHP_EOL;
		endfor;
		return $to_return;
	}
}

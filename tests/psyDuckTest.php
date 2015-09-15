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
		$to_insert = $this->generateArrayToinsert(true);
		
		// file content expected to be like this
		$expected_content = $this->generateContentToCompare(true);
		
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
		$to_insert = $this->generateArrayToinsert(false);

		// file content expected to be like this
		$expected_content  = $this->generateContentToCompare(false);
		
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
				if ($data['chave'] == 'valor9')	return true;
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
		$expected_result_entire = $this->generateArrayToinsert(true,true);

		$result_entire = array();
		foreach (self::$psy->find() as $value)
			$result_entire[] = $value;

		$this->assertEquals( $expected_result_entire, $result_entire);

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


	protected function generateArrayToinsert ($first=false, $entire=false)
	{
		if( $first && !$entire ):
			return array( 'Primeiro', 'chave'=>'valor1', 'numero'=>2015 );
		elseif ( $first && $entire ):
			$result = array( $this->generateArrayToinsert(true) );
			foreach ($this->generateArrayToinsert(false) as $value )
				$result[] = $value;
			return $result;
		else:
			return array( 
					['Segundo', 'chave'=>'valor2', 'numero'=>2016],
					['Terceiro', 'chave'=>'valor3', 'numero'=>2017],
					['Quarto', 'chave'=>'valor4', 'numero'=>2018],
					['Quinto', 'chave'=>'valor5', 'numero'=>2019],
					['Sexto', 'chave'=>'valor6', 'numero'=>2020],
				);
		endif;
	}

	protected function generateContentToCompare($first=false)
	{
		if($first):
			return '{"0":"Primeiro","chave":"valor1","numero":2015}'.PHP_EOL;
		else:
			$toreturn  = '{"0":"Primeiro","chave":"valor1","numero":2015}'.PHP_EOL;
			$toreturn .= '{"0":"Segundo","chave":"valor2","numero":2016}'.PHP_EOL;
			$toreturn .= '{"0":"Terceiro","chave":"valor3","numero":2017}'.PHP_EOL;
			$toreturn .= '{"0":"Quarto","chave":"valor4","numero":2018}'.PHP_EOL;
			$toreturn .= '{"0":"Quinto","chave":"valor5","numero":2019}'.PHP_EOL;
			$toreturn .= '{"0":"Sexto","chave":"valor6","numero":2020}'.PHP_EOL;
			return $toreturn;
		endif;
	}
}

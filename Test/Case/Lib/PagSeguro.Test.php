<?php
App::uses('PagSeguro', 'PagSeguro.Lib');
App::uses('TransactionStatuses', 'PagSeguro.Lib/Map');

class PagSeguroTestCase extends CakeTestCase {

	/**
	* setUp
	*
	* @retun void
	* @access public
	*/
	public function setUp()
	{
		parent::setUp();

		Configure::write('PagSeguro', null);
		$this->PagSeguro = new PagSeguro();
	}

	public function tearDown()
	{
		$this->PagSeguro = null;
	}

	/**
	 * @expectedException PagSeguroException
	 * @expectedExceptionMessage Erro de configuração - Atributo 'email' não definido.
	 */
	public function testIncompleteConfig() {
		$this->PagSeguro->config(array(
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	public function testConfig() {
		$this->PagSeguro->config(array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));

		$this->assertEqual($this->PagSeguro->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	public function testGlobalConfig() {
		Configure::write('PagSeguro', array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
			)
		);

		$this->PagSeguro = new PagSeguro();

		$this->assertEqual($this->PagSeguro->config(), array(
			'email' => 'email@email.com',
			'token' => '3C2B7B8F25EB42648516DAF4BCF49953'
		));
	}

	public function testGetStatusName() {
		$this->assertSame('Situação inválida', PagSeguro::getStatusName(999));
		$this->assertEquals(TransactionStatuses::$map[1], PagSeguro::getStatusName(1));
	}
}

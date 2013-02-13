<?php
App::uses('Validation', 'Utility');

/**
 * Regras de validação definidas pela API do PagSeguro
 *
 * PHP versions 5.3+
 * Copyright 2010-2013, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author	 	 Felipe Theodoro Gonçalves
 * @author       Cauan Cabral
 * @link         https://github.com/ftgoncalves/pagseguro/
 * @license      MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version      2.2
 */

/**
 * PagSeguroValidation
 *
 * @package       PagSeguro.Lib
 */
class PagSeguroValidation
{

	public static $lastError = null;

/**
 * Checa o nome do remetente de acordo com
 * as regras do PagSeguro
 *
 * @param string $check The value to check.
 * @return boolean
 */
	public static function name($check)
	{
		if(!self::genericValidate($check, 50)) {
			return false;
		}

		$parts = explode(' ', $check);

		if (count($parts) < 2) {
			return false;
		}

		foreach ($parts as $part) {
			if (!Validation::alphaNumeric($part)) {
				return false;
			}
		}

		return self::success();
	}

/**
 * Checa se o email corresponde ao formato esperado
 * pela API do PagSeguro.
 *
 * @param  string $check Email
 * @return boolean
 */
	public static function email($check)
	{
		return self::genericValidate($check, 60);
	}

/**
 * Checa se o token corresponde ao formato esperado
 * pela API do PagSeguro.
 *
 * @param  string $check Token
 * @return boolean
 */
	public static function token($check)
	{
		return self::genericValidate($check, 32);
	}

/**
 * Método genérico para validação que verifica se o campo
 * não está vazio e se respeita um determinado limite de
 * tamanho.
 *
 * @param  string $value   Valor do campo que será checado
 * @param  int    $maxSize Tamanho máximo permitido para o campo
 * @return boolean
 */
	protected static function genericValidate($value, $maxSize)
	{
		if (empty($value)) {
			self::$lastError = __("Atributo ':attr:' está vazio.");
			return false;
		}

		if (strlen($value) > $maxSize) {
			self::$lastError = __("Atributo ':attr:' possuí tamanho maior que o permitido ({$maxSize}).");
			return false;
		}

		return self::success();
	}

/**
 * Método auxiliar para limpar o histórico de erros e retornar
 * true, usado quando uma validação é bem sucedida.
 *
 * @return boolean
 */
	protected static function success()
	{
		self::$lastError = null;
		return true;
	}
}

<?php


namespace PayMayaNexGen\Payment\Model;

use PayMayaNexGen\Payment\Model\Ui\TestBitch;

class DefaultBitch extends TestBitch
{
    const METHOD_CODE = 'paymayanexgen_payment';

    protected $_code = self::METHOD_CODE;
}

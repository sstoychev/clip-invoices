<?php
/**
 * Class to handle the index
 *
 * PHP version 7
 *
 * @category PHP
 * @package  Clippings
 * @author   Stoycho Stoychev <sstoychev@sstoychev.name>
 * @license  MIT
 * @version  GIT: 124
 * @link     -
 */


namespace App\Clippings\Constants;

/**
 * Class to handle the index
 *
 * @category PHP
 * @package  Clippings
 * @author   Stoycho Stoychev <sstoychev@sstoychev.name>
 * @license  MIT
 * @version  Release: @package_version@
 * @link     -
 */

class Constants
{
    const COLUMN_CUSTOMER = 'Customer';
    const COLUMN_VAT = 'Vat number';
    const COLUMN_DOCUMENT = 'Document number';
    const COLUMN_TYPE = 'Type';
    const COLUMN_PARENT = 'Parent document';
    const COLUMN_CURRENCY = 'Currency';
    const COLUMN_TOTAL = 'Total';

    const CURRNECY_PREFIX = 'currency-';

    const TYPE_INVOICE = 1;
    const TYPE_CREDIT = 2;
    const TYPE_DEBIT = 3;

    const OUTPUT_CURRENCY = 'output_currency';
    const FILE_TO_UPLOAD = 'fileToUpload';
    const CUSTOMER_FILTER = 'customer_filter';
}

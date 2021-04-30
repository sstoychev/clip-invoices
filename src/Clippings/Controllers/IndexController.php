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


namespace App\Clippings\Controllers;

use App\Application\Settings\SettingsInterface;
use App\Clippings\Constants\Constants as C;
use Slim\Psr7\Response;

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

class IndexController extends Base
{
    protected $template = './../templates/index.mustache';
    /**
     * Show the index page
     *
     * @param Request  $request  -
     * @param Response $response -
     * @param array    $args     -
     *
     * @return Response
     */
    public function indexGet($request, $response, $args)
    {
        global $app;
        $container = $app->getContainer();
        $settings = $container->get(SettingsInterface::class);
        $currencies = $settings->get('currencies');
        $data = [
            'currencies' => $currencies,
            'currency_prefix' => C::CURRNECY_PREFIX,
            'output_currency' => C::OUTPUT_CURRENCY,
            'fileToUpload' => C::FILE_TO_UPLOAD,
            'customer_filter' => C::CUSTOMER_FILTER,
        ];
        $this->data = $data;
        $response->getBody()->write($this->render());
        return $response;
    }
    /**
     * Process the data
     *
     * @param Request  $request  -
     * @param Response $response -
     * @param array    $args     -
     *
     * @return Response
     */
    public function indexPost($request, $response, $args)
    {
        $params = $request->getParsedBody();
        list($errors, $exchangeRates) = $this->validateParams($params);

        if (!empty($errors)) {
            $response->getBody()->write('Error: ' . implode(',', $errors));
            return $response;
        }

        $uploadedFiles = $request->getUploadedFiles();
        if (!isset($uploadedFiles[C::FILE_TO_UPLOAD]) ||
            $uploadedFiles[C::FILE_TO_UPLOAD]->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write('Error: No file uploaded');
            return $response;
        }

        $output = '';
        global $app;
        $container = $app->getContainer();
        $settings = $container->get(SettingsInterface::class);
        $requiredHeaders = $settings->get('csv_headers');
        $currencies = $settings->get('currencies');

        $fileContent =  array_map('str_getcsv', file($_FILES[C::FILE_TO_UPLOAD]['tmp_name']));
        $headers = $fileContent[0];
        unset($fileContent[0]); // remove the fist element with the headers so we have only data

        // validate the file
        list($missingHeaders, $headerIndexes) = $this->validateHeaders($headers, $requiredHeaders);
        if (!empty($missingHeaders)) {
            $response->getBody()->write('Error: ' . implode(',', $missingHeaders));
            return $response;
        }

        $dataErrors = $this->validateData($fileContent, $headerIndexes, $currencies);
        if (!empty($dataErrors)) {
            $response->getBody()->write('Error: ' . implode(',', $dataErrors));
            return $response;
        }

        // check if we are good to go
        $total = $this->calcTotal($fileContent, $headerIndexes, $params, $exchangeRates);
        $output = $total . ' ' . $params[C::OUTPUT_CURRENCY];
        $response->getBody()->write($output);
        return $response;
    }
    /**
     * Validate the post parameters
     *
     * @param array $params -
     *
     * @return array
     */
    private function validateParams($params)
    {
        $errors = [];
        if (!isset($params[C::OUTPUT_CURRENCY]) || !$params[C::OUTPUT_CURRENCY]) {
            $errors[] = ' Output currency is required';
        }
        // validate currencies
        $exchangeRates = [];
        $currencyPrefix = 'currency-';
        $prefixLen = strlen($currencyPrefix);
        $mainCurrency = 0;
        foreach ($params as $key => $value) {
            if (substr($key, 0, $prefixLen) == $currencyPrefix) {
                $floatVal = floatval($value);
                if ($floatVal == 0) {
                    $errors[] = 'Incorrect value for '.$key;
                }
                if ($floatVal > 0.99 && $floatVal < 1.01) {
                    $mainCurrency++;
                }
                $exchangeRates[substr($key, $prefixLen)] = $floatVal;
            }
        }

        if ($mainCurrency != 1) {
            $errors[] = 'There should be one and only one currency with exchange rate 1';
        }
        return [$errors, $exchangeRates];
    }
    /**
     * Validate if all required headers are present
     *
     * @param array $headers -
     *
     * @return array
     */
    private function validateHeaders($headers, $requiredHeaders)
    {
        $missing = [];
        $headerIndexes = [];
        foreach ($requiredHeaders as $header) {
            $index = array_search($header, $headers);
            if ($index === false) {
                $missing[] = $header;
            } else {
                $headerIndexes[$header] = $index;
            }
        }
        return [$missing, $headerIndexes];
    }
    /**
     * Validate if all parents are correct
     *
     * @param array $headers -
     *
     * @return array
     */
    private function validateData($fileContent, $headerIndexes, $currencies)
    {
        $currencyColumn  = $headerIndexes[C::COLUMN_CURRENCY];
        $documentColumn = $headerIndexes[C::COLUMN_DOCUMENT];
        $parentColumn = $headerIndexes[C::COLUMN_PARENT];

        $dataErrors = [];
        $documents = [];
        $parentDocuments = [];
        foreach ($fileContent as $line) {
            $currency = $line[$currencyColumn];
            $document = $line[$documentColumn];
            $parentDocument = $line[$parentColumn];

            if (!in_array($currency, $currencies)) {
                $dataErrors[] = ' Unknown currency' . $currency;
            }
            $documents[] = $document;

            if ($parentDocument != '') {
                $parentDocuments[] = $parentDocument;
            }
        }
        if (count(array_intersect($parentDocuments, $documents)) != count($parentDocuments)) {
            $dataErrors[] = 'Invalid parent fields';
        }

        return $dataErrors;
    }

    private function calcTotal($fileContent, $headerIndexes, $params, $exchangeRates)
    {
        $customerFilter = $params[C::CUSTOMER_FILTER];
        $outputCurrency = $params[C::OUTPUT_CURRENCY];

        $typeColumn  = $headerIndexes[C::COLUMN_TYPE];
        $totalColumn = $headerIndexes[C::COLUMN_TOTAL];
        $vatColumn = $headerIndexes[C::COLUMN_VAT];
        $currencyColumn = $headerIndexes[C::COLUMN_CURRENCY];
        $total = 0; // total in default currency
        foreach ($fileContent as $line) {
            if ($customerFilter && $line[$vatColumn] != $customerFilter) {
                continue;
            }
            // depending on the document we will add or substract
            $typeCoef = 1;
            if ($line[$typeColumn] == C::TYPE_CREDIT) {
                $typeCoef = -1;
            }
            // get the exchange rate to the default currency
            $currncyCoef = $exchangeRates[$line[$currencyColumn]];
            $total += ($typeCoef * $currncyCoef * (float) $line[$totalColumn]);
        }

        // convert total to the output currency
        $total *= $exchangeRates[$outputCurrency];
        $output = 'Total:'. $total;
        return $output;
    }
}

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
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['fileToUpload'];
        $output = 'ERROR';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $fileContent =  array_map('str_getcsv', file($_FILES['fileToUpload']['tmp_name']));
            $output = '<pre>'.print_r($fileContent, true).'</pre><br />';
            $headers = $fileContent[0];
            // validate the file
            list($missingHeaders, $headerIndexes) = $this->validateHeaders($headers);
            if ($missingHeaders) {
                $output .= 'ERROR: missing columns:'.implode(',', $missingHeaders);
            }
            $missingParents = $this->validateParent($fileContent, $headerIndexes);
            $missingCurrencies = $this->validateCurrencies($fileContent, $headerIndexes);
            // check if we are good to go
            if (!$missingHeaders && !$missingParents && !$missingCurrencies) {
                $this->calcTotal($fileContent, $headerIndexes);
            }
        }
        $response->getBody()->write($output);
        return $response;
    }
    /**
     * Validate if all required headers are present
     *
     * @param array $headers -
     *
     * @return array
     */
    private function validateHeaders($headers)
    {
        global $app;
        $container = $app->getContainer();
        $settings = $container->get(SettingsInterface::class);
        $requiredHeaders = $settings->get('csv_headers');

        $missing = [];
        $headerIndexes = [];
        foreach ($requiredHeaders as $header) {
            $index = array_search($header, $headers);
            if ($index === false) {
                $missing[] = $header;
            }
        }
        return ['missing' => $missing, 'headerIndexes' => $headerIndexes];
    }
    /**
     * Validate if all parents are correct
     *
     * @param array $headers -
     *
     * @return array
     */
    private function validateParent($fileContent, $headerIndexes)
    {
        return []; // TODO(Stoycho)
    }
    /**
     * Validate if all parents are correct
     *
     * @param array $headers -
     *
     * @return array
     */
    private function validateCurrencies($fileContent, $headerIndexes)
    {
        return []; // TODO(Stoycho)
    }

    private function calcTotal($fileContent, $headerIndexes)
    {
        $typeInvoice = 1; // constant
        $typeCredit = 2; // constant
        $typeDebit = 3; // constant
        $typeColumn  = $headerIndexes['Type'];
        $totalColumn = $headerIndexes['Total'];
        $total = 0;
        for ($i=1; $i<count($fileContent); $i++) {
            if ($fileContent[$i][$typeColumn] == $typeInvoice) {
                $total += (float) $fileContent[$i][$totalColumn];
            }
        }
        $output = 'Total:'. $total;
        return $output;
    }
}

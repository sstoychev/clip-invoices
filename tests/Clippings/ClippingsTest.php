<?php
declare(strict_types=1);

namespace Tests\Clippings;

use App\Clippings\Controllers\IndexController;
use DI\Container;
use Tests\TestCase;

class ClippingsTest extends TestCase
{

    public function getSimulatedRequest($data)
    {
        return new class($data) {
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function getParsedBody()
            {
                return $this->data;
            }
            public function getUploadedFiles()
            {
                return [];
            }
        };
    }

    public function getSimulatedResponse()
    {
        return new class {
            public function __construct()
            {
                $this->body = new class {
                    public function write($msg)
                    {
                        $this->body = $msg;
                    }
                    public function read()
                    {
                        return $this->body;
                    }

                };
            }
            public function getBody()
            {
                return $this->body;
            }
        };
    }

    public function testNoFileUpload()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $data = [
            'currency-EUR' => 1,
            'currency-USD' => 1.21,
            'currency-GBP' => 0.87,
            'currency-BGN' => 1.95,
            'output_currency' => 'BGN',
            'customer_filter' => 123456789,
        ];

        $simulatedRequest = $this->getSimulatedRequest($data);

        $similatedResponse = $this->getSimulatedResponse();

        $indexController = new IndexController($container);

        $response = $indexController->indexPost($simulatedRequest, $similatedResponse, null);
        $responseBody = $response->getBody();

        $this->assertEquals('Error: No file uploaded', $responseBody->body);
    }
    public function testIncorrectParams()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $indexController = new IndexController($container);
        $data = [
            'currency-EUR' => 1,
            'currency-USD' => 1,
            'currency-GBP' => 0.87,
            'currency-BGN' => 1.95,
            'output_currency' => 'BGN',
            'customer_filter' => 123456789,
        ];

        $simulatedRequest = $this->getSimulatedRequest($data);

        $similatedResponse = $this->getSimulatedResponse();

        $indexController = new IndexController($container);

        $response = $indexController->indexPost($simulatedRequest, $similatedResponse, null);
        $responseBody = $response->getBody();

        $this->assertEquals('Error: There should be one and only one currency with exchange rate 1', $responseBody->body);
    }
}

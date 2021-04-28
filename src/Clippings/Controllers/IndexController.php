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
    }
}

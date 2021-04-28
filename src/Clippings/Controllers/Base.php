<?php
/**
 * Base class for all controllers
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

use Psr\Container\ContainerInterface;

/**
 * Base class for all controllers
 *
 * @category PHP
 * @package  Clippings
 * @author   Stoycho Stoychev <sstoychev@sstoychev.name>
 * @license  MIT
 * @version  Release: @package_version@
 * @link     -
 */

abstract class Base
{
    protected $container;
    protected $template = '';
    protected $paging_template = 'templates/pages.mustache';
    protected $data = [];
    protected $pages = 0;

    /**
     * constructor receives container instance
     * https://www.slimframework.com/docs/v4/objects/routing.html#container-resolution
     *
     * @param ContainerInterface $container -
     *
     * @return return_type
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * This function will render the template and add paging
     *
     * @return string
     */
    public function render()
    {
        // generate the body
        $m = $this->container->get('mustache');
        if (!file_exists($this->template)) {
            throw  new \Exception('Cannot find '.getcwd().$this->template);
        }
        $body = $m->render(file_get_contents($this->template), $this->data);

        return $body;
    }
    /**
     * Get key/values for csrf
     *
     * @param Request $request -
     *
     * @return return_type
     */
    public function getCsfr($request)
    {
        $csrf = $this->container->get('csrf');
        $nameKey = $csrf->getTokenNameKey();
        $valueKey = $csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        return [
            'csrfNameKey' => $nameKey,
            'csrfValueKey' => $valueKey,
            'csrfName' => $name,
            'csrfValue' => $value
        ];
    }
}

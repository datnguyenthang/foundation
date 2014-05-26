<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Request;

/**
 * FuelPHP Request base class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
abstract class Base
{
	/**
	 * @var  Application  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  RequestInjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $request = '';

	/**
	 * @var  \Fuel\Foundation\Input
	 *
	 * @since  2.0.0
	 */
	protected $input;

	/**
	 * @var  \Fuel\Foundation\Uri
	 */
	protected $uri;

	/**
	 * @var  array  associative array of named params in the URI
	 *
	 * @since  1.0.0
	 */
	protected $params = array();

	/**
	 * @var  Response  Response after execution
	 *
	 * @since  1.0.0
	 */
	protected $response;

	/**
	 * @var  Route  Current route
	 *
	 * @since  1.0.0
	 */
	protected $route;

	/**
	 * Constructor
	 *
	 * @param  string  $resource
	 * @param  array|Input  $input
	 *
	 * @since  1.0.0
	 */
	public function __construct($app, $resource = '', $inputInstance = null, RequestInjectionFactory $factory)
	{
		// store the current application
		$this->app = $app;

		// store the injecttion factory
		$this->factory = $factory;

		// store the requests input container
		$this->input = $inputInstance;

		// store the request
		$this->request = $resource;
	}

	/**
	 * Execute the request
	 *
	 * @return  Request
	 * @throws  \Exception
	 * @throws  \DomainException
	 *
	 * @since  1.0.0
	 */
	abstract public function execute();

	/**
	 * Returns this requests Application instance
	 *
	 * @return  Application
	 *
	 * @since  1.1.0
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Sets this requests Application instance
	 *
	 * @param  Application  $app
	 *
	 * @since  1.1.0
	 */
	public function setApplication($app)
	{
		$this->app = $app;
	}

	/**
	 * Fetch a named parameter from the request
	 *
	 * @param   null|string  $param
	 * @param   mixed        $default
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getParam($param = null, $default = null)
	{
		if (is_null($param))
		{
			return $this->params;
		}

		return isset($this->params[$param]) ? $this->params[$param] : $default;
	}

	/**
	 * Fetch the request response after execution
	 *
	 * @return  \Fuel\Foundation\Response
	 *
	 * @since  1.0.0
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Returns this requests Input instance
	 *
	 * @return  Input
	 *
	 * @since  1.1.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Returns this requests current active Route
	 *
	 * @return  Route
	 *
	 * @since  1.1.0
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Returns this requests current Uri object
	 *
	 * @return  Uri
	 *
	 * @since  1.1.0
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Allows setting a response object for errors or executing a fallback
	 *
	 * @param   \Fuel\Foundation\Exception\Base  $e
	 * @return  \Fuel\Foundation\Response
	 * @throws  \Fuel\Foundation\Exception\Base
	 */
	protected function errorResponse(Exception\Base $e)
	{
		throw $e;
	}
}

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

use Fuel\Foundation\Exception\NotFound;

/**
 * FuelPHP cli URI Request class
 *
 * executes a request to a cli task
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Cli extends Base
{
	/**
	 * @var  \Fuel\Config\Container
	 *
	 * @since  2.0.0
	 */
	protected $config;

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
		parent::__construct($app, $resource, $inputInstance, $factory);

		// store this requests config container
		$this->config = $app->getConfig();

		// make sure the request has the correct format
		$this->request  = '/'.trim(strval($resource), '/');
	}

	/**
	 * Returns this requests current Config object
	 *
	 * @return  Uri
	 *
	 * @since  1.1.0
	 */
	public function getConfig()
	{
		return $this->config;
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
	public function execute()
	{
		$this->factory->setActiveRequest($this);

		// log the request
		$this->log->info('Executing request');

		// get a route object for this request
		$this->route = $this->translate($this->request, $this->input->getMethod() );

		// log the request destination
		$this->log->info($this->route->method.' request routed to '.(is_callable($this->route->translation) ? 'Closure' : $this->route->translation));

		// store the request parameters
		$this->params = array_merge($this->params, $this->route->parameters);

		// push any remaining segments so they'll be available as action arguments
		if ( ! empty($this->route->segments))
		{
			$this->route->parameters = array_merge($this->route->parameters, $this->route->segments);
		}

		if (empty($this->route->controller))
		{
			throw new NotFound('No route match has been found for this request.');
		}

		if (is_callable($this->route->controller))
		{
			$controller = $this->route->controller;
		}
		else
		{
			$controller = $this->factory->createControllerInstance($this->route->controller);
		}

		if ( ! is_callable($controller))
		{
			throw new NotFound('The Task returned by routing is not callable. Does it extend a base task?');
		}

		// push the route so we have access to it in the task
		array_unshift($this->route->parameters, $this->route);

		// add the root path to the config, lang and view manager objects
		if (isset($this->route->path))
		{
			$this->app->getViewManager()->getFinder()->addPath($this->route->path);
			$this->config->addPath($this->route->path.'config'.DS);
			$this->app->getLanguage()->addPath($this->route->path.'lang'.DS.$this->config->get('lang.fallback', 'en').DS);
		}

		try
		{
			$this->response = call_user_func($controller, $this->route->parameters);
		}
		catch (Exception\Base $e)
		{
			$this->response = $this->errorResponse($e);
		}
		catch (\Exception $e)
		{
			// log the request termination
			$this->log->info('Request executed, but failed: '.$e->getMessage());

			// rethrow
			throw $e;
		}

		// remove the root path to the config, lang and view manager objects
		if (isset($this->route->path))
		{
			$this->app->getLanguage()->removePath($this->route->path.'lang'.DS.$this->config->get('lang.fallback', 'en').DS);
			$this->config->removePath($this->route->path.'config'.DS);
			$this->app->getViewManager()->getFinder()->removePath($this->route->path);
		}

		// log the request termination
		$this->log->info('Request executed');

		$this->factory->resetActiveRequest();

		return $this;
	}

	/**
	 * Find a route for the given Uri and request method
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public function translate($uri, $method)
	{
		// resolve the route
		$route = $this->router->translate($uri, $method);

		// create a URI object
		$this->uri = $this->factory->createUriInstance($route->uri);

		// is the route target a closure?
		if ($route->translation instanceOf \Closure)
		{
			$route->controller = $route->translation;
		}
		else
		{
			// find a match
			foreach ($this->app->getNamespaces() as $namespace)
			{
				// skip non-routeable namespaces
				if ( ! $namespace['routeable'] and $this->factory->isMainRequest())
				{
					continue;
				}

				// skip if we don't have a prefix match
				if ($namespace['prefix'] and strpos($route->translation, $namespace['prefix']) !== 0)
				{
					continue;
				}

				$route->setNamespace($namespace['namespace']);

				// get the segments from the translated route
				$segments = explode('/', ltrim(substr($route->translation, strlen($namespace['prefix'])),'/'));

				$arguments = array();
				while(count($segments))
				{
					$class = $route->namespace.'Task\\'.implode('\\', array_map('ucfirst', $segments));
					if (class_exists($class))
					{
						$route->path = $namespace['path'];
						$route->controller = $class;
						break;
					}
					array_unshift($arguments, array_pop($segments));
				}

				// did we find a match
				if ($route->controller)
				{
					// then stop looking
					break;
				}
			}
		}

		// any segments left?
		if ( ! empty($segments))
		{
			$route->action = ucfirst(array_shift($arguments));
		}

		// more? return them as additional segments
		$route->segments = empty($arguments) ? array() : $arguments;

		return $route;
	}
}

<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * LibraryProvider base class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class LibraryProvider
{
	/**
	 * @var  Fuel\Dependency\Container  Fuel's DiC
	 */
	protected $dic;

	/**
	 * @var  string  base namespace of this package
	 */
	protected $namespace;

	/**
	 * @var  array  array of paths defined for this namespace
	 */
	protected $paths = array();

	/**
	 * Class constructor, initialize the package, check for existence of a
	 * bootstrap file, and if found, process it
	 *
	 * @param
	 *
	 * @since 2.0.0
	 */
	public function __construct($dic, $namespace, Array $paths = array())
	{
		// store the DiC
		$this->dic = $dic;

		// normalize the namespace and store it
		$this->namespace = trim($namespace, '\\').'\\';

		// check and normalize the paths, and store them
		foreach ($paths as $path)
		{
			if ($path = realpath($path))
			{
				$this->paths[] = $path.DS;
			}
		}
	}

	/**
	 * Library's initialization method. This method is called as soon as the library
	 * is initially loaded, either by the framework bootstrap, or when you manually
	 * load a new library into the autoloader using the Application's getLibrary() method.
	 *
	 * @since 2.0.0
	 */
	public function initialize()
	{
	}

	/**
	 * Library enabler method.
	 *
	 * When you instruct your application to use the library, this enabler gets
	 * called. You can use it to prep the application for use of the library.
	 * By default, a loaded library is disabled.
	 *
	 * @param  Application  $app  The application instance that wants to enable this library
	 *
	 * @since 2.0.0
	 */
	public function enable($app)
	{
	}

	/**
	 * Library disabler method.
	 *
	 * When you instruct your application to unload a library, this disabler gets
	 * called. You can use it to cleanup any setup the library has made in the
	 * application that was using it.
	 *
	 * @param  Application  $app  The application instance that had enabled this library
	 *
	 * @since 2.0.0
	 */
	public function disable($app)
	{
	}
}

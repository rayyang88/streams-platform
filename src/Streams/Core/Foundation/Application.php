<?php namespace Streams\Platform\Foundation;

use Composer\Autoload\ClassLoader;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Request;
use Streams\Platform\Foundation\Model\ApplicationModel;

class Application
{
    /**
     * The application reference.
     *
     * @var null
     */
    protected $appRef = null;

    /**
     * Keep installed status around.
     *
     * @var null
     */
    protected $installed = null;

    /**
     * Create a new Application instance
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->apps = new ApplicationModel();
    }

    /**
     * Boot the application environment.
     */
    public function boot($appRef = null)
    {
        if ($appRef) {
            $this->appRef = $appRef;
        } else {
            if (!$this->isLocated()) {
                $this->locate();
            }
        }

        $this->setTablePrefix();
        $this->registerEntryModels();
        $this->registerAddons();
    }

    /**
     * Set the database table prefix going forward.
     * We really don't need a core table from here on out.
     */
    public function setTablePrefix()
    {
        \Schema::getConnection()->getSchemaGrammar()->setTablePrefix($this->tablePrefix());
        \Schema::getConnection()->setTablePrefix($this->tablePrefix());
    }

    /**
     * Register entry models generated by streams.
     */
    protected function registerEntryModels()
    {
        $this->app['streams.classloader']->addPsr4(
            'Streams\Platform\Model\\',
            base_path('storage/models/streams/' . $this->getAppRef())
        );

        $this->app['streams.classloader']->register();
    }

    /**
     * Register all of our addon types.
     */
    protected function registerAddons()
    {
        \App::make('streams.addon_types')->boot($this->app);
    }

    /**
     * Locate the app by request or passed variable and set the application reference.
     *
     * @return bool
     */
    public function locate($domain = null)
    {
        if (\Schema::hasTable('apps')) {
            if (!$this->appRef) {
                if (!$domain) {
                    $domain = Request::root();
                }

                if ($app = $this->apps->findByDomain($domain)) {

                    $this->installed = true;

                    $this->appRef = $app->reference;

                    return true;
                }

                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get the current app ref
     *
     * @return null
     */
    public function getAppRef()
    {
        return $this->appRef;
    }

    /**
     * Return the app reference.
     *
     * @return string
     */
    public function tablePrefix()
    {
        if (!$this->appRef) {
            $this->locate();
        }

        return $this->appRef . '_';
    }

    /**
     * Is the application installed?
     *
     * @return bool
     */
    public function isInstalled()
    {
        return ($this->installed or $this->locate());
    }

    /**
     * Has the application already been located?
     *
     * @return null
     */
    protected function isLocated()
    {
        return $this->installed;
    }

    /**
     * Does the installer directory exist?
     *
     * @return bool
     */
    public function installerExists()
    {
        return (is_dir(base_path('installer')));
    }
}

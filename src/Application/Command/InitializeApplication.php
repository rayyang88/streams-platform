<?php namespace Anomaly\Streams\Platform\Application\Command;

use Anomaly\Streams\Platform\Application\Application;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Foundation\Application as Laravel;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class InitializeApplication
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Application\Command
 */
class InitializeApplication implements SelfHandling
{

    /**
     * Handle the command.
     *
     * @param Application $application
     */
    public function handle(Application $application, Laravel $laravel)
    {
        $app = env('APPLICATION_REFERENCE', 'default');

        if ($laravel->runningInConsole()) {

            $app = (new ArgvInput())->getParameterOption('--app', $app);

            $laravel->bind('path.public', function() use ($laravel) {

                if ($path = env('PUBLIC_PATH')) {
                    return base_path($path);
                }

                // Check default path.
                if (file_exists($path = base_path('public/index.php'))) {
                    return dirname($path);
                }

                // Check common alternative.
                if (file_exists($path = base_path('public_html/index.php'))) {
                    return dirname($path);
                }

                return base_path('public');
            });
        }

        /**
         * Set the reference to our default first.
         * When in a dev environment and working
         * with Artisan this the same as locating.
         */
        $application->setReference($app);

        /**
         * If the application is installed
         * then locate the application and
         * initialize.
         */
        if ($application->isInstalled()) {

            if (env('DB_DRIVER')) {
                $application->locate();
                $application->setup();
            }

            return;
        }

        /**
         * If we're not installed just
         * assume default for now.
         */
        $application->setReference('default');
    }
}

<?php

namespace SteemPress;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Application extends SilexApplication
{
    private $rootDir;
    private $env;

    public function __construct($env)
    {
        $this->rootDir = __DIR__.'/../../';
        $this->env = $env;

        parent::__construct();

        $app = $this;

        // Override these values in resources/config/prod.php file
        $app['var_dir'] = $this->rootDir.'/var';
        $app['locale'] = 'en';
        $app['steemd'] = 'https://steem.steemstats.com';
        $app['http_cache.cache_dir'] = $app->share(function (Application $app) {
            return $app['var_dir'].'/http';
        });
        $configFile = sprintf('%s/resources/config/%s.php', $this->rootDir, $env);
        if (!file_exists($configFile)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $configFile));
        }
        require $configFile;

        $app->register(new HttpCacheServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new FormServiceProvider());
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new DoctrineServiceProvider());
        $app->register(new SteemProvider(), [
          'steemd.host' => $app['steemd']
        ]);

        $app->register(new TranslationServiceProvider());
        $app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', $this->rootDir.'/resources/translations/fr.yml', 'fr');
            return $translator;
        }));

        $app->register(new TwigServiceProvider(), array(
            'twig.options' => array(
                'cache' => $app['var_dir'].'/cache/twig',
                'strict_variables' => true,
            ),
            'twig.path' => array($this->rootDir.'/resources/templates/'.$app['theme']),
        ));
        $app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
                $base = $app['request_stack']->getCurrentRequest()->getBasePath();
                return sprintf($base.'/'.$asset, ltrim($asset, '/'));
            }));
            $twig->addGlobal('steem', $app['steem']);
            $twig->addGlobal('blog', $app['blog']);
            return $twig;
        }));

        $app->mount('', new ControllerProvider());
    }

    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function getEnv()
    {
        return $this->env;
    }
}

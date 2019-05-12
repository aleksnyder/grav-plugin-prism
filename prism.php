<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class PrismPlugin
 * @package Grav\Plugin
 */
class PrismPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onMarkdownInitialized' => ['onMarkdownInitialized', 0]
        ]);
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event['markdown'];
        
        $config = $this->config->get('plugins.prism');
        $assets = $this->grav['assets'];

        if ($config['use_builtin_assets']) {
            $prism = [];
            $prism[] = "plugin://grav-plugin-prism/dist/prism.css";
            $prism[] = "plugin://grav-plugin-prism/dist/prism.js";
            $assets->registerCollection('prism', $prism);
            $assets->add('prism', 100);
        }

        // Initialize Text example
        $markdown->addInlineType('{', 'Prism');
        // Add function to handle this
        $markdown->inlinePrism = function($excerpt) {
            if (preg_match('/^{prism:([#\w]\w+)}([^{]+){\/prism}/', $excerpt['text'], $matches))
            {
                $Element = [
                    'name' => 'code',
                    'handler' => 'line',
                    'attributes' => [
                        'class' => 'language-'. $matches[1]
                    ],
                    'text' => $matches[2],
                ];
            
                $Block = [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'name' => 'pre',
                        'handler' => 'elements',
                        'attributes' => [
                            'class' => 'language-'. $matches[1]
                        ],
                        'text' => [ $Element ],
                    ]
                ];
                return $Block;
            }
        };
    }
}

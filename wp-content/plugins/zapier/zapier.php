<?php

/**
 * Plugin Name:       Zapier for WordPress
 * Description:       Zapier enables you to automatically share your posts to social media, create WordPress posts from Mailchimp newsletters, and much more. Visit https://zapier.com/apps/wordpress/integrations for more details.
 * Version:           1.0.4
 * Author:            Zapier
 * Author URI:        https://zapier.com
 * License:           Expat (MIT License)
 * License URI:       https://spdx.org/licenses/MIT.html
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';
use \Firebase\JWT\JWT;


class Zapier_Auth_Loader
{
    protected $actions;
    protected $filters;

    public function __construct()
    {
        $this->actions = array();
        $this->filters = array();
    }

    public function add_plugin_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function add_plugin_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
    {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        );

        return $hooks;
    }

    public function run()
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}


class Zapier_Auth
{
    private $error = null;
    protected $namespace;
    protected $loader;

    public function __construct()
    {
        $this->namespace = 'zapier/v1';
        $this->loader = new Zapier_Auth_Loader();
        $this->define_public_hooks();
    }

    private function define_public_hooks()
    {
        $this->loader->add_plugin_action('rest_api_init', $this, 'add_api_routes');
        $this->loader->add_plugin_filter('rest_pre_dispatch', $this, 'rest_pre_dispatch');
        $this->loader->add_plugin_filter('determine_current_user', $this, 'determine_current_user');
    }

    public function run()
    {
        $this->loader->run();
    }

    public function add_api_routes()
    {
        register_rest_route($this->namespace, '/token', array(
            'methods' => "POST",
            'callback' => array($this, 'generate_token'),
            'permission_callback' => '__return_true'
        ));
    }

    public function generate_token($request)
    {
        $secret_key = get_option('zapier_secret');
        $username = $request->get_param('username');
        $password = $request->get_param('password');
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();
            return new WP_Error(
                $error_code,
                $user->get_error_message($error_code),
                array(
                    'status' => 401,
                )
            );
        }

        $issuedAt = time();
        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $issuedAt + 300,
            'data' => array(
                'user_id' => $user->data->ID,
            ),
        );

        return array(
            'token' => JWT::encode($token, $secret_key),
        );
    }

    public function get_user_from_token()
    {
        try {
            JWT::$leeway = 240; // $leeway in seconds
            $token = JWT::decode(
                $_SERVER['HTTP_X_ZAPIER_AUTH'],
                get_option('zapier_secret'),
                array('HS256')
            );

            if ($token->iss != get_bloginfo('url')) {
                $this->error = new WP_Error(
                    'bad_issuer',
                    'The issuer does not match with this server',
                    array(
                        'status' => 401,
                    )
                );
            } elseif (!isset($token->data->user_id)) {
                $this->error = new WP_Error(
                    'bad_request',
                    'Incomplete data',
                    array(
                        'status' => 401,
                    )
                );
            } else {
                return $token->data->user_id;
            }
        } catch (Exception $e) {
            $this->error = new WP_Error(
                'invalid_token',
                $e->getMessage(),
                array(
                    'status' => 403,
                )
            );
        }
    }

    public function determine_current_user($user)
    {
        $rest_api_slug = rest_get_url_prefix();
        $is_valid_rest_api_uri = strpos($_SERVER['REQUEST_URI'], $rest_api_slug);
        $is_valid_token_uri = strpos($_SERVER['REQUEST_URI'], $this->namespace . '/token');
        $is_zapier_request = $_SERVER['HTTP_USER_AGENT'] === 'Zapier' && isset($_SERVER['HTTP_X_ZAPIER_AUTH']);

        if ($is_zapier_request && $is_valid_rest_api_uri && !$is_valid_token_uri) {
            $user_id = $this->get_user_from_token();
            if ($user_id) {
                return $user_id;
            }
        }

        return $user;
    }

    public function rest_pre_dispatch($request)
    {
        if (is_wp_error($this->error)) {
            return $this->error;
        }
        return $request;
    }
}

register_activation_hook(__FILE__, 'zapier_add_secret_key');
register_deactivation_hook(__FILE__, 'zapier_delete_secret_key');

function zapier_add_secret_key() {
    // the resulting value for the zapier_secret is 256 in length
    add_option('zapier_secret', bin2hex(random_bytes(128)));
}

function zapier_delete_secret_key() {
    delete_option('zapier_secret');
}

$plugin = new Zapier_Auth();
$plugin->run();

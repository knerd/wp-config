<?php
  /**
   * WP_Config
   * basic class to handle loading and maintaining default constants
   * supports quick setup of where wordpress lives and where you want your content directory to be
   * WP_DEBUG can also be turned on by using environment vars
   * @version 0.0.0
   * @author xopherdeep
   * @see README.md
   */
  class WP_Config{        
    private $CONSTANTS;
    private $DEFAULTS;
    private $VENDOR_DIR;

    /**
     * get_defaults
     * 
     * returns an array of defaults to be set
     *
     * @return array 
     */
    function get_defaults(){
      extract( $this->getenv_defaults() );

      //? IN PRODUCTION ENV?
      $isProduction = $this->is_production( $WP_DEBUG );

      return [
        'PROJECT_ROOT'       => __DIR__,
        'ABSPATH'            => __DIR__ . "/{$DOCROOT}/{$WP_DIR}",
        'VENDOR_PATH'        => __DIR__ . "/{$this->VENDOR_DIR}",
        'DISALLOW_FILE_EDIT' => $isProduction,
        'IS_PRODUCTION'      => $isProduction,
        'WP_DEBUG'           => !$isProduction,
        'WP_DEBUG_LOG'       => !$isProduction,
        'WP_DEBUG_DISPLAY'   => !$isProduction,
        'WP_HOME'            => "{$SITE_SCHEME}://{$SITE_HOST}",
        'WP_SITEURL'         => "{$SITE_SCHEME}://{$SITE_HOST}/{$WP_DIR}",
        'WP_CONTENT_URL'     => "{$SITE_SCHEME}://{$SITE_HOST}/{$WP_CONTENT}",
        'WP_CONTENT_DIR'     => __DIR__ . "/{$DOCROOT}/{$WP_CONTENT}"
      ];
    }

    function __construct( $DEFAULTS ) {
      $this->DEFAULTS   = $DEFAULTS;
      $this->VENDOR_DIR = $this->get_vendor_dir();
      $this->CONSTANTS  = $this->get_defaults();
      $this->CONSTANTS += $this->get_official_defaults( $DEFAULTS );
      $this->autoload();
    }

    /**
     * get_vendor_dir
     *
     * @return string 
     */
    function get_vendor_dir(){
      $json       = __DIR__ . "/composer.json";
      $json       = file_get_contents( $json );
      $json       = json_decode( $json );
      $vendor_dir = $json->config->{'vendor-dir'} ?? 'vendor';
      return $vendor_dir;
    }
    
    /**
     * getenv_defaults
     * these values can be overridden using $ENV vars
     *
     * @return array 
     */
    function getenv_defaults(){
      extract( $this->DEFAULTS );

      $prefix = getenv('MYSQL_PREFIX') ?: $DB_PREFIX;
      if($prefix)
        $GLOBALS['table_prefix'] = $prefix;

      return [
        // ENV VARS OVERRIDE DEFAULTS 
        'DOCROOT'     => getenv('DOCROOT')      ?: $DOCROOT,
        'SITE_HOST'   => getenv('VIRTUAL_HOST') ?: $_SERVER['SERVER_NAME'],
        'SITE_SCHEME' => getenv('SITE_SCHEME')  ?: $SITE_SCHEME, 
        'WP_CONTENT'  => getenv('WP_CONTENT')   ?: $WP_CONTENT,
        'WP_DEBUG'    => getenv('WP_DEBUG')     ?: $WP_DEBUG,
        'WP_DIR'      => getenv('WP_DIR')       ?: $WP_DIR,
      ];
    }
    
    /**
     * get_official_defaults
     * removes custom defaults to make things official 
     *
     * @param  mixed $defaults
     * @return array 
     */
    function get_official_defaults( $defaults ){
      $custom = [
        'DOCROOT',
        'SITE_SCHEME',
        'WP_CONTENT', 
        'WP_DIR'
      ]; 

      foreach( $custom as $unofficial )
        unset( $defaults[ $unofficial ] );

      return $defaults;
    }

    /**
     * autoload
     * defines constants and setup psr-4 class auto-loading if using composer
     *
     * @return void
     */
    private function autoload(){
      $this->define_constants();
      $this->require( $this->VENDOR_DIR . '/autoload.php' ); 
    }
    
    /**
     * define_constants
     * loads local configs before looping through defaults to set $CONSTANTS
     *
     * @return void
     */
    function define_constants(){
      $this->load_local_config();

      // LOOP $CONSTANTS AND DEFINE DEFAULT I
      foreach ( $this->CONSTANTS as $CONST => $X )
        if ( !defined( $CONST ) ) 
          define( $CONST, $X );
    }
    
    /**
     * load_local_config
     * loads local configuration file if found, otherwise use salts provided
     *
     * @return void
     */
    function load_local_config(){
      $local   = __DIR__ . "/wp-config-local.php";
      $secrets = __DIR__ . "/wp-secrets.php";
      $this->require( $local, $secrets ); 
    }

    /**
     * is_production
     * debug is automatically turned on for devs using docksal. 
     * add your own logic here to determine how you define is_production     
     *
     * @param  mixed $debug
     * @return bool 
     */
    function is_production( $isDebug ){
      $isDocksal = getenv('DOCKSAL_STACK');
      return !$isDocksal && !$isDebug;
    }
    
    /**
     * require method that checks file before requiring,
     * pass 2nd argument to include a separate file should the 1st file not exists
     *
     * @param  mixed $file
     * @param  mixed $else
     * @return void
     */
    private function require( $file, $else = false ){
      if ( file_exists( $file ) ) 
        require_once $file;
      else if ( $else && file_exists( $else ) )
        require_once $else;
    }
  }
<?php
  /**
   * WP_Config
   * basic class to handle loading and maintaining default constants
   * supports quick setup of where wordpress lives and where you want your content directory to be
   * WP_DEBUG can also be turned on by using environment vars
   * @version 0.0.4
   * @author Knerd 
   * @see README.md
   */
  class WP_Config{        
    private $CONSTANTS;
    private $CUSTOM = [
      'DIR',
      'DOCROOT',
      'SITE_SCHEME',
      'WP_CONTENT', 
      'WP_DIR'
    ];
    private $DEFAULTS;
    private $DIR;
    private $VENDOR_DIR;
    
    /**
     * __construct
     * pass defaults to class to define default constants
     * will autoload composer if found and define any constants not set in local config
     *
     * @param  mixed $DEFAULTS
     * @return void
     */
    function __construct( $defaults ) {
      $this->set_defaults( $defaults );
      $this->set_vendor_dir( $this->get_vendor_dir() );
      $this->set_constants( $this->get_constants() );
    }
    
    /**
     * set_defaults
     * @param  mixed $defaults
     * @return void
     */
    public function set_defaults( $defaults ){
      $this->DEFAULTS = $defaults;
    }

    public function set_vendor_dir( $vendor_dir ){
      $this->VENDOR_DIR = $vendor_dir;
    }

    public function set_constants( $constants ){
      $this->CONSTANTS = $constants;
    }

    /**
     * get_vendor_dir
     * reads vendor-dir setting if found in composer.json
     * otherwise defaults to vendor 
     *
     * @return string 
     */
    public function get_vendor_dir(){
      $this->DIR  = $this->DEFAULTS['DIR'];
      if(!$this->DIR)
        throw new Exception('DIR not specified', 1);
      $json       = "{$this->DIR}/composer.json";
      $json       = json_decode( file_get_contents( $json ) );
      $vendor_dir = $json->config->{'vendor-dir'} ?? 'vendor';
      return $vendor_dir;
    }
    
    /**
     * get_constants
     *
     * @return void
     */
    public function get_constants(){
      $CONSTANTS  = $this->get_defaults();
      $CONSTANTS += $this->get_official_defaults( $this->DEFAULTS );
      return $CONSTANTS;
    }

    /**
     * get_defaults
     * 
     * returns an array of defaults to be set
     *
     * @return array 
     */
    public function get_defaults(){
      extract( $this->getenv_defaults() );

      //? IN PRODUCTION ENV?
      $isProduction = $this->is_production( $WP_DEBUG );

      return [
        'IS_PRODUCTION'      => $isProduction,
        'DISALLOW_FILE_EDIT' => $isProduction,
        'WP_DEBUG'           => !$isProduction,
        'WP_DEBUG_LOG'       => !$isProduction,
        'WP_DEBUG_DISPLAY'   => !$isProduction,
        'WP_HOME'            => "{$SITE_SCHEME}://{$SITE_HOST}",
        'WP_SITEURL'         => "{$SITE_SCHEME}://{$SITE_HOST}/{$WP_DIR}",
        'WP_CONTENT_URL'     => "{$SITE_SCHEME}://{$SITE_HOST}/{$WP_CONTENT}",
        'WP_CONTENT_DIR'     => "{$this->DIR}/{$DOCROOT}/{$WP_CONTENT}",
        'ABSPATH'            => "{$this->DIR}/{$DOCROOT}/{$WP_DIR}",
        'DOCROOT'            => "{$this->DIR}/{$DOCROOT}",
        'VENDOR_PATH'        => "{$this->DIR}/{$this->VENDOR_DIR}",
        'PROJECT_ROOT'       => "{$this->DIR}"
      ];
    }

    /**
     * getenv_defaults
     * these values can be overridden using $ENV vars
     *
     * @return array 
     */
    private function getenv_defaults(){
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
    private function get_official_defaults( $defaults ){
      foreach( $this->CUSTOM as $unofficial )
        unset( $defaults[ $unofficial ] );

      return $defaults;
    }

    /**
     * autoload
     * defines constants and setup psr-4 class auto-loading if using composer
     *
     * @return void
     */
    public function autoload(){
      $this->define_constants();
      $this->require( $this->VENDOR_DIR . '/autoload.php' ); 
    }
    
    /**
     * define_constants
     * loads local configs before looping through defaults to set $CONSTANTS
     *
     * @return void
     */
    private function define_constants(){
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
    private function load_local_config(){
      $local   = $this->DIR . "/wp-config-local.php";
      $secrets = $this->DIR . "/wp-secrets.php";
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
    private function is_production( $isDebug ){
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
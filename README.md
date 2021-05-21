# wp-config
Souped Up WP_Config class to help make wp-config.php a bit more manageable

### example wp-config.php file

```php
<?php
  ...
  
  $table_prefix = 'wp_';

  /**
   * WP_Config
   * add any WordPress Constant to the array below. 
   * quickly set your content and wp dir using the WP_DIR and and WP_CONTENT options
   ** * Unofficial settings used to setup custom wp & wp-content dirs
   */
  require_once( __DIR__ . '/vendor/knerd/wp-config/class.php' );

  new WP_Config([
    // CONFIG SCHEME & FILE STRUCTURE 
      'SITE_SCHEME'       => 'http',    //* WORDPRESS SITE SCHEME
      'DIR'               => __DIR__,   //* Current Directory
      'DOCROOT'           => 'docroot', //* PUBLIC DIRECTORY
      'WP_CONTENT'        => 'content', //* WORDPRESS CONTENT DIRECTORY
      'WP_DIR'            => 'wp',      //* BLANK UNLESS WORDPRESS IS IN SUB DIRECTORY
    // OFFICIAL WORDPRESS CONSTANTS
      'WP_DEBUG'          => false,     // FOR DEVELOPERS: TURN OFF/ON WORDPRESS DEBUGGING MODE
      'WPLANG'            => '',            
      'WP_MEMORY_LIMIT'   => '128M',        
      'WP_POST_REVISIONS' => 10,            
      // DEFAULT DB CREDENTIALS 
      'DB_CHARSET'        => 'utf8',        
      'DB_COLLATE'        => '',            
      'DB_HOST'           => 'db',          
      'DB_NAME'           => 'default',     
      'DB_USER'           => 'user',        
      'DB_PASSWORD'       => 'user',        
      'DB_PREFIX'         => 'wp_',          
    // END: OFFICIAL
  ]);

  /* That's all, stop editing! Happy blogging. */

  //* LOAD WORDPRESS
  require_once( ABSPATH . '/wp-settings.php' );
```

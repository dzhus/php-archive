core.cfg.xml                                                                                        0000644 0001750 0001750 00000001634 10472270130 012672  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?xml version="1.0" encoding="utf-8" standalone="no"?>
<!DOCTYPE config SYSTEM "./dtd/core.cfg.dtd">
<config>
        <setting name="UseCaching" value="1" type="bool" />
        <setting name="CacheDir" value="core_misc/cache" type="string" />
        <setting name="ModuleListXML" value="modlist.xml" type="string" />
        <setting name="DepCheck" value="1" type="bool" />
        <setting name="MoreErrorInfo" value="1" type="bool" />
        <setting name="DefaultLayout" value="blog" type="string" />
        <setting name="LayoutListXML" value="layouts.xml" type="string" />
        <setting name="SkinDir" value="skins" type="string" />
        <setting name="DefaultSkin" value="d" type="string" />
        <setting name="LangDir" value="lang" type="string" />
        <setting name="DefaultLang" value="ru" type="string" />
        <setting name="LayoutSubDir" value="layout_templates" type="string" />
</config>
                                                                                                    core.cfg.xml.cache.php                                                                              0000644 0001750 0001750 00000000512 10472421603 014517  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?php
return array (
"CacheDir" => ".",
"UseCaching" => "1",
"ModuleListXML" => "modlist.xml",
"DepCheck" => "1",
"MoreErrorInfo" => "1",
"DefaultLayout" => "blog",
"LayoutListXML" => "layouts.xml",
"SkinDir" => "skins",
"DefaultSkin" => "d",
"LangDir" => "lang",
"DefaultLang" => "ru",
"LayoutSubDir" => "layout_templates",
);
?>                                                                                                                                                                                      core_misc/                                                                                          0000755 0001750 0001750 00000000000 10472264741 012434  5                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 core_misc/cache/                                                                                    0000755 0001750 0001750 00000000000 10472421603 013467  5                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 core_misc/cache/modlist.xml.cache.php                                                               0000644 0001750 0001750 00000001212 10472421603 017510  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?php
return array (
"db" => array (
"name" => "db",
"type" => "std",
"file" => "db_loader.php",
"version" => "0.0.1",
"settings" => array (
"Driver" => array (
"name" => "Driver",
"desc" => "Internal db driver name",
"type" => "string",
"value" => "mysql",
),
"DriversFolder" => array (
"name" => "DriversFolder",
"type" => "string",
"desc" => "Path to folder with db drivers",
"value" => "shared/db_drivers",
),
),
),
"error_logger" => array (
"name" => "error_logger",
"file" => "error_logger.php",
"type" => "core",
"version" => "0.0.1",
),
"blog" => array (
"name" => "blog",
"type" => "std",
"file" => "blog.php",
"version" => "0.0.1",
),
);
?>                                                                                                                                                                                                                                                                                                                                                                                      core_misc/cache/layouts.xml.cache.php                                                               0000644 0001750 0001750 00000000243 10472421603 017540  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?php
return array (
"blog" => array (
"name" => "blog",
"title" => "Blog",
"desc" => "Blog page layout",
"modules" => "blog",
"template" => "blog.xhtml",
),
);
?>                                                                                                                                                                                                                                                                                                                                                             core.php                                                                                            0000644 0001750 0001750 00000105430 10472273112 012125  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?php

/** 
 * @brief Module and layout handling, consistency checking, caching methods, running system
 * @author Sphinx
 * @date 2006
 *
 * @todo Examine class variables' scopes. Maybe some pseudo-__get&__set methods should be added (e.g. for retrieval of config, modlist, runlist)
 * @todo Module description
 * @todo Advanced module handling functions: 
 *      - module uninstalling (+depchecks&warnings, 2 modes: remove from list/remove from disk, 2-step-action)
 *      - module installing
 * @todo Choose between SimpleXML and DOM. 
 *      - What extension to use for parsing xml lists?
 *      - Is DTD validation really necessary (~fast?, -sophisticated)?
 * @todo i18n support in Core error messages?
 */

class Core
{
        /**
         * %Core configuration container.
         *
         * @see Core::LoadConfig()
         */
        static private $Config;

        /**
         * DOM tree from XML with core configuration
         */
        static private $ConfigDOM;

        /**
         * Parsed module list.
         *
         * @see Core::LoadModuleList()
         */
        static private $ModuleList;

        /**
         * DOM tree from XML with module list.
         */
        static private $ModuleListDOM;

        /**
         * Current layout container.
         *
         * @note This is NOT the name of current layout, but the whole corresponding ENTRY from Core::$LayoutList!
         */
        static private $Layout;

        /**
         * Parsed layout list.
         *
         * @see Core::LoadLayoutList()
         */
        static private $LayoutList;

        /**
         * DOM tree from XML with layout list.
         */
        static private $LayoutListDOM;

        /**
         * List of modules to run in current layout
         *
         * @note This list contains list of module NAMES from Core::$ModuleList
         *
         * @see Core::MakeRunList()
         */
        static private $RunList;

        /**
         * Internal name of currently selected skin for user.
         * Skin internal name is a name of directory in default system skin folder.
         *
         * @see Core::SetSkin() Core::RestoreSkinAndLang() Core::GetSkinAndLang()
         */
        static private $CurrentSkin;

        /**
         * Internal name of currently selected language for user
         * Language internal name is a name of directory in default system language folder
         *
         * @see Core::SetLang() Core::RestoreSkinAndLang() Core::GetSkinAndLang()
         */
        static private $CurrentLang;

        /**
         * Timers container, used for perfomance measuring.
         *
         * @see Core::StartTimer() Core::GetTimer()
         */
        static private $Timers;

        /**
         * Errors container, used to keep non-fatal error
         * entries generated via Core::Error().
         */
        static private $Errors;

       
        /**
         * @brief Main method in the whole system
         *
         * This is a wrapper method actually making system work.\n
         * First we prepare core, then load module list and layout we need, make list of modules to run and sequentually run them one-by-one.
         * Sounds easy, eh?
         *
         * @see Core::Startup() Core::LoadModuleList() Core::LoadLayout() Core::MakeRunList()
         *
         * @return void
         */
        static public function Run ()
        {
                self::Startup();

                self::LoadModuleList();

                self::LoadLayout();

                self::MakeRunList();

                self::RestoreSkinAndLang();

                $html = @file_get_contents(self::$Config['SkinDir'].'/'.self::$CurrentSkin.'/'.self::$Config['LayoutSubDir'].'/'.self::$Layout['template']);

                if ( !$html )
                {
                        trigger_error("Layout template for ".self::$Layout['name']." in skin ".self::$CurrentSkin." is missing!", E_USER_ERROR);
                }

                foreach ( self::$RunList as $module )
                {
                        if ( is_readable(self::$ModuleList[$module]['file']) )
                        {
                                require(self::$ModuleList[$module]['file']);  
                        }
                        else
                        {
                                trigger_error("Source file not found for module {$module}!", E_USER_ERROR);
                        }

                        $class_name = str_replace(" ", "", ucwords(preg_replace("/_([a-z])/", " $1", $module)));

                        if ( !is_subclass_of($class_name, "BaseModule") )
                        {
                                trigger_error("{$class_name} does not extend BaseModule class!", E_USER_ERROR);
                        }

                        $methods = array("BaseStartup","Startup","Run");
                        foreach ( $methods as $method )
                        {
                                call_user_func(array($class_name, $method));
                        }

                        $html = str_replace("<% {$module} %>", call_user_func(array($class_name, "GetHtml")), $html);
                }

                echo $html;
        }

        /**
         * @brief Prepare Core for work
         *
         * Set error handling directives, call some misc methodz.
         *
         * @return void
         */
        static private function Startup ()
        {
                /// Start basic timer (to measure performance later)
                self::StartTimer();

                /// Core handles errors. Yep.
                set_error_handler(array("self", "Error"), E_USER_ERROR+E_USER_WARNING+E_USER_NOTICE );

                /// Enable track_errors to have $php_errormsg
                ini_set("track_errors", 1);

                /// Load configuration of Core
                self::LoadConfig();
        }

        /**
         * Set and store skin for current user
         *
         * @param $skin         string  Internal name of skin to choose (name in skins directory)
         *
         * @return void
         */
        static public function SetSkin ($skin)
        {
                if ( !is_dir($skin) || !is_readable($skin) )
                {
                        trigger_error("Selected unaccessible skin {$skin}!", E_USER_WARNING);

                        self::$CurrentSkin = self::$Config['DefaultSkin'];
                }
                else
                {
                        self::$CurrentSkin = $skin;

                        /// Cookies have higher priority in choosing skin than session
                        SetCookie('skin', $skin, time()*2);

                        /// But what if user switched cookies off?
                        $_SESSION['skin'] = $skin;
                }
        }

        /**
         * Set and store language for current user
         *
         * @note Haha, it's y&p'ed from Core::SetSkin() method, lol rofol!!!!!!!
         *
         * @param $lang         string  Internal name of language to choose (name in langs directory)
         *
         * @return void
         */
        static public function SetLang ($lang)
        {
                if ( !is_dir($lang) || !is_readable($lang) )
                {
                        trigger_error("Selected unaccessible language {$lang}!", E_USER_WARNING);

                        self::$CurrentLang = self::$Config['DefaultLang'];
                }
                else
                {
                        self::$CurrentLang= $lang;

                        SetCookie('lang', $lang, time()*2);

                        $_SESSION['lang'] = $lang;
                }
        }

        /**
         * Get current skin and language internal names
         *
         * @return      array   Array with two elements, first one with name of current skin, second with language name
         */
        static public function GetSkinAndLang ()
        {
                return array(self::$CurrentSkin, self::$CurrentLang);
        }

        /**
         * Set Core::$CurrentSkin and Core::$CurrentLang values according to user's preferences which are stored in cookies/session data
         *
         * @note This is low-level core method which is called at each system run.\n
         *       Skin and language values set by this method may be overriden by system modules'
         *       behaviour, e.g. skin may be selected from user preferences which are kept in database and
         *       managed by system module, not system core.
         * @note Previous note is really hard to understand.
         *
         * @return void
         */
        static public function RestoreSkinAndLang ()
        {
                if ( isset($_COOKIE['skin']) )
                {
                        self::SetSkin($_COOKIE['skin']);
                }
                elseif ( isset($_SESSION['skin']) )
                {
                        self::SetSkin($_SESSION['skin']);
                }
                else
                {
                        self::SetSkin(self::$Config['DefaultSkin']);
                }

                if ( isset($_COOKIE['lang']) )
                {
                        self::SetSkin($_COOKIE['lang']);
                }
                elseif ( isset($_SESSION['lang']) )
                {
                        self::SetSkin($_SESSION['lang']);
                }
                else
                {
                        self::SetSkin(self::$Config['DefaultLang']);
                }
        }

        /**
         * @brief Load Core configuration
         *
         * Loads core configuration either from cache file or from XML config.
         * Parsed XML configuration is cached to improve perfomance later.
         *
         * @return void
         */
        static private function LoadConfig ()
        {
                self::$Config['CacheDir'] = ".";

                /// Load list from cache if it's up to date
                if ( self::IsCacheOk("core.cfg.xml") )
                {
                        self::$Config = require(self::GetCacheName("core.cfg.xml"));
                }
                else
                {
                        self::LoadDOM("core.cfg.xml", self::$ConfigDOM);
                        self::ParseConfigDOM();

                        /// Cache config if necessary
                        if ( self::$Config['UseCaching'] )
                        {
                                /// Trick to put core config cache to root directory
                                $old_dir = self::$Config['CacheDir'];
                                self::$Config['CacheDir'] = ".";
                                self::DumpArrayToCache(self::$Config, self::GetCacheName("core.cfg.xml"));
                                self::$Config['CacheDir'] = $old_dir;
                        }
                }
        }

        /**
         * If everything's fine, we get nice'n'chill Core::$ModuleList array after all, in addition to cached modlist if caching's on.\n
         * Also perform a dependency check if Core::$Config['DepCheck'] is TRUE.
         *
         * @see Core::LoadDOM() Core::ParseModuleListDOM()
         *
         * @return void
         */
        static private function LoadModuleList ()
        {
                /// Load list from cache if it's up to date and caching's on
                if ( self::$Config['UseCaching'] && self::IsCacheOk(self::$Config['ModuleListXML']) )
                {
                        self::$ModuleList = require(self::GetCacheName(self::$Config['ModuleListXML']));
                }
                else
                {
                        /// Load original XML, parse it and check it
                        self::LoadDOM(self::$Config['ModuleListXML'], self::$ModuleListDOM);
                        self::ParseModuleListDOM();

                        if ( self::$Config['DepCheck'] )
                        {
                                self::CheckModuleList();
                        }

                        /// Cache it to get a perfomance bonus in future
                        if ( self::$Config['UseCaching'] )
                        {
                                self::DumpArrayToCache(self::$ModuleList, self::GetCacheName(self::$Config['ModuleListXML']));
                        }
                }
        }

        /**
         * Load selected layout or default one.\n
         * Name of layout is selected from 'a' parameter in script query string.\n
         * The result is usable Core::$Layout, Core::$LayoutList and cached layout list if caching is enabled.
         *
         * @see Core::LoadDOM() Core::ParseLayoutListDOM()
         *
         * @return void
         */
        static private function LoadLayout ()
        {
                /// What about loading layout from cache?
                if ( self::$Config['UseCaching'] && self::IsCacheOk(self::$Config['LayoutListXML']) )
                {
                        self::$LayoutList = require(self::GetCacheName(self::$Config['LayoutListXML']));
                }
                else
                {
                        /// Load DOM, parse it and cache it if needed
                        self::LoadDOM(self::$Config['LayoutListXML'], self::$LayoutListDOM);
                        self::ParseLayoutListDOM();

                        /// Cache it to get a perfomance bonus in future
                        if ( self::$Config['UseCaching'] )
                        {
                                self::DumpArrayToCache(self::$LayoutList, self::GetCacheName(self::$Config['LayoutListXML']));
                        }
                }

                /// Load requested layout if possible, load default one otherwise
                if ( !array_key_exists($_GET['a'], self::$LayoutList) )
                {
                        if ( array_key_exists(self::$Config['DefaultLayout'], self::$LayoutList) )
                        {
                                self::$Layout = self::$LayoutList[self::$Config['DefaultLayout']];
                                trigger_error("Requested layout not found, default layout '".self::$Config['DefaultLayout']."' loaded!", E_USER_WARNING);
                        }
                        else
                        {
                                trigger_error("Default layout '".self::$Config['DefaultLayout']."' not found in layout list!", E_USER_ERROR);
                        }
                }
                else
                {
                        self::$Layout = self::$LayoutList[$_GET['a']];
                }
                
                /// Layout loaded!
        }

        /**
         * Load some XML, validate and turn it into a DOM tree.
         *
         * @note This method checks whether target XML file is accessible and raises error on failure.
         *
         * @param $source       string  Path to XML file to load
         * @param $r_variable   mixed   Link to variable which will get DOM after loading
         *
         * @return void
         */
        static private function LoadDOM ($source, &$r_variable)
        {
                /// Check if target file is accessible
                if ( !is_readable($source) ) 
                {
                        trigger_error("Could not access ".$source, E_USER_ERROR);
                }

                $r_variable = new DOMDocument();

                $r_variable->preserveWhiteSpace = false;
                
                /// Load file
                $r_variable->load($source);

                /*
                /// Validate file 
                if ( !$r_variable->validate() )
                {
                        trigger_error("File ".$source." failed DTD validation! ".$php_errormsg."..", E_USER_ERROR);
                }
                */
        }

        /**
         * Parse module list XML, Core::$ModuleList gets a parsed module list on success.
         *
         * @return void
         */
        static private function ParseModuleListDOM ()
        {
                /// Get all <module ... /> tags
                $modules = self::$ModuleListDOM->getElementsByTagName("module");

                /// Run through the whole list of modules
                foreach ( $modules as $current_module )
                {
                        /// @internal Link to corresponding $ModuleList entry
                        $r_module =& self::$ModuleList[$current_module->getAttribute("name")];

                        /// Now run through attribute list for each module
                        foreach ( $current_module->attributes as $attribute )
                        {
                                $r_module[$attribute->name] = $attribute->value;
                        }

                        /// Go through settings list as well
                        if ( $current_module->hasChildNodes() )
                        {
                                foreach ( $current_module->childNodes as $setting )
                                {
                                        $setting_name = $setting->attributes->getNamedItem("name")->value;

                                        foreach ( $setting->attributes as $setting_attribute )
                                        {

                                                $r_module['settings'][$setting_name][$setting_attribute->name] = $setting_attribute->value;
                                        }
                                }
                        }
                }

                /// Tree was successfully parsed!
        }

        /**
         * Parse layout list XML file into Core::$LayoutList.
         *
         * @note Surely I know that it was copypasted (y&p'ed, to be exactly) from Core::ParseModuleListDOM() method. So what?
         * @note You may ask, "Why is DOM extension used here instead of fluffy SimpleXML?". Answer: SimpleXML is n00b shit. We need tr00 DOM functions to op modules in future.
         *
         * @return void
         */
        static private function ParseLayoutListDOM ()
        {
                /// Get all <layout ... /> tags
                $layouts = self::$LayoutListDOM->getElementsByTagName("layout");

                /// Run through the whole list of layouts 
                foreach ( $layouts as $current_layout )
                {
                        /// @internal Link to corresponding $LayoutList entry
                        $r_layout =& self::$LayoutList[$current_layout->getAttribute("name")];

                        /// Now run through attribute list for each layout
                        foreach ( $current_layout->attributes as $attribute )
                        {
                                $r_layout[$attribute->name] = $attribute->value;
                        }
                }

                /// Tree was successfully parsed!
        }

        /**
         * Parses core configuration DOM tree, previously loaded with Core::LoadDOM()
         * Core::Config() gets a configuration on success
         *
         * @note All these ParseFooDOM methods are getting smaller and smaller as we move through the class ^_^
         *
         * @return void
         */
        static private function ParseConfigDOM ()
        {
                /// Get all <setting ... /> tags
                $settings = self::$ConfigDOM->getElementsByTagName("setting");

                /// Run through the whole list of settings 
                foreach ( $settings as $current_setting )
                {
                        /// @internal Link to corresponding $LayoutList entry
                        $r_setting =& self::$Config[$current_setting->getAttribute("name")];

                        $r_setting = $current_setting->getAttribute("value");
                }

                /// Tree was successfully parsed!
        }


        /**
         * Prepares a "runlist" in Core::$RunList, list of modules to run according to current layout.
         *
         * @return void
         */
        static private function MakeRunList ()
        {

                self::$RunList = array();


                /// Add 'core' modules first
                foreach ( self::$ModuleList as $module )
                {
                        if ( $module['type'] == 'core' )
                        {
                                self::AddModuleToRunList($module['name']);
                        }
                }

                /// Add modules requested by current layout
                foreach ( explode(" ", self::$Layout['modules']) as $module )
                {
                        if ( !array_key_exists($module, self::$ModuleList) )
                        {
                                trigger_error("Module '{$module}' in layout '".self::$Layout['name']."' not found in module list!", E_USER_ERROR);
                        }

                        self::AddModuleToRunlist($module);
                }
        }
                                
        /**
         * Add module to current runlist. Takes care of all dependency stuff.
         *
         * @param $module       string  Name of module to add
         *
         * @return void
         */
        static private function AddModuleToRunlist ($module)
        {
                /// Check if module has already been added to runlist
                if ( in_array($module, self::$RunList) )
                {
                        return;
                }
                else
                {
                        if ( self::$ModuleList[$module]['deps'] )
                        {
                                /// Add all deps as well, if there're any
                                foreach ( explode(" ", self::$ModuleList[$module]['deps']) as $dep )
                                {
                                        self::AddModuleToRunlist($dep);
                                }
                        }

                        self::$RunList[] = $module;
                }
        }

        /**
         * Check all modules in module list for self-depending or depending on non-existent modules
         *
         * @return void
         */
        static private function CheckModuleList ()
        {
                foreach ( self::$ModuleList as $module )
                {
                        self::CheckModuleDependencies($module['name']);
                }
        }

        /**
         * Traces a dependency tree for module, 
         * if any module is encountered in its own dep-tree or
         * non-existent dependency encountered, generate an error.
         *
         * @param $module       string          Name of module to check
         * @param $tracePath    array           Dep-tree for current module (used only for recurrent walk-through, do not use!)
         *
         * @warning DO NOT pass any argument other than $module!
         *
         * @return              boolean TRUE if check succeeded
         */
        static private function CheckModuleDependencies ( $module, $tracePath = array () )
        {
                if ( self::$ModuleList[$module]['deps'] == "" )
                {
                        return true;
                }

                foreach ( explode(" ", self::$ModuleList[$module]['deps']) as $dep )
                {

                        /// Wasn't this dependency in our trace path?
                        if ( in_array($dep, $tracePath) ) 
                        {
                                trigger_error("Module dependency loop encountered: {$module} -> ".join(" -> ", $tracePath ), E_USER_ERROR );
                        }

                        /// Does this dependecy exist at all?
                        if ( !array_key_exists($dep, self::$ModuleList) )
                        {
                                trigger_error("Module '{$module}' depends on non-existing module {$dep}!", E_USER_ERROR);
                        }

                        /// add dep to trace path
                        $tracePath[] = $dep;

                        self::CheckModuleDependencies($dep, $tracePath);

                }

                return true;
        }

        /**
         * Checks whether cache for specified target file is up to date
         *
         * @param $source_file          string          Name of 'source' file
         * @param $prefix               string          Source-specific cache prefix 
         *
         * @return                      boolean         Return true if cache is up to date and false if it's too old or does not exist at all
         */
        static public function IsCacheOk ($source_file, $prefix="")
        {
                $cache_file = self::GetCacheName($source_file, $prefix); 

                /// Check if cache doesn't exist
                if ( !is_readable($cache_file) )
                {
                        return false;
                }

                /// Check for existence of source file
                if ( !is_readable($source_file) )
                {
                        trigger_error("Could not access {$source_file}", E_USER_ERROR);
                }

                /// Check that source file is older than cache
                if ( filemtime($source_file) >= filemtime($cache_file) )
                {
                        return false;
                }

                return true;
        }

        /**
         * Get name of cache for target file
         *
         * @param $source_file          string  Target file name
         * @param $prefix               string  Cache prefix. Useful to when caching several different files with the same names
         *
         * @return                      string  Path to cache file
         */
         static public function GetCacheName ($source_file, $prefix="")
         {
                 if ( strlen($prefix) )
                 {
                         $prefix .= '.';
                 }
                 return self::$Config['CacheDir']."/".$prefix.$source_file.".cache.php";
         }

        /**
         * Dump some array into specified cache file.
         * Currently used to rebuild module list and layout list caches.
         *
         * @see Core::ArrayToDefinition() Core::GetCacheName()
         *
         * @param $contents     array   Contents of array to be cached  
         * @param $file         string  Path to destination file (cache file) 
         *
         * @note It may be needed to use Core::GetCacheName() method to make a proper path to destination file to pass it as $file argument for this method
         * @note Cache file will RETURN proper array definition, not declare it, 
         *       so to use cache contents later you'll need to do it in a such way: $some_variable=require("cache.file.php")
         * @note I hope you catch the idea of previous note.
         *
         * @return void
         */
        static public function DumpArrayToCache ($contents, $file)
        {
                if ( !is_array($contents) )
                {
                        trigger_error("Attempted to cache non-array datatype with Core::DumpArrayToCache()", E_USER_WARNING);
                }
                
                $cache .= "<?php\nreturn ";

                $cache .= self::ArrayToDefinition($contents);

                $cache .= ";\n?>";

                if ( @!file_put_contents($file, $cache) )
                {
                        trigger_error("Could not write to {$file}!", E_USER_ERROR);
                }
        }

        /**
         * Dump any string data into specified cache file
         *
         * @param $contents     string  Contents of string to be cached
         * @param $file         string  Path to destination file (cache file)
         *
         * @return void
         */
        static public function DumpStringToCache ($contents, $file)
        {
                $cache .= "<?php\nreturn ";

                $cache .= "'";

                $cache .= $contents;

                $cache .= "';\n?>";

                if ( @!file_put_contents($file, $cache) )
                {
                        trigger_error("Could not write to {$file}!", E_USER_ERROR);
                }
        }


        /**
         * Convert an array to piece of valid PHP array definition, used for caching in Core::DumpArrayToCache() method.
         *
         * @todo It would be nice to make ArrayToDefinition method public in future
         *
         * @param $array        array           Array to convert
         * @param $cache        string          Currently formed cache
         * @param $inner        boolean         Whether this function was called from itself
         *
         * @warning DO NOT pass any argument other than $array
         *
         * @return              string          Valid PHP array definition
         */
        static private function ArrayToDefinition ($array, $cache="", $inner=false)
        {
                $cache = "array (\n";
                        
                foreach ( $array as $key => $value )
                {
                        if ( gettype($value) == "array" )
                        {
                                /// recurrent function call
                                $cache .= "\"{$key}\" => ".self::ArrayToDefinition($value, $cache, true);
                        }
                        else
                        {
                                $cache .= "\"{$key}\" => \"{$value}\",\n";
                        }
                }

                $cache .= ")";

                if ( $inner ) $cache .= ",\n";

                return $cache;
        }

        /**
         * @brief Get miscellanous information
         *
         * Method provides us various information on user request data, session variables, Core::$Config and timers' values.
         *
         * @return      array   Array containing information
         */
        static private function GetDebugInfo ()
        {
                $message[] = "[REQUEST]";
                foreach ( $_REQUEST as $key => $value )
                {
                        $message[] = "{$key}: {$value}";
                }

                $message[] = "[SESSION]";
                foreach ( $_SESSION as $key => $value )
                {
                        $message[] = "{$key}: {$value}";
                }

                $message[] = "[COOKIE]";
                foreach ( $_COOKIE as $key => $value )
                {
                        $message[] = "{$key}: {$value}";
                }

                $message[] = "[CONFIG]";
                foreach ( self::$Config as $key => $value )
                {
                        if ( $value == false ) $value = 0;
                        $message[] = "{$key}: {$value}";
                }

                $message[] = "[TIMERS]";
                foreach ( self::$Timers as $timer => $Value )
                {
                        $message[] = "Timer #{$timer}: ".self::GetTimer($timer);
                }

                return $message;
        }

        /**
         * @brief Built-in error handler
         *
         * Generates error messages of different types, depending
         * on $ErrorCode value.\n
         * On E_USER_ERROR generates generic fatal error message and halts
         * the system. If $Config['MoreErrorInfo']is true, additional information is included in the
         * output (Core::GetDebugInfo() and error line/file ).\n
         * If E_USER_NOTICE or E_USER_WARNING occur, adds a new entry into Core::$Errors container.
         * 
         * @warning Do not call this method directly, use <pre>trigger_error("OMG error!", E_USER_ERROR);</pre> instead!
         *
         * @param $errorCode      E_USER_ERROR, E_USER_WARNING or E_USER_NOTICE 
         * @param $errorMessage   string  Text of error
         */
        static private function Error ($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)
        {
                /// Array containing CSS styles for messages
                $styles = array 
                (
                        E_USER_ERROR => "padding: 15%; text-align: center; margin-top: 10px; margin-left: 10px; margin-right: 10px; font-size: x-large; background-color: #f58169; font-weight: bold; color: #571b1b; valign: center;",
                        E_USER_NOTICE => "background-color: #a1ea8d; text-align: left; font-size: medium; color: black; padding: 2%;",
                        E_USER_WARNING => "background-color: #eac583; text-align: left; font-size: large; color: #571b1b; padding: 2%;"
                );


                /// Generate the whole page on critical errors and halt system
                if ( $errorCode == E_USER_ERROR )
                {
                        if ( self::$Config['MoreErrorInfo'] )
                        {
                                $errorMessage .= "<br /> File {$errorFile}, line {$errorLine}";
                                $errorMessage .= "<br />".join(self::GetDebugInfo(), "<br />");
                        }

                        echo "
                        <?xml version=\"1.0\" encoding=\"utf-8\">
                        <!DOCTYPE html 
                        PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
                        \"http://w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
                        <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
                                <head>
                                        <title>Core Error</title>
                                </head>
                                <body>
                                        <div style=\"{$styles[E_USER_ERROR]}\"> 
                                        {$errorMessage}
                                        </div>
                                </body>
                        </html>
                        ";

                        exit ();
                }
                else
                {
                        /// Add entry to Core::$Errors on non-critical errors
                        self::$Errors[] = array
                        (
                                'errorCode' => $errorCode,
                                'errorFile' => $errorFile,
                                'errorMessage' => $errorMessage,
                        );
                }
        }

        /**
         * Get current Core config
         *
         * @return              array Core::$Config
         */
        static public function GetConfig ()
        {
                return self::$Config;
        }

        /**
         * Start ticking specified timer
         *
         * @param $number       integer         Number of timer to start ticking
         *
         * @return              void
         */
        static private function StartTimer ($number=1)
        {
                self::$Timers[$number] = microtime();
        }

        /**
         * Get specified timer value
         *
         * @param $number       integer         Number of timer which is about to give us its current value lol
         *
         * @return              integer Current timer value
         */
        static public function GetTimer ($number=1)
        {
               return ( microtime() - self::$Timers[$number] ); 
        }
}

Core::Run();
?>
                                                                                                                                                                                                                                        doc/                                                                                                0000755 0001750 0001750 00000000000 10472013102 011215  5                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 doc/html/                                                                                           0000755 0001750 0001750 00000000000 10472421406 012173  5                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 doc/html/doxygen.png                                                                                0000644 0001750 0001750 00000002401 10472421406 014353  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR   d   -   ok>�   gAMA  ���OX2   tEXtSoftware Adobe ImageReadyq�e<   ]PLTE   Ǿ�"&���ﶻ������ޠ���������������{�ԍ�눙נ��������������������ED9���hg]_X<@:#mhU�����1   tRNS������������������������������ �v  �IDATx�b�C ��:  d#�����h`	@ �X",***LK �.� ], �X@t� ��b	@ ��� B�D�6� �%""�� ���%��B:H����f@�  �RPy"K`\PbC(!II!h���(���!���C�ą�l!0[X\J\$TM�(�>a$S���@Ш@R.$��LJBR��A�G1
��(F���Phh�T���%!`�&q�%u�P ��� � �CT$B��|���W���l��!B`R$(�������@ A�%���%@,(�����%$���RPmB U`1I�YB 99� \�1  yCCC�f"[N�' �=TGȒ�l8�^K�5�<�S��Rɤ�%�@@ ���b1�q�A�XH���&��B�R y	n�P��� 4A ��j���>� �t!�+(.��WQ�A2��MU܂�����`1�%`19� F<3cZ�`�e!\� D�+.83����!lYYA -6�EJ��V �@��XXX 4��
�@86�`RdB��4I	"�	"��@xr ʌ�H��A�`�f	�ȰC�"X V0���Cb@2���H
�ȓ p)!( � �0�4�)(%R��	�$�T ʀ���b�b�,s� �@7 � �Ѱ���?f�֗\PIx!I��"��Ȉ�3�
QY��t�^^��gv- }>W �JOAV`$&#��8���8�\FF �SFJ$�ƀ�Ɗ��� ������4�����	 -��� �H�������f	?��5� ��k1� d�,��	����.� "�F��ˀ���I�� "�� 4�H�gx�|�f �m)))9�.aM D�&  �X@t� ��b	@ ��� ��% DK �.� ], �X@t� ��b	@� d`�ɽS�O    IEND�B`�                                                                                                                                                                                                                                                               doc/html/tab_b.gif                                                                                  0000644 0001750 0001750 00000000043 10472421406 013726  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 GIF89a  �  ���   ,       D ;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             doc/html/tab_l.gif                                                                                  0000644 0001750 0001750 00000001302 10472421406 013737  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 GIF89a	 ,�  ��������������������������������������������������������������������薴ŝ�ɯ�ͻ���������������������������������������������������������                                                      ,    	 , �@�P��`H$!%Cq Ve2X���J(�Ġ+��32$�� �kv��-���u*�"}�|}|~q("$f�'�l(�&&$r����&!�)���{���r�����������������������������Ʋ��εҽͼ�����и������������������������������������P�?�����Bm�A�����%V܈�!�k��/�Đ;^���$Ɩ#Mf)f͇(W�L���K�҄� �I)L:eD�C��x*4U�h�
%A���^�NKb��e�X�� ��k�x!���2t��	 !��5t�����]$��%�X��.i[�]Y���f��E��kg`���:z��Ҟ;�}��j�aa��M���׸c瞽�v�ۺ���8��ȋ'?��9�積G_>�yu�_ߞ]zw�߭�Ǿ��m�浏G~����თ/�>���٫��������|�W�} v ;                                                                                                                                                                                                                                                                                                                              doc/html/tab_r.gif                                                                                  0000644 0001750 0001750 00000005031 10472421406 013750  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 GIF89a�,�  ��������������������������������������������������������������������薴ŝ�ɯ�ͻ���������������������������������������������������������                                                      ,    �, �@�pH,�Ȥr�l:�ШtJ�Z�جv�h<�pkL.���z�n���|N�����~�wVa+���������������������������*)^,*�������������������������ö)'��������������������������ڥ("���������������� 
H�����K�"�RH�H��ŋ3j�ȱ�Ǐ C�I��ɓ(S�\�Łu&@� ���͛8s��ɳ�O��(���DУH�*]ʴ�ӧP�J�J��իX�j�ʵ�ׯS84� �hӪ]˶�۷p�ʝK��ݻx���˷�߿��} �D�f(^̸��ǐ#K�L���˘3k�̹��ϠC�m��C�H�Xͺ��װc˞M���۸s��ͻ�����N��J� �����УK�N����سk�ν����ËO�<v1�+_.������˟O��������Ͽ��� (���_Yɕ@�r�5���F(�Vh�f��v��� �(�$��a��H��,���0�(�4�h�	*�"�<���@)�Di�H&��L6��PF)�T&�\�;V��\v��`�)�Z^�%	d���l���p�hf�q�i�x��sR�枀*蠄��矅&�袌6:䡎F*餔�	i��f��O^�駠�ʩ���jꩄ��ꪬ�ʦ���*�S�J뭸�*�����물�*찥K��Vjl��6���F+-��Nk��kV����v�m���۩�t�k�Q~�����n���n��r;o��N{o��2�o� �o��:p�K��	7��Gl*�W�)�g�)�w)���(�"�,(�&��'�*�l��,3�,�l3�5߬3�0���=�,t�9m��E���A/�t�I?-5�QOm5�U_=u�Z?�u�K��b�=t�f��v�;����n�=s�r�Lw�*ߍ��z�-r�~{x�N�ņ.q�;�x�	?y��Op���y��n�y��o袷Kz�랎����.n뮃{��6M��ֳ�~m��k{�R��������+]|��.ϼ��?/����J}�G����ok��+�%�᫭}�v��~���w������O��ߏx��/���� #'�R�����2�s| �"(��Q���� S�����|C(���v��		��F|.dc82�PZ3�a�l��f將D�!���!ꩈF���(,$2�NN|���(�쵰�L!���.z�`��H�.zaH����6��p���H�:���x̣���>��d�@� ��L�"��F:򑐌�$9�  (8�&��Nz��(G�FB^�!˨ )W��V��l�)1�w��̥.w�Y����0�I�b�|�Hp�f:���epJ���}Ȧ6���nz���8�0 ��%"��8���v�����<�P�Q`�%�$�>���~�� �@JЂ��M�B�І:����D'ZPKF ּ&16�юz�� �HGJRb �L�5��Җ����0��LgJӚ�#(e>��Ӟ���@�P�JԢ��HM�R��Ԧ:��P��T�Jժ&5;%�U��ծz��`�X�JV��C���jY��ֶ���p��\�U����xͫ^��׾�����i)$�����M�b���:v�,ಘͬf7���z����hGK�Қ���M�jW��ֺ����*$�SP���ͭnw��������m + ���M�r����:���E�?�9��Z���ͮv�9��"����x�K��b���L��z��^A������|�������ͯ0���������/�L����N���(�;��	n0�'La�J���0{/��{ؘ���G|����(��S��Cr�.���	����v�1�w�c6��@��Ld��HN��d/��P��LeO��X�p��|�+s����2��L_1����53��M5����t3��_:���w�s���g�ʹπp���?��/F����F��Ў�t!-�J�Җ��1��N��Ӟu�A-�P��ԝ>5�3��UW�ծ�4�c��Y�ZѶ���s��A�׀�5��,�a��ƶ3��=�e3���~���-�3S��c�6����mo����2��M�q���>7�ӭn$���D~7��,�y����1��m�}����v����\�/��N�3�����#��S\�gu�-m�O��0�C����\�'_��S^���|�.�c.�0ל�4�9~s��=���<�y|�.�4]�D?�z���67]�O�3ӣ�̩S�W�v��l>��3��d�:�u)��?���F;�ˮ�W����|;�W)�����vt�˽w���|��=x���A  ;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       doc/html/tabs.css                                                                                   0000644 0001750 0001750 00000003336 10472421406 013643  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 /* tabs styles, based on http://www.alistapart.com/articles/slidingdoors */

DIV.tabs
{
   float            : left;
   width            : 100%;
   background       : url("tab_b.gif") repeat-x bottom;
   margin-bottom    : 4px;
}

DIV.tabs UL
{
   margin           : 0px;
   padding-left     : 10px;
   list-style       : none;
}

DIV.tabs LI, DIV.tabs FORM
{
   display          : inline;
   margin           : 0px;
   padding          : 0px;
}

DIV.tabs FORM
{
   float            : right;
}

DIV.tabs A
{
   float            : left;
   background       : url("tab_r.gif") no-repeat right top;
   border-bottom    : 1px solid #84B0C7;
   font-size        : x-small;
   font-weight      : bold;
   text-decoration  : none;
}

DIV.tabs A:hover
{
   background-position: 100% -150px;
}

DIV.tabs A:link, DIV.tabs A:visited,
DIV.tabs A:active, DIV.tabs A:hover
{
       color: #1A419D;
}

DIV.tabs SPAN
{
   float            : left;
   display          : block;
   background       : url("tab_l.gif") no-repeat left top;
   padding          : 5px 9px;
   white-space      : nowrap;
}

DIV.tabs INPUT
{
   float            : right;
   display          : inline;
   font-size        : 1em;
}

DIV.tabs TD
{
   font-size        : x-small;
   font-weight      : bold;
   text-decoration  : none;
}



/* Commented Backslash Hack hides rule from IE5-Mac \*/
DIV.tabs SPAN {float : none;}
/* End IE5-Mac hack */

DIV.tabs A:hover SPAN
{
   background-position: 0% -150px;
}

DIV.tabs LI#current A
{
   background-position: 100% -150px;
   border-width     : 0px;
}

DIV.tabs LI#current SPAN
{
   background-position: 0% -150px;
   padding-bottom   : 6px;
}

DIV.nav
{
   background       : none;
   border           : none;
   border-bottom    : 1px solid #84B0C7;
}
                                                                                                                                                                                                                                                                                                  doc/html/doxygen.css                                                                                0000644 0001750 0001750 00000017567 10472421406 014402  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 BODY,H1,H2,H3,H4,H5,H6,P,CENTER,TD,TH,UL,DL,DIV {
	font-family: Geneva, Arial, Helvetica, sans-serif;
}
BODY,TD {
       font-size: 90%;
}
H1 {
	text-align: center;
       font-size: 160%;
}
H2 {
       font-size: 120%;
}
H3 {
       font-size: 100%;
}
CAPTION { font-weight: bold }
DIV.qindex {
	width: 100%;
	background-color: #e8eef2;
	border: 1px solid #84b0c7;
	text-align: center;
	margin: 2px;
	padding: 2px;
	line-height: 140%;
}
DIV.nav {
	width: 100%;
	background-color: #e8eef2;
	border: 1px solid #84b0c7;
	text-align: center;
	margin: 2px;
	padding: 2px;
	line-height: 140%;
}
DIV.navtab {
       background-color: #e8eef2;
       border: 1px solid #84b0c7;
       text-align: center;
       margin: 2px;
       margin-right: 15px;
       padding: 2px;
}
TD.navtab {
       font-size: 70%;
}
A.qindex {
       text-decoration: none;
       font-weight: bold;
       color: #1A419D;
}
A.qindex:visited {
       text-decoration: none;
       font-weight: bold;
       color: #1A419D
}
A.qindex:hover {
	text-decoration: none;
	background-color: #ddddff;
}
A.qindexHL {
	text-decoration: none;
	font-weight: bold;
	background-color: #6666cc;
	color: #ffffff;
	border: 1px double #9295C2;
}
A.qindexHL:hover {
	text-decoration: none;
	background-color: #6666cc;
	color: #ffffff;
}
A.qindexHL:visited { text-decoration: none; background-color: #6666cc; color: #ffffff }
A.el { text-decoration: none; font-weight: bold }
A.elRef { font-weight: bold }
A.code:link { text-decoration: none; font-weight: normal; color: #0000FF}
A.code:visited { text-decoration: none; font-weight: normal; color: #0000FF}
A.codeRef:link { font-weight: normal; color: #0000FF}
A.codeRef:visited { font-weight: normal; color: #0000FF}
A:hover { text-decoration: none; background-color: #f2f2ff }
DL.el { margin-left: -1cm }
.fragment {
       font-family: monospace, fixed;
       font-size: 95%;
}
PRE.fragment {
	border: 1px solid #CCCCCC;
	background-color: #f5f5f5;
	margin-top: 4px;
	margin-bottom: 4px;
	margin-left: 2px;
	margin-right: 8px;
	padding-left: 6px;
	padding-right: 6px;
	padding-top: 4px;
	padding-bottom: 4px;
}
DIV.ah { background-color: black; font-weight: bold; color: #ffffff; margin-bottom: 3px; margin-top: 3px }

DIV.groupHeader {
       margin-left: 16px;
       margin-top: 12px;
       margin-bottom: 6px;
       font-weight: bold;
}
DIV.groupText { margin-left: 16px; font-style: italic; font-size: 90% }
BODY {
	background: white;
	color: black;
	margin-right: 20px;
	margin-left: 20px;
}
TD.indexkey {
	background-color: #e8eef2;
	font-weight: bold;
	padding-right  : 10px;
	padding-top    : 2px;
	padding-left   : 10px;
	padding-bottom : 2px;
	margin-left    : 0px;
	margin-right   : 0px;
	margin-top     : 2px;
	margin-bottom  : 2px;
	border: 1px solid #CCCCCC;
}
TD.indexvalue {
	background-color: #e8eef2;
	font-style: italic;
	padding-right  : 10px;
	padding-top    : 2px;
	padding-left   : 10px;
	padding-bottom : 2px;
	margin-left    : 0px;
	margin-right   : 0px;
	margin-top     : 2px;
	margin-bottom  : 2px;
	border: 1px solid #CCCCCC;
}
TR.memlist {
   background-color: #f0f0f0; 
}
P.formulaDsp { text-align: center; }
IMG.formulaDsp { }
IMG.formulaInl { vertical-align: middle; }
SPAN.keyword       { color: #008000 }
SPAN.keywordtype   { color: #604020 }
SPAN.keywordflow   { color: #e08000 }
SPAN.comment       { color: #800000 }
SPAN.preprocessor  { color: #806020 }
SPAN.stringliteral { color: #002080 }
SPAN.charliteral   { color: #008080 }
.mdescLeft {
       padding: 0px 8px 4px 8px;
	font-size: 80%;
	font-style: italic;
	background-color: #FAFAFA;
	border-top: 1px none #E0E0E0;
	border-right: 1px none #E0E0E0;
	border-bottom: 1px none #E0E0E0;
	border-left: 1px none #E0E0E0;
	margin: 0px;
}
.mdescRight {
       padding: 0px 8px 4px 8px;
	font-size: 80%;
	font-style: italic;
	background-color: #FAFAFA;
	border-top: 1px none #E0E0E0;
	border-right: 1px none #E0E0E0;
	border-bottom: 1px none #E0E0E0;
	border-left: 1px none #E0E0E0;
	margin: 0px;
}
.memItemLeft {
	padding: 1px 0px 0px 8px;
	margin: 4px;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-color: #E0E0E0;
	border-right-color: #E0E0E0;
	border-bottom-color: #E0E0E0;
	border-left-color: #E0E0E0;
	border-top-style: solid;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
	background-color: #FAFAFA;
	font-size: 80%;
}
.memItemRight {
	padding: 1px 8px 0px 8px;
	margin: 4px;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-color: #E0E0E0;
	border-right-color: #E0E0E0;
	border-bottom-color: #E0E0E0;
	border-left-color: #E0E0E0;
	border-top-style: solid;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
	background-color: #FAFAFA;
	font-size: 80%;
}
.memTemplItemLeft {
	padding: 1px 0px 0px 8px;
	margin: 4px;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-color: #E0E0E0;
	border-right-color: #E0E0E0;
	border-bottom-color: #E0E0E0;
	border-left-color: #E0E0E0;
	border-top-style: none;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
	background-color: #FAFAFA;
	font-size: 80%;
}
.memTemplItemRight {
	padding: 1px 8px 0px 8px;
	margin: 4px;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-color: #E0E0E0;
	border-right-color: #E0E0E0;
	border-bottom-color: #E0E0E0;
	border-left-color: #E0E0E0;
	border-top-style: none;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
	background-color: #FAFAFA;
	font-size: 80%;
}
.memTemplParams {
	padding: 1px 0px 0px 8px;
	margin: 4px;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-color: #E0E0E0;
	border-right-color: #E0E0E0;
	border-bottom-color: #E0E0E0;
	border-left-color: #E0E0E0;
	border-top-style: solid;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
       color: #606060;
	background-color: #FAFAFA;
	font-size: 80%;
}
.search     { color: #003399;
              font-weight: bold;
}
FORM.search {
              margin-bottom: 0px;
              margin-top: 0px;
}
INPUT.search { font-size: 75%;
               color: #000080;
               font-weight: normal;
               background-color: #e8eef2;
}
TD.tiny      { font-size: 75%;
}
a {
	color: #1A41A8;
}
a:visited {
	color: #2A3798;
}
.dirtab { padding: 4px;
          border-collapse: collapse;
          border: 1px solid #84b0c7;
}
TH.dirtab { background: #e8eef2;
            font-weight: bold;
}
HR { height: 1px;
     border: none;
     border-top: 1px solid black;
}

/* Style for detailed member documentation */
.memtemplate {
  font-size: 80%;
  color: #606060;
  font-weight: normal;
} 
.memnav { 
  background-color: #e8eef2;
  border: 1px solid #84b0c7;
  text-align: center;
  margin: 2px;
  margin-right: 15px;
  padding: 2px;
}
.memitem {
  padding: 4px;
  background-color: #eef3f5;
  border-width: 1px;
  border-style: solid;
  border-color: #dedeee;
  -moz-border-radius: 8px 8px 8px 8px;
}
.memname {
  white-space: nowrap;
  font-weight: bold;
}
.memdoc{
  padding-left: 10px;
}
.memproto {
  background-color: #d5e1e8;
  width: 100%;
  border-width: 1px;
  border-style: solid;
  border-color: #84b0c7;
  font-weight: bold;
  -moz-border-radius: 8px 8px 8px 8px;
}
.paramkey {
  text-align: right;
}
.paramtype {
  white-space: nowrap;
}
.paramname {
  color: #602020;
  font-style: italic;
}
/* End Styling for detailed member documentation */

/* for the tree view */
.ftvtree {
	font-family: sans-serif;
	margin:0.5em;
}
.directory { font-size: 9pt; font-weight: bold; }
.directory h3 { margin: 0px; margin-top: 1em; font-size: 11pt; }
.directory > h3 { margin-top: 0; }
.directory p { margin: 0px; white-space: nowrap; }
.directory div { display: none; margin: 0px; }
.directory img { vertical-align: -30%; }

                                                                                                                                         doc/html/index.html                                                                                 0000644 0001750 0001750 00000000515 10472421406 014171  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System</title></head>
<frameset cols="250,*">
  <frame src="tree.html" name="treefrm">
  <frame src="main.html" name="basefrm">
</frameset>
</html>
                                                                                                                                                                                   doc/html/a00005.html                                                                                0000644 0001750 0001750 00000003014 10472257756 013703  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: /home/sphinx/Projects/PHP/PhpWS/base_module.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/PhpWS/base_module.php File Reference</h1>
<p>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Classes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">class &nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html">BaseModule</a></td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Abstract module class, providing basic skin and language methods.  <a href="a00003.html#_details">More...</a><br></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    doc/html/a00006.html                                                                                0000644 0001750 0001750 00000003011 10472257756 013701  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: /home/sphinx/Projects/PHP/PhpWS/core.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/PhpWS/core.php File Reference</h1>
<p>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Classes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">class &nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html">Core</a></td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Module and layout handling, consistency checking, caching methods, running system.  <a href="a00004.html#_details">More...</a><br></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       doc/html/todo.html                                                                                  0000644 0001750 0001750 00000003761 10472421406 014035  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Todo List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1><a class="anchor" name="todo">Todo List</a></h1><a class="anchor" name="_todo000001"></a> <dl>
<dt>Class <a class="el" href="classCore.html">Core</a>  </dt>
<dd>Examine class variables' scopes. Maybe some pseudo-__get&amp;__set methods should be added (e.g. for retrieval of config, modlist, runlist) <p>
Module description <p>
Advanced module handling functions:<ul>
<li>module uninstalling (+depchecks&amp;warnings, 2 modes: remove from list/remove from disk, 2-step-action)</li><li>module installing </li></ul>
<p>
Choose between SimpleXML and DOM.<ul>
<li>What extension to use for parsing xml lists?</li><li>Is DTD validation really necessary (~fast?, -sophisticated)? </li></ul>
<p>
i18n support in <a class="el" href="classCore.html">Core</a> error messages? </dd>
</dl>
<p>
<a class="anchor" name="_todo000002"></a> <dl>
<dt>Member <a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">Core::ArrayToDefinition</a> ($array, $cache="", $inner=false) </dt>
<dd>It would be nice to make ArrayToDefinition method public in future<p>
</dd>
</dl>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
               doc/html/annotated.html                                                                             0000644 0001750 0001750 00000003254 10472421406 015042  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Class List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li id="current"><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>YAMWS: Yet Another Modular Web System Class List</h1>Here are the classes, structs, unions and interfaces with brief descriptions:<table>
  <tr><td class="indexkey"><a class="el" href="classBaseModule.html">BaseModule</a></td><td class="indexvalue">Abstract module class, providing basic skin and language methods </td></tr>
  <tr><td class="indexkey"><a class="el" href="classCore.html">Core</a></td><td class="indexvalue">Module and layout handling, consistency checking, caching methods, running system </td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                    doc/html/functions.html                                                                             0000644 0001750 0001750 00000017354 10472421406 015103  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Class Members</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li id="current"><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li id="current"><a href="functions.html"><span>All</span></a></li>
    <li><a href="functions_func.html"><span>Functions</span></a></li>
    <li><a href="functions_vars.html"><span>Variables</span></a></li>
  </ul>
</div>
<div class="tabs">
  <ul>
    <li><a href="#index_$"><span>$</span></a></li>
    <li><a href="#index_a"><span>a</span></a></li>
    <li><a href="#index_b"><span>b</span></a></li>
    <li><a href="#index_c"><span>c</span></a></li>
    <li><a href="#index_d"><span>d</span></a></li>
    <li><a href="#index_e"><span>e</span></a></li>
    <li><a href="#index_g"><span>g</span></a></li>
    <li><a href="#index_i"><span>i</span></a></li>
    <li><a href="#index_l"><span>l</span></a></li>
    <li><a href="#index_m"><span>m</span></a></li>
    <li><a href="#index_p"><span>p</span></a></li>
    <li><a href="#index_r"><span>r</span></a></li>
    <li><a href="#index_s"><span>s</span></a></li>
  </ul>
</div>

<p>
Here is a list of all class members with links to the classes they belong to:
<p>
<h3><a class="anchor" name="index_$">- $ -</a></h3><ul>
<li>$Config
: <a class="el" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">Core</a>, <a class="el" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">BaseModule</a><li>$ConfigDOM
: <a class="el" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">Core</a><li>$CurrentLang
: <a class="el" href="classCore.html#855055f9bbc19bc705ea05747653644a">Core</a><li>$CurrentSkin
: <a class="el" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">Core</a><li>$Errors
: <a class="el" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">Core</a><li>$Html
: <a class="el" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">BaseModule</a><li>$Lang
: <a class="el" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">BaseModule</a><li>$Layout
: <a class="el" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">Core</a><li>$LayoutList
: <a class="el" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">Core</a><li>$LayoutListDOM
: <a class="el" href="classCore.html#89f8206b99ec81d78b8604b47768b757">Core</a><li>$ModuleList
: <a class="el" href="classCore.html#9669b1e25f58a6327346627e0718714b">Core</a><li>$ModuleListDOM
: <a class="el" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">Core</a><li>$ModuleName
: <a class="el" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">BaseModule</a><li>$RunList
: <a class="el" href="classCore.html#135f0995570f8535b094481c563132f4">Core</a><li>$Timers
: <a class="el" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">Core</a></ul>
<h3><a class="anchor" name="index_a">- a -</a></h3><ul>
<li>AddModuleToRunlist()
: <a class="el" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">Core</a><li>ArrayToDefinition()
: <a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">Core</a></ul>
<h3><a class="anchor" name="index_b">- b -</a></h3><ul>
<li>BaseStartup()
: <a class="el" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseModule</a></ul>
<h3><a class="anchor" name="index_c">- c -</a></h3><ul>
<li>CheckModuleDependencies()
: <a class="el" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">Core</a><li>CheckModuleList()
: <a class="el" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">Core</a></ul>
<h3><a class="anchor" name="index_d">- d -</a></h3><ul>
<li>DumpArrayToCache()
: <a class="el" href="classCore.html#9c2596c82e620380c71bed110db40ca7">Core</a><li>DumpStringToCache()
: <a class="el" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">Core</a></ul>
<h3><a class="anchor" name="index_e">- e -</a></h3><ul>
<li>Error()
: <a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Core</a></ul>
<h3><a class="anchor" name="index_g">- g -</a></h3><ul>
<li>GetCacheName()
: <a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">Core</a><li>GetConfig()
: <a class="el" href="classCore.html#c8a8d74c512360172e80b0343028b605">Core</a><li>GetDebugInfo()
: <a class="el" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">Core</a><li>GetHtml()
: <a class="el" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">BaseModule</a><li>GetSkinAndLang()
: <a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">Core</a><li>GetTimer()
: <a class="el" href="classCore.html#b779a4778fcbfe097daab723acb4f092">Core</a></ul>
<h3><a class="anchor" name="index_i">- i -</a></h3><ul>
<li>IsCacheOk()
: <a class="el" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">Core</a></ul>
<h3><a class="anchor" name="index_l">- l -</a></h3><ul>
<li>Lang()
: <a class="el" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">BaseModule</a><li>LoadConfig()
: <a class="el" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">Core</a><li>LoadDOM()
: <a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core</a><li>LoadLang()
: <a class="el" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">BaseModule</a><li>LoadLayout()
: <a class="el" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">Core</a><li>LoadModuleList()
: <a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">Core</a></ul>
<h3><a class="anchor" name="index_m">- m -</a></h3><ul>
<li>MakeRunList()
: <a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">Core</a></ul>
<h3><a class="anchor" name="index_p">- p -</a></h3><ul>
<li>ParseConfigDOM()
: <a class="el" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">Core</a><li>ParseLayoutListDOM()
: <a class="el" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">Core</a><li>ParseModuleListDOM()
: <a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">Core</a></ul>
<h3><a class="anchor" name="index_r">- r -</a></h3><ul>
<li>RestoreSkinAndLang()
: <a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">Core</a><li>Run()
: <a class="el" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Core</a>, <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">BaseModule</a></ul>
<h3><a class="anchor" name="index_s">- s -</a></h3><ul>
<li>SetLang()
: <a class="el" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">Core</a><li>SetSkin()
: <a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">Core</a><li>Skin()
: <a class="el" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">BaseModule</a><li>StartTimer()
: <a class="el" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">Core</a><li>Startup()
: <a class="el" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Core</a>, <a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">BaseModule</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                    doc/html/functions_func.html                                                                        0000644 0001750 0001750 00000014110 10472421406 016101  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Class Members - Functions</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li id="current"><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="functions.html"><span>All</span></a></li>
    <li id="current"><a href="functions_func.html"><span>Functions</span></a></li>
    <li><a href="functions_vars.html"><span>Variables</span></a></li>
  </ul>
</div>
<div class="tabs">
  <ul>
    <li><a href="#index_a"><span>a</span></a></li>
    <li><a href="#index_b"><span>b</span></a></li>
    <li><a href="#index_c"><span>c</span></a></li>
    <li><a href="#index_d"><span>d</span></a></li>
    <li><a href="#index_e"><span>e</span></a></li>
    <li><a href="#index_g"><span>g</span></a></li>
    <li><a href="#index_i"><span>i</span></a></li>
    <li><a href="#index_l"><span>l</span></a></li>
    <li><a href="#index_m"><span>m</span></a></li>
    <li><a href="#index_p"><span>p</span></a></li>
    <li><a href="#index_r"><span>r</span></a></li>
    <li><a href="#index_s"><span>s</span></a></li>
  </ul>
</div>

<p>
&nbsp;
<p>
<h3><a class="anchor" name="index_a">- a -</a></h3><ul>
<li>AddModuleToRunlist()
: <a class="el" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">Core</a><li>ArrayToDefinition()
: <a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">Core</a></ul>
<h3><a class="anchor" name="index_b">- b -</a></h3><ul>
<li>BaseStartup()
: <a class="el" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseModule</a></ul>
<h3><a class="anchor" name="index_c">- c -</a></h3><ul>
<li>CheckModuleDependencies()
: <a class="el" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">Core</a><li>CheckModuleList()
: <a class="el" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">Core</a></ul>
<h3><a class="anchor" name="index_d">- d -</a></h3><ul>
<li>DumpArrayToCache()
: <a class="el" href="classCore.html#9c2596c82e620380c71bed110db40ca7">Core</a><li>DumpStringToCache()
: <a class="el" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">Core</a></ul>
<h3><a class="anchor" name="index_e">- e -</a></h3><ul>
<li>Error()
: <a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Core</a></ul>
<h3><a class="anchor" name="index_g">- g -</a></h3><ul>
<li>GetCacheName()
: <a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">Core</a><li>GetConfig()
: <a class="el" href="classCore.html#c8a8d74c512360172e80b0343028b605">Core</a><li>GetDebugInfo()
: <a class="el" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">Core</a><li>GetHtml()
: <a class="el" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">BaseModule</a><li>GetSkinAndLang()
: <a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">Core</a><li>GetTimer()
: <a class="el" href="classCore.html#b779a4778fcbfe097daab723acb4f092">Core</a></ul>
<h3><a class="anchor" name="index_i">- i -</a></h3><ul>
<li>IsCacheOk()
: <a class="el" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">Core</a></ul>
<h3><a class="anchor" name="index_l">- l -</a></h3><ul>
<li>Lang()
: <a class="el" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">BaseModule</a><li>LoadConfig()
: <a class="el" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">Core</a><li>LoadDOM()
: <a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core</a><li>LoadLang()
: <a class="el" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">BaseModule</a><li>LoadLayout()
: <a class="el" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">Core</a><li>LoadModuleList()
: <a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">Core</a></ul>
<h3><a class="anchor" name="index_m">- m -</a></h3><ul>
<li>MakeRunList()
: <a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">Core</a></ul>
<h3><a class="anchor" name="index_p">- p -</a></h3><ul>
<li>ParseConfigDOM()
: <a class="el" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">Core</a><li>ParseLayoutListDOM()
: <a class="el" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">Core</a><li>ParseModuleListDOM()
: <a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">Core</a></ul>
<h3><a class="anchor" name="index_r">- r -</a></h3><ul>
<li>RestoreSkinAndLang()
: <a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">Core</a><li>Run()
: <a class="el" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Core</a>, <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">BaseModule</a></ul>
<h3><a class="anchor" name="index_s">- s -</a></h3><ul>
<li>SetLang()
: <a class="el" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">Core</a><li>SetSkin()
: <a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">Core</a><li>Skin()
: <a class="el" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">BaseModule</a><li>StartTimer()
: <a class="el" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">Core</a><li>Startup()
: <a class="el" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Core</a>, <a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">BaseModule</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                        doc/html/functions_vars.html                                                                        0000644 0001750 0001750 00000005664 10472421406 016137  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Class Members - Variables</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li id="current"><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="functions.html"><span>All</span></a></li>
    <li><a href="functions_func.html"><span>Functions</span></a></li>
    <li id="current"><a href="functions_vars.html"><span>Variables</span></a></li>
  </ul>
</div>
&nbsp;
<p>
<ul>
<li>$Config
: <a class="el" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">Core</a>, <a class="el" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">BaseModule</a><li>$ConfigDOM
: <a class="el" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">Core</a><li>$CurrentLang
: <a class="el" href="classCore.html#855055f9bbc19bc705ea05747653644a">Core</a><li>$CurrentSkin
: <a class="el" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">Core</a><li>$Errors
: <a class="el" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">Core</a><li>$Html
: <a class="el" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">BaseModule</a><li>$Lang
: <a class="el" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">BaseModule</a><li>$Layout
: <a class="el" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">Core</a><li>$LayoutList
: <a class="el" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">Core</a><li>$LayoutListDOM
: <a class="el" href="classCore.html#89f8206b99ec81d78b8604b47768b757">Core</a><li>$ModuleList
: <a class="el" href="classCore.html#9669b1e25f58a6327346627e0718714b">Core</a><li>$ModuleListDOM
: <a class="el" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">Core</a><li>$ModuleName
: <a class="el" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">BaseModule</a><li>$RunList
: <a class="el" href="classCore.html#135f0995570f8535b094481c563132f4">Core</a><li>$Timers
: <a class="el" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">Core</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                            doc/html/a00003.html                                                                                0000644 0001750 0001750 00000054326 10472257756 013715  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: BaseModule Class Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>BaseModule Class Reference</h1><!-- doxytag: class="BaseModule" --><a href="a00001.html">List of all members.</a><hr><a name="_details"></a><h2>Detailed Description</h2>
Abstract module class, providing basic skin and language methods. 
<p>
<dl compact><dt><b>Author:</b></dt><dd>Sphinx </dd></dl>
<dl compact><dt><b>Date:</b></dt><dd>2006</dd></dl>
All modules in system should extend this very class to provide single skin&amp;language handling facilities.<br>
 Study <a class="el" href="a00003.html">BaseModule</a> members' docs to learn more about default module methods and data containers.<p>
<dl compact><dt><b>Note:</b></dt><dd><a class="el" href="a00003.html">BaseModule</a> errors are handled by <a class="el" href="a00004.html#6d24271a9d5c59bf494eaeff5175f15a">Core::Error()</a> </dd></dl>

<p>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Static Public Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#11334f6f33dc88122b0a94c887725052">BaseStartup</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">Run</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#2d76af4157b2673d4740c554e08aef96">Skin</a> ($bitName, $arguments, $useCaching=0, $returnData=0)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#035af0942841311dd3f3e7f663db8323">Lang</a> ($bitName, $arguments=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#6faeccfec86169417e6318fc760ba577">GetHtml</a> ()</td></tr>

<tr><td colspan="2"><br><h2>Static Private Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#5359716a861284e5a6b1ff2296cf31aa">LoadLang</a> ()</td></tr>

<tr><td colspan="2"><br><h2>Static Private Attributes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#7006215baccd8082453dd6fe14500e5b">$Config</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#2b73679e0907a7220f6f5c21e7f8bfab">$Html</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#200f3961846b0fee2935abcf6a166ac0">$ModuleName</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00003.html#f56f5fcd34b6eba1b15f513d52592a7d">$Lang</a></td></tr>

</table>
<hr><h2>Member Function Documentation</h2>
<a class="anchor" name="11334f6f33dc88122b0a94c887725052"></a><!-- doxytag: member="BaseModule::BaseStartup" ref="11334f6f33dc88122b0a94c887725052" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::BaseStartup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepare module. This method is called by <a class="el" href="a00004.html">Core</a> before calling module's <a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> method every time module runs!<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">BaseModule::Startup()</a> <a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">BaseModule::Run()</a> </dd></dl>

<p>
Get local copy of <a class="el" href="a00004.html">Core</a> config<p>
Load skin&amp;language names from <a class="el" href="a00004.html">Core</a><p>
Load language<p>
Make $ModuleName <div class="fragment"><pre class="fragment"><a name="l00045"></a>00045         {
<a name="l00047"></a>00047                 self::$CoreConfig = <a class="code" href="a00004.html#c8a8d74c512360172e80b0343028b605">Core::GetConfig</a>();
<a name="l00048"></a>00048 
<a name="l00050"></a>00050                 $s_l = <a class="code" href="a00004.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang</a>();
<a name="l00051"></a>00051                 self::$CurrentSkin = $s_l[0];
<a name="l00052"></a>00052                 self::$CurrentLang = $s_l[1];
<a name="l00053"></a>00053 
<a name="l00055"></a>00055                 self::LoadLang();
<a name="l00056"></a>00056 
<a name="l00058"></a>00058                 self::$ModuleName = strtolower(preg_replace(<span class="stringliteral">"/(.)([A-Z])/"</span>, <span class="stringliteral">"$1_$2"</span>, __CLASS__));
<a name="l00059"></a>00059         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="a02aa1a71b87ff5ea8130861df6b1bf8"></a><!-- doxytag: member="BaseModule::Startup" ref="a02aa1a71b87ff5ea8130861df6b1bf8" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Startup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, abstract]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module-specific pre-run routines should be defined in this method. <a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> method is called by <a class="el" href="a00004.html">Core</a> before calling for module's <a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">Run()</a> method. <dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00003.html#11334f6f33dc88122b0a94c887725052">BaseModule::BaseStartup()</a> <a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">BaseModule::Run()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="090f2e30c601c0b3072e97dd02f91260"></a><!-- doxytag: member="BaseModule::Run" ref="090f2e30c601c0b3072e97dd02f91260" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Run           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, abstract]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module-specific run routines must be defined in this method. <a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">Run()</a> is called by <a class="el" href="a00004.html">Core</a> after <a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> 
</div>
</div><p>
<a class="anchor" name="2d76af4157b2673d4740c554e08aef96"></a><!-- doxytag: member="BaseModule::Skin" ref="2d76af4157b2673d4740c554e08aef96" args="($bitName, $arguments, $useCaching=0, $returnData=0)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Skin           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>bitName</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>arguments</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>useCaching</em> = <code>0</code>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>returnData</em> = <code>0</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Put specific skin bit to module's $Html Skin bits are .xhtml files in module's skin directory, which is selected as<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$bitName</em>&nbsp;</td><td>string Name of skin bit (.xhtml file in module's skin directory) </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$arguments</em>&nbsp;</td><td>array Associative array, where keys are strings occuring in skin bit, which are replaced on output by corresponding data in values in $arguments. <b>EXAMPLE</b>: say, we have $arguments = array("%msg%"=&gt;"Success!"), then all strings 'msg' in skin bit will be replaced by "Success!" </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$useCaching</em>&nbsp;</td><td>bool If TRUE, supercaching for this very call will be used </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$returnData</em>&nbsp;</td><td>bool If TRUE, Skin method will return HTML data instead of adding it to module's $Html. May be useful when using output of Skin method in another one instead of immediately adding it to $Html </td></tr>
  </table>
</dl>
<dl compact><dt><b><a class="el" href="todo.html#_todo000001">Todo:</a></b></dt><dd>Per-call iCaching (w00t!) </dd></dl>

<p>
Fucking slow place follows <div class="fragment"><pre class="fragment"><a name="l00086"></a>00086         {
<a name="l00087"></a>00087                 $html = @file_get_contents(self::$CoreConfig['SkinDir'].<span class="charliteral">'/'</span>.<a class="code" href="a00004.html#26ebc052e21bea592bfba86e793dc48b">Core::$CurrentSkin</a>.<span class="charliteral">'/'</span>.self::$ModuleName.<span class="charliteral">'/'</span>.$bitName.'.xhtml'); 
<a name="l00088"></a>00088 
<a name="l00089"></a>00089                 <span class="keywordflow">if</span> ( !$html )
<a name="l00090"></a>00090                 {
<a name="l00091"></a>00091                        trigger_error(<span class="stringliteral">"Requested skin bit {$bitName} in skin "</span>.<a class="code" href="a00004.html#26ebc052e21bea592bfba86e793dc48b">Core::$CurrentSkin</a>.<span class="stringliteral">"for module "</span>.self::$ModuleName.<span class="stringliteral">" is missing!"</span>, E_USER_ERROR)
<a name="l00092"></a>00092                 }
<a name="l00093"></a>00093 
<a name="l00095"></a>00095                 <span class="keywordflow">if</span> ( $arguments )
<a name="l00096"></a>00096                 {
<a name="l00097"></a>00097                         foreach ( $arguments as $s =&gt; $r )
<a name="l00098"></a>00098                         {
<a name="l00099"></a>00099                                $html = str_replace($s, $r, $html);
<a name="l00100"></a>00100                         }
<a name="l00101"></a>00101                 }
<a name="l00102"></a>00102 
<a name="l00103"></a>00103                 <span class="keywordflow">if</span> ( $returnData )
<a name="l00104"></a>00104                 {
<a name="l00105"></a>00105                        <span class="keywordflow">return</span> $html;
<a name="l00106"></a>00106                 }
<a name="l00107"></a>00107                 <span class="keywordflow">else</span>
<a name="l00108"></a>00108                 {
<a name="l00109"></a>00109                        self::$Html .= $html;
<a name="l00110"></a>00110                 }
<a name="l00111"></a>00111         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="5359716a861284e5a6b1ff2296cf31aa"></a><!-- doxytag: member="BaseModule::LoadLang" ref="5359716a861284e5a6b1ff2296cf31aa" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::LoadLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load modules's $Lang to allow usage of <a class="el" href="a00003.html#035af0942841311dd3f3e7f663db8323">Lang()</a> method and $Lang container 
<p>
Check file for existence<p>
Check if language cache is available<p>
Load language file XML and _PARSE_IT_<p>
Dump $Lang to cache if needed <div class="fragment"><pre class="fragment"><a name="l00117"></a>00117         {
<a name="l00118"></a>00118                 $file_name = self::$ModuleName.'.xml';
<a name="l00119"></a>00119 
<a name="l00120"></a>00120                 $path_to_file = self::$CoreConfig['LangDir'].<span class="charliteral">'/'</span>.self::$CurrentLang.<span class="charliteral">'/'</span>.$file_name;
<a name="l00121"></a>00121 
<a name="l00123"></a>00123                 <span class="keywordflow">if</span> ( !is_readable($path_to_file) )
<a name="l00124"></a>00124                 {
<a name="l00125"></a>00125                         trigger_error(<span class="stringliteral">"Language file in "</span>.self::$CurrentLang.<span class="stringliteral">" for module "</span>.self::$ModuleName.<span class="stringliteral">" is missing!"</span>, E_USER_ERROR)
<a name="l00126"></a>00126                 }
<a name="l00127"></a>00127 
<a name="l00129"></a>00129                 <span class="keywordflow">if</span> ( self::$CoreConfig['UseCaching'] &amp;&amp; <a class="code" href="a00004.html#992b63771f336cc051fcf9e59f6b143f">Core::IsCacheOk</a>($file_name, self::$CurrentLang) )
<a name="l00130"></a>00130                 {
<a name="l00131"></a>00131                         self::$Lang = GetCacheName($file_name, self::$CurrentLang);
<a name="l00132"></a>00132                 }
<a name="l00133"></a>00133                 <span class="keywordflow">else</span>
<a name="l00135"></a>00135                 {
<a name="l00136"></a>00136                         <a class="code" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM</a>($path_to_file, $lang_dom);
<a name="l00137"></a>00137 
<a name="l00138"></a>00138                         $bits = -&gt;getElementsByTagName('bit');
<a name="l00139"></a>00139 
<a name="l00140"></a>00140                         foreach ( $bits as $current_bit)
<a name="l00141"></a>00141                         {
<a name="l00142"></a>00142                                 self::$Lang[$current_bit-&gt;getAttribute('name')] = $current_bit-&gt;data;
<a name="l00143"></a>00143                         }
<a name="l00144"></a>00144 
<a name="l00146"></a>00146                         <span class="keywordflow">if</span> ( self::$CoreConfig['UseCaching'] )
<a name="l00147"></a>00147                         {
<a name="l00148"></a>00148                                 <a class="code" href="a00004.html#9c2596c82e620380c71bed110db40ca7">Core::DumpArrayToCache</a>(self::$Lang, <a class="code" href="a00004.html#7e860483a3033de70dc374e452900079">Core::GetCacheName</a>($file_name, self::$CurrentLang) );
<a name="l00149"></a>00149                         }
<a name="l00150"></a>00150                 }
<a name="l00151"></a>00151         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="035af0942841311dd3f3e7f663db8323"></a><!-- doxytag: member="BaseModule::Lang" ref="035af0942841311dd3f3e7f663db8323" args="($bitName, $arguments=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Lang           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>bitName</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>arguments</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Return specified language bit<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$bitName</em>&nbsp;</td><td>string Name of language bit </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$arguments</em>&nbsp;</td><td>array List of arguments which are used in vsprintf() function with selected language bit. <b>EXAMPLE</b>: say, language bit is "current user %s has %d posts" and $arguments is array("Alex",15), then method will output the following to module's <dl compact><dt><b>Html</b></dt><dd>"current user Alex has 15 posts". If </dd></dl>
arguments is empty, then no string substitution will be performed. </td></tr>
  </table>
</dl>
<div class="fragment"><pre class="fragment"><a name="l00162"></a>00162         {
<a name="l00163"></a>00163                 <span class="keywordflow">if</span> ( is_array($arguments) )
<a name="l00164"></a>00164                 {
<a name="l00165"></a>00165                         <span class="keywordflow">return</span> vsprintf(self::$Lang[$bitName], $arguments);
<a name="l00166"></a>00166                 }
<a name="l00167"></a>00167                 <span class="keywordflow">else</span>
<a name="l00168"></a>00168                 {
<a name="l00169"></a>00169                         <span class="keywordflow">return</span> self::$Lang[$bitName];
<a name="l00170"></a>00170                 }
<a name="l00171"></a>00171         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="6faeccfec86169417e6318fc760ba577"></a><!-- doxytag: member="BaseModule::GetHtml" ref="6faeccfec86169417e6318fc760ba577" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::GetHtml           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Returns module current $Html<p>
<dl compact><dt><b>Returns:</b></dt><dd>string All html generated by module </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00179"></a>00179         {
<a name="l00180"></a>00180                 <span class="keywordflow">return</span> self::$Html;
<a name="l00181"></a>00181         }
</pre></div>
<p>

</div>
</div><p>
<hr><h2>Member Data Documentation</h2>
<a class="anchor" name="7006215baccd8082453dd6fe14500e5b"></a><!-- doxytag: member="BaseModule::$Config" ref="7006215baccd8082453dd6fe14500e5b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Config<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module configuration container<p>
<dl compact><dt><b>See also:</b></dt><dd>BaseModule::GetConfig() </dd></dl>

</div>
</div><p>
<a class="anchor" name="2b73679e0907a7220f6f5c21e7f8bfab"></a><!-- doxytag: member="BaseModule::$Html" ref="2b73679e0907a7220f6f5c21e7f8bfab" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Html<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
All HTML data generated by module 
</div>
</div><p>
<a class="anchor" name="200f3961846b0fee2935abcf6a166ac0"></a><!-- doxytag: member="BaseModule::$ModuleName" ref="200f3961846b0fee2935abcf6a166ac0" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$ModuleName<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module internal name in really_proper_format 
</div>
</div><p>
<a class="anchor" name="f56f5fcd34b6eba1b15f513d52592a7d"></a><!-- doxytag: member="BaseModule::$Lang" ref="f56f5fcd34b6eba1b15f513d52592a7d" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Lang<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module language container.<br>
 It's an associative array where keys are language bit names and values are translated string, e.g.: 'BRD_POSTS' =&gt; 'Board has d posts' 
</div>
</div><p>
<hr>The documentation for this class was generated from the following file:<ul>
<li>/home/sphinx/Projects/PHP/PhpWS/<a class="el" href="a00005.html">base_module.php</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                          doc/html/a00001.html                                                                                0000644 0001750 0001750 00000007061 10472257756 013705  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: Member List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>BaseModule Member List</h1>This is the complete list of members for <a class="el" href="a00003.html">BaseModule</a>, including all inherited members.<p><table>
  <tr class="memlist"><td><a class="el" href="a00003.html#7006215baccd8082453dd6fe14500e5b">$Config</a></td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#2b73679e0907a7220f6f5c21e7f8bfab">$Html</a></td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#f56f5fcd34b6eba1b15f513d52592a7d">$Lang</a></td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#200f3961846b0fee2935abcf6a166ac0">$ModuleName</a></td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#11334f6f33dc88122b0a94c887725052">BaseStartup</a>()</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#6faeccfec86169417e6318fc760ba577">GetHtml</a>()</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#035af0942841311dd3f3e7f663db8323">Lang</a>($bitName, $arguments=&quot;&quot;)</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#5359716a861284e5a6b1ff2296cf31aa">LoadLang</a>()</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#090f2e30c601c0b3072e97dd02f91260">Run</a>()</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#2d76af4157b2673d4740c554e08aef96">Skin</a>($bitName, $arguments, $useCaching=0, $returnData=0)</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00003.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup</a>()</td><td><a class="el" href="a00003.html">BaseModule</a></td><td><code> [static]</code></td></tr>
</table><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                               doc/html/a00004.html                                                                                0000644 0001750 0001750 00000255313 10472257756 013715  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: Core Class Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>Core Class Reference</h1><!-- doxytag: class="Core" --><a href="a00002.html">List of all members.</a><hr><a name="_details"></a><h2>Detailed Description</h2>
Module and layout handling, consistency checking, caching methods, running system. 
<p>
<dl compact><dt><b>Author:</b></dt><dd>Sphinx </dd></dl>
<dl compact><dt><b>Date:</b></dt><dd>2006</dd></dl>
<dl compact><dt><b><a class="el" href="todo.html#_todo000002">Todo:</a></b></dt><dd>Examine class variables' scopes. Maybe some pseudo-__get&amp;__set methods should be added (e.g. for retrieval of config, modlist, runlist) <p>
Module description <p>
Advanced module handling functions:<ul>
<li>module uninstalling (+depchecks&amp;warnings, 2 modes: remove from list/remove from disk, 2-step-action)</li><li>module installing </li></ul>
<p>
Choose between SimpleXML and DOM.<ul>
<li>What extension to use for parsing xml lists?</li><li>Is DTD validation really necessary (~fast?, -sophisticated)? </li></ul>
<p>
i18n support in <a class="el" href="a00004.html">Core</a> error messages? </dd></dl>

<p>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Static Public Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#d1b6c2ef037c930720235b0bfd51a13b">Run</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Main method in the whole system.  <a href="#d1b6c2ef037c930720235b0bfd51a13b"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#b013cd82ca954a4c4f31d30ad3a9901a">SetSkin</a> ($skin)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#ab69b5da88ad2901f68b2e010e0ec308">SetLang</a> ($lang)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#85d74c60de9057926fa4abacaa71f3e9">GetSkinAndLang</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#8b789def87026eb966ddf3ad2babfae3">RestoreSkinAndLang</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#992b63771f336cc051fcf9e59f6b143f">IsCacheOk</a> ($source_file, $prefix=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#7e860483a3033de70dc374e452900079">GetCacheName</a> ($source_file, $prefix=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#9c2596c82e620380c71bed110db40ca7">DumpArrayToCache</a> ($contents, $file)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#c8a8d74c512360172e80b0343028b605">GetConfig</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#b779a4778fcbfe097daab723acb4f092">GetTimer</a> ($number=1)</td></tr>

<tr><td colspan="2"><br><h2>Static Private Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#3a99f8ccf5081616ebc92d9af3a9562a">Startup</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Prepare <a class="el" href="a00004.html">Core</a> for work.  <a href="#3a99f8ccf5081616ebc92d9af3a9562a"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">LoadConfig</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Load <a class="el" href="a00004.html">Core</a> configuration.  <a href="#b1f22bca6ee5f4e8d6431e0a008cbdcf"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#da9dbb1f356160b8174d1d815e75120f">LoadModuleList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#7c84d7f528dfa0178955627a1f83a715">LoadLayout</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">LoadDOM</a> ($source, &amp;$r_variable)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#9b4be0a58f539006b8c05287d4cda753">ParseModuleListDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#92fe821bc14994668b0d2e58d2fb71ef">ParseLayoutListDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#5ab0c08fca09009556bad907ea604ec1">ParseConfigDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#119b3cb7f093eefba2e1096baeaadab8">MakeRunList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#4cf7e042593cd3c058bb4eb9bd35c33a">AddModuleToRunlist</a> ($module)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#4ee5f73d2b08c38a8887b9d51e245300">CheckModuleList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#ce80a07ac8a6d86d867705a8061a9a9b">CheckModuleDependencies</a> ($module, $tracePath=array())</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#1735ab51c34cbedda6b963629de1839d">ArrayToDefinition</a> ($array, $cache=&quot;&quot;, $inner=false)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#cb511097b2a57cd46dfde95b9600c68d">GetDebugInfo</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Get miscellanous information.  <a href="#cb511097b2a57cd46dfde95b9600c68d"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#6d24271a9d5c59bf494eaeff5175f15a">Error</a> ($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Built-in error handler.  <a href="#6d24271a9d5c59bf494eaeff5175f15a"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#f7fea025d37b95401bcad814d34fb4f5">StartTimer</a> ($number=1)</td></tr>

<tr><td colspan="2"><br><h2>Static Private Attributes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#0a4233d6c7fb2f45a854df1e395c9326">$Config</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#8f4a69e9060afbb9eea926f029e41055">$ConfigDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#9669b1e25f58a6327346627e0718714b">$ModuleList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#19f8bd4f1c50b416fa32043a7a53b715">$ModuleListDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#de337a75ba1c07b69c1a17b63313f18f">$Layout</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#c23a132a6ef90503a264e5f2b16c81d5">$LayoutList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#89f8206b99ec81d78b8604b47768b757">$LayoutListDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#135f0995570f8535b094481c563132f4">$RunList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#26ebc052e21bea592bfba86e793dc48b">$CurrentSkin</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#855055f9bbc19bc705ea05747653644a">$CurrentLang</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#6d83c303b0d63c3c5157b062e1a12d5a">$Timers</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="a00004.html#1cbc06a311ea7ac7fcc4dc60fbede26d">$Errors</a></td></tr>

</table>
<hr><h2>Member Function Documentation</h2>
<a class="anchor" name="d1b6c2ef037c930720235b0bfd51a13b"></a><!-- doxytag: member="Core::Run" ref="d1b6c2ef037c930720235b0bfd51a13b" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Run           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Main method in the whole system. 
<p>
This is a wrapper method actually making system work.<br>
 First we prepare core, then load module list and layout we need, make list of modules to run and sequentually run them one-by-one. Sounds easy, eh?<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#3a99f8ccf5081616ebc92d9af3a9562a">Core::Startup()</a> <a class="el" href="a00004.html#da9dbb1f356160b8174d1d815e75120f">Core::LoadModuleList()</a> <a class="el" href="a00004.html#7c84d7f528dfa0178955627a1f83a715">Core::LoadLayout()</a> <a class="el" href="a00004.html#119b3cb7f093eefba2e1096baeaadab8">Core::MakeRunList()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00115"></a>00115         {
<a name="l00116"></a>00116                 self::Startup();
<a name="l00117"></a>00117 
<a name="l00118"></a>00118                 self::LoadModuleList();
<a name="l00119"></a>00119 
<a name="l00120"></a>00120                 self::LoadLayout();
<a name="l00121"></a>00121 
<a name="l00122"></a>00122                 self::MakeRunList();
<a name="l00123"></a>00123 
<a name="l00124"></a>00124                 self::RestoreSkinAndLang();
<a name="l00125"></a>00125 
<a name="l00126"></a>00126                 foreach ( self::$RunList as $module )
<a name="l00127"></a>00127                 {
<a name="l00128"></a>00128                         <span class="keywordflow">if</span> ( is_readable(self::$ModuleList[$module]['file']) )
<a name="l00129"></a>00129                         {
<a name="l00130"></a>00130                                 require(self::$ModuleList[$module]['file']);  
<a name="l00131"></a>00131                         }
<a name="l00132"></a>00132                         <span class="keywordflow">else</span>
<a name="l00133"></a>00133                         {
<a name="l00134"></a>00134                                 trigger_error(<span class="stringliteral">"Source file not found for module {$module}!"</span>, E_USER_ERROR);
<a name="l00135"></a>00135                         }
<a name="l00136"></a>00136                 }
<a name="l00137"></a>00137         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="3a99f8ccf5081616ebc92d9af3a9562a"></a><!-- doxytag: member="Core::Startup" ref="3a99f8ccf5081616ebc92d9af3a9562a" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Startup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepare <a class="el" href="a00004.html">Core</a> for work. 
<p>
Set error handling directives, call some misc methodz.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Start basic timer (to measure performance later)<p>
<a class="el" href="a00004.html">Core</a> handles errors. Yep.<p>
Enable track_errors to have $php_errormsg<p>
Load configuration of <a class="el" href="a00004.html">Core</a> <div class="fragment"><pre class="fragment"><a name="l00147"></a>00147         {
<a name="l00149"></a>00149                 self::StartTimer();
<a name="l00150"></a>00150 
<a name="l00152"></a>00152                 set_error_handler(array(<span class="stringliteral">"self"</span>, <span class="stringliteral">"Error"</span>), E_USER_ERROR+E_USER_WARNING+E_USER_NOTICE );
<a name="l00153"></a>00153 
<a name="l00155"></a>00155                 ini_set(<span class="stringliteral">"track_errors"</span>, 1);
<a name="l00156"></a>00156 
<a name="l00158"></a>00158                 self::LoadConfig();
<a name="l00159"></a>00159         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="b013cd82ca954a4c4f31d30ad3a9901a"></a><!-- doxytag: member="Core::SetSkin" ref="b013cd82ca954a4c4f31d30ad3a9901a" args="($skin)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::SetSkin           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>skin</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set and store skin for current user<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$skin</em>&nbsp;</td><td>string Internal name of skin to choose (name in skins directory)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Cookies have higher priority in choosing skin than session<p>
But what if user switched cookies off? <div class="fragment"><pre class="fragment"><a name="l00169"></a>00169         {
<a name="l00170"></a>00170                 <span class="keywordflow">if</span> ( !is_dir($skin) || !is_readable($skin) )
<a name="l00171"></a>00171                 {
<a name="l00172"></a>00172                         trigger_error(<span class="stringliteral">"Selected unaccessible skin {$skin}!"</span>, E_USER_WARNING);
<a name="l00173"></a>00173 
<a name="l00174"></a>00174                         self::$CurrentSkin = self::$Config['DefaultSkin'];
<a name="l00175"></a>00175                 }
<a name="l00176"></a>00176                 <span class="keywordflow">else</span>
<a name="l00177"></a>00177                 {
<a name="l00178"></a>00178                         self::$CurrentSkin = $skin;
<a name="l00179"></a>00179 
<a name="l00181"></a>00181                         SetCookie('skin', $skin, time()*2);
<a name="l00182"></a>00182 
<a name="l00184"></a>00184                         $_SESSION['skin'] = $skin;
<a name="l00185"></a>00185                 }
<a name="l00186"></a>00186         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="ab69b5da88ad2901f68b2e010e0ec308"></a><!-- doxytag: member="Core::SetLang" ref="ab69b5da88ad2901f68b2e010e0ec308" args="($lang)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::SetLang           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>lang</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set and store language for current user<p>
<dl compact><dt><b>Note:</b></dt><dd>Haha, it's y&amp;p'ed from <a class="el" href="a00004.html#b013cd82ca954a4c4f31d30ad3a9901a">Core::SetSkin()</a> method, lol rofol!!!!!!!</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$lang</em>&nbsp;</td><td>string Internal name of language to choose (name in langs directory)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00198"></a>00198         {
<a name="l00199"></a>00199                 <span class="keywordflow">if</span> ( !is_dir($lang) || !is_readable($lang) )
<a name="l00200"></a>00200                 {
<a name="l00201"></a>00201                         trigger_error(<span class="stringliteral">"Selected unaccessible language {$lang}!"</span>, E_USER_WARNING);
<a name="l00202"></a>00202 
<a name="l00203"></a>00203                         self::$CurrentLang = self::$Config['DefaultLang'];
<a name="l00204"></a>00204                 }
<a name="l00205"></a>00205                 <span class="keywordflow">else</span>
<a name="l00206"></a>00206                 {
<a name="l00207"></a>00207                         self::$CurrentLang= $lang;
<a name="l00208"></a>00208 
<a name="l00209"></a>00209                         SetCookie('lang', $lang, time()*2);
<a name="l00210"></a>00210 
<a name="l00211"></a>00211                         $_SESSION['lang'] = $lang;
<a name="l00212"></a>00212                 }
<a name="l00213"></a>00213         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="85d74c60de9057926fa4abacaa71f3e9"></a><!-- doxytag: member="Core::GetSkinAndLang" ref="85d74c60de9057926fa4abacaa71f3e9" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetSkinAndLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get current skin and language internal names<p>
<dl compact><dt><b>Returns:</b></dt><dd>array Array with two elements, first one with name of current skin, second with language name </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00221"></a>00221         {
<a name="l00222"></a>00222                 <span class="keywordflow">return</span> array(self::$CurrentSkin, self::$CurrentLang);
<a name="l00223"></a>00223         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="8b789def87026eb966ddf3ad2babfae3"></a><!-- doxytag: member="Core::RestoreSkinAndLang" ref="8b789def87026eb966ddf3ad2babfae3" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::RestoreSkinAndLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set <a class="el" href="a00004.html">Core</a>::$CurrentSkin and <a class="el" href="a00004.html">Core</a>::$CurrentLang values according to user's preferences which are stored in cookies/session data<p>
<dl compact><dt><b>Note:</b></dt><dd>This is low-level core method which is called at each system run.<br>
 Skin and language values set by this method may be overriden by system modules' behaviour, e.g. skin may be selected from user preferences which are kept in database and managed by system module, not system core. <p>
Previous note is really hard to understand.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00237"></a>00237         {
<a name="l00238"></a>00238                 <span class="keywordflow">if</span> ( isset($_COOKIE['skin']) )
<a name="l00239"></a>00239                 {
<a name="l00240"></a>00240                         self::SetSkin($_COOKIE['skin']);
<a name="l00241"></a>00241                 }
<a name="l00242"></a>00242                 elseif ( isset($_SESSION['skin']) )
<a name="l00243"></a>00243                 {
<a name="l00244"></a>00244                         self::SetSkin($_SESSION['skin']);
<a name="l00245"></a>00245                 }
<a name="l00246"></a>00246                 <span class="keywordflow">else</span>
<a name="l00247"></a>00247                 {
<a name="l00248"></a>00248                         self::SetSkin(self::$Config['DefaultSkin']);
<a name="l00249"></a>00249                 }
<a name="l00250"></a>00250 
<a name="l00251"></a>00251                 <span class="keywordflow">if</span> ( isset($_COOKIE['lang']) )
<a name="l00252"></a>00252                 {
<a name="l00253"></a>00253                         self::SetSkin($_COOKIE['lang']);
<a name="l00254"></a>00254                 }
<a name="l00255"></a>00255                 elseif ( isset($_SESSION['lang']) )
<a name="l00256"></a>00256                 {
<a name="l00257"></a>00257                         self::SetSkin($_SESSION['lang']);
<a name="l00258"></a>00258                 }
<a name="l00259"></a>00259                 <span class="keywordflow">else</span>
<a name="l00260"></a>00260                 {
<a name="l00261"></a>00261                         self::SetSkin(self::$Config['DefaultLang']);
<a name="l00262"></a>00262                 }
<a name="l00263"></a>00263         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="b1f22bca6ee5f4e8d6431e0a008cbdcf"></a><!-- doxytag: member="Core::LoadConfig" ref="b1f22bca6ee5f4e8d6431e0a008cbdcf" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadConfig           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load <a class="el" href="a00004.html">Core</a> configuration. 
<p>
Loads core configuration either from cache file or from XML config. Parsed XML configuration is cached to improve perfomance later.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Load list from cache if it's up to date<p>
Cache config if necessary <div class="fragment"><pre class="fragment"><a name="l00274"></a>00274         {
<a name="l00275"></a>00275                 self::$Config['CacheDir'] = <span class="stringliteral">"."</span>;
<a name="l00276"></a>00276 
<a name="l00278"></a>00278                 <span class="keywordflow">if</span> ( self::IsCacheOk(<span class="stringliteral">"core.cfg.xml"</span>) )
<a name="l00279"></a>00279                 {
<a name="l00280"></a>00280                         self::$Config = require(self::GetCacheName(<span class="stringliteral">"core.cfg.xml"</span>));
<a name="l00281"></a>00281                 }
<a name="l00282"></a>00282                 <span class="keywordflow">else</span>
<a name="l00283"></a>00283                 {
<a name="l00284"></a>00284                         self::LoadDOM(<span class="stringliteral">"core.cfg.xml"</span>, self::$ConfigDOM);
<a name="l00285"></a>00285                         self::ParseConfigDOM();
<a name="l00286"></a>00286 
<a name="l00288"></a>00288                         <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00289"></a>00289                         {
<a name="l00290"></a>00290                                 self::DumpArrayToCache(self::$Config, self::GetCacheName(<span class="stringliteral">"core.cfg.xml"</span>, <span class="stringliteral">"../../"</span>));
<a name="l00291"></a>00291                         }
<a name="l00292"></a>00292                 }
<a name="l00293"></a>00293         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="da9dbb1f356160b8174d1d815e75120f"></a><!-- doxytag: member="Core::LoadModuleList" ref="da9dbb1f356160b8174d1d815e75120f" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadModuleList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
If everything's fine, we get nice'n'chill <a class="el" href="a00004.html">Core</a>::$ModuleList array after all, in addition to cached modlist if caching's on.<br>
 Also perform a dependency check if <a class="el" href="a00004.html">Core</a>::$Config['DepCheck'] is TRUE.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> <a class="el" href="a00004.html#9b4be0a58f539006b8c05287d4cda753">Core::ParseModuleListDOM()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Load list from cache if it's up to date and caching's on<p>
Load original XML, parse it and check it<p>
Cache it to get a perfomance bonus in future <div class="fragment"><pre class="fragment"><a name="l00304"></a>00304         {
<a name="l00306"></a>00306                 <span class="keywordflow">if</span> ( self::$Config['UseCaching'] &amp;&amp; self::IsCacheOk(self::$Config['ModuleListXML']) )
<a name="l00307"></a>00307                 {
<a name="l00308"></a>00308                         self::$ModuleList = require(self::GetCacheName(self::$Config['ModuleListXML']));
<a name="l00309"></a>00309                 }
<a name="l00310"></a>00310                 <span class="keywordflow">else</span>
<a name="l00311"></a>00311                 {
<a name="l00313"></a>00313                         self::LoadDOM(self::$Config['ModuleListXML'], self::$ModuleListDOM);
<a name="l00314"></a>00314                         self::ParseModuleListDOM();
<a name="l00315"></a>00315 
<a name="l00316"></a>00316                         <span class="keywordflow">if</span> ( self::$Config['DepCheck'] )
<a name="l00317"></a>00317                         {
<a name="l00318"></a>00318                                 self::CheckModuleList();
<a name="l00319"></a>00319                         }
<a name="l00320"></a>00320 
<a name="l00322"></a>00322                         <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00323"></a>00323                         {
<a name="l00324"></a>00324                                 self::DumpArrayToCache(self::$ModuleList, self::GetCacheName(self::$Config['ModuleListXML']));
<a name="l00325"></a>00325                         }
<a name="l00326"></a>00326                 }
<a name="l00327"></a>00327         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="7c84d7f528dfa0178955627a1f83a715"></a><!-- doxytag: member="Core::LoadLayout" ref="7c84d7f528dfa0178955627a1f83a715" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadLayout           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load selected layout or default one.<br>
 Name of layout is selected from 'a' parameter in script query string.<br>
 The result is usable <a class="el" href="a00004.html">Core</a>::$Layout, <a class="el" href="a00004.html">Core</a>::$LayoutList and cached layout list if caching is enabled.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> <a class="el" href="a00004.html#92fe821bc14994668b0d2e58d2fb71ef">Core::ParseLayoutListDOM()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
What about loading layout from cache?<p>
Load DOM, parse it and cache it if needed<p>
Cache it to get a perfomance bonus in future<p>
Load requested layout if possible, load default one otherwise<p>
Layout loaded! <div class="fragment"><pre class="fragment"><a name="l00339"></a>00339         {
<a name="l00341"></a>00341                 <span class="keywordflow">if</span> ( self::$Config['UseCaching'] &amp;&amp; self::IsCacheOk(self::$Config['LayoutListXML']) )
<a name="l00342"></a>00342                 {
<a name="l00343"></a>00343                         self::$LayoutList = require(self::GetCacheName(self::$Config['LayoutListXML']));
<a name="l00344"></a>00344                 }
<a name="l00345"></a>00345                 <span class="keywordflow">else</span>
<a name="l00346"></a>00346                 {
<a name="l00348"></a>00348                         self::LoadDOM(self::$Config['LayoutListXML'], self::$LayoutListDOM);
<a name="l00349"></a>00349                         self::ParseLayoutListDOM();
<a name="l00350"></a>00350 
<a name="l00352"></a>00352                         <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00353"></a>00353                         {
<a name="l00354"></a>00354                                 self::DumpArrayToCache(self::$LayoutList, self::GetCacheName(self::$Config['LayoutListXML']));
<a name="l00355"></a>00355                         }
<a name="l00356"></a>00356                 }
<a name="l00357"></a>00357 
<a name="l00359"></a>00359                 <span class="keywordflow">if</span> ( !array_key_exists($_GET[<span class="charliteral">'a'</span>], self::$LayoutList) )
<a name="l00360"></a>00360                 {
<a name="l00361"></a>00361                         <span class="keywordflow">if</span> ( array_key_exists(self::$Config['DefaultLayout'], self::$LayoutList) )
<a name="l00362"></a>00362                         {
<a name="l00363"></a>00363                                 self::$Layout = self::$LayoutList[self::$Config['DefaultLayout']];
<a name="l00364"></a>00364                                 trigger_error(<span class="stringliteral">"Requested layout not found, default layout '"</span>.self::$Config['DefaultLayout'].<span class="stringliteral">"' loaded!"</span>, E_USER_WARNING);
<a name="l00365"></a>00365                         }
<a name="l00366"></a>00366                         <span class="keywordflow">else</span>
<a name="l00367"></a>00367                         {
<a name="l00368"></a>00368                                 trigger_error(<span class="stringliteral">"Default layout '"</span>.self::$Config['DefaultLayout'].<span class="stringliteral">"' not found in layout list!"</span>, E_USER_ERROR);
<a name="l00369"></a>00369                         }
<a name="l00370"></a>00370                 }
<a name="l00371"></a>00371                 <span class="keywordflow">else</span>
<a name="l00372"></a>00372                 {
<a name="l00373"></a>00373                         self::$Layout = self::$LayoutList[$_GET[<span class="charliteral">'a'</span>]];
<a name="l00374"></a>00374                 }
<a name="l00375"></a>00375                 
<a name="l00377"></a>00377         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="58fd0063e86c9cbb6470b3c560fcd37b"></a><!-- doxytag: member="Core::LoadDOM" ref="58fd0063e86c9cbb6470b3c560fcd37b" args="($source, &amp;$r_variable)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadDOM           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">&amp;$&nbsp;</td>
          <td class="paramname"> <em>r_variable</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load some XML, validate and turn it into a DOM tree.<p>
<dl compact><dt><b>Note:</b></dt><dd>This method checks whether target XML file is accessible and raises error on failure.</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source</em>&nbsp;</td><td>string Path to XML file to load </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$r_variable</em>&nbsp;</td><td>mixed Link to variable which will get DOM after loading</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Check if target file is accessible<p>
Load file <div class="fragment"><pre class="fragment"><a name="l00390"></a>00390         {
<a name="l00392"></a>00392                 <span class="keywordflow">if</span> ( !is_readable($source) ) 
<a name="l00393"></a>00393                 {
<a name="l00394"></a>00394                         trigger_error(<span class="stringliteral">"Could not access "</span>.$source, E_USER_ERROR);
<a name="l00395"></a>00395                 }
<a name="l00396"></a>00396 
<a name="l00397"></a>00397                 $r_variable = <span class="keyword">new</span> DOMDocument();
<a name="l00398"></a>00398 
<a name="l00399"></a>00399                 $r_variable-&gt;preserveWhiteSpace = <span class="keyword">false</span>;
<a name="l00400"></a>00400                 
<a name="l00402"></a>00402                 $r_variable-&gt;load($source);
<a name="l00403"></a>00403 
<a name="l00404"></a>00404                 <span class="comment">/*</span>
<a name="l00406"></a>00406 <span class="comment">                if ( !$r_variable-&gt;validate() )</span>
<a name="l00407"></a>00407 <span class="comment">                {</span>
<a name="l00408"></a>00408 <span class="comment">                        trigger_error("File ".$source." failed DTD validation! ".$php_errormsg."..", E_USER_ERROR);</span>
<a name="l00409"></a>00409 <span class="comment">                }</span>
<a name="l00410"></a>00410 <span class="comment">                */</span>
<a name="l00411"></a>00411         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="9b4be0a58f539006b8c05287d4cda753"></a><!-- doxytag: member="Core::ParseModuleListDOM" ref="9b4be0a58f539006b8c05287d4cda753" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseModuleListDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parse module list XML, <a class="el" href="a00004.html">Core</a>::$ModuleList gets a parsed module list on success.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Get all &lt;module ... /&gt; tags<p>
Run through the whole list of modules<p>
Now run through attribute list for each module<p>
Go through settings list as well<p>
Tree was successfully parsed! <div class="fragment"><pre class="fragment"><a name="l00419"></a>00419         {
<a name="l00421"></a>00421                 $modules = self::$ModuleListDOM-&gt;getElementsByTagName(<span class="stringliteral">"module"</span>);
<a name="l00422"></a>00422 
<a name="l00424"></a>00424                 foreach ( $modules as $current_module )
<a name="l00425"></a>00425                 {
<a name="l00427"></a>00427                         $r_module =&amp; self::$ModuleList[$current_module-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00428"></a>00428 
<a name="l00430"></a>00430                         foreach ( $current_module-&gt;attributes as $attribute )
<a name="l00431"></a>00431                         {
<a name="l00432"></a>00432                                 $r_module[$attribute-&gt;name] = $attribute-&gt;value;
<a name="l00433"></a>00433                         }
<a name="l00434"></a>00434 
<a name="l00436"></a>00436                         <span class="keywordflow">if</span> ( $current_module-&gt;hasChildNodes() )
<a name="l00437"></a>00437                         {
<a name="l00438"></a>00438                                 foreach ( $current_module-&gt;childNodes as $setting )
<a name="l00439"></a>00439                                 {
<a name="l00440"></a>00440                                         $setting_name = $setting-&gt;attributes-&gt;getNamedItem(<span class="stringliteral">"name"</span>)-&gt;value;
<a name="l00441"></a>00441 
<a name="l00442"></a>00442                                         foreach ( $setting-&gt;attributes as $setting_attribute )
<a name="l00443"></a>00443                                         {
<a name="l00444"></a>00444 
<a name="l00445"></a>00445                                                 $r_module['settings'][$setting_name][$setting_attribute-&gt;name] = $setting_attribute-&gt;value;
<a name="l00446"></a>00446                                         }
<a name="l00447"></a>00447                                 }
<a name="l00448"></a>00448                         }
<a name="l00449"></a>00449                 }
<a name="l00450"></a>00450 
<a name="l00452"></a>00452         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="92fe821bc14994668b0d2e58d2fb71ef"></a><!-- doxytag: member="Core::ParseLayoutListDOM" ref="92fe821bc14994668b0d2e58d2fb71ef" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseLayoutListDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parse layout list XML file into <a class="el" href="a00004.html">Core</a>::$LayoutList.<p>
<dl compact><dt><b>Note:</b></dt><dd>Surely I know that it was copypasted (y&amp;p'ed, to be exactly) from <a class="el" href="a00004.html#9b4be0a58f539006b8c05287d4cda753">Core::ParseModuleListDOM()</a> method. So what? <p>
You may ask, "Why is DOM extension used here instead of fluffy SimpleXML?". Answer: SimpleXML is n00b shit. We need tr00 DOM functions to op modules in future.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Get all &lt;layout ... /&gt; tags<p>
Run through the whole list of layouts<p>
Now run through attribute list for each layout<p>
Tree was successfully parsed! <div class="fragment"><pre class="fragment"><a name="l00463"></a>00463         {
<a name="l00465"></a>00465                 $layouts = self::$LayoutListDOM-&gt;getElementsByTagName(<span class="stringliteral">"layout"</span>);
<a name="l00466"></a>00466 
<a name="l00468"></a>00468                 foreach ( $layouts as $current_layout )
<a name="l00469"></a>00469                 {
<a name="l00471"></a>00471                         $r_layout =&amp; self::$LayoutList[$current_layout-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00472"></a>00472 
<a name="l00474"></a>00474                         foreach ( $current_layout-&gt;attributes as $attribute )
<a name="l00475"></a>00475                         {
<a name="l00476"></a>00476                                 $r_layout[$attribute-&gt;name] = $attribute-&gt;value;
<a name="l00477"></a>00477                         }
<a name="l00478"></a>00478                 }
<a name="l00479"></a>00479 
<a name="l00481"></a>00481         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="5ab0c08fca09009556bad907ea604ec1"></a><!-- doxytag: member="Core::ParseConfigDOM" ref="5ab0c08fca09009556bad907ea604ec1" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseConfigDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parses core configuration DOM tree, previously loaded with <a class="el" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> Core::Config() gets a configuration on success<p>
<dl compact><dt><b>Note:</b></dt><dd>All these ParseFooDOM methods are getting smaller and smaller as we move through the class ^_^</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Get all &lt;setting ... /&gt; tags<p>
Run through the whole list of settings<p>
Tree was successfully parsed! <div class="fragment"><pre class="fragment"><a name="l00492"></a>00492         {
<a name="l00494"></a>00494                 $settings = self::$ConfigDOM-&gt;getElementsByTagName(<span class="stringliteral">"setting"</span>);
<a name="l00495"></a>00495 
<a name="l00497"></a>00497                 foreach ( $settings as $current_setting )
<a name="l00498"></a>00498                 {
<a name="l00500"></a>00500                         $r_setting =&amp; self::$Config[$current_setting-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00501"></a>00501 
<a name="l00502"></a>00502                         $r_setting = $current_setting-&gt;getAttribute(<span class="stringliteral">"value"</span>);
<a name="l00503"></a>00503                 }
<a name="l00504"></a>00504 
<a name="l00506"></a>00506         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="119b3cb7f093eefba2e1096baeaadab8"></a><!-- doxytag: member="Core::MakeRunList" ref="119b3cb7f093eefba2e1096baeaadab8" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::MakeRunList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepares a "runlist" in <a class="el" href="a00004.html">Core</a>::$RunList, list of modules to run according to current layout.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Add 'core' modules first<p>
Add modules requested by current layout <div class="fragment"><pre class="fragment"><a name="l00515"></a>00515         {
<a name="l00516"></a>00516 
<a name="l00517"></a>00517                 self::$RunList = array();
<a name="l00518"></a>00518 
<a name="l00519"></a>00519 
<a name="l00521"></a>00521                 foreach ( self::$ModuleList as $module )
<a name="l00522"></a>00522                 {
<a name="l00523"></a>00523                         <span class="keywordflow">if</span> ( $module['type'] == 'core' )
<a name="l00524"></a>00524                         {
<a name="l00525"></a>00525                                 self::AddModuleToRunList($module['name']);
<a name="l00526"></a>00526                         }
<a name="l00527"></a>00527                 }
<a name="l00528"></a>00528 
<a name="l00530"></a>00530                 foreach ( explode(<span class="stringliteral">" "</span>, self::$Layout['modules']) as $module )
<a name="l00531"></a>00531                 {
<a name="l00532"></a>00532                         <span class="keywordflow">if</span> ( !array_key_exists($module, self::$ModuleList) )
<a name="l00533"></a>00533                         {
<a name="l00534"></a>00534                                 trigger_error(<span class="stringliteral">"Module '{$module}' in layout '"</span>.self::$Layout['name'].<span class="stringliteral">"' not found in module list!"</span>, E_USER_ERROR);
<a name="l00535"></a>00535                         }
<a name="l00536"></a>00536 
<a name="l00537"></a>00537                         self::AddModuleToRunlist($module);
<a name="l00538"></a>00538                 }
<a name="l00539"></a>00539         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="4cf7e042593cd3c058bb4eb9bd35c33a"></a><!-- doxytag: member="Core::AddModuleToRunlist" ref="4cf7e042593cd3c058bb4eb9bd35c33a" args="($module)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::AddModuleToRunlist           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>module</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Add module to current runlist. Takes care of all dependency stuff.<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$module</em>&nbsp;</td><td>string Name of module to add</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Check if module has already been added to runlist<p>
Add all deps as well, if there're any <div class="fragment"><pre class="fragment"><a name="l00549"></a>00549         {
<a name="l00551"></a>00551                 <span class="keywordflow">if</span> ( in_array($module, self::$RunList) )
<a name="l00552"></a>00552                 {
<a name="l00553"></a>00553                         <span class="keywordflow">return</span>;
<a name="l00554"></a>00554                 }
<a name="l00555"></a>00555                 <span class="keywordflow">else</span>
<a name="l00556"></a>00556                 {
<a name="l00557"></a>00557                         <span class="keywordflow">if</span> ( self::$ModuleList[$module]['deps'] )
<a name="l00558"></a>00558                         {
<a name="l00560"></a>00560                                 foreach ( explode(<span class="stringliteral">" "</span>, self::$ModuleList[$module]['deps']) as $dep )
<a name="l00561"></a>00561                                 {
<a name="l00562"></a>00562                                         self::AddModuleToRunlist($dep);
<a name="l00563"></a>00563                                 }
<a name="l00564"></a>00564                         }
<a name="l00565"></a>00565 
<a name="l00566"></a>00566                         self::$RunList[] = $module;
<a name="l00567"></a>00567                 }
<a name="l00568"></a>00568         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="4ee5f73d2b08c38a8887b9d51e245300"></a><!-- doxytag: member="Core::CheckModuleList" ref="4ee5f73d2b08c38a8887b9d51e245300" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::CheckModuleList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Check all modules in module list for self-depending or depending on non-existent modules<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00576"></a>00576         {
<a name="l00577"></a>00577                 foreach ( self::$ModuleList as $module )
<a name="l00578"></a>00578                 {
<a name="l00579"></a>00579                         self::CheckModuleDependencies($module['name']);
<a name="l00580"></a>00580                 }
<a name="l00581"></a>00581         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="ce80a07ac8a6d86d867705a8061a9a9b"></a><!-- doxytag: member="Core::CheckModuleDependencies" ref="ce80a07ac8a6d86d867705a8061a9a9b" args="($module, $tracePath=array())" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::CheckModuleDependencies           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>module</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>tracePath</em> = <code>array&nbsp;()</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Traces a dependency tree for module, if any module is encountered in its own dep-tree or non-existent dependency encountered, generate an error.<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$module</em>&nbsp;</td><td>string Name of module to check </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$tracePath</em>&nbsp;</td><td>array Dep-tree for current module (used only for recurrent walk-through, do not use!)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Warning:</b></dt><dd>DO NOT pass any argument other than $module!</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>boolean TRUE if check succeeded </dd></dl>

<p>
Wasn't this dependency in our trace path?<p>
Does this dependecy exist at all?<p>
add dep to trace path <div class="fragment"><pre class="fragment"><a name="l00596"></a>00596         {
<a name="l00597"></a>00597                 <span class="keywordflow">if</span> ( self::$ModuleList[$module]['deps'] == <span class="stringliteral">""</span> )
<a name="l00598"></a>00598                 {
<a name="l00599"></a>00599                         <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00600"></a>00600                 }
<a name="l00601"></a>00601 
<a name="l00602"></a>00602                 foreach ( explode(<span class="stringliteral">" "</span>, self::$ModuleList[$module]['deps']) as $dep )
<a name="l00603"></a>00603                 {
<a name="l00604"></a>00604 
<a name="l00606"></a>00606                         <span class="keywordflow">if</span> ( in_array($dep, $tracePath) ) 
<a name="l00607"></a>00607                         {
<a name="l00608"></a>00608                                 trigger_error(<span class="stringliteral">"Module dependency loop encountered: {$module} -&gt; "</span>.join(<span class="stringliteral">" -&gt; "</span>, $tracePath ), E_USER_ERROR );
<a name="l00609"></a>00609                         }
<a name="l00610"></a>00610 
<a name="l00612"></a>00612                         <span class="keywordflow">if</span> ( !array_key_exists($dep, self::$ModuleList) )
<a name="l00613"></a>00613                         {
<a name="l00614"></a>00614                                 trigger_error(<span class="stringliteral">"Module '{$module}' depends on non-existing module {$dep}!"</span>, E_USER_ERROR);
<a name="l00615"></a>00615                         }
<a name="l00616"></a>00616 
<a name="l00618"></a>00618                         $tracePath[] = $dep;
<a name="l00619"></a>00619 
<a name="l00620"></a>00620                         self::CheckModuleDependencies($dep, $tracePath);
<a name="l00621"></a>00621 
<a name="l00622"></a>00622                 }
<a name="l00623"></a>00623 
<a name="l00624"></a>00624                 <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00625"></a>00625         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="992b63771f336cc051fcf9e59f6b143f"></a><!-- doxytag: member="Core::IsCacheOk" ref="992b63771f336cc051fcf9e59f6b143f" args="($source_file, $prefix=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::IsCacheOk           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source_file</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>prefix</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Checks whether cache for specified target file is up to date<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source_file</em>&nbsp;</td><td>string Name of 'source' file </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$prefix</em>&nbsp;</td><td>string Source-specific cache prefix</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>boolean Return true if cache is up to date and false if it's too old or does not exist at all </dd></dl>

<p>
Check if cache doesn't exist<p>
Check for existence of source file<p>
Check that source file is older than cache <div class="fragment"><pre class="fragment"><a name="l00636"></a>00636         {
<a name="l00637"></a>00637                 $cache_file = self::GetCacheName($source_file, $prefix); 
<a name="l00638"></a>00638 
<a name="l00640"></a>00640                 <span class="keywordflow">if</span> ( !is_readable($cache_file) )
<a name="l00641"></a>00641                 {
<a name="l00642"></a>00642                         <span class="keywordflow">return</span> <span class="keyword">false</span>;
<a name="l00643"></a>00643                 }
<a name="l00644"></a>00644 
<a name="l00646"></a>00646                 <span class="keywordflow">if</span> ( !is_readable($source_file) )
<a name="l00647"></a>00647                 {
<a name="l00648"></a>00648                         trigger_error(<span class="stringliteral">"Could not access {$source_file}"</span>, E_USER_ERROR);
<a name="l00649"></a>00649                 }
<a name="l00650"></a>00650 
<a name="l00652"></a>00652                 <span class="keywordflow">if</span> ( filemtime($source_file) &gt;= filemtime($cache_file) )
<a name="l00653"></a>00653                 {
<a name="l00654"></a>00654                         <span class="keywordflow">return</span> <span class="keyword">false</span>;
<a name="l00655"></a>00655                 }
<a name="l00656"></a>00656 
<a name="l00657"></a>00657                 <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00658"></a>00658         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="7e860483a3033de70dc374e452900079"></a><!-- doxytag: member="Core::GetCacheName" ref="7e860483a3033de70dc374e452900079" args="($source_file, $prefix=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetCacheName           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source_file</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>prefix</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get name of cache for target file<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source_file</em>&nbsp;</td><td>string Target file name </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$prefix</em>&nbsp;</td><td>string Cache prefix. Useful to when caching several different files with the same names</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>string Path to cache file </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00669"></a>00669          {
<a name="l00670"></a>00670                  <span class="keywordflow">if</span> ( strlen($prefix) )
<a name="l00671"></a>00671                  {
<a name="l00672"></a>00672                          $prefix .= <span class="charliteral">'.'</span>;
<a name="l00673"></a>00673                  }
<a name="l00674"></a>00674                  <span class="keywordflow">return</span> self::$Config['CacheDir'].<span class="stringliteral">"/"</span>.$prefix.$source_file.<span class="stringliteral">".cache.php"</span>;
<a name="l00675"></a>00675          }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="9c2596c82e620380c71bed110db40ca7"></a><!-- doxytag: member="Core::DumpArrayToCache" ref="9c2596c82e620380c71bed110db40ca7" args="($contents, $file)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::DumpArrayToCache           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>contents</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>file</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Dump some array into specified cache file. Currently used to rebuild module list and layout list caches.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#1735ab51c34cbedda6b963629de1839d">Core::ArrayToDefinition()</a> <a class="el" href="a00004.html#7e860483a3033de70dc374e452900079">Core::GetCacheName()</a></dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$contents</em>&nbsp;</td><td>array Contents of array to be cached </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$file</em>&nbsp;</td><td>string Path to destination file (cache file)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Note:</b></dt><dd>It may be needed to use <a class="el" href="a00004.html#7e860483a3033de70dc374e452900079">Core::GetCacheName()</a> method to make a proper path to destination file to pass it as $file argument for this method <p>
Cache file will RETURN proper array definition, not declare it, so to use cache contents later you'll need to do it in a such way: $some_variable=require("cache.file.php") <p>
I hope you catch the idea of previous note.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

<p>
Start forming a cache<p>
Finalize cache file <div class="fragment"><pre class="fragment"><a name="l00694"></a>00694         {
<a name="l00695"></a>00695                 <span class="keywordflow">if</span> ( !is_array($contents) )
<a name="l00696"></a>00696                 {
<a name="l00697"></a>00697                         trigger_error(<span class="stringliteral">"Attempted to cache non-array datatype with Core::DumpArrayToCache"</span>, E_USER_WARNING);
<a name="l00698"></a>00698                 }
<a name="l00699"></a>00699 
<a name="l00701"></a>00701                 $cache .= <span class="stringliteral">"&lt;?php\nreturn "</span>;
<a name="l00702"></a>00702 
<a name="l00703"></a>00703                 $cache .= self::ArrayToDefinition($contents);
<a name="l00704"></a>00704 
<a name="l00706"></a>00706                 $cache .= <span class="stringliteral">";\n?&gt;"</span>;
<a name="l00707"></a>00707                 <span class="keywordflow">if</span> ( @!file_put_contents($file, $cache) )
<a name="l00708"></a>00708                 {
<a name="l00709"></a>00709                         trigger_error(<span class="stringliteral">"Could not write to {$file}!"</span>, E_USER_ERROR);
<a name="l00710"></a>00710                 }
<a name="l00711"></a>00711         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="1735ab51c34cbedda6b963629de1839d"></a><!-- doxytag: member="Core::ArrayToDefinition" ref="1735ab51c34cbedda6b963629de1839d" args="($array, $cache=&quot;&quot;, $inner=false)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ArrayToDefinition           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>array</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>cache</em> = <code>&quot;&quot;</code>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>inner</em> = <code>false</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Convert an array to piece of valid PHP array definition, used for caching in <a class="el" href="a00004.html#9c2596c82e620380c71bed110db40ca7">Core::DumpArrayToCache()</a> method.<p>
<dl compact><dt><b><a class="el" href="todo.html#_todo000003">Todo:</a></b></dt><dd>It would be nice to make ArrayToDefinition method public in future</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$array</em>&nbsp;</td><td>array Array to convert </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$cache</em>&nbsp;</td><td>string Currently formed cache </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$inner</em>&nbsp;</td><td>boolean Whether this function was called from itself</td></tr>
  </table>
</dl>
<dl compact><dt><b>Warning:</b></dt><dd>DO NOT pass any argument other than $array</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>string Valid PHP array definition </dd></dl>

<p>
recurrent function call <div class="fragment"><pre class="fragment"><a name="l00727"></a>00727         {
<a name="l00728"></a>00728                 $cache = <span class="stringliteral">"array (\n"</span>;
<a name="l00729"></a>00729                         
<a name="l00730"></a>00730                 foreach ( $array as $key =&gt; $value )
<a name="l00731"></a>00731                 {
<a name="l00732"></a>00732                         <span class="keywordflow">if</span> ( gettype($value) == <span class="stringliteral">"array"</span> )
<a name="l00733"></a>00733                         {
<a name="l00735"></a>00735                                 $cache .= <span class="stringliteral">"\"{$key}\" =&gt; "</span>.self::ArrayToDefinition($value, $cache, <span class="keyword">true</span>);
<a name="l00736"></a>00736                         }
<a name="l00737"></a>00737                         <span class="keywordflow">else</span>
<a name="l00738"></a>00738                         {
<a name="l00739"></a>00739                                 $cache .= <span class="stringliteral">"\"{$key}\" =&gt; \"{$value}\",\n"</span>;
<a name="l00740"></a>00740                         }
<a name="l00741"></a>00741                 }
<a name="l00742"></a>00742 
<a name="l00743"></a>00743                 $cache .= <span class="stringliteral">")"</span>;
<a name="l00744"></a>00744 
<a name="l00745"></a>00745                 <span class="keywordflow">if</span> ( $inner ) $cache .= <span class="stringliteral">",\n"</span>;
<a name="l00746"></a>00746 
<a name="l00747"></a>00747                 <span class="keywordflow">return</span> $cache;
<a name="l00748"></a>00748         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="cb511097b2a57cd46dfde95b9600c68d"></a><!-- doxytag: member="Core::GetDebugInfo" ref="cb511097b2a57cd46dfde95b9600c68d" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetDebugInfo           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get miscellanous information. 
<p>
Method provides us various information on user request data, session variables, <a class="el" href="a00004.html">Core</a>::$Config and timers' values.<p>
<dl compact><dt><b>Returns:</b></dt><dd>array Array containing information </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00758"></a>00758         {
<a name="l00759"></a>00759                 $message[] = <span class="stringliteral">"[REQUEST]"</span>;
<a name="l00760"></a>00760                 foreach ( $_REQUEST as $key =&gt; $value )
<a name="l00761"></a>00761                 {
<a name="l00762"></a>00762                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00763"></a>00763                 }
<a name="l00764"></a>00764 
<a name="l00765"></a>00765                 $message[] = <span class="stringliteral">"[SESSION]"</span>;
<a name="l00766"></a>00766                 foreach ( $_SESSION as $key =&gt; $value )
<a name="l00767"></a>00767                 {
<a name="l00768"></a>00768                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00769"></a>00769                 }
<a name="l00770"></a>00770 
<a name="l00771"></a>00771                 $message[] = <span class="stringliteral">"[COOKIE]"</span>;
<a name="l00772"></a>00772                 foreach ( $_COOKIE as $key =&gt; $value )
<a name="l00773"></a>00773                 {
<a name="l00774"></a>00774                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00775"></a>00775                 }
<a name="l00776"></a>00776 
<a name="l00777"></a>00777                 $message[] = <span class="stringliteral">"[CONFIG]"</span>;
<a name="l00778"></a>00778                 foreach ( self::$Config as $key =&gt; $value )
<a name="l00779"></a>00779                 {
<a name="l00780"></a>00780                         <span class="keywordflow">if</span> ( $value == <span class="keyword">false</span> ) $value = 0;
<a name="l00781"></a>00781                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00782"></a>00782                 }
<a name="l00783"></a>00783 
<a name="l00784"></a>00784                 $message[] = <span class="stringliteral">"[TIMERS]"</span>;
<a name="l00785"></a>00785                 foreach ( self::$Timers as $timer =&gt; $Value )
<a name="l00786"></a>00786                 {
<a name="l00787"></a>00787                         $message[] = <span class="stringliteral">"Timer #{$timer}: "</span>.self::GetTimer($timer);
<a name="l00788"></a>00788                 }
<a name="l00789"></a>00789 
<a name="l00790"></a>00790                 <span class="keywordflow">return</span> $message;
<a name="l00791"></a>00791         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="6d24271a9d5c59bf494eaeff5175f15a"></a><!-- doxytag: member="Core::Error" ref="6d24271a9d5c59bf494eaeff5175f15a" args="($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Error           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorCode</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorMessage</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorFile</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorLine</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorContext</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Built-in error handler. 
<p>
Generates error messages of different types, depending on $ErrorCode value.<br>
 On E_USER_ERROR generates generic fatal error message and halts the system. If $Config['MoreErrorInfo']is true, additional information is included in the output (<a class="el" href="a00004.html#cb511097b2a57cd46dfde95b9600c68d">Core::GetDebugInfo()</a> and error line/file ).<br>
 If E_USER_NOTICE or E_USER_WARNING occur, adds a new entry into <a class="el" href="a00004.html">Core</a>::$Errors container.<p>
<dl compact><dt><b>Warning:</b></dt><dd>Do not call this method directly, use<pre>trigger_error("OMG error!", E_USER_ERROR);</pre> instead!</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$errorCode</em>&nbsp;</td><td>E_USER_ERROR, E_USER_WARNING or E_USER_NOTICE </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$errorMessage</em>&nbsp;</td><td>string Text of error </td></tr>
  </table>
</dl>

<p>
Array containing CSS styles for messages<p>
Generate the whole page on critical errors and halt system<p>
Add entry to <a class="el" href="a00004.html">Core</a>::$Errors on non-critical errors <div class="fragment"><pre class="fragment"><a name="l00809"></a>00809         {
<a name="l00811"></a>00811                 $styles = array 
<a name="l00812"></a>00812                 (
<a name="l00813"></a>00813                         E_USER_ERROR =&gt; <span class="stringliteral">"padding: 15%; text-align: center; margin-top: 10px; margin-left: 10px; margin-right: 10px; font-size: x-large; background-color: #f58169; font-weight: bold; color: #571b1b; valign: center;"</span>,
<a name="l00814"></a>00814                         E_USER_NOTICE =&gt; <span class="stringliteral">"background-color: #a1ea8d; text-align: left; font-size: medium; color: black; padding: 2%;"</span>,
<a name="l00815"></a>00815                         E_USER_WARNING =&gt; <span class="stringliteral">"background-color: #eac583; text-align: left; font-size: large; color: #571b1b; padding: 2%;"</span>
<a name="l00816"></a>00816                 );
<a name="l00817"></a>00817 
<a name="l00818"></a>00818 
<a name="l00820"></a>00820                 <span class="keywordflow">if</span> ( $errorCode == E_USER_ERROR )
<a name="l00821"></a>00821                 {
<a name="l00822"></a>00822                         <span class="keywordflow">if</span> ( self::$Config['MoreErrorInfo'] )
<a name="l00823"></a>00823                         {
<a name="l00824"></a>00824                                 $errorMessage .= <span class="stringliteral">"&lt;br /&gt; File {$errorFile}, line {$errorLine}"</span>;
<a name="l00825"></a>00825                                 $errorMessage .= <span class="stringliteral">"&lt;br /&gt;"</span>.join(self::GetDebugInfo(), <span class="stringliteral">"&lt;br /&gt;"</span>);
<a name="l00826"></a>00826                         }
<a name="l00827"></a>00827 
<a name="l00828"></a>00828                         echo <span class="stringliteral">"</span>
<a name="l00829"></a>00829 <span class="stringliteral">                        &lt;?xml version=\"1.0\" encoding=\"utf-8\"&gt;</span>
<a name="l00830"></a>00830 <span class="stringliteral">                        &lt;!DOCTYPE html </span>
<a name="l00831"></a>00831 <span class="stringliteral">                        PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"</span>
<a name="l00832"></a>00832 <span class="stringliteral">                        \"http://w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"&gt;</span>
<a name="l00833"></a>00833 <span class="stringliteral">                        &lt;html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\"&gt;</span>
<a name="l00834"></a>00834 <span class="stringliteral">                                &lt;head&gt;</span>
<a name="l00835"></a>00835 <span class="stringliteral">                                        &lt;title&gt;Core Error&lt;/title&gt;</span>
<a name="l00836"></a>00836 <span class="stringliteral">                                &lt;/head&gt;</span>
<a name="l00837"></a>00837 <span class="stringliteral">                                &lt;body&gt;</span>
<a name="l00838"></a>00838 <span class="stringliteral">                                        &lt;div style=\"{$styles[E_USER_ERROR]}\"&gt; </span>
<a name="l00839"></a>00839 <span class="stringliteral">                                        {$errorMessage}</span>
<a name="l00840"></a>00840 <span class="stringliteral">                                        &lt;/div&gt;</span>
<a name="l00841"></a>00841 <span class="stringliteral">                                &lt;/body&gt;</span>
<a name="l00842"></a>00842 <span class="stringliteral">                        &lt;/html&gt;</span>
<a name="l00843"></a>00843 <span class="stringliteral">                        "</span>;
<a name="l00844"></a>00844 
<a name="l00845"></a>00845                         exit ();
<a name="l00846"></a>00846                 }
<a name="l00847"></a>00847                 <span class="keywordflow">else</span>
<a name="l00848"></a>00848                 {
<a name="l00850"></a>00850                         self::$Errors[] = array
<a name="l00851"></a>00851                         (
<a name="l00852"></a>00852                                 'errorCode' =&gt; $errorCode,
<a name="l00853"></a>00853                                 'errorFile' =&gt; $errorFile,
<a name="l00854"></a>00854                                 'errorMessage' =&gt; $errorMessage,
<a name="l00855"></a>00855                         );
<a name="l00856"></a>00856                 }
<a name="l00857"></a>00857         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="c8a8d74c512360172e80b0343028b605"></a><!-- doxytag: member="Core::GetConfig" ref="c8a8d74c512360172e80b0343028b605" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetConfig           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get current <a class="el" href="a00004.html">Core</a> config<p>
<dl compact><dt><b>Returns:</b></dt><dd>array <a class="el" href="a00004.html">Core</a>::$Config </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00865"></a>00865         {
<a name="l00866"></a>00866                 <span class="keywordflow">return</span> self::$Config;
<a name="l00867"></a>00867         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="f7fea025d37b95401bcad814d34fb4f5"></a><!-- doxytag: member="Core::StartTimer" ref="f7fea025d37b95401bcad814d34fb4f5" args="($number=1)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::StartTimer           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>number</em> = <code>1</code>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Start ticking specified timer<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$number</em>&nbsp;</td><td>integer Number of timer to start ticking</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00877"></a>00877         {
<a name="l00878"></a>00878                 self::$Timers[$number] = microtime();
<a name="l00879"></a>00879         }
</pre></div>
<p>

</div>
</div><p>
<a class="anchor" name="b779a4778fcbfe097daab723acb4f092"></a><!-- doxytag: member="Core::GetTimer" ref="b779a4778fcbfe097daab723acb4f092" args="($number=1)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetTimer           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>number</em> = <code>1</code>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get specified timer value<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$number</em>&nbsp;</td><td>integer Number of timer which is about to give us its current value lol</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>integer Current timer value </dd></dl>
<div class="fragment"><pre class="fragment"><a name="l00889"></a>00889         {
<a name="l00890"></a>00890                <span class="keywordflow">return</span> ( microtime() - self::$Timers[$number] ); 
<a name="l00891"></a>00891         }
</pre></div>
<p>

</div>
</div><p>
<hr><h2>Member Data Documentation</h2>
<a class="anchor" name="0a4233d6c7fb2f45a854df1e395c9326"></a><!-- doxytag: member="Core::$Config" ref="0a4233d6c7fb2f45a854df1e395c9326" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Config<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Core configuration container.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">Core::LoadConfig()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="8f4a69e9060afbb9eea926f029e41055"></a><!-- doxytag: member="Core::$ConfigDOM" ref="8f4a69e9060afbb9eea926f029e41055" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ConfigDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with core configuration 
</div>
</div><p>
<a class="anchor" name="9669b1e25f58a6327346627e0718714b"></a><!-- doxytag: member="Core::$ModuleList" ref="9669b1e25f58a6327346627e0718714b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ModuleList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parsed module list.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#da9dbb1f356160b8174d1d815e75120f">Core::LoadModuleList()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="19f8bd4f1c50b416fa32043a7a53b715"></a><!-- doxytag: member="Core::$ModuleListDOM" ref="19f8bd4f1c50b416fa32043a7a53b715" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ModuleListDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with module list. 
</div>
</div><p>
<a class="anchor" name="de337a75ba1c07b69c1a17b63313f18f"></a><!-- doxytag: member="Core::$Layout" ref="de337a75ba1c07b69c1a17b63313f18f" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Layout<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Current layout container.<p>
<dl compact><dt><b>Note:</b></dt><dd>This is NOT the name of current layout, but the whole corresponding ENTRY from <a class="el" href="a00004.html">Core</a>::$LayoutList! </dd></dl>

</div>
</div><p>
<a class="anchor" name="c23a132a6ef90503a264e5f2b16c81d5"></a><!-- doxytag: member="Core::$LayoutList" ref="c23a132a6ef90503a264e5f2b16c81d5" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$LayoutList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parsed layout list.<p>
<dl compact><dt><b>See also:</b></dt><dd>Core::LoadLayoutList() </dd></dl>

</div>
</div><p>
<a class="anchor" name="89f8206b99ec81d78b8604b47768b757"></a><!-- doxytag: member="Core::$LayoutListDOM" ref="89f8206b99ec81d78b8604b47768b757" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$LayoutListDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with layout list. 
</div>
</div><p>
<a class="anchor" name="135f0995570f8535b094481c563132f4"></a><!-- doxytag: member="Core::$RunList" ref="135f0995570f8535b094481c563132f4" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$RunList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
List of modules to run in current layout<p>
<dl compact><dt><b>Note:</b></dt><dd>This list contains list of module NAMES from <a class="el" href="a00004.html">Core</a>::$ModuleList</dd></dl>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#119b3cb7f093eefba2e1096baeaadab8">Core::MakeRunList()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="26ebc052e21bea592bfba86e793dc48b"></a><!-- doxytag: member="Core::$CurrentSkin" ref="26ebc052e21bea592bfba86e793dc48b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$CurrentSkin<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Internal name of currently selected skin for user. Skin internal name is a name of directory in default system skin folder.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#b013cd82ca954a4c4f31d30ad3a9901a">Core::SetSkin()</a> <a class="el" href="a00004.html#8b789def87026eb966ddf3ad2babfae3">Core::RestoreSkinAndLang()</a> <a class="el" href="a00004.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="855055f9bbc19bc705ea05747653644a"></a><!-- doxytag: member="Core::$CurrentLang" ref="855055f9bbc19bc705ea05747653644a" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$CurrentLang<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Internal name of currently selected language for user Language internal name is a name of directory in default system language folder<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#ab69b5da88ad2901f68b2e010e0ec308">Core::SetLang()</a> <a class="el" href="a00004.html#8b789def87026eb966ddf3ad2babfae3">Core::RestoreSkinAndLang()</a> <a class="el" href="a00004.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="6d83c303b0d63c3c5157b062e1a12d5a"></a><!-- doxytag: member="Core::$Timers" ref="6d83c303b0d63c3c5157b062e1a12d5a" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Timers<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Timers container, used for perfomance measuring.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="a00004.html#f7fea025d37b95401bcad814d34fb4f5">Core::StartTimer()</a> <a class="el" href="a00004.html#b779a4778fcbfe097daab723acb4f092">Core::GetTimer()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="1cbc06a311ea7ac7fcc4dc60fbede26d"></a><!-- doxytag: member="Core::$Errors" ref="1cbc06a311ea7ac7fcc4dc60fbede26d" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Errors<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Errors container, used to keep non-fatal error entries generated via <a class="el" href="a00004.html#6d24271a9d5c59bf494eaeff5175f15a">Core::Error()</a>. 
</div>
</div><p>
<hr>The documentation for this class was generated from the following file:<ul>
<li>/home/sphinx/Projects/PHP/PhpWS/<a class="el" href="a00006.html">core.php</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                     doc/html/a00002.html                                                                                0000644 0001750 0001750 00000022325 10472257756 013706  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>Web System: Member List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="index.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>Core Member List</h1>This is the complete list of members for <a class="el" href="a00004.html">Core</a>, including all inherited members.<p><table>
  <tr class="memlist"><td><a class="el" href="a00004.html#0a4233d6c7fb2f45a854df1e395c9326">$Config</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#8f4a69e9060afbb9eea926f029e41055">$ConfigDOM</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#855055f9bbc19bc705ea05747653644a">$CurrentLang</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#26ebc052e21bea592bfba86e793dc48b">$CurrentSkin</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#1cbc06a311ea7ac7fcc4dc60fbede26d">$Errors</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#de337a75ba1c07b69c1a17b63313f18f">$Layout</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#c23a132a6ef90503a264e5f2b16c81d5">$LayoutList</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#89f8206b99ec81d78b8604b47768b757">$LayoutListDOM</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#9669b1e25f58a6327346627e0718714b">$ModuleList</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#19f8bd4f1c50b416fa32043a7a53b715">$ModuleListDOM</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#135f0995570f8535b094481c563132f4">$RunList</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#6d83c303b0d63c3c5157b062e1a12d5a">$Timers</a></td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#4cf7e042593cd3c058bb4eb9bd35c33a">AddModuleToRunlist</a>($module)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#1735ab51c34cbedda6b963629de1839d">ArrayToDefinition</a>($array, $cache=&quot;&quot;, $inner=false)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#ce80a07ac8a6d86d867705a8061a9a9b">CheckModuleDependencies</a>($module, $tracePath=array())</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#4ee5f73d2b08c38a8887b9d51e245300">CheckModuleList</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#9c2596c82e620380c71bed110db40ca7">DumpArrayToCache</a>($contents, $file)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#6d24271a9d5c59bf494eaeff5175f15a">Error</a>($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#7e860483a3033de70dc374e452900079">GetCacheName</a>($source_file, $prefix=&quot;&quot;)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#c8a8d74c512360172e80b0343028b605">GetConfig</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#cb511097b2a57cd46dfde95b9600c68d">GetDebugInfo</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#85d74c60de9057926fa4abacaa71f3e9">GetSkinAndLang</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#b779a4778fcbfe097daab723acb4f092">GetTimer</a>($number=1)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#992b63771f336cc051fcf9e59f6b143f">IsCacheOk</a>($source_file, $prefix=&quot;&quot;)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">LoadConfig</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#58fd0063e86c9cbb6470b3c560fcd37b">LoadDOM</a>($source, &amp;$r_variable)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#7c84d7f528dfa0178955627a1f83a715">LoadLayout</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#da9dbb1f356160b8174d1d815e75120f">LoadModuleList</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#119b3cb7f093eefba2e1096baeaadab8">MakeRunList</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#5ab0c08fca09009556bad907ea604ec1">ParseConfigDOM</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#92fe821bc14994668b0d2e58d2fb71ef">ParseLayoutListDOM</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#9b4be0a58f539006b8c05287d4cda753">ParseModuleListDOM</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#8b789def87026eb966ddf3ad2babfae3">RestoreSkinAndLang</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#d1b6c2ef037c930720235b0bfd51a13b">Run</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#ab69b5da88ad2901f68b2e010e0ec308">SetLang</a>($lang)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#b013cd82ca954a4c4f31d30ad3a9901a">SetSkin</a>($skin)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#f7fea025d37b95401bcad814d34fb4f5">StartTimer</a>($number=1)</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="a00004.html#3a99f8ccf5081616ebc92d9af3a9562a">Startup</a>()</td><td><a class="el" href="a00004.html">Core</a></td><td><code> [private, static]</code></td></tr>
</table><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 11:25:34 2006 for Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                           doc/html/files.html                                                                                 0000644 0001750 0001750 00000003030 10472421406 014157  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: File Index</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>YAMWS: Yet Another Modular Web System File List</h1>Here is a list of all files with brief descriptions:<table>
  <tr><td class="indexkey">/home/sphinx/Projects/PHP/YAMWS/<a class="el" href="base__module_8php.html">base_module.php</a></td><td class="indexvalue"></td></tr>
  <tr><td class="indexkey">/home/sphinx/Projects/PHP/YAMWS/<a class="el" href="core_8php.html">core.php</a></td><td class="indexvalue"></td></tr>
  <tr><td class="indexkey">/home/sphinx/Projects/PHP/YAMWS/<a class="el" href="index_8php.html">index.php</a></td><td class="indexvalue"></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        doc/html/pages.html                                                                                 0000644 0001750 0001750 00000002177 10472421406 014167  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Page Index</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li id="current"><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>YAMWS: Yet Another Modular Web System Related Pages</h1>Here is a list of all related documentation pages:<ul>
<li><a class="el" href="todo.html">Todo List</a>

</ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                 doc/html/main.html                                                                                  0000644 0001750 0001750 00000002024 10472421406 014003  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Main Page</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li id="current"><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>YAMWS: Yet Another Modular Web System Documentation</h1>
<p>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            doc/html/base__module_8php-source.html                                                              0000644 0001750 0001750 00000052412 10472274067 017751  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: /home/sphinx/Projects/PHP/YAMWS/base_module.php Source File</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/base_module.php</h1><a href="base__module_8php.html">Go to the documentation of this file.</a><div class="fragment"><pre class="fragment"><a name="l00001"></a>00001 &lt;?php
<a name="l00002"></a>00002 <span class="comment"></span>
<a name="l00003"></a>00003 <span class="comment">/**</span>
<a name="l00004"></a>00004 <span class="comment"> * @brief Abstract module class, providing basic skin and language methods</span>
<a name="l00005"></a>00005 <span class="comment"> * @author Sphinx</span>
<a name="l00006"></a>00006 <span class="comment"> * @date 2006</span>
<a name="l00007"></a>00007 <span class="comment"> *</span>
<a name="l00008"></a>00008 <span class="comment"> * All modules in system should extend this very class to provide single skin&amp;language handling facilities.\n</span>
<a name="l00009"></a>00009 <span class="comment"> * Study BaseModule members' docs to learn more about default module methods and data containers.</span>
<a name="l00010"></a>00010 <span class="comment"> *</span>
<a name="l00011"></a>00011 <span class="comment"> * @note BaseModule errors are handled by Core::Error()</span>
<a name="l00012"></a>00012 <span class="comment"> */</span>
<a name="l00013"></a><a class="code" href="classBaseModule.html">00013</a> <span class="keyword">abstract</span> <span class="keyword">class </span><a class="code" href="classBaseModule.html">BaseModule</a>
<a name="l00014"></a>00014 {<span class="comment"></span>
<a name="l00015"></a>00015 <span class="comment">        /**</span>
<a name="l00016"></a>00016 <span class="comment">         * %Module configuration container</span>
<a name="l00017"></a>00017 <span class="comment">         *</span>
<a name="l00018"></a>00018 <span class="comment">         * @see BaseModule::GetConfig()</span>
<a name="l00019"></a>00019 <span class="comment">         */</span>
<a name="l00020"></a><a class="code" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">00020</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">$Config</a>;
<a name="l00021"></a>00021 <span class="comment"></span>
<a name="l00022"></a>00022 <span class="comment">        /**</span>
<a name="l00023"></a>00023 <span class="comment">         * All HTML data generated by module</span>
<a name="l00024"></a>00024 <span class="comment">         */</span>
<a name="l00025"></a><a class="code" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">00025</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">$Html</a>;
<a name="l00026"></a>00026 <span class="comment"></span>
<a name="l00027"></a>00027 <span class="comment">        /**</span>
<a name="l00028"></a>00028 <span class="comment">         * Module internal name in really_proper_format</span>
<a name="l00029"></a>00029 <span class="comment">         */</span>
<a name="l00030"></a><a class="code" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">00030</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">$ModuleName</a>;
<a name="l00031"></a>00031 <span class="comment"></span>
<a name="l00032"></a>00032 <span class="comment">        /**</span>
<a name="l00033"></a>00033 <span class="comment">         * Module language container.\n</span>
<a name="l00034"></a>00034 <span class="comment">         * It's an associative array where keys are language bit names and values are translated string,</span>
<a name="l00035"></a>00035 <span class="comment">         * e.g.: 'BRD_POSTS' =&gt; 'Board has %d posts'</span>
<a name="l00036"></a>00036 <span class="comment">         */</span>
<a name="l00037"></a><a class="code" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">00037</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">$Lang</a>;
<a name="l00038"></a>00038 <span class="comment"></span>
<a name="l00039"></a>00039 <span class="comment">        /**</span>
<a name="l00040"></a>00040 <span class="comment">         * Prepare module. This method is called by Core before calling module's Startup() method every time module runs!</span>
<a name="l00041"></a>00041 <span class="comment">         *</span>
<a name="l00042"></a>00042 <span class="comment">         * @see BaseModule::Startup() BaseModule::Run()</span>
<a name="l00043"></a>00043 <span class="comment">         */</span>
<a name="l00044"></a><a class="code" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">00044</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseStartup</a> ()
<a name="l00045"></a>00045         {<span class="comment"></span>
<a name="l00046"></a>00046 <span class="comment">                /// Get local copy of Core config</span>
<a name="l00047"></a>00047 <span class="comment"></span>                self::$CoreConfig = <a class="code" href="classCore.html#c8a8d74c512360172e80b0343028b605">Core::GetConfig</a>();
<a name="l00048"></a>00048 <span class="comment"></span>
<a name="l00049"></a>00049 <span class="comment">                /// Load skin&amp;language names from Core</span>
<a name="l00050"></a>00050 <span class="comment"></span>                $s_l = <a class="code" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang</a>();
<a name="l00051"></a>00051                 self::$CurrentSkin = $s_l[0];
<a name="l00052"></a>00052                 self::$CurrentLang = $s_l[1];
<a name="l00053"></a>00053 <span class="comment"></span>
<a name="l00054"></a>00054 <span class="comment">                /// Load language</span>
<a name="l00055"></a>00055 <span class="comment"></span>                self::LoadLang();
<a name="l00056"></a>00056 <span class="comment"></span>
<a name="l00057"></a>00057 <span class="comment">                /// Make $ModuleName</span>
<a name="l00058"></a>00058 <span class="comment"></span>                self::$ModuleName = strtolower(preg_replace(<span class="stringliteral">"/(.)([A-Z])/"</span>, <span class="stringliteral">"$1_$2"</span>, __CLASS__));
<a name="l00059"></a>00059         }
<a name="l00060"></a>00060         <span class="comment"></span>
<a name="l00061"></a>00061 <span class="comment">        /**</span>
<a name="l00062"></a>00062 <span class="comment">         * Module-specific pre-run routines should be defined in this method.</span>
<a name="l00063"></a>00063 <span class="comment">         * Startup() method is called by Core before calling for module's Run() method.</span>
<a name="l00064"></a>00064 <span class="comment">         * @see BaseModule::BaseStartup() BaseModule::Run()</span>
<a name="l00065"></a>00065 <span class="comment">         */</span>
<a name="l00066"></a>00066         <span class="keyword">abstract</span> <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup</a> ();
<a name="l00067"></a>00067 <span class="comment"></span>
<a name="l00068"></a>00068 <span class="comment">        /**</span>
<a name="l00069"></a>00069 <span class="comment">         * Module-specific run routines must be defined in this method. Run() is called by Core after Startup() </span>
<a name="l00070"></a>00070 <span class="comment">         */</span>
<a name="l00071"></a>00071         <span class="keyword">abstract</span> <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">Run</a> ();
<a name="l00072"></a>00072 <span class="comment"></span>
<a name="l00073"></a>00073 <span class="comment">        /**</span>
<a name="l00074"></a>00074 <span class="comment">         * Put specific skin bit to module's $Html</span>
<a name="l00075"></a>00075 <span class="comment">         * Skin bits are .xhtml files in module's skin directory, which is selected as</span>
<a name="l00076"></a>00076 <span class="comment">         *</span>
<a name="l00077"></a>00077 <span class="comment">         * @param $bitName      string  Name of skin bit (.xhtml file in module's skin directory)</span>
<a name="l00078"></a>00078 <span class="comment">         * @param $arguments    array   Associative array, where keys are strings occuring in skin bit, which are replaced on output by corresponding data in values in $arguments.</span>
<a name="l00079"></a>00079 <span class="comment">                                        &lt;b&gt;EXAMPLE&lt;/b&gt;: say, we have $arguments = array("%msg%"=&gt;"Success!"), then all strings '%msg' in skin bit will be replaced by "Success!"</span>
<a name="l00080"></a>00080 <span class="comment">         * @param $useCaching   bool    If TRUE, supercaching for this very call will be used</span>
<a name="l00081"></a>00081 <span class="comment">         * @param $returnData   bool    If TRUE, Skin method will return HTML data instead of adding it to module's $Html.</span>
<a name="l00082"></a>00082 <span class="comment">                                        May be useful when using output of Skin method in another one instead of immediately adding it to $Html</span>
<a name="l00083"></a>00083 <span class="comment">         */</span>
<a name="l00084"></a><a class="code" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">00084</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">Skin</a> ($bitName, $arguments, $useCaching=0, $returnData=0)
<a name="l00085"></a>00085         {
<a name="l00086"></a>00086                 <span class="keywordflow">if</span> ( $useCaching )
<a name="l00087"></a>00087                 {
<a name="l00088"></a>00088                         $prefix = self::$ModuleName.<span class="charliteral">'.'</span>.self::$CurrentSkin.<span class="charliteral">'.'</span>.md5(serialize($arguments));
<a name="l00089"></a>00089 
<a name="l00090"></a>00090                         <span class="keywordflow">if</span> ( Core::IsCacheOK($bitName.'.xhtml', $prefix) )
<a name="l00091"></a>00091                         {
<a name="l00092"></a>00092                                 <span class="keywordflow">return</span> file_get_contents(<a class="code" href="classCore.html#7e860483a3033de70dc374e452900079">Core::GetCacheName</a>($bitName.'.xhtml', $prefix)); 
<a name="l00093"></a>00093                         }
<a name="l00094"></a>00094                 }
<a name="l00095"></a>00095 
<a name="l00096"></a>00096                 $html = @file_get_contents(self::$CoreConfig['SkinDir'].<span class="charliteral">'/'</span>.<a class="code" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">Core::$CurrentSkin</a>.<span class="charliteral">'/'</span>.self::$ModuleName.<span class="charliteral">'/'</span>.$bitName.'.xhtml'); 
<a name="l00097"></a>00097 
<a name="l00098"></a>00098                 <span class="keywordflow">if</span> ( !$html )
<a name="l00099"></a>00099                 {
<a name="l00100"></a>00100                        trigger_error(<span class="stringliteral">"Requested skin bit {$bitName} in skin "</span>.<a class="code" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">Core::$CurrentSkin</a>.<span class="stringliteral">"for module "</span>.self::$ModuleName.<span class="stringliteral">" is missing!"</span>, E_USER_ERROR);
<a name="l00101"></a>00101                 }
<a name="l00102"></a>00102 <span class="comment"></span>
<a name="l00103"></a>00103 <span class="comment">                /// Fucking slow place follows</span>
<a name="l00104"></a>00104 <span class="comment"></span>                <span class="keywordflow">if</span> ( $arguments )
<a name="l00105"></a>00105                 {
<a name="l00106"></a>00106                         foreach ( $arguments as $s =&gt; $r )
<a name="l00107"></a>00107                         {
<a name="l00108"></a>00108                                $html = str_replace($s, $r, $html);
<a name="l00109"></a>00109                         }
<a name="l00110"></a>00110                 }
<a name="l00111"></a>00111 
<a name="l00112"></a>00112                 <span class="keywordflow">if</span> ( $useCaching )
<a name="l00113"></a>00113                 {
<a name="l00114"></a>00114                         <a class="code" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">Core::DumpStringToCache</a>($html, <a class="code" href="classCore.html#7e860483a3033de70dc374e452900079">Core::GetCacheName</a>($bitName.'.xhtml', $prefix));
<a name="l00115"></a>00115                 }
<a name="l00116"></a>00116 
<a name="l00117"></a>00117                 <span class="keywordflow">if</span> ( $returnData )
<a name="l00118"></a>00118                 {
<a name="l00119"></a>00119                        <span class="keywordflow">return</span> $html;
<a name="l00120"></a>00120                 }
<a name="l00121"></a>00121                 <span class="keywordflow">else</span>
<a name="l00122"></a>00122                 {
<a name="l00123"></a>00123                        self::$Html .= $html;
<a name="l00124"></a>00124                 }
<a name="l00125"></a>00125         }
<a name="l00126"></a>00126 <span class="comment"></span>
<a name="l00127"></a>00127 <span class="comment">        /**</span>
<a name="l00128"></a>00128 <span class="comment">         * Load modules's $Lang to allow usage of Lang() method and $Lang container</span>
<a name="l00129"></a>00129 <span class="comment">         */</span>
<a name="l00130"></a><a class="code" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">00130</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">LoadLang</a> ()
<a name="l00131"></a>00131         {
<a name="l00132"></a>00132                 $file_name = self::$ModuleName.'.xml';
<a name="l00133"></a>00133 
<a name="l00134"></a>00134                 $path_to_file = self::$CoreConfig['LangDir'].<span class="charliteral">'/'</span>.self::$CurrentLang.<span class="charliteral">'/'</span>.$file_name;
<a name="l00135"></a>00135 <span class="comment"></span>
<a name="l00136"></a>00136 <span class="comment">                /// Check file for existence</span>
<a name="l00137"></a>00137 <span class="comment"></span>                <span class="keywordflow">if</span> ( !is_readable($path_to_file) )
<a name="l00138"></a>00138                 {
<a name="l00139"></a>00139                         trigger_error(<span class="stringliteral">"Language file in "</span>.self::$CurrentLang.<span class="stringliteral">" for module "</span>.self::$ModuleName.<span class="stringliteral">" is missing!"</span>, E_USER_ERROR);
<a name="l00140"></a>00140                 }
<a name="l00141"></a>00141 <span class="comment"></span>
<a name="l00142"></a>00142 <span class="comment">                /// Check if language cache is available</span>
<a name="l00143"></a>00143 <span class="comment"></span>                <span class="keywordflow">if</span> ( self::$CoreConfig['UseCaching'] &amp;&amp; <a class="code" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">Core::IsCacheOk</a>($file_name, self::$CurrentLang) )
<a name="l00144"></a>00144                 {
<a name="l00145"></a>00145                         self::$Lang = GetCacheName($file_name, self::$CurrentLang);
<a name="l00146"></a>00146                 }
<a name="l00147"></a>00147                 <span class="keywordflow">else</span><span class="comment"></span>
<a name="l00148"></a>00148 <span class="comment">                /// Load language file XML and _PARSE_IT_</span>
<a name="l00149"></a>00149 <span class="comment"></span>                {
<a name="l00150"></a>00150                         <a class="code" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM</a>($path_to_file, $lang_dom);
<a name="l00151"></a>00151 
<a name="l00152"></a>00152                         $bits = $lang_dom-&gt;getElementsByTagName('bit');
<a name="l00153"></a>00153 
<a name="l00154"></a>00154                         foreach ( $bits as $current_bit )
<a name="l00155"></a>00155                         {
<a name="l00156"></a>00156                                 self::$Lang[$current_bit-&gt;getAttribute('name')] = $current_bit-&gt;data;
<a name="l00157"></a>00157                         }
<a name="l00158"></a>00158 <span class="comment"></span>
<a name="l00159"></a>00159 <span class="comment">                        /// Dump $Lang to cache if needed</span>
<a name="l00160"></a>00160 <span class="comment"></span>                        <span class="keywordflow">if</span> ( self::$CoreConfig['UseCaching'] )
<a name="l00161"></a>00161                         {
<a name="l00162"></a>00162                                 <a class="code" href="classCore.html#9c2596c82e620380c71bed110db40ca7">Core::DumpArrayToCache</a>(self::$Lang, <a class="code" href="classCore.html#7e860483a3033de70dc374e452900079">Core::GetCacheName</a>($file_name, self::$CurrentLang) );
<a name="l00163"></a>00163                         }
<a name="l00164"></a>00164                 }
<a name="l00165"></a>00165         }
<a name="l00166"></a>00166 <span class="comment"></span>
<a name="l00167"></a>00167 <span class="comment">        /**</span>
<a name="l00168"></a>00168 <span class="comment">         * Return specified language bit</span>
<a name="l00169"></a>00169 <span class="comment">         *</span>
<a name="l00170"></a>00170 <span class="comment">         * @param $bitName      string  Name of language bit</span>
<a name="l00171"></a>00171 <span class="comment">         * @param $arguments    array   List of arguments which are used in vsprintf() function with selected language bit.</span>
<a name="l00172"></a>00172 <span class="comment">                                        &lt;b&gt;EXAMPLE&lt;/b&gt;: say, language bit is "current user %s has %d posts" and $arguments is array("Alex",15), then method will output</span>
<a name="l00173"></a>00173 <span class="comment">                                        the following to module's $Html: "current user Alex has 15 posts". If $arguments is empty, then no string substitution will be performed.</span>
<a name="l00174"></a>00174 <span class="comment">         */</span>
<a name="l00175"></a><a class="code" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">00175</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">Lang</a> ($bitName, $arguments=<span class="stringliteral">""</span>)
<a name="l00176"></a>00176         {
<a name="l00177"></a>00177                 <span class="keywordflow">if</span> ( is_array($arguments) )
<a name="l00178"></a>00178                 {
<a name="l00179"></a>00179                         <span class="keywordflow">return</span> vsprintf(self::$Lang[$bitName], $arguments);
<a name="l00180"></a>00180                 }
<a name="l00181"></a>00181                 <span class="keywordflow">else</span>
<a name="l00182"></a>00182                 {
<a name="l00183"></a>00183                         <span class="keywordflow">return</span> self::$Lang[$bitName];
<a name="l00184"></a>00184                 }
<a name="l00185"></a>00185         }
<a name="l00186"></a>00186 <span class="comment"></span>
<a name="l00187"></a>00187 <span class="comment">        /**</span>
<a name="l00188"></a>00188 <span class="comment">         * Returns module current $Html</span>
<a name="l00189"></a>00189 <span class="comment">         *</span>
<a name="l00190"></a>00190 <span class="comment">         * @return string All html generated by module </span>
<a name="l00191"></a>00191 <span class="comment">         */</span>
<a name="l00192"></a><a class="code" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">00192</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">GetHtml</a> ()
<a name="l00193"></a>00193         {
<a name="l00194"></a>00194                 <span class="keywordflow">return</span> self::$Html;
<a name="l00195"></a>00195         }
<a name="l00196"></a>00196 }
<a name="l00197"></a>00197 
<a name="l00198"></a>00198 ?&gt;
</pre></div><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 13:09:11 2006 for YAMWS by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                      doc/html/core_8cfg_8xml_8cache_8php-source.html                                                     0000644 0001750 0001750 00000005444 10472273662 021360  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: /home/sphinx/Projects/PHP/YAMWS/core.cfg.xml.cache.php Source File</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/core.cfg.xml.cache.php</h1><a href="core_8cfg_8xml_8cache_8php.html">Go to the documentation of this file.</a><div class="fragment"><pre class="fragment"><a name="l00001"></a>00001 &lt;?php
<a name="l00002"></a>00002 <span class="keywordflow">return</span> array (
<a name="l00003"></a>00003 <span class="stringliteral">"CacheDir"</span> =&gt; <span class="stringliteral">"."</span>,
<a name="l00004"></a>00004 <span class="stringliteral">"UseCaching"</span> =&gt; <span class="stringliteral">"1"</span>,
<a name="l00005"></a>00005 <span class="stringliteral">"ModuleListXML"</span> =&gt; <span class="stringliteral">"modlist.xml"</span>,
<a name="l00006"></a>00006 <span class="stringliteral">"DepCheck"</span> =&gt; <span class="stringliteral">"1"</span>,
<a name="l00007"></a>00007 <span class="stringliteral">"MoreErrorInfo"</span> =&gt; <span class="stringliteral">"1"</span>,
<a name="l00008"></a>00008 <span class="stringliteral">"DefaultLayout"</span> =&gt; <span class="stringliteral">"blog"</span>,
<a name="l00009"></a>00009 <span class="stringliteral">"LayoutListXML"</span> =&gt; <span class="stringliteral">"layouts.xml"</span>,
<a name="l00010"></a>00010 <span class="stringliteral">"SkinDir"</span> =&gt; <span class="stringliteral">"skins"</span>,
<a name="l00011"></a>00011 <span class="stringliteral">"DefaultSkin"</span> =&gt; <span class="stringliteral">"d"</span>,
<a name="l00012"></a>00012 <span class="stringliteral">"LangDir"</span> =&gt; <span class="stringliteral">"lang"</span>,
<a name="l00013"></a>00013 <span class="stringliteral">"DefaultLang"</span> =&gt; <span class="stringliteral">"ru"</span>,
<a name="l00014"></a>00014 <span class="stringliteral">"LayoutSubDir"</span> =&gt; <span class="stringliteral">"layout_templates"</span>,
<a name="l00015"></a>00015 );
<a name="l00016"></a>00016 ?&gt;
</pre></div><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 13:06:58 2006 for YAMWS by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                            doc/html/core_8php-source.html                                                                      0000644 0001750 0001750 00000262035 10472274067 016267  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: /home/sphinx/Projects/PHP/YAMWS/core.php Source File</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/core.php</h1><a href="core_8php.html">Go to the documentation of this file.</a><div class="fragment"><pre class="fragment"><a name="l00001"></a>00001 &lt;?php
<a name="l00002"></a>00002 <span class="comment"></span>
<a name="l00003"></a>00003 <span class="comment">/** </span>
<a name="l00004"></a>00004 <span class="comment"> * @brief Module and layout handling, consistency checking, caching methods, running system</span>
<a name="l00005"></a>00005 <span class="comment"> * @author Sphinx</span>
<a name="l00006"></a>00006 <span class="comment"> * @date 2006</span>
<a name="l00007"></a>00007 <span class="comment"> *</span>
<a name="l00008"></a>00008 <span class="comment"> * @todo Examine class variables' scopes. Maybe some pseudo-__get&amp;__set methods should be added (e.g. for retrieval of config, modlist, runlist)</span>
<a name="l00009"></a>00009 <span class="comment"> * @todo Module description</span>
<a name="l00010"></a>00010 <span class="comment"> * @todo Advanced module handling functions: </span>
<a name="l00011"></a>00011 <span class="comment"> *      - module uninstalling (+depchecks&amp;warnings, 2 modes: remove from list/remove from disk, 2-step-action)</span>
<a name="l00012"></a>00012 <span class="comment"> *      - module installing</span>
<a name="l00013"></a>00013 <span class="comment"> * @todo Choose between SimpleXML and DOM. </span>
<a name="l00014"></a>00014 <span class="comment"> *      - What extension to use for parsing xml lists?</span>
<a name="l00015"></a>00015 <span class="comment"> *      - Is DTD validation really necessary (~fast?, -sophisticated)?</span>
<a name="l00016"></a>00016 <span class="comment"> * @todo i18n support in Core error messages?</span>
<a name="l00017"></a>00017 <span class="comment"> */</span>
<a name="l00018"></a>00018 
<a name="l00019"></a><a class="code" href="classCore.html">00019</a> <span class="keyword">class </span><a class="code" href="classCore.html">Core</a>
<a name="l00020"></a>00020 {<span class="comment"></span>
<a name="l00021"></a>00021 <span class="comment">        /**</span>
<a name="l00022"></a>00022 <span class="comment">         * %Core configuration container.</span>
<a name="l00023"></a>00023 <span class="comment">         *</span>
<a name="l00024"></a>00024 <span class="comment">         * @see Core::LoadConfig()</span>
<a name="l00025"></a>00025 <span class="comment">         */</span>
<a name="l00026"></a><a class="code" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">00026</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">$Config</a>;
<a name="l00027"></a>00027 <span class="comment"></span>
<a name="l00028"></a>00028 <span class="comment">        /**</span>
<a name="l00029"></a>00029 <span class="comment">         * DOM tree from XML with core configuration</span>
<a name="l00030"></a>00030 <span class="comment">         */</span>
<a name="l00031"></a><a class="code" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">00031</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">$ConfigDOM</a>;
<a name="l00032"></a>00032 <span class="comment"></span>
<a name="l00033"></a>00033 <span class="comment">        /**</span>
<a name="l00034"></a>00034 <span class="comment">         * Parsed module list.</span>
<a name="l00035"></a>00035 <span class="comment">         *</span>
<a name="l00036"></a>00036 <span class="comment">         * @see Core::LoadModuleList()</span>
<a name="l00037"></a>00037 <span class="comment">         */</span>
<a name="l00038"></a><a class="code" href="classCore.html#9669b1e25f58a6327346627e0718714b">00038</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#9669b1e25f58a6327346627e0718714b">$ModuleList</a>;
<a name="l00039"></a>00039 <span class="comment"></span>
<a name="l00040"></a>00040 <span class="comment">        /**</span>
<a name="l00041"></a>00041 <span class="comment">         * DOM tree from XML with module list.</span>
<a name="l00042"></a>00042 <span class="comment">         */</span>
<a name="l00043"></a><a class="code" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">00043</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">$ModuleListDOM</a>;
<a name="l00044"></a>00044 <span class="comment"></span>
<a name="l00045"></a>00045 <span class="comment">        /**</span>
<a name="l00046"></a>00046 <span class="comment">         * Current layout container.</span>
<a name="l00047"></a>00047 <span class="comment">         *</span>
<a name="l00048"></a>00048 <span class="comment">         * @note This is NOT the name of current layout, but the whole corresponding ENTRY from Core::$LayoutList!</span>
<a name="l00049"></a>00049 <span class="comment">         */</span>
<a name="l00050"></a><a class="code" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">00050</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">$Layout</a>;
<a name="l00051"></a>00051 <span class="comment"></span>
<a name="l00052"></a>00052 <span class="comment">        /**</span>
<a name="l00053"></a>00053 <span class="comment">         * Parsed layout list.</span>
<a name="l00054"></a>00054 <span class="comment">         *</span>
<a name="l00055"></a>00055 <span class="comment">         * @see Core::LoadLayoutList()</span>
<a name="l00056"></a>00056 <span class="comment">         */</span>
<a name="l00057"></a><a class="code" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">00057</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">$LayoutList</a>;
<a name="l00058"></a>00058 <span class="comment"></span>
<a name="l00059"></a>00059 <span class="comment">        /**</span>
<a name="l00060"></a>00060 <span class="comment">         * DOM tree from XML with layout list.</span>
<a name="l00061"></a>00061 <span class="comment">         */</span>
<a name="l00062"></a><a class="code" href="classCore.html#89f8206b99ec81d78b8604b47768b757">00062</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#89f8206b99ec81d78b8604b47768b757">$LayoutListDOM</a>;
<a name="l00063"></a>00063 <span class="comment"></span>
<a name="l00064"></a>00064 <span class="comment">        /**</span>
<a name="l00065"></a>00065 <span class="comment">         * List of modules to run in current layout</span>
<a name="l00066"></a>00066 <span class="comment">         *</span>
<a name="l00067"></a>00067 <span class="comment">         * @note This list contains list of module NAMES from Core::$ModuleList</span>
<a name="l00068"></a>00068 <span class="comment">         *</span>
<a name="l00069"></a>00069 <span class="comment">         * @see Core::MakeRunList()</span>
<a name="l00070"></a>00070 <span class="comment">         */</span>
<a name="l00071"></a><a class="code" href="classCore.html#135f0995570f8535b094481c563132f4">00071</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#135f0995570f8535b094481c563132f4">$RunList</a>;
<a name="l00072"></a>00072 <span class="comment"></span>
<a name="l00073"></a>00073 <span class="comment">        /**</span>
<a name="l00074"></a>00074 <span class="comment">         * Internal name of currently selected skin for user.</span>
<a name="l00075"></a>00075 <span class="comment">         * Skin internal name is a name of directory in default system skin folder.</span>
<a name="l00076"></a>00076 <span class="comment">         *</span>
<a name="l00077"></a>00077 <span class="comment">         * @see Core::SetSkin() Core::RestoreSkinAndLang() Core::GetSkinAndLang()</span>
<a name="l00078"></a>00078 <span class="comment">         */</span>
<a name="l00079"></a><a class="code" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">00079</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">$CurrentSkin</a>;
<a name="l00080"></a>00080 <span class="comment"></span>
<a name="l00081"></a>00081 <span class="comment">        /**</span>
<a name="l00082"></a>00082 <span class="comment">         * Internal name of currently selected language for user</span>
<a name="l00083"></a>00083 <span class="comment">         * Language internal name is a name of directory in default system language folder</span>
<a name="l00084"></a>00084 <span class="comment">         *</span>
<a name="l00085"></a>00085 <span class="comment">         * @see Core::SetLang() Core::RestoreSkinAndLang() Core::GetSkinAndLang()</span>
<a name="l00086"></a>00086 <span class="comment">         */</span>
<a name="l00087"></a><a class="code" href="classCore.html#855055f9bbc19bc705ea05747653644a">00087</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#855055f9bbc19bc705ea05747653644a">$CurrentLang</a>;
<a name="l00088"></a>00088 <span class="comment"></span>
<a name="l00089"></a>00089 <span class="comment">        /**</span>
<a name="l00090"></a>00090 <span class="comment">         * Timers container, used for perfomance measuring.</span>
<a name="l00091"></a>00091 <span class="comment">         *</span>
<a name="l00092"></a>00092 <span class="comment">         * @see Core::StartTimer() Core::GetTimer()</span>
<a name="l00093"></a>00093 <span class="comment">         */</span>
<a name="l00094"></a><a class="code" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">00094</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">$Timers</a>;
<a name="l00095"></a>00095 <span class="comment"></span>
<a name="l00096"></a>00096 <span class="comment">        /**</span>
<a name="l00097"></a>00097 <span class="comment">         * Errors container, used to keep non-fatal error</span>
<a name="l00098"></a>00098 <span class="comment">         * entries generated via Core::Error().</span>
<a name="l00099"></a>00099 <span class="comment">         */</span>
<a name="l00100"></a><a class="code" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">00100</a>         <span class="keyword">static</span> <span class="keyword">private</span> <a class="code" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">$Errors</a>;
<a name="l00101"></a>00101 
<a name="l00102"></a>00102        <span class="comment"></span>
<a name="l00103"></a>00103 <span class="comment">        /**</span>
<a name="l00104"></a>00104 <span class="comment">         * @brief Main method in the whole system</span>
<a name="l00105"></a>00105 <span class="comment">         *</span>
<a name="l00106"></a>00106 <span class="comment">         * This is a wrapper method actually making system work.\n</span>
<a name="l00107"></a>00107 <span class="comment">         * First we prepare core, then load module list and layout we need, make list of modules to run and sequentually run them one-by-one.</span>
<a name="l00108"></a>00108 <span class="comment">         * Sounds easy, eh?</span>
<a name="l00109"></a>00109 <span class="comment">         *</span>
<a name="l00110"></a>00110 <span class="comment">         * @see Core::Startup() Core::LoadModuleList() Core::LoadLayout() Core::MakeRunList()</span>
<a name="l00111"></a>00111 <span class="comment">         *</span>
<a name="l00112"></a>00112 <span class="comment">         * @return void</span>
<a name="l00113"></a>00113 <span class="comment">         */</span>
<a name="l00114"></a><a class="code" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">00114</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Run</a> ()
<a name="l00115"></a>00115         {
<a name="l00116"></a>00116                 self::Startup();
<a name="l00117"></a>00117 
<a name="l00118"></a>00118                 self::LoadModuleList();
<a name="l00119"></a>00119 
<a name="l00120"></a>00120                 self::LoadLayout();
<a name="l00121"></a>00121 
<a name="l00122"></a>00122                 self::MakeRunList();
<a name="l00123"></a>00123 
<a name="l00124"></a>00124                 self::RestoreSkinAndLang();
<a name="l00125"></a>00125 
<a name="l00126"></a>00126                 $html = @file_get_contents(self::$Config['SkinDir'].<span class="charliteral">'/'</span>.self::$CurrentSkin.<span class="charliteral">'/'</span>.self::$Config['LayoutSubDir'].<span class="charliteral">'/'</span>.self::$Layout['<span class="keyword">template</span>']);
<a name="l00127"></a>00127 
<a name="l00128"></a>00128                 <span class="keywordflow">if</span> ( !$html )
<a name="l00129"></a>00129                 {
<a name="l00130"></a>00130                         trigger_error(<span class="stringliteral">"Layout template for "</span>.self::$Layout['name'].<span class="stringliteral">" in skin "</span>.self::$CurrentSkin.<span class="stringliteral">" is missing!"</span>, E_USER_ERROR);
<a name="l00131"></a>00131                 }
<a name="l00132"></a>00132 
<a name="l00133"></a>00133                 foreach ( self::$RunList as $module )
<a name="l00134"></a>00134                 {
<a name="l00135"></a>00135                         <span class="keywordflow">if</span> ( is_readable(self::$ModuleList[$module]['file']) )
<a name="l00136"></a>00136                         {
<a name="l00137"></a>00137                                 require(self::$ModuleList[$module]['file']);  
<a name="l00138"></a>00138                         }
<a name="l00139"></a>00139                         <span class="keywordflow">else</span>
<a name="l00140"></a>00140                         {
<a name="l00141"></a>00141                                 trigger_error(<span class="stringliteral">"Source file not found for module {$module}!"</span>, E_USER_ERROR);
<a name="l00142"></a>00142                         }
<a name="l00143"></a>00143 
<a name="l00144"></a>00144                         $class_name = str_replace(<span class="stringliteral">" "</span>, <span class="stringliteral">""</span>, ucwords(preg_replace(<span class="stringliteral">"/_([a-z])/"</span>, <span class="stringliteral">" $1"</span>, $module)));
<a name="l00145"></a>00145 
<a name="l00146"></a>00146                         <span class="keywordflow">if</span> ( !is_subclass_of($class_name, <span class="stringliteral">"BaseModule"</span>) )
<a name="l00147"></a>00147                         {
<a name="l00148"></a>00148                                 trigger_error(<span class="stringliteral">"{$class_name} does not extend BaseModule class!"</span>, E_USER_ERROR);
<a name="l00149"></a>00149                         }
<a name="l00150"></a>00150 
<a name="l00151"></a>00151                         $methods = array(<span class="stringliteral">"BaseStartup"</span>,<span class="stringliteral">"Startup"</span>,<span class="stringliteral">"Run"</span>);
<a name="l00152"></a>00152                         foreach ( $methods as $method )
<a name="l00153"></a>00153                         {
<a name="l00154"></a>00154                                 call_user_func(array($class_name, $method));
<a name="l00155"></a>00155                         }
<a name="l00156"></a>00156 
<a name="l00157"></a>00157                         $html = str_replace(<span class="stringliteral">"&lt;% {$module} %&gt;"</span>, call_user_func(array($class_name, <span class="stringliteral">"GetHtml"</span>)), $html);
<a name="l00158"></a>00158                 }
<a name="l00159"></a>00159 
<a name="l00160"></a>00160                 echo $html;
<a name="l00161"></a>00161         }
<a name="l00162"></a>00162 <span class="comment"></span>
<a name="l00163"></a>00163 <span class="comment">        /**</span>
<a name="l00164"></a>00164 <span class="comment">         * @brief Prepare Core for work</span>
<a name="l00165"></a>00165 <span class="comment">         *</span>
<a name="l00166"></a>00166 <span class="comment">         * Set error handling directives, call some misc methodz.</span>
<a name="l00167"></a>00167 <span class="comment">         *</span>
<a name="l00168"></a>00168 <span class="comment">         * @return void</span>
<a name="l00169"></a>00169 <span class="comment">         */</span>
<a name="l00170"></a><a class="code" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">00170</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Startup</a> ()
<a name="l00171"></a>00171         {<span class="comment"></span>
<a name="l00172"></a>00172 <span class="comment">                /// Start basic timer (to measure performance later)</span>
<a name="l00173"></a>00173 <span class="comment"></span>                self::StartTimer();
<a name="l00174"></a>00174 <span class="comment"></span>
<a name="l00175"></a>00175 <span class="comment">                /// Core handles errors. Yep.</span>
<a name="l00176"></a>00176 <span class="comment"></span>                set_error_handler(array(<span class="stringliteral">"self"</span>, <span class="stringliteral">"Error"</span>), E_USER_ERROR+E_USER_WARNING+E_USER_NOTICE );
<a name="l00177"></a>00177 <span class="comment"></span>
<a name="l00178"></a>00178 <span class="comment">                /// Enable track_errors to have $php_errormsg</span>
<a name="l00179"></a>00179 <span class="comment"></span>                ini_set(<span class="stringliteral">"track_errors"</span>, 1);
<a name="l00180"></a>00180 <span class="comment"></span>
<a name="l00181"></a>00181 <span class="comment">                /// Load configuration of Core</span>
<a name="l00182"></a>00182 <span class="comment"></span>                self::LoadConfig();
<a name="l00183"></a>00183         }
<a name="l00184"></a>00184 <span class="comment"></span>
<a name="l00185"></a>00185 <span class="comment">        /**</span>
<a name="l00186"></a>00186 <span class="comment">         * Set and store skin for current user</span>
<a name="l00187"></a>00187 <span class="comment">         *</span>
<a name="l00188"></a>00188 <span class="comment">         * @param $skin         string  Internal name of skin to choose (name in skins directory)</span>
<a name="l00189"></a>00189 <span class="comment">         *</span>
<a name="l00190"></a>00190 <span class="comment">         * @return void</span>
<a name="l00191"></a>00191 <span class="comment">         */</span>
<a name="l00192"></a><a class="code" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">00192</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">SetSkin</a> ($skin)
<a name="l00193"></a>00193         {
<a name="l00194"></a>00194                 <span class="keywordflow">if</span> ( !is_dir($skin) || !is_readable($skin) )
<a name="l00195"></a>00195                 {
<a name="l00196"></a>00196                         trigger_error(<span class="stringliteral">"Selected unaccessible skin {$skin}!"</span>, E_USER_WARNING);
<a name="l00197"></a>00197 
<a name="l00198"></a>00198                         self::$CurrentSkin = self::$Config['DefaultSkin'];
<a name="l00199"></a>00199                 }
<a name="l00200"></a>00200                 <span class="keywordflow">else</span>
<a name="l00201"></a>00201                 {
<a name="l00202"></a>00202                         self::$CurrentSkin = $skin;
<a name="l00203"></a>00203 <span class="comment"></span>
<a name="l00204"></a>00204 <span class="comment">                        /// Cookies have higher priority in choosing skin than session</span>
<a name="l00205"></a>00205 <span class="comment"></span>                        SetCookie('skin', $skin, time()*2);
<a name="l00206"></a>00206 <span class="comment"></span>
<a name="l00207"></a>00207 <span class="comment">                        /// But what if user switched cookies off?</span>
<a name="l00208"></a>00208 <span class="comment"></span>                        $_SESSION['skin'] = $skin;
<a name="l00209"></a>00209                 }
<a name="l00210"></a>00210         }
<a name="l00211"></a>00211 <span class="comment"></span>
<a name="l00212"></a>00212 <span class="comment">        /**</span>
<a name="l00213"></a>00213 <span class="comment">         * Set and store language for current user</span>
<a name="l00214"></a>00214 <span class="comment">         *</span>
<a name="l00215"></a>00215 <span class="comment">         * @note Haha, it's y&amp;p'ed from Core::SetSkin() method, lol rofol!!!!!!!</span>
<a name="l00216"></a>00216 <span class="comment">         *</span>
<a name="l00217"></a>00217 <span class="comment">         * @param $lang         string  Internal name of language to choose (name in langs directory)</span>
<a name="l00218"></a>00218 <span class="comment">         *</span>
<a name="l00219"></a>00219 <span class="comment">         * @return void</span>
<a name="l00220"></a>00220 <span class="comment">         */</span>
<a name="l00221"></a><a class="code" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">00221</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">SetLang</a> ($lang)
<a name="l00222"></a>00222         {
<a name="l00223"></a>00223                 <span class="keywordflow">if</span> ( !is_dir($lang) || !is_readable($lang) )
<a name="l00224"></a>00224                 {
<a name="l00225"></a>00225                         trigger_error(<span class="stringliteral">"Selected unaccessible language {$lang}!"</span>, E_USER_WARNING);
<a name="l00226"></a>00226 
<a name="l00227"></a>00227                         self::$CurrentLang = self::$Config['DefaultLang'];
<a name="l00228"></a>00228                 }
<a name="l00229"></a>00229                 <span class="keywordflow">else</span>
<a name="l00230"></a>00230                 {
<a name="l00231"></a>00231                         self::$CurrentLang= $lang;
<a name="l00232"></a>00232 
<a name="l00233"></a>00233                         SetCookie('lang', $lang, time()*2);
<a name="l00234"></a>00234 
<a name="l00235"></a>00235                         $_SESSION['lang'] = $lang;
<a name="l00236"></a>00236                 }
<a name="l00237"></a>00237         }
<a name="l00238"></a>00238 <span class="comment"></span>
<a name="l00239"></a>00239 <span class="comment">        /**</span>
<a name="l00240"></a>00240 <span class="comment">         * Get current skin and language internal names</span>
<a name="l00241"></a>00241 <span class="comment">         *</span>
<a name="l00242"></a>00242 <span class="comment">         * @return      array   Array with two elements, first one with name of current skin, second with language name</span>
<a name="l00243"></a>00243 <span class="comment">         */</span>
<a name="l00244"></a><a class="code" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">00244</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">GetSkinAndLang</a> ()
<a name="l00245"></a>00245         {
<a name="l00246"></a>00246                 <span class="keywordflow">return</span> array(self::$CurrentSkin, self::$CurrentLang);
<a name="l00247"></a>00247         }
<a name="l00248"></a>00248 <span class="comment"></span>
<a name="l00249"></a>00249 <span class="comment">        /**</span>
<a name="l00250"></a>00250 <span class="comment">         * Set Core::$CurrentSkin and Core::$CurrentLang values according to user's preferences which are stored in cookies/session data</span>
<a name="l00251"></a>00251 <span class="comment">         *</span>
<a name="l00252"></a>00252 <span class="comment">         * @note This is low-level core method which is called at each system run.\n</span>
<a name="l00253"></a>00253 <span class="comment">         *       Skin and language values set by this method may be overriden by system modules'</span>
<a name="l00254"></a>00254 <span class="comment">         *       behaviour, e.g. skin may be selected from user preferences which are kept in database and</span>
<a name="l00255"></a>00255 <span class="comment">         *       managed by system module, not system core.</span>
<a name="l00256"></a>00256 <span class="comment">         * @note Previous note is really hard to understand.</span>
<a name="l00257"></a>00257 <span class="comment">         *</span>
<a name="l00258"></a>00258 <span class="comment">         * @return void</span>
<a name="l00259"></a>00259 <span class="comment">         */</span>
<a name="l00260"></a><a class="code" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">00260</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">RestoreSkinAndLang</a> ()
<a name="l00261"></a>00261         {
<a name="l00262"></a>00262                 <span class="keywordflow">if</span> ( isset($_COOKIE['skin']) )
<a name="l00263"></a>00263                 {
<a name="l00264"></a>00264                         self::SetSkin($_COOKIE['skin']);
<a name="l00265"></a>00265                 }
<a name="l00266"></a>00266                 elseif ( isset($_SESSION['skin']) )
<a name="l00267"></a>00267                 {
<a name="l00268"></a>00268                         self::SetSkin($_SESSION['skin']);
<a name="l00269"></a>00269                 }
<a name="l00270"></a>00270                 <span class="keywordflow">else</span>
<a name="l00271"></a>00271                 {
<a name="l00272"></a>00272                         self::SetSkin(self::$Config['DefaultSkin']);
<a name="l00273"></a>00273                 }
<a name="l00274"></a>00274 
<a name="l00275"></a>00275                 <span class="keywordflow">if</span> ( isset($_COOKIE['lang']) )
<a name="l00276"></a>00276                 {
<a name="l00277"></a>00277                         self::SetSkin($_COOKIE['lang']);
<a name="l00278"></a>00278                 }
<a name="l00279"></a>00279                 elseif ( isset($_SESSION['lang']) )
<a name="l00280"></a>00280                 {
<a name="l00281"></a>00281                         self::SetSkin($_SESSION['lang']);
<a name="l00282"></a>00282                 }
<a name="l00283"></a>00283                 <span class="keywordflow">else</span>
<a name="l00284"></a>00284                 {
<a name="l00285"></a>00285                         self::SetSkin(self::$Config['DefaultLang']);
<a name="l00286"></a>00286                 }
<a name="l00287"></a>00287         }
<a name="l00288"></a>00288 <span class="comment"></span>
<a name="l00289"></a>00289 <span class="comment">        /**</span>
<a name="l00290"></a>00290 <span class="comment">         * @brief Load Core configuration</span>
<a name="l00291"></a>00291 <span class="comment">         *</span>
<a name="l00292"></a>00292 <span class="comment">         * Loads core configuration either from cache file or from XML config.</span>
<a name="l00293"></a>00293 <span class="comment">         * Parsed XML configuration is cached to improve perfomance later.</span>
<a name="l00294"></a>00294 <span class="comment">         *</span>
<a name="l00295"></a>00295 <span class="comment">         * @return void</span>
<a name="l00296"></a>00296 <span class="comment">         */</span>
<a name="l00297"></a><a class="code" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">00297</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">LoadConfig</a> ()
<a name="l00298"></a>00298         {
<a name="l00299"></a>00299                 self::$Config['CacheDir'] = <span class="stringliteral">"."</span>;
<a name="l00300"></a>00300 <span class="comment"></span>
<a name="l00301"></a>00301 <span class="comment">                /// Load list from cache if it's up to date</span>
<a name="l00302"></a>00302 <span class="comment"></span>                <span class="keywordflow">if</span> ( self::IsCacheOk(<span class="stringliteral">"core.cfg.xml"</span>) )
<a name="l00303"></a>00303                 {
<a name="l00304"></a>00304                         self::$Config = require(self::GetCacheName(<span class="stringliteral">"core.cfg.xml"</span>));
<a name="l00305"></a>00305                 }
<a name="l00306"></a>00306                 <span class="keywordflow">else</span>
<a name="l00307"></a>00307                 {
<a name="l00308"></a>00308                         self::LoadDOM(<span class="stringliteral">"core.cfg.xml"</span>, self::$ConfigDOM);
<a name="l00309"></a>00309                         self::ParseConfigDOM();
<a name="l00310"></a>00310 <span class="comment"></span>
<a name="l00311"></a>00311 <span class="comment">                        /// Cache config if necessary</span>
<a name="l00312"></a>00312 <span class="comment"></span>                        <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00313"></a>00313                         {<span class="comment"></span>
<a name="l00314"></a>00314 <span class="comment">                                /// Trick to put core config cache to root directory</span>
<a name="l00315"></a>00315 <span class="comment"></span>                                $old_dir = self::$Config['CacheDir'];
<a name="l00316"></a>00316                                 self::$Config['CacheDir'] = <span class="stringliteral">"."</span>;
<a name="l00317"></a>00317                                 self::DumpArrayToCache(self::$Config, self::GetCacheName(<span class="stringliteral">"core.cfg.xml"</span>));
<a name="l00318"></a>00318                                 self::$Config['CacheDir'] = $old_dir;
<a name="l00319"></a>00319                         }
<a name="l00320"></a>00320                 }
<a name="l00321"></a>00321         }
<a name="l00322"></a>00322 <span class="comment"></span>
<a name="l00323"></a>00323 <span class="comment">        /**</span>
<a name="l00324"></a>00324 <span class="comment">         * If everything's fine, we get nice'n'chill Core::$ModuleList array after all, in addition to cached modlist if caching's on.\n</span>
<a name="l00325"></a>00325 <span class="comment">         * Also perform a dependency check if Core::$Config['DepCheck'] is TRUE.</span>
<a name="l00326"></a>00326 <span class="comment">         *</span>
<a name="l00327"></a>00327 <span class="comment">         * @see Core::LoadDOM() Core::ParseModuleListDOM()</span>
<a name="l00328"></a>00328 <span class="comment">         *</span>
<a name="l00329"></a>00329 <span class="comment">         * @return void</span>
<a name="l00330"></a>00330 <span class="comment">         */</span>
<a name="l00331"></a><a class="code" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">00331</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">LoadModuleList</a> ()
<a name="l00332"></a>00332         {<span class="comment"></span>
<a name="l00333"></a>00333 <span class="comment">                /// Load list from cache if it's up to date and caching's on</span>
<a name="l00334"></a>00334 <span class="comment"></span>                <span class="keywordflow">if</span> ( self::$Config['UseCaching'] &amp;&amp; self::IsCacheOk(self::$Config['ModuleListXML']) )
<a name="l00335"></a>00335                 {
<a name="l00336"></a>00336                         self::$ModuleList = require(self::GetCacheName(self::$Config['ModuleListXML']));
<a name="l00337"></a>00337                 }
<a name="l00338"></a>00338                 <span class="keywordflow">else</span>
<a name="l00339"></a>00339                 {<span class="comment"></span>
<a name="l00340"></a>00340 <span class="comment">                        /// Load original XML, parse it and check it</span>
<a name="l00341"></a>00341 <span class="comment"></span>                        self::LoadDOM(self::$Config['ModuleListXML'], self::$ModuleListDOM);
<a name="l00342"></a>00342                         self::ParseModuleListDOM();
<a name="l00343"></a>00343 
<a name="l00344"></a>00344                         <span class="keywordflow">if</span> ( self::$Config['DepCheck'] )
<a name="l00345"></a>00345                         {
<a name="l00346"></a>00346                                 self::CheckModuleList();
<a name="l00347"></a>00347                         }
<a name="l00348"></a>00348 <span class="comment"></span>
<a name="l00349"></a>00349 <span class="comment">                        /// Cache it to get a perfomance bonus in future</span>
<a name="l00350"></a>00350 <span class="comment"></span>                        <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00351"></a>00351                         {
<a name="l00352"></a>00352                                 self::DumpArrayToCache(self::$ModuleList, self::GetCacheName(self::$Config['ModuleListXML']));
<a name="l00353"></a>00353                         }
<a name="l00354"></a>00354                 }
<a name="l00355"></a>00355         }
<a name="l00356"></a>00356 <span class="comment"></span>
<a name="l00357"></a>00357 <span class="comment">        /**</span>
<a name="l00358"></a>00358 <span class="comment">         * Load selected layout or default one.\n</span>
<a name="l00359"></a>00359 <span class="comment">         * Name of layout is selected from 'a' parameter in script query string.\n</span>
<a name="l00360"></a>00360 <span class="comment">         * The result is usable Core::$Layout, Core::$LayoutList and cached layout list if caching is enabled.</span>
<a name="l00361"></a>00361 <span class="comment">         *</span>
<a name="l00362"></a>00362 <span class="comment">         * @see Core::LoadDOM() Core::ParseLayoutListDOM()</span>
<a name="l00363"></a>00363 <span class="comment">         *</span>
<a name="l00364"></a>00364 <span class="comment">         * @return void</span>
<a name="l00365"></a>00365 <span class="comment">         */</span>
<a name="l00366"></a><a class="code" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">00366</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">LoadLayout</a> ()
<a name="l00367"></a>00367         {<span class="comment"></span>
<a name="l00368"></a>00368 <span class="comment">                /// What about loading layout from cache?</span>
<a name="l00369"></a>00369 <span class="comment"></span>                <span class="keywordflow">if</span> ( self::$Config['UseCaching'] &amp;&amp; self::IsCacheOk(self::$Config['LayoutListXML']) )
<a name="l00370"></a>00370                 {
<a name="l00371"></a>00371                         self::$LayoutList = require(self::GetCacheName(self::$Config['LayoutListXML']));
<a name="l00372"></a>00372                 }
<a name="l00373"></a>00373                 <span class="keywordflow">else</span>
<a name="l00374"></a>00374                 {<span class="comment"></span>
<a name="l00375"></a>00375 <span class="comment">                        /// Load DOM, parse it and cache it if needed</span>
<a name="l00376"></a>00376 <span class="comment"></span>                        self::LoadDOM(self::$Config['LayoutListXML'], self::$LayoutListDOM);
<a name="l00377"></a>00377                         self::ParseLayoutListDOM();
<a name="l00378"></a>00378 <span class="comment"></span>
<a name="l00379"></a>00379 <span class="comment">                        /// Cache it to get a perfomance bonus in future</span>
<a name="l00380"></a>00380 <span class="comment"></span>                        <span class="keywordflow">if</span> ( self::$Config['UseCaching'] )
<a name="l00381"></a>00381                         {
<a name="l00382"></a>00382                                 self::DumpArrayToCache(self::$LayoutList, self::GetCacheName(self::$Config['LayoutListXML']));
<a name="l00383"></a>00383                         }
<a name="l00384"></a>00384                 }
<a name="l00385"></a>00385 <span class="comment"></span>
<a name="l00386"></a>00386 <span class="comment">                /// Load requested layout if possible, load default one otherwise</span>
<a name="l00387"></a>00387 <span class="comment"></span>                <span class="keywordflow">if</span> ( !array_key_exists($_GET[<span class="charliteral">'a'</span>], self::$LayoutList) )
<a name="l00388"></a>00388                 {
<a name="l00389"></a>00389                         <span class="keywordflow">if</span> ( array_key_exists(self::$Config['DefaultLayout'], self::$LayoutList) )
<a name="l00390"></a>00390                         {
<a name="l00391"></a>00391                                 self::$Layout = self::$LayoutList[self::$Config['DefaultLayout']];
<a name="l00392"></a>00392                                 trigger_error(<span class="stringliteral">"Requested layout not found, default layout '"</span>.self::$Config['DefaultLayout'].<span class="stringliteral">"' loaded!"</span>, E_USER_WARNING);
<a name="l00393"></a>00393                         }
<a name="l00394"></a>00394                         <span class="keywordflow">else</span>
<a name="l00395"></a>00395                         {
<a name="l00396"></a>00396                                 trigger_error(<span class="stringliteral">"Default layout '"</span>.self::$Config['DefaultLayout'].<span class="stringliteral">"' not found in layout list!"</span>, E_USER_ERROR);
<a name="l00397"></a>00397                         }
<a name="l00398"></a>00398                 }
<a name="l00399"></a>00399                 <span class="keywordflow">else</span>
<a name="l00400"></a>00400                 {
<a name="l00401"></a>00401                         self::$Layout = self::$LayoutList[$_GET[<span class="charliteral">'a'</span>]];
<a name="l00402"></a>00402                 }
<a name="l00403"></a>00403                 <span class="comment"></span>
<a name="l00404"></a>00404 <span class="comment">                /// Layout loaded!</span>
<a name="l00405"></a>00405 <span class="comment"></span>        }
<a name="l00406"></a>00406 <span class="comment"></span>
<a name="l00407"></a>00407 <span class="comment">        /**</span>
<a name="l00408"></a>00408 <span class="comment">         * Load some XML, validate and turn it into a DOM tree.</span>
<a name="l00409"></a>00409 <span class="comment">         *</span>
<a name="l00410"></a>00410 <span class="comment">         * @note This method checks whether target XML file is accessible and raises error on failure.</span>
<a name="l00411"></a>00411 <span class="comment">         *</span>
<a name="l00412"></a>00412 <span class="comment">         * @param $source       string  Path to XML file to load</span>
<a name="l00413"></a>00413 <span class="comment">         * @param $r_variable   mixed   Link to variable which will get DOM after loading</span>
<a name="l00414"></a>00414 <span class="comment">         *</span>
<a name="l00415"></a>00415 <span class="comment">         * @return void</span>
<a name="l00416"></a>00416 <span class="comment">         */</span>
<a name="l00417"></a><a class="code" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">00417</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">LoadDOM</a> ($source, &amp;$r_variable)
<a name="l00418"></a>00418         {<span class="comment"></span>
<a name="l00419"></a>00419 <span class="comment">                /// Check if target file is accessible</span>
<a name="l00420"></a>00420 <span class="comment"></span>                <span class="keywordflow">if</span> ( !is_readable($source) ) 
<a name="l00421"></a>00421                 {
<a name="l00422"></a>00422                         trigger_error(<span class="stringliteral">"Could not access "</span>.$source, E_USER_ERROR);
<a name="l00423"></a>00423                 }
<a name="l00424"></a>00424 
<a name="l00425"></a>00425                 $r_variable = <span class="keyword">new</span> DOMDocument();
<a name="l00426"></a>00426 
<a name="l00427"></a>00427                 $r_variable-&gt;preserveWhiteSpace = <span class="keyword">false</span>;
<a name="l00428"></a>00428                 <span class="comment"></span>
<a name="l00429"></a>00429 <span class="comment">                /// Load file</span>
<a name="l00430"></a>00430 <span class="comment"></span>                $r_variable-&gt;load($source);
<a name="l00431"></a>00431 
<a name="l00432"></a>00432                 <span class="comment">/*</span><span class="comment"></span>
<a name="l00433"></a>00433 <span class="comment">                /// Validate file </span>
<a name="l00434"></a>00434 <span class="comment"></span>                if ( !$r_variable-&gt;validate() )
<a name="l00435"></a>00435                 {
<a name="l00436"></a>00436                         trigger_error("File ".$source." failed DTD validation! ".$php_errormsg."..", E_USER_ERROR);
<a name="l00437"></a>00437                 }
<a name="l00438"></a>00438                 */
<a name="l00439"></a>00439         }
<a name="l00440"></a>00440 <span class="comment"></span>
<a name="l00441"></a>00441 <span class="comment">        /**</span>
<a name="l00442"></a>00442 <span class="comment">         * Parse module list XML, Core::$ModuleList gets a parsed module list on success.</span>
<a name="l00443"></a>00443 <span class="comment">         *</span>
<a name="l00444"></a>00444 <span class="comment">         * @return void</span>
<a name="l00445"></a>00445 <span class="comment">         */</span>
<a name="l00446"></a><a class="code" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">00446</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">ParseModuleListDOM</a> ()
<a name="l00447"></a>00447         {<span class="comment"></span>
<a name="l00448"></a>00448 <span class="comment">                /// Get all &lt;module ... /&gt; tags</span>
<a name="l00449"></a>00449 <span class="comment"></span>                $modules = self::$ModuleListDOM-&gt;getElementsByTagName(<span class="stringliteral">"module"</span>);
<a name="l00450"></a>00450 <span class="comment"></span>
<a name="l00451"></a>00451 <span class="comment">                /// Run through the whole list of modules</span>
<a name="l00452"></a>00452 <span class="comment"></span>                foreach ( $modules as $current_module )
<a name="l00453"></a>00453                 {<span class="comment"></span>
<a name="l00454"></a>00454 <span class="comment">                        /// @internal Link to corresponding $ModuleList entry</span>
<a name="l00455"></a>00455 <span class="comment"></span>                        $r_module =&amp; self::$ModuleList[$current_module-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00456"></a>00456 <span class="comment"></span>
<a name="l00457"></a>00457 <span class="comment">                        /// Now run through attribute list for each module</span>
<a name="l00458"></a>00458 <span class="comment"></span>                        foreach ( $current_module-&gt;attributes as $attribute )
<a name="l00459"></a>00459                         {
<a name="l00460"></a>00460                                 $r_module[$attribute-&gt;name] = $attribute-&gt;value;
<a name="l00461"></a>00461                         }
<a name="l00462"></a>00462 <span class="comment"></span>
<a name="l00463"></a>00463 <span class="comment">                        /// Go through settings list as well</span>
<a name="l00464"></a>00464 <span class="comment"></span>                        <span class="keywordflow">if</span> ( $current_module-&gt;hasChildNodes() )
<a name="l00465"></a>00465                         {
<a name="l00466"></a>00466                                 foreach ( $current_module-&gt;childNodes as $setting )
<a name="l00467"></a>00467                                 {
<a name="l00468"></a>00468                                         $setting_name = $setting-&gt;attributes-&gt;getNamedItem(<span class="stringliteral">"name"</span>)-&gt;value;
<a name="l00469"></a>00469 
<a name="l00470"></a>00470                                         foreach ( $setting-&gt;attributes as $setting_attribute )
<a name="l00471"></a>00471                                         {
<a name="l00472"></a>00472 
<a name="l00473"></a>00473                                                 $r_module['settings'][$setting_name][$setting_attribute-&gt;name] = $setting_attribute-&gt;value;
<a name="l00474"></a>00474                                         }
<a name="l00475"></a>00475                                 }
<a name="l00476"></a>00476                         }
<a name="l00477"></a>00477                 }
<a name="l00478"></a>00478 <span class="comment"></span>
<a name="l00479"></a>00479 <span class="comment">                /// Tree was successfully parsed!</span>
<a name="l00480"></a>00480 <span class="comment"></span>        }
<a name="l00481"></a>00481 <span class="comment"></span>
<a name="l00482"></a>00482 <span class="comment">        /**</span>
<a name="l00483"></a>00483 <span class="comment">         * Parse layout list XML file into Core::$LayoutList.</span>
<a name="l00484"></a>00484 <span class="comment">         *</span>
<a name="l00485"></a>00485 <span class="comment">         * @note Surely I know that it was copypasted (y&amp;p'ed, to be exactly) from Core::ParseModuleListDOM() method. So what?</span>
<a name="l00486"></a>00486 <span class="comment">         * @note You may ask, "Why is DOM extension used here instead of fluffy SimpleXML?". Answer: SimpleXML is n00b shit. We need tr00 DOM functions to op modules in future.</span>
<a name="l00487"></a>00487 <span class="comment">         *</span>
<a name="l00488"></a>00488 <span class="comment">         * @return void</span>
<a name="l00489"></a>00489 <span class="comment">         */</span>
<a name="l00490"></a><a class="code" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">00490</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">ParseLayoutListDOM</a> ()
<a name="l00491"></a>00491         {<span class="comment"></span>
<a name="l00492"></a>00492 <span class="comment">                /// Get all &lt;layout ... /&gt; tags</span>
<a name="l00493"></a>00493 <span class="comment"></span>                $layouts = self::$LayoutListDOM-&gt;getElementsByTagName(<span class="stringliteral">"layout"</span>);
<a name="l00494"></a>00494 <span class="comment"></span>
<a name="l00495"></a>00495 <span class="comment">                /// Run through the whole list of layouts </span>
<a name="l00496"></a>00496 <span class="comment"></span>                foreach ( $layouts as $current_layout )
<a name="l00497"></a>00497                 {<span class="comment"></span>
<a name="l00498"></a>00498 <span class="comment">                        /// @internal Link to corresponding $LayoutList entry</span>
<a name="l00499"></a>00499 <span class="comment"></span>                        $r_layout =&amp; self::$LayoutList[$current_layout-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00500"></a>00500 <span class="comment"></span>
<a name="l00501"></a>00501 <span class="comment">                        /// Now run through attribute list for each layout</span>
<a name="l00502"></a>00502 <span class="comment"></span>                        foreach ( $current_layout-&gt;attributes as $attribute )
<a name="l00503"></a>00503                         {
<a name="l00504"></a>00504                                 $r_layout[$attribute-&gt;name] = $attribute-&gt;value;
<a name="l00505"></a>00505                         }
<a name="l00506"></a>00506                 }
<a name="l00507"></a>00507 <span class="comment"></span>
<a name="l00508"></a>00508 <span class="comment">                /// Tree was successfully parsed!</span>
<a name="l00509"></a>00509 <span class="comment"></span>        }
<a name="l00510"></a>00510 <span class="comment"></span>
<a name="l00511"></a>00511 <span class="comment">        /**</span>
<a name="l00512"></a>00512 <span class="comment">         * Parses core configuration DOM tree, previously loaded with Core::LoadDOM()</span>
<a name="l00513"></a>00513 <span class="comment">         * Core::Config() gets a configuration on success</span>
<a name="l00514"></a>00514 <span class="comment">         *</span>
<a name="l00515"></a>00515 <span class="comment">         * @note All these ParseFooDOM methods are getting smaller and smaller as we move through the class ^_^</span>
<a name="l00516"></a>00516 <span class="comment">         *</span>
<a name="l00517"></a>00517 <span class="comment">         * @return void</span>
<a name="l00518"></a>00518 <span class="comment">         */</span>
<a name="l00519"></a><a class="code" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">00519</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">ParseConfigDOM</a> ()
<a name="l00520"></a>00520         {<span class="comment"></span>
<a name="l00521"></a>00521 <span class="comment">                /// Get all &lt;setting ... /&gt; tags</span>
<a name="l00522"></a>00522 <span class="comment"></span>                $settings = self::$ConfigDOM-&gt;getElementsByTagName(<span class="stringliteral">"setting"</span>);
<a name="l00523"></a>00523 <span class="comment"></span>
<a name="l00524"></a>00524 <span class="comment">                /// Run through the whole list of settings </span>
<a name="l00525"></a>00525 <span class="comment"></span>                foreach ( $settings as $current_setting )
<a name="l00526"></a>00526                 {<span class="comment"></span>
<a name="l00527"></a>00527 <span class="comment">                        /// @internal Link to corresponding $LayoutList entry</span>
<a name="l00528"></a>00528 <span class="comment"></span>                        $r_setting =&amp; self::$Config[$current_setting-&gt;getAttribute(<span class="stringliteral">"name"</span>)];
<a name="l00529"></a>00529 
<a name="l00530"></a>00530                         $r_setting = $current_setting-&gt;getAttribute(<span class="stringliteral">"value"</span>);
<a name="l00531"></a>00531                 }
<a name="l00532"></a>00532 <span class="comment"></span>
<a name="l00533"></a>00533 <span class="comment">                /// Tree was successfully parsed!</span>
<a name="l00534"></a>00534 <span class="comment"></span>        }
<a name="l00535"></a>00535 
<a name="l00536"></a>00536 <span class="comment"></span>
<a name="l00537"></a>00537 <span class="comment">        /**</span>
<a name="l00538"></a>00538 <span class="comment">         * Prepares a "runlist" in Core::$RunList, list of modules to run according to current layout.</span>
<a name="l00539"></a>00539 <span class="comment">         *</span>
<a name="l00540"></a>00540 <span class="comment">         * @return void</span>
<a name="l00541"></a>00541 <span class="comment">         */</span>
<a name="l00542"></a><a class="code" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">00542</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">MakeRunList</a> ()
<a name="l00543"></a>00543         {
<a name="l00544"></a>00544 
<a name="l00545"></a>00545                 self::$RunList = array();
<a name="l00546"></a>00546 
<a name="l00547"></a>00547 <span class="comment"></span>
<a name="l00548"></a>00548 <span class="comment">                /// Add 'core' modules first</span>
<a name="l00549"></a>00549 <span class="comment"></span>                foreach ( self::$ModuleList as $module )
<a name="l00550"></a>00550                 {
<a name="l00551"></a>00551                         <span class="keywordflow">if</span> ( $module['type'] == 'core' )
<a name="l00552"></a>00552                         {
<a name="l00553"></a>00553                                 self::AddModuleToRunList($module['name']);
<a name="l00554"></a>00554                         }
<a name="l00555"></a>00555                 }
<a name="l00556"></a>00556 <span class="comment"></span>
<a name="l00557"></a>00557 <span class="comment">                /// Add modules requested by current layout</span>
<a name="l00558"></a>00558 <span class="comment"></span>                foreach ( explode(<span class="stringliteral">" "</span>, self::$Layout['modules']) as $module )
<a name="l00559"></a>00559                 {
<a name="l00560"></a>00560                         <span class="keywordflow">if</span> ( !array_key_exists($module, self::$ModuleList) )
<a name="l00561"></a>00561                         {
<a name="l00562"></a>00562                                 trigger_error(<span class="stringliteral">"Module '{$module}' in layout '"</span>.self::$Layout['name'].<span class="stringliteral">"' not found in module list!"</span>, E_USER_ERROR);
<a name="l00563"></a>00563                         }
<a name="l00564"></a>00564 
<a name="l00565"></a>00565                         self::AddModuleToRunlist($module);
<a name="l00566"></a>00566                 }
<a name="l00567"></a>00567         }
<a name="l00568"></a>00568                                 <span class="comment"></span>
<a name="l00569"></a>00569 <span class="comment">        /**</span>
<a name="l00570"></a>00570 <span class="comment">         * Add module to current runlist. Takes care of all dependency stuff.</span>
<a name="l00571"></a>00571 <span class="comment">         *</span>
<a name="l00572"></a>00572 <span class="comment">         * @param $module       string  Name of module to add</span>
<a name="l00573"></a>00573 <span class="comment">         *</span>
<a name="l00574"></a>00574 <span class="comment">         * @return void</span>
<a name="l00575"></a>00575 <span class="comment">         */</span>
<a name="l00576"></a><a class="code" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">00576</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">AddModuleToRunlist</a> ($module)
<a name="l00577"></a>00577         {<span class="comment"></span>
<a name="l00578"></a>00578 <span class="comment">                /// Check if module has already been added to runlist</span>
<a name="l00579"></a>00579 <span class="comment"></span>                <span class="keywordflow">if</span> ( in_array($module, self::$RunList) )
<a name="l00580"></a>00580                 {
<a name="l00581"></a>00581                         <span class="keywordflow">return</span>;
<a name="l00582"></a>00582                 }
<a name="l00583"></a>00583                 <span class="keywordflow">else</span>
<a name="l00584"></a>00584                 {
<a name="l00585"></a>00585                         <span class="keywordflow">if</span> ( self::$ModuleList[$module]['deps'] )
<a name="l00586"></a>00586                         {<span class="comment"></span>
<a name="l00587"></a>00587 <span class="comment">                                /// Add all deps as well, if there're any</span>
<a name="l00588"></a>00588 <span class="comment"></span>                                foreach ( explode(<span class="stringliteral">" "</span>, self::$ModuleList[$module]['deps']) as $dep )
<a name="l00589"></a>00589                                 {
<a name="l00590"></a>00590                                         self::AddModuleToRunlist($dep);
<a name="l00591"></a>00591                                 }
<a name="l00592"></a>00592                         }
<a name="l00593"></a>00593 
<a name="l00594"></a>00594                         self::$RunList[] = $module;
<a name="l00595"></a>00595                 }
<a name="l00596"></a>00596         }
<a name="l00597"></a>00597 <span class="comment"></span>
<a name="l00598"></a>00598 <span class="comment">        /**</span>
<a name="l00599"></a>00599 <span class="comment">         * Check all modules in module list for self-depending or depending on non-existent modules</span>
<a name="l00600"></a>00600 <span class="comment">         *</span>
<a name="l00601"></a>00601 <span class="comment">         * @return void</span>
<a name="l00602"></a>00602 <span class="comment">         */</span>
<a name="l00603"></a><a class="code" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">00603</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">CheckModuleList</a> ()
<a name="l00604"></a>00604         {
<a name="l00605"></a>00605                 foreach ( self::$ModuleList as $module )
<a name="l00606"></a>00606                 {
<a name="l00607"></a>00607                         self::CheckModuleDependencies($module['name']);
<a name="l00608"></a>00608                 }
<a name="l00609"></a>00609         }
<a name="l00610"></a>00610 <span class="comment"></span>
<a name="l00611"></a>00611 <span class="comment">        /**</span>
<a name="l00612"></a>00612 <span class="comment">         * Traces a dependency tree for module, </span>
<a name="l00613"></a>00613 <span class="comment">         * if any module is encountered in its own dep-tree or</span>
<a name="l00614"></a>00614 <span class="comment">         * non-existent dependency encountered, generate an error.</span>
<a name="l00615"></a>00615 <span class="comment">         *</span>
<a name="l00616"></a>00616 <span class="comment">         * @param $module       string          Name of module to check</span>
<a name="l00617"></a>00617 <span class="comment">         * @param $tracePath    array           Dep-tree for current module (used only for recurrent walk-through, do not use!)</span>
<a name="l00618"></a>00618 <span class="comment">         *</span>
<a name="l00619"></a>00619 <span class="comment">         * @warning DO NOT pass any argument other than $module!</span>
<a name="l00620"></a>00620 <span class="comment">         *</span>
<a name="l00621"></a>00621 <span class="comment">         * @return              boolean TRUE if check succeeded</span>
<a name="l00622"></a>00622 <span class="comment">         */</span>
<a name="l00623"></a><a class="code" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">00623</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">CheckModuleDependencies</a> ( $module, $tracePath = array () )
<a name="l00624"></a>00624         {
<a name="l00625"></a>00625                 <span class="keywordflow">if</span> ( self::$ModuleList[$module]['deps'] == <span class="stringliteral">""</span> )
<a name="l00626"></a>00626                 {
<a name="l00627"></a>00627                         <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00628"></a>00628                 }
<a name="l00629"></a>00629 
<a name="l00630"></a>00630                 foreach ( explode(<span class="stringliteral">" "</span>, self::$ModuleList[$module]['deps']) as $dep )
<a name="l00631"></a>00631                 {
<a name="l00632"></a>00632 <span class="comment"></span>
<a name="l00633"></a>00633 <span class="comment">                        /// Wasn't this dependency in our trace path?</span>
<a name="l00634"></a>00634 <span class="comment"></span>                        <span class="keywordflow">if</span> ( in_array($dep, $tracePath) ) 
<a name="l00635"></a>00635                         {
<a name="l00636"></a>00636                                 trigger_error(<span class="stringliteral">"Module dependency loop encountered: {$module} -&gt; "</span>.join(<span class="stringliteral">" -&gt; "</span>, $tracePath ), E_USER_ERROR );
<a name="l00637"></a>00637                         }
<a name="l00638"></a>00638 <span class="comment"></span>
<a name="l00639"></a>00639 <span class="comment">                        /// Does this dependecy exist at all?</span>
<a name="l00640"></a>00640 <span class="comment"></span>                        <span class="keywordflow">if</span> ( !array_key_exists($dep, self::$ModuleList) )
<a name="l00641"></a>00641                         {
<a name="l00642"></a>00642                                 trigger_error(<span class="stringliteral">"Module '{$module}' depends on non-existing module {$dep}!"</span>, E_USER_ERROR);
<a name="l00643"></a>00643                         }
<a name="l00644"></a>00644 <span class="comment"></span>
<a name="l00645"></a>00645 <span class="comment">                        /// add dep to trace path</span>
<a name="l00646"></a>00646 <span class="comment"></span>                        $tracePath[] = $dep;
<a name="l00647"></a>00647 
<a name="l00648"></a>00648                         self::CheckModuleDependencies($dep, $tracePath);
<a name="l00649"></a>00649 
<a name="l00650"></a>00650                 }
<a name="l00651"></a>00651 
<a name="l00652"></a>00652                 <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00653"></a>00653         }
<a name="l00654"></a>00654 <span class="comment"></span>
<a name="l00655"></a>00655 <span class="comment">        /**</span>
<a name="l00656"></a>00656 <span class="comment">         * Checks whether cache for specified target file is up to date</span>
<a name="l00657"></a>00657 <span class="comment">         *</span>
<a name="l00658"></a>00658 <span class="comment">         * @param $source_file          string          Name of 'source' file</span>
<a name="l00659"></a>00659 <span class="comment">         * @param $prefix               string          Source-specific cache prefix </span>
<a name="l00660"></a>00660 <span class="comment">         *</span>
<a name="l00661"></a>00661 <span class="comment">         * @return                      boolean         Return true if cache is up to date and false if it's too old or does not exist at all</span>
<a name="l00662"></a>00662 <span class="comment">         */</span>
<a name="l00663"></a><a class="code" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">00663</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">IsCacheOk</a> ($source_file, $prefix=<span class="stringliteral">""</span>)
<a name="l00664"></a>00664         {
<a name="l00665"></a>00665                 $cache_file = self::GetCacheName($source_file, $prefix); 
<a name="l00666"></a>00666 <span class="comment"></span>
<a name="l00667"></a>00667 <span class="comment">                /// Check if cache doesn't exist</span>
<a name="l00668"></a>00668 <span class="comment"></span>                <span class="keywordflow">if</span> ( !is_readable($cache_file) )
<a name="l00669"></a>00669                 {
<a name="l00670"></a>00670                         <span class="keywordflow">return</span> <span class="keyword">false</span>;
<a name="l00671"></a>00671                 }
<a name="l00672"></a>00672 <span class="comment"></span>
<a name="l00673"></a>00673 <span class="comment">                /// Check for existence of source file</span>
<a name="l00674"></a>00674 <span class="comment"></span>                <span class="keywordflow">if</span> ( !is_readable($source_file) )
<a name="l00675"></a>00675                 {
<a name="l00676"></a>00676                         trigger_error(<span class="stringliteral">"Could not access {$source_file}"</span>, E_USER_ERROR);
<a name="l00677"></a>00677                 }
<a name="l00678"></a>00678 <span class="comment"></span>
<a name="l00679"></a>00679 <span class="comment">                /// Check that source file is older than cache</span>
<a name="l00680"></a>00680 <span class="comment"></span>                <span class="keywordflow">if</span> ( filemtime($source_file) &gt;= filemtime($cache_file) )
<a name="l00681"></a>00681                 {
<a name="l00682"></a>00682                         <span class="keywordflow">return</span> <span class="keyword">false</span>;
<a name="l00683"></a>00683                 }
<a name="l00684"></a>00684 
<a name="l00685"></a>00685                 <span class="keywordflow">return</span> <span class="keyword">true</span>;
<a name="l00686"></a>00686         }
<a name="l00687"></a>00687 <span class="comment"></span>
<a name="l00688"></a>00688 <span class="comment">        /**</span>
<a name="l00689"></a>00689 <span class="comment">         * Get name of cache for target file</span>
<a name="l00690"></a>00690 <span class="comment">         *</span>
<a name="l00691"></a>00691 <span class="comment">         * @param $source_file          string  Target file name</span>
<a name="l00692"></a>00692 <span class="comment">         * @param $prefix               string  Cache prefix. Useful to when caching several different files with the same names</span>
<a name="l00693"></a>00693 <span class="comment">         *</span>
<a name="l00694"></a>00694 <span class="comment">         * @return                      string  Path to cache file</span>
<a name="l00695"></a>00695 <span class="comment">         */</span>
<a name="l00696"></a><a class="code" href="classCore.html#7e860483a3033de70dc374e452900079">00696</a>          <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#7e860483a3033de70dc374e452900079">GetCacheName</a> ($source_file, $prefix=<span class="stringliteral">""</span>)
<a name="l00697"></a>00697          {
<a name="l00698"></a>00698                  <span class="keywordflow">if</span> ( strlen($prefix) )
<a name="l00699"></a>00699                  {
<a name="l00700"></a>00700                          $prefix .= <span class="charliteral">'.'</span>;
<a name="l00701"></a>00701                  }
<a name="l00702"></a>00702                  <span class="keywordflow">return</span> self::$Config['CacheDir'].<span class="stringliteral">"/"</span>.$prefix.$source_file.<span class="stringliteral">".cache.php"</span>;
<a name="l00703"></a>00703          }
<a name="l00704"></a>00704 <span class="comment"></span>
<a name="l00705"></a>00705 <span class="comment">        /**</span>
<a name="l00706"></a>00706 <span class="comment">         * Dump some array into specified cache file.</span>
<a name="l00707"></a>00707 <span class="comment">         * Currently used to rebuild module list and layout list caches.</span>
<a name="l00708"></a>00708 <span class="comment">         *</span>
<a name="l00709"></a>00709 <span class="comment">         * @see Core::ArrayToDefinition() Core::GetCacheName()</span>
<a name="l00710"></a>00710 <span class="comment">         *</span>
<a name="l00711"></a>00711 <span class="comment">         * @param $contents     array   Contents of array to be cached  </span>
<a name="l00712"></a>00712 <span class="comment">         * @param $file         string  Path to destination file (cache file) </span>
<a name="l00713"></a>00713 <span class="comment">         *</span>
<a name="l00714"></a>00714 <span class="comment">         * @note It may be needed to use Core::GetCacheName() method to make a proper path to destination file to pass it as $file argument for this method</span>
<a name="l00715"></a>00715 <span class="comment">         * @note Cache file will RETURN proper array definition, not declare it, </span>
<a name="l00716"></a>00716 <span class="comment">         *       so to use cache contents later you'll need to do it in a such way: $some_variable=require("cache.file.php")</span>
<a name="l00717"></a>00717 <span class="comment">         * @note I hope you catch the idea of previous note.</span>
<a name="l00718"></a>00718 <span class="comment">         *</span>
<a name="l00719"></a>00719 <span class="comment">         * @return void</span>
<a name="l00720"></a>00720 <span class="comment">         */</span>
<a name="l00721"></a><a class="code" href="classCore.html#9c2596c82e620380c71bed110db40ca7">00721</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#9c2596c82e620380c71bed110db40ca7">DumpArrayToCache</a> ($contents, $file)
<a name="l00722"></a>00722         {
<a name="l00723"></a>00723                 <span class="keywordflow">if</span> ( !is_array($contents) )
<a name="l00724"></a>00724                 {
<a name="l00725"></a>00725                         trigger_error(<span class="stringliteral">"Attempted to cache non-array datatype with Core::DumpArrayToCache()"</span>, E_USER_WARNING);
<a name="l00726"></a>00726                 }
<a name="l00727"></a>00727                 
<a name="l00728"></a>00728                 $cache .= <span class="stringliteral">"&lt;?php\nreturn "</span>;
<a name="l00729"></a>00729 
<a name="l00730"></a>00730                 $cache .= self::ArrayToDefinition($contents);
<a name="l00731"></a>00731 
<a name="l00732"></a>00732                 $cache .= <span class="stringliteral">";\n?&gt;"</span>;
<a name="l00733"></a>00733 
<a name="l00734"></a>00734                 <span class="keywordflow">if</span> ( @!file_put_contents($file, $cache) )
<a name="l00735"></a>00735                 {
<a name="l00736"></a>00736                         trigger_error(<span class="stringliteral">"Could not write to {$file}!"</span>, E_USER_ERROR);
<a name="l00737"></a>00737                 }
<a name="l00738"></a>00738         }
<a name="l00739"></a>00739 <span class="comment"></span>
<a name="l00740"></a>00740 <span class="comment">        /**</span>
<a name="l00741"></a>00741 <span class="comment">         * Dump any string data into specified cache file</span>
<a name="l00742"></a>00742 <span class="comment">         *</span>
<a name="l00743"></a>00743 <span class="comment">         * @param $contents     string  Contents of string to be cached</span>
<a name="l00744"></a>00744 <span class="comment">         * @param $file         string  Path to destination file (cache file)</span>
<a name="l00745"></a>00745 <span class="comment">         *</span>
<a name="l00746"></a>00746 <span class="comment">         * @return void</span>
<a name="l00747"></a>00747 <span class="comment">         */</span>
<a name="l00748"></a><a class="code" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">00748</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">DumpStringToCache</a> ($contents, $file)
<a name="l00749"></a>00749         {
<a name="l00750"></a>00750                 $cache .= <span class="stringliteral">"&lt;?php\nreturn "</span>;
<a name="l00751"></a>00751 
<a name="l00752"></a>00752                 $cache .= <span class="stringliteral">"'"</span>;
<a name="l00753"></a>00753 
<a name="l00754"></a>00754                 $cache .= $contents;
<a name="l00755"></a>00755 
<a name="l00756"></a>00756                 $cache .= <span class="stringliteral">"';\n?&gt;"</span>;
<a name="l00757"></a>00757 
<a name="l00758"></a>00758                 <span class="keywordflow">if</span> ( @!file_put_contents($file, $cache) )
<a name="l00759"></a>00759                 {
<a name="l00760"></a>00760                         trigger_error(<span class="stringliteral">"Could not write to {$file}!"</span>, E_USER_ERROR);
<a name="l00761"></a>00761                 }
<a name="l00762"></a>00762         }
<a name="l00763"></a>00763 
<a name="l00764"></a>00764 <span class="comment"></span>
<a name="l00765"></a>00765 <span class="comment">        /**</span>
<a name="l00766"></a>00766 <span class="comment">         * Convert an array to piece of valid PHP array definition, used for caching in Core::DumpArrayToCache() method.</span>
<a name="l00767"></a>00767 <span class="comment">         *</span>
<a name="l00768"></a>00768 <span class="comment">         * @todo It would be nice to make ArrayToDefinition method public in future</span>
<a name="l00769"></a>00769 <span class="comment">         *</span>
<a name="l00770"></a>00770 <span class="comment">         * @param $array        array           Array to convert</span>
<a name="l00771"></a>00771 <span class="comment">         * @param $cache        string          Currently formed cache</span>
<a name="l00772"></a>00772 <span class="comment">         * @param $inner        boolean         Whether this function was called from itself</span>
<a name="l00773"></a>00773 <span class="comment">         *</span>
<a name="l00774"></a>00774 <span class="comment">         * @warning DO NOT pass any argument other than $array</span>
<a name="l00775"></a>00775 <span class="comment">         *</span>
<a name="l00776"></a>00776 <span class="comment">         * @return              string          Valid PHP array definition</span>
<a name="l00777"></a>00777 <span class="comment">         */</span>
<a name="l00778"></a><a class="code" href="classCore.html#1735ab51c34cbedda6b963629de1839d">00778</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#1735ab51c34cbedda6b963629de1839d">ArrayToDefinition</a> ($array, $cache=<span class="stringliteral">""</span>, $inner=<span class="keyword">false</span>)
<a name="l00779"></a>00779         {
<a name="l00780"></a>00780                 $cache = <span class="stringliteral">"array (\n"</span>;
<a name="l00781"></a>00781                         
<a name="l00782"></a>00782                 foreach ( $array as $key =&gt; $value )
<a name="l00783"></a>00783                 {
<a name="l00784"></a>00784                         <span class="keywordflow">if</span> ( gettype($value) == <span class="stringliteral">"array"</span> )
<a name="l00785"></a>00785                         {<span class="comment"></span>
<a name="l00786"></a>00786 <span class="comment">                                /// recurrent function call</span>
<a name="l00787"></a>00787 <span class="comment"></span>                                $cache .= <span class="stringliteral">"\"{$key}\" =&gt; "</span>.self::ArrayToDefinition($value, $cache, <span class="keyword">true</span>);
<a name="l00788"></a>00788                         }
<a name="l00789"></a>00789                         <span class="keywordflow">else</span>
<a name="l00790"></a>00790                         {
<a name="l00791"></a>00791                                 $cache .= <span class="stringliteral">"\"{$key}\" =&gt; \"{$value}\",\n"</span>;
<a name="l00792"></a>00792                         }
<a name="l00793"></a>00793                 }
<a name="l00794"></a>00794 
<a name="l00795"></a>00795                 $cache .= <span class="stringliteral">")"</span>;
<a name="l00796"></a>00796 
<a name="l00797"></a>00797                 <span class="keywordflow">if</span> ( $inner ) $cache .= <span class="stringliteral">",\n"</span>;
<a name="l00798"></a>00798 
<a name="l00799"></a>00799                 <span class="keywordflow">return</span> $cache;
<a name="l00800"></a>00800         }
<a name="l00801"></a>00801 <span class="comment"></span>
<a name="l00802"></a>00802 <span class="comment">        /**</span>
<a name="l00803"></a>00803 <span class="comment">         * @brief Get miscellanous information</span>
<a name="l00804"></a>00804 <span class="comment">         *</span>
<a name="l00805"></a>00805 <span class="comment">         * Method provides us various information on user request data, session variables, Core::$Config and timers' values.</span>
<a name="l00806"></a>00806 <span class="comment">         *</span>
<a name="l00807"></a>00807 <span class="comment">         * @return      array   Array containing information</span>
<a name="l00808"></a>00808 <span class="comment">         */</span>
<a name="l00809"></a><a class="code" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">00809</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">GetDebugInfo</a> ()
<a name="l00810"></a>00810         {
<a name="l00811"></a>00811                 $message[] = <span class="stringliteral">"[REQUEST]"</span>;
<a name="l00812"></a>00812                 foreach ( $_REQUEST as $key =&gt; $value )
<a name="l00813"></a>00813                 {
<a name="l00814"></a>00814                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00815"></a>00815                 }
<a name="l00816"></a>00816 
<a name="l00817"></a>00817                 $message[] = <span class="stringliteral">"[SESSION]"</span>;
<a name="l00818"></a>00818                 foreach ( $_SESSION as $key =&gt; $value )
<a name="l00819"></a>00819                 {
<a name="l00820"></a>00820                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00821"></a>00821                 }
<a name="l00822"></a>00822 
<a name="l00823"></a>00823                 $message[] = <span class="stringliteral">"[COOKIE]"</span>;
<a name="l00824"></a>00824                 foreach ( $_COOKIE as $key =&gt; $value )
<a name="l00825"></a>00825                 {
<a name="l00826"></a>00826                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00827"></a>00827                 }
<a name="l00828"></a>00828 
<a name="l00829"></a>00829                 $message[] = <span class="stringliteral">"[CONFIG]"</span>;
<a name="l00830"></a>00830                 foreach ( self::$Config as $key =&gt; $value )
<a name="l00831"></a>00831                 {
<a name="l00832"></a>00832                         <span class="keywordflow">if</span> ( $value == <span class="keyword">false</span> ) $value = 0;
<a name="l00833"></a>00833                         $message[] = <span class="stringliteral">"{$key}: {$value}"</span>;
<a name="l00834"></a>00834                 }
<a name="l00835"></a>00835 
<a name="l00836"></a>00836                 $message[] = <span class="stringliteral">"[TIMERS]"</span>;
<a name="l00837"></a>00837                 foreach ( self::$Timers as $timer =&gt; $Value )
<a name="l00838"></a>00838                 {
<a name="l00839"></a>00839                         $message[] = <span class="stringliteral">"Timer #{$timer}: "</span>.self::GetTimer($timer);
<a name="l00840"></a>00840                 }
<a name="l00841"></a>00841 
<a name="l00842"></a>00842                 <span class="keywordflow">return</span> $message;
<a name="l00843"></a>00843         }
<a name="l00844"></a>00844 <span class="comment"></span>
<a name="l00845"></a>00845 <span class="comment">        /**</span>
<a name="l00846"></a>00846 <span class="comment">         * @brief Built-in error handler</span>
<a name="l00847"></a>00847 <span class="comment">         *</span>
<a name="l00848"></a>00848 <span class="comment">         * Generates error messages of different types, depending</span>
<a name="l00849"></a>00849 <span class="comment">         * on $ErrorCode value.\n</span>
<a name="l00850"></a>00850 <span class="comment">         * On E_USER_ERROR generates generic fatal error message and halts</span>
<a name="l00851"></a>00851 <span class="comment">         * the system. If $Config['MoreErrorInfo']is true, additional information is included in the</span>
<a name="l00852"></a>00852 <span class="comment">         * output (Core::GetDebugInfo() and error line/file ).\n</span>
<a name="l00853"></a>00853 <span class="comment">         * If E_USER_NOTICE or E_USER_WARNING occur, adds a new entry into Core::$Errors container.</span>
<a name="l00854"></a>00854 <span class="comment">         * </span>
<a name="l00855"></a>00855 <span class="comment">         * @warning Do not call this method directly, use &lt;pre&gt;trigger_error("OMG error!", E_USER_ERROR);&lt;/pre&gt; instead!</span>
<a name="l00856"></a>00856 <span class="comment">         *</span>
<a name="l00857"></a>00857 <span class="comment">         * @param $errorCode      E_USER_ERROR, E_USER_WARNING or E_USER_NOTICE </span>
<a name="l00858"></a>00858 <span class="comment">         * @param $errorMessage   string  Text of error</span>
<a name="l00859"></a>00859 <span class="comment">         */</span>
<a name="l00860"></a><a class="code" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">00860</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Error</a> ($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)
<a name="l00861"></a>00861         {<span class="comment"></span>
<a name="l00862"></a>00862 <span class="comment">                /// Array containing CSS styles for messages</span>
<a name="l00863"></a>00863 <span class="comment"></span>                $styles = array 
<a name="l00864"></a>00864                 (
<a name="l00865"></a>00865                         E_USER_ERROR =&gt; <span class="stringliteral">"padding: 15%; text-align: center; margin-top: 10px; margin-left: 10px; margin-right: 10px; font-size: x-large; background-color: #f58169; font-weight: bold; color: #571b1b; valign: center;"</span>,
<a name="l00866"></a>00866                         E_USER_NOTICE =&gt; <span class="stringliteral">"background-color: #a1ea8d; text-align: left; font-size: medium; color: black; padding: 2%;"</span>,
<a name="l00867"></a>00867                         E_USER_WARNING =&gt; <span class="stringliteral">"background-color: #eac583; text-align: left; font-size: large; color: #571b1b; padding: 2%;"</span>
<a name="l00868"></a>00868                 );
<a name="l00869"></a>00869 
<a name="l00870"></a>00870 <span class="comment"></span>
<a name="l00871"></a>00871 <span class="comment">                /// Generate the whole page on critical errors and halt system</span>
<a name="l00872"></a>00872 <span class="comment"></span>                <span class="keywordflow">if</span> ( $errorCode == E_USER_ERROR )
<a name="l00873"></a>00873                 {
<a name="l00874"></a>00874                         <span class="keywordflow">if</span> ( self::$Config['MoreErrorInfo'] )
<a name="l00875"></a>00875                         {
<a name="l00876"></a>00876                                 $errorMessage .= <span class="stringliteral">"&lt;br /&gt; File {$errorFile}, line {$errorLine}"</span>;
<a name="l00877"></a>00877                                 $errorMessage .= <span class="stringliteral">"&lt;br /&gt;"</span>.join(self::GetDebugInfo(), <span class="stringliteral">"&lt;br /&gt;"</span>);
<a name="l00878"></a>00878                         }
<a name="l00879"></a>00879 
<a name="l00880"></a>00880                         echo <span class="stringliteral">"</span>
<a name="l00881"></a>00881 <span class="stringliteral">                        &lt;?xml version=\"1.0\" encoding=\"utf-8\"&gt;</span>
<a name="l00882"></a>00882 <span class="stringliteral">                        &lt;!DOCTYPE html </span>
<a name="l00883"></a>00883 <span class="stringliteral">                        PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"</span>
<a name="l00884"></a>00884 <span class="stringliteral">                        \"http://w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"&gt;</span>
<a name="l00885"></a>00885 <span class="stringliteral">                        &lt;html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\"&gt;</span>
<a name="l00886"></a>00886 <span class="stringliteral">                                &lt;head&gt;</span>
<a name="l00887"></a>00887 <span class="stringliteral">                                        &lt;title&gt;Core Error&lt;/title&gt;</span>
<a name="l00888"></a>00888 <span class="stringliteral">                                &lt;/head&gt;</span>
<a name="l00889"></a>00889 <span class="stringliteral">                                &lt;body&gt;</span>
<a name="l00890"></a>00890 <span class="stringliteral">                                        &lt;div style=\"{$styles[E_USER_ERROR]}\"&gt; </span>
<a name="l00891"></a>00891 <span class="stringliteral">                                        {$errorMessage}</span>
<a name="l00892"></a>00892 <span class="stringliteral">                                        &lt;/div&gt;</span>
<a name="l00893"></a>00893 <span class="stringliteral">                                &lt;/body&gt;</span>
<a name="l00894"></a>00894 <span class="stringliteral">                        &lt;/html&gt;</span>
<a name="l00895"></a>00895 <span class="stringliteral">                        "</span>;
<a name="l00896"></a>00896 
<a name="l00897"></a>00897                         exit ();
<a name="l00898"></a>00898                 }
<a name="l00899"></a>00899                 <span class="keywordflow">else</span>
<a name="l00900"></a>00900                 {<span class="comment"></span>
<a name="l00901"></a>00901 <span class="comment">                        /// Add entry to Core::$Errors on non-critical errors</span>
<a name="l00902"></a>00902 <span class="comment"></span>                        self::$Errors[] = array
<a name="l00903"></a>00903                         (
<a name="l00904"></a>00904                                 'errorCode' =&gt; $errorCode,
<a name="l00905"></a>00905                                 'errorFile' =&gt; $errorFile,
<a name="l00906"></a>00906                                 'errorMessage' =&gt; $errorMessage,
<a name="l00907"></a>00907                         );
<a name="l00908"></a>00908                 }
<a name="l00909"></a>00909         }
<a name="l00910"></a>00910 <span class="comment"></span>
<a name="l00911"></a>00911 <span class="comment">        /**</span>
<a name="l00912"></a>00912 <span class="comment">         * Get current Core config</span>
<a name="l00913"></a>00913 <span class="comment">         *</span>
<a name="l00914"></a>00914 <span class="comment">         * @return              array Core::$Config</span>
<a name="l00915"></a>00915 <span class="comment">         */</span>
<a name="l00916"></a><a class="code" href="classCore.html#c8a8d74c512360172e80b0343028b605">00916</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#c8a8d74c512360172e80b0343028b605">GetConfig</a> ()
<a name="l00917"></a>00917         {
<a name="l00918"></a>00918                 <span class="keywordflow">return</span> self::$Config;
<a name="l00919"></a>00919         }
<a name="l00920"></a>00920 <span class="comment"></span>
<a name="l00921"></a>00921 <span class="comment">        /**</span>
<a name="l00922"></a>00922 <span class="comment">         * Start ticking specified timer</span>
<a name="l00923"></a>00923 <span class="comment">         *</span>
<a name="l00924"></a>00924 <span class="comment">         * @param $number       integer         Number of timer to start ticking</span>
<a name="l00925"></a>00925 <span class="comment">         *</span>
<a name="l00926"></a>00926 <span class="comment">         * @return              void</span>
<a name="l00927"></a>00927 <span class="comment">         */</span>
<a name="l00928"></a><a class="code" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">00928</a>         <span class="keyword">static</span> <span class="keyword">private</span> function <a class="code" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">StartTimer</a> ($number=1)
<a name="l00929"></a>00929         {
<a name="l00930"></a>00930                 self::$Timers[$number] = microtime();
<a name="l00931"></a>00931         }
<a name="l00932"></a>00932 <span class="comment"></span>
<a name="l00933"></a>00933 <span class="comment">        /**</span>
<a name="l00934"></a>00934 <span class="comment">         * Get specified timer value</span>
<a name="l00935"></a>00935 <span class="comment">         *</span>
<a name="l00936"></a>00936 <span class="comment">         * @param $number       integer         Number of timer which is about to give us its current value lol</span>
<a name="l00937"></a>00937 <span class="comment">         *</span>
<a name="l00938"></a>00938 <span class="comment">         * @return              integer Current timer value</span>
<a name="l00939"></a>00939 <span class="comment">         */</span>
<a name="l00940"></a><a class="code" href="classCore.html#b779a4778fcbfe097daab723acb4f092">00940</a>         <span class="keyword">static</span> <span class="keyword">public</span> function <a class="code" href="classCore.html#b779a4778fcbfe097daab723acb4f092">GetTimer</a> ($number=1)
<a name="l00941"></a>00941         {
<a name="l00942"></a>00942                <span class="keywordflow">return</span> ( microtime() - self::$Timers[$number] ); 
<a name="l00943"></a>00943         }
<a name="l00944"></a>00944 }
<a name="l00945"></a>00945 
<a name="l00946"></a>00946 <a class="code" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Core::Run</a>();
<a name="l00947"></a>00947 ?&gt;
</pre></div><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 13:09:11 2006 for YAMWS by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   doc/html/index_8php-source.html                                                                     0000644 0001750 0001750 00000003011 10472274067 016431  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: /home/sphinx/Projects/PHP/YAMWS/index.php Source File</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/index.php</h1><a href="index_8php.html">Go to the documentation of this file.</a><div class="fragment"><pre class="fragment"><a name="l00001"></a>00001 &lt;?php
<a name="l00002"></a>00002 
<a name="l00003"></a>00003 require(<span class="stringliteral">"base_module.php"</span>);
<a name="l00004"></a>00004 require(<span class="stringliteral">"core.php"</span>);
<a name="l00005"></a>00005 <a class="code" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Core::Run</a>();
<a name="l00006"></a>00006 
<a name="l00007"></a>00007 ?&gt;
</pre></div><hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 13:09:11 2006 for YAMWS by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       doc/html/base__module_8php.html                                                                     0000644 0001750 0001750 00000003116 10472421406 016437  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: /home/sphinx/Projects/PHP/YAMWS/base_module.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/base_module.php File Reference</h1><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Classes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">class &nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html">BaseModule</a></td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Abstract module class, providing basic skin and language methods.  <a href="classBaseModule.html#_details">More...</a><br></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                  doc/html/core_8cfg_8xml_8cache_8php.html                                                            0000644 0001750 0001750 00000002311 10472273662 020050  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: /home/sphinx/Projects/PHP/YAMWS/core.cfg.xml.cache.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/core.cfg.xml.cache.php File Reference</h1>
<p>
<a href="core_8cfg_8xml_8cache_8php-source.html">Go to the source code of this file.</a><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Mon Aug 21 13:06:58 2006 for YAMWS by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                       doc/html/core_8php.html                                                                             0000644 0001750 0001750 00000003077 10472421406 014757  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: /home/sphinx/Projects/PHP/YAMWS/core.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/core.php File Reference</h1><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Classes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">class &nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html">Core</a></td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Module and layout handling, consistency checking, caching methods, running system.  <a href="classCore.html#_details">More...</a><br></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                 doc/html/index_8php.html                                                                            0000644 0001750 0001750 00000002222 10472421406 015125  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: /home/sphinx/Projects/PHP/YAMWS/index.php File Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li><a href="annotated.html"><span>Classes</span></a></li>
    <li id="current"><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<h1>/home/sphinx/Projects/PHP/YAMWS/index.php File Reference</h1><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
</table>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                              doc/html/classBaseModule.html                                                                       0000644 0001750 0001750 00000037016 10472421406 016136  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: BaseModule Class Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>BaseModule Class Reference</h1><!-- doxytag: class="BaseModule" -->Abstract module class, providing basic skin and language methods.  
<a href="#_details">More...</a>
<p>
<a href="classBaseModule-members.html">List of all members.</a><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Static Public Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseStartup</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">Run</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">Skin</a> ($bitName, $arguments, $useCaching=0, $returnData=0)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">Lang</a> ($bitName, $arguments=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">GetHtml</a> ()</td></tr>

<tr><td colspan="2"><br><h2>Static Private Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">LoadLang</a> ()</td></tr>

<tr><td colspan="2"><br><h2>Static Private Attributes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">$Config</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">$Html</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">$ModuleName</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">$Lang</a></td></tr>

</table>
<hr><a name="_details"></a><h2>Detailed Description</h2>
Abstract module class, providing basic skin and language methods. 
<p>
<dl compact><dt><b>Author:</b></dt><dd>Sphinx </dd></dl>
<dl compact><dt><b>Date:</b></dt><dd>2006</dd></dl>
All modules in system should extend this very class to provide single skin&amp;language handling facilities.<br>
 Study <a class="el" href="classBaseModule.html">BaseModule</a> members' docs to learn more about default module methods and data containers.<p>
<dl compact><dt><b>Note:</b></dt><dd><a class="el" href="classBaseModule.html">BaseModule</a> errors are handled by <a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Core::Error()</a> </dd></dl>

<p>
<hr><h2>Member Function Documentation</h2>
<a class="anchor" name="11334f6f33dc88122b0a94c887725052"></a><!-- doxytag: member="BaseModule::BaseStartup" ref="11334f6f33dc88122b0a94c887725052" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::BaseStartup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepare module. This method is called by <a class="el" href="classCore.html">Core</a> before calling module's <a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> method every time module runs!<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">BaseModule::Startup()</a> <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">BaseModule::Run()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="6faeccfec86169417e6318fc760ba577"></a><!-- doxytag: member="BaseModule::GetHtml" ref="6faeccfec86169417e6318fc760ba577" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::GetHtml           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Returns module current $Html<p>
<dl compact><dt><b>Returns:</b></dt><dd>string All html generated by module </dd></dl>

</div>
</div><p>
<a class="anchor" name="035af0942841311dd3f3e7f663db8323"></a><!-- doxytag: member="BaseModule::Lang" ref="035af0942841311dd3f3e7f663db8323" args="($bitName, $arguments=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Lang           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>bitName</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>arguments</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Return specified language bit<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$bitName</em>&nbsp;</td><td>string Name of language bit </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$arguments</em>&nbsp;</td><td>array List of arguments which are used in vsprintf() function with selected language bit. <b>EXAMPLE</b>: say, language bit is "current user %s has %d posts" and $arguments is array("Alex",15), then method will output the following to module's <dl compact><dt><b>Html</b></dt><dd>"current user Alex has 15 posts". If </dd></dl>
arguments is empty, then no string substitution will be performed. </td></tr>
  </table>
</dl>

</div>
</div><p>
<a class="anchor" name="5359716a861284e5a6b1ff2296cf31aa"></a><!-- doxytag: member="BaseModule::LoadLang" ref="5359716a861284e5a6b1ff2296cf31aa" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::LoadLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load modules's $Lang to allow usage of <a class="el" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">Lang()</a> method and $Lang container 
</div>
</div><p>
<a class="anchor" name="090f2e30c601c0b3072e97dd02f91260"></a><!-- doxytag: member="BaseModule::Run" ref="090f2e30c601c0b3072e97dd02f91260" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Run           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, abstract]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module-specific run routines must be defined in this method. <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">Run()</a> is called by <a class="el" href="classCore.html">Core</a> after <a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> 
</div>
</div><p>
<a class="anchor" name="2d76af4157b2673d4740c554e08aef96"></a><!-- doxytag: member="BaseModule::Skin" ref="2d76af4157b2673d4740c554e08aef96" args="($bitName, $arguments, $useCaching=0, $returnData=0)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Skin           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>bitName</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>arguments</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>useCaching</em> = <code>0</code>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>returnData</em> = <code>0</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Put specific skin bit to module's $Html Skin bits are .xhtml files in module's skin directory, which is selected as<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$bitName</em>&nbsp;</td><td>string Name of skin bit (.xhtml file in module's skin directory) </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$arguments</em>&nbsp;</td><td>array Associative array, where keys are strings occuring in skin bit, which are replaced on output by corresponding data in values in $arguments. <b>EXAMPLE</b>: say, we have $arguments = array("%msg%"=&gt;"Success!"), then all strings 'msg' in skin bit will be replaced by "Success!" </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$useCaching</em>&nbsp;</td><td>bool If TRUE, supercaching for this very call will be used </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$returnData</em>&nbsp;</td><td>bool If TRUE, Skin method will return HTML data instead of adding it to module's $Html. May be useful when using output of Skin method in another one instead of immediately adding it to $Html </td></tr>
  </table>
</dl>

</div>
</div><p>
<a class="anchor" name="a02aa1a71b87ff5ea8130861df6b1bf8"></a><!-- doxytag: member="BaseModule::Startup" ref="a02aa1a71b87ff5ea8130861df6b1bf8" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static BaseModule::Startup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, abstract]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module-specific pre-run routines should be defined in this method. <a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup()</a> method is called by <a class="el" href="classCore.html">Core</a> before calling for module's <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">Run()</a> method. <dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseModule::BaseStartup()</a> <a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">BaseModule::Run()</a> </dd></dl>

</div>
</div><p>
<hr><h2>Member Data Documentation</h2>
<a class="anchor" name="7006215baccd8082453dd6fe14500e5b"></a><!-- doxytag: member="BaseModule::$Config" ref="7006215baccd8082453dd6fe14500e5b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Config<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module configuration container<p>
<dl compact><dt><b>See also:</b></dt><dd>BaseModule::GetConfig() </dd></dl>

</div>
</div><p>
<a class="anchor" name="2b73679e0907a7220f6f5c21e7f8bfab"></a><!-- doxytag: member="BaseModule::$Html" ref="2b73679e0907a7220f6f5c21e7f8bfab" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Html<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
All HTML data generated by module 
</div>
</div><p>
<a class="anchor" name="f56f5fcd34b6eba1b15f513d52592a7d"></a><!-- doxytag: member="BaseModule::$Lang" ref="f56f5fcd34b6eba1b15f513d52592a7d" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$Lang<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module language container.<br>
 It's an associative array where keys are language bit names and values are translated string, e.g.: 'BRD_POSTS' =&gt; 'Board has d posts' 
</div>
</div><p>
<a class="anchor" name="200f3961846b0fee2935abcf6a166ac0"></a><!-- doxytag: member="BaseModule::$ModuleName" ref="200f3961846b0fee2935abcf6a166ac0" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">BaseModule::$ModuleName<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Module internal name in really_proper_format 
</div>
</div><p>
<hr>The documentation for this class was generated from the following file:<ul>
<li>/home/sphinx/Projects/PHP/YAMWS/<a class="el" href="base__module_8php.html">base_module.php</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  doc/html/classBaseModule-members.html                                                               0000644 0001750 0001750 00000007465 10472421406 017573  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Member List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>BaseModule Member List</h1>This is the complete list of members for <a class="el" href="classBaseModule.html">BaseModule</a>, including all inherited members.<p><table>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#7006215baccd8082453dd6fe14500e5b">$Config</a></td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#2b73679e0907a7220f6f5c21e7f8bfab">$Html</a></td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#f56f5fcd34b6eba1b15f513d52592a7d">$Lang</a></td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#200f3961846b0fee2935abcf6a166ac0">$ModuleName</a></td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#11334f6f33dc88122b0a94c887725052">BaseStartup</a>()</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#6faeccfec86169417e6318fc760ba577">GetHtml</a>()</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#035af0942841311dd3f3e7f663db8323">Lang</a>($bitName, $arguments=&quot;&quot;)</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#5359716a861284e5a6b1ff2296cf31aa">LoadLang</a>()</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#090f2e30c601c0b3072e97dd02f91260">Run</a>()</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#2d76af4157b2673d4740c554e08aef96">Skin</a>($bitName, $arguments, $useCaching=0, $returnData=0)</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classBaseModule.html#a02aa1a71b87ff5ea8130861df6b1bf8">Startup</a>()</td><td><a class="el" href="classBaseModule.html">BaseModule</a></td><td><code> [static]</code></td></tr>
</table><hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                           doc/html/classCore.html                                                                             0000644 0001750 0001750 00000146351 10472421406 015011  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Core Class Reference</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>Core Class Reference</h1><!-- doxytag: class="Core" -->Module and layout handling, consistency checking, caching methods, running system.  
<a href="#_details">More...</a>
<p>
<a href="classCore-members.html">List of all members.</a><table border="0" cellpadding="0" cellspacing="0">
<tr><td></td></tr>
<tr><td colspan="2"><br><h2>Static Public Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Run</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Main method in the whole system.  <a href="#d1b6c2ef037c930720235b0bfd51a13b"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">SetSkin</a> ($skin)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">SetLang</a> ($lang)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">GetSkinAndLang</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">RestoreSkinAndLang</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">IsCacheOk</a> ($source_file, $prefix=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">GetCacheName</a> ($source_file, $prefix=&quot;&quot;)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#9c2596c82e620380c71bed110db40ca7">DumpArrayToCache</a> ($contents, $file)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">DumpStringToCache</a> ($contents, $file)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#c8a8d74c512360172e80b0343028b605">GetConfig</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#b779a4778fcbfe097daab723acb4f092">GetTimer</a> ($number=1)</td></tr>

<tr><td colspan="2"><br><h2>Static Private Member Functions</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Startup</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Prepare <a class="el" href="classCore.html">Core</a> for work.  <a href="#3a99f8ccf5081616ebc92d9af3a9562a"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">LoadConfig</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Load <a class="el" href="classCore.html">Core</a> configuration.  <a href="#b1f22bca6ee5f4e8d6431e0a008cbdcf"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">LoadModuleList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">LoadLayout</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">LoadDOM</a> ($source, &amp;$r_variable)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">ParseModuleListDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">ParseLayoutListDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">ParseConfigDOM</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">MakeRunList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">AddModuleToRunlist</a> ($module)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">CheckModuleList</a> ()</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">CheckModuleDependencies</a> ($module, $tracePath=array())</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">ArrayToDefinition</a> ($array, $cache=&quot;&quot;, $inner=false)</td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">GetDebugInfo</a> ()</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Get miscellanous information.  <a href="#cb511097b2a57cd46dfde95b9600c68d"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Error</a> ($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)</td></tr>

<tr><td class="mdescLeft">&nbsp;</td><td class="mdescRight">Built-in error handler.  <a href="#6d24271a9d5c59bf494eaeff5175f15a"></a><br></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">StartTimer</a> ($number=1)</td></tr>

<tr><td colspan="2"><br><h2>Static Private Attributes</h2></td></tr>
<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">$Config</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">$ConfigDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#9669b1e25f58a6327346627e0718714b">$ModuleList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">$ModuleListDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">$Layout</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">$LayoutList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#89f8206b99ec81d78b8604b47768b757">$LayoutListDOM</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#135f0995570f8535b094481c563132f4">$RunList</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">$CurrentSkin</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#855055f9bbc19bc705ea05747653644a">$CurrentLang</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">$Timers</a></td></tr>

<tr><td class="memItemLeft" nowrap align="right" valign="top">static&nbsp;</td><td class="memItemRight" valign="bottom"><a class="el" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">$Errors</a></td></tr>

</table>
<hr><a name="_details"></a><h2>Detailed Description</h2>
Module and layout handling, consistency checking, caching methods, running system. 
<p>
<dl compact><dt><b>Author:</b></dt><dd>Sphinx </dd></dl>
<dl compact><dt><b>Date:</b></dt><dd>2006</dd></dl>
<dl compact><dt><b><a class="el" href="todo.html#_todo000001">Todo:</a></b></dt><dd>Examine class variables' scopes. Maybe some pseudo-__get&amp;__set methods should be added (e.g. for retrieval of config, modlist, runlist) <p>
Module description <p>
Advanced module handling functions:<ul>
<li>module uninstalling (+depchecks&amp;warnings, 2 modes: remove from list/remove from disk, 2-step-action)</li><li>module installing </li></ul>
<p>
Choose between SimpleXML and DOM.<ul>
<li>What extension to use for parsing xml lists?</li><li>Is DTD validation really necessary (~fast?, -sophisticated)? </li></ul>
<p>
i18n support in <a class="el" href="classCore.html">Core</a> error messages? </dd></dl>

<p>
<hr><h2>Member Function Documentation</h2>
<a class="anchor" name="4cf7e042593cd3c058bb4eb9bd35c33a"></a><!-- doxytag: member="Core::AddModuleToRunlist" ref="4cf7e042593cd3c058bb4eb9bd35c33a" args="($module)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::AddModuleToRunlist           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>module</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Add module to current runlist. Takes care of all dependency stuff.<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$module</em>&nbsp;</td><td>string Name of module to add</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="1735ab51c34cbedda6b963629de1839d"></a><!-- doxytag: member="Core::ArrayToDefinition" ref="1735ab51c34cbedda6b963629de1839d" args="($array, $cache=&quot;&quot;, $inner=false)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ArrayToDefinition           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>array</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>cache</em> = <code>&quot;&quot;</code>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>inner</em> = <code>false</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Convert an array to piece of valid PHP array definition, used for caching in <a class="el" href="classCore.html#9c2596c82e620380c71bed110db40ca7">Core::DumpArrayToCache()</a> method.<p>
<dl compact><dt><b><a class="el" href="todo.html#_todo000002">Todo:</a></b></dt><dd>It would be nice to make ArrayToDefinition method public in future</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$array</em>&nbsp;</td><td>array Array to convert </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$cache</em>&nbsp;</td><td>string Currently formed cache </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$inner</em>&nbsp;</td><td>boolean Whether this function was called from itself</td></tr>
  </table>
</dl>
<dl compact><dt><b>Warning:</b></dt><dd>DO NOT pass any argument other than $array</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>string Valid PHP array definition </dd></dl>

</div>
</div><p>
<a class="anchor" name="ce80a07ac8a6d86d867705a8061a9a9b"></a><!-- doxytag: member="Core::CheckModuleDependencies" ref="ce80a07ac8a6d86d867705a8061a9a9b" args="($module, $tracePath=array())" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::CheckModuleDependencies           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>module</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>tracePath</em> = <code>array&nbsp;()</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Traces a dependency tree for module, if any module is encountered in its own dep-tree or non-existent dependency encountered, generate an error.<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$module</em>&nbsp;</td><td>string Name of module to check </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$tracePath</em>&nbsp;</td><td>array Dep-tree for current module (used only for recurrent walk-through, do not use!)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Warning:</b></dt><dd>DO NOT pass any argument other than $module!</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>boolean TRUE if check succeeded </dd></dl>

</div>
</div><p>
<a class="anchor" name="4ee5f73d2b08c38a8887b9d51e245300"></a><!-- doxytag: member="Core::CheckModuleList" ref="4ee5f73d2b08c38a8887b9d51e245300" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::CheckModuleList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Check all modules in module list for self-depending or depending on non-existent modules<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="9c2596c82e620380c71bed110db40ca7"></a><!-- doxytag: member="Core::DumpArrayToCache" ref="9c2596c82e620380c71bed110db40ca7" args="($contents, $file)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::DumpArrayToCache           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>contents</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>file</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Dump some array into specified cache file. Currently used to rebuild module list and layout list caches.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">Core::ArrayToDefinition()</a> <a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">Core::GetCacheName()</a></dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$contents</em>&nbsp;</td><td>array Contents of array to be cached </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$file</em>&nbsp;</td><td>string Path to destination file (cache file)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Note:</b></dt><dd>It may be needed to use <a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">Core::GetCacheName()</a> method to make a proper path to destination file to pass it as $file argument for this method <p>
Cache file will RETURN proper array definition, not declare it, so to use cache contents later you'll need to do it in a such way: $some_variable=require("cache.file.php") <p>
I hope you catch the idea of previous note.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="016b101e5b3db10be21ebffed42e9b19"></a><!-- doxytag: member="Core::DumpStringToCache" ref="016b101e5b3db10be21ebffed42e9b19" args="($contents, $file)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::DumpStringToCache           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>contents</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>file</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Dump any string data into specified cache file<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$contents</em>&nbsp;</td><td>string Contents of string to be cached </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$file</em>&nbsp;</td><td>string Path to destination file (cache file)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="6d24271a9d5c59bf494eaeff5175f15a"></a><!-- doxytag: member="Core::Error" ref="6d24271a9d5c59bf494eaeff5175f15a" args="($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Error           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorCode</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorMessage</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorFile</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorLine</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>errorContext</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Built-in error handler. 
<p>
Generates error messages of different types, depending on $ErrorCode value.<br>
 On E_USER_ERROR generates generic fatal error message and halts the system. If $Config['MoreErrorInfo']is true, additional information is included in the output (<a class="el" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">Core::GetDebugInfo()</a> and error line/file ).<br>
 If E_USER_NOTICE or E_USER_WARNING occur, adds a new entry into <a class="el" href="classCore.html">Core</a>::$Errors container.<p>
<dl compact><dt><b>Warning:</b></dt><dd>Do not call this method directly, use<pre>trigger_error("OMG error!", E_USER_ERROR);</pre> instead!</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$errorCode</em>&nbsp;</td><td>E_USER_ERROR, E_USER_WARNING or E_USER_NOTICE </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$errorMessage</em>&nbsp;</td><td>string Text of error </td></tr>
  </table>
</dl>

</div>
</div><p>
<a class="anchor" name="7e860483a3033de70dc374e452900079"></a><!-- doxytag: member="Core::GetCacheName" ref="7e860483a3033de70dc374e452900079" args="($source_file, $prefix=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetCacheName           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source_file</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>prefix</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get name of cache for target file<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source_file</em>&nbsp;</td><td>string Target file name </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$prefix</em>&nbsp;</td><td>string Cache prefix. Useful to when caching several different files with the same names</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>string Path to cache file </dd></dl>

</div>
</div><p>
<a class="anchor" name="c8a8d74c512360172e80b0343028b605"></a><!-- doxytag: member="Core::GetConfig" ref="c8a8d74c512360172e80b0343028b605" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetConfig           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get current <a class="el" href="classCore.html">Core</a> config<p>
<dl compact><dt><b>Returns:</b></dt><dd>array <a class="el" href="classCore.html">Core</a>::$Config </dd></dl>

</div>
</div><p>
<a class="anchor" name="cb511097b2a57cd46dfde95b9600c68d"></a><!-- doxytag: member="Core::GetDebugInfo" ref="cb511097b2a57cd46dfde95b9600c68d" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetDebugInfo           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get miscellanous information. 
<p>
Method provides us various information on user request data, session variables, <a class="el" href="classCore.html">Core</a>::$Config and timers' values.<p>
<dl compact><dt><b>Returns:</b></dt><dd>array Array containing information </dd></dl>

</div>
</div><p>
<a class="anchor" name="85d74c60de9057926fa4abacaa71f3e9"></a><!-- doxytag: member="Core::GetSkinAndLang" ref="85d74c60de9057926fa4abacaa71f3e9" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetSkinAndLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get current skin and language internal names<p>
<dl compact><dt><b>Returns:</b></dt><dd>array Array with two elements, first one with name of current skin, second with language name </dd></dl>

</div>
</div><p>
<a class="anchor" name="b779a4778fcbfe097daab723acb4f092"></a><!-- doxytag: member="Core::GetTimer" ref="b779a4778fcbfe097daab723acb4f092" args="($number=1)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::GetTimer           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>number</em> = <code>1</code>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Get specified timer value<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$number</em>&nbsp;</td><td>integer Number of timer which is about to give us its current value lol</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>integer Current timer value </dd></dl>

</div>
</div><p>
<a class="anchor" name="992b63771f336cc051fcf9e59f6b143f"></a><!-- doxytag: member="Core::IsCacheOk" ref="992b63771f336cc051fcf9e59f6b143f" args="($source_file, $prefix=&quot;&quot;)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::IsCacheOk           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source_file</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>prefix</em> = <code>&quot;&quot;</code></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Checks whether cache for specified target file is up to date<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source_file</em>&nbsp;</td><td>string Name of 'source' file </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$prefix</em>&nbsp;</td><td>string Source-specific cache prefix</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>boolean Return true if cache is up to date and false if it's too old or does not exist at all </dd></dl>

</div>
</div><p>
<a class="anchor" name="b1f22bca6ee5f4e8d6431e0a008cbdcf"></a><!-- doxytag: member="Core::LoadConfig" ref="b1f22bca6ee5f4e8d6431e0a008cbdcf" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadConfig           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load <a class="el" href="classCore.html">Core</a> configuration. 
<p>
Loads core configuration either from cache file or from XML config. Parsed XML configuration is cached to improve perfomance later.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="58fd0063e86c9cbb6470b3c560fcd37b"></a><!-- doxytag: member="Core::LoadDOM" ref="58fd0063e86c9cbb6470b3c560fcd37b" args="($source, &amp;$r_variable)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadDOM           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>source</em>, </td>
        </tr>
        <tr>
          <td class="paramkey"></td>
          <td></td>
          <td class="paramtype">&amp;$&nbsp;</td>
          <td class="paramname"> <em>r_variable</em></td><td>&nbsp;</td>
        </tr>
        <tr>
          <td></td>
          <td>)</td>
          <td></td><td></td><td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load some XML, validate and turn it into a DOM tree.<p>
<dl compact><dt><b>Note:</b></dt><dd>This method checks whether target XML file is accessible and raises error on failure.</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$source</em>&nbsp;</td><td>string Path to XML file to load </td></tr>
    <tr><td valign="top"></td><td valign="top"><em>$r_variable</em>&nbsp;</td><td>mixed Link to variable which will get DOM after loading</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="7c84d7f528dfa0178955627a1f83a715"></a><!-- doxytag: member="Core::LoadLayout" ref="7c84d7f528dfa0178955627a1f83a715" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadLayout           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Load selected layout or default one.<br>
 Name of layout is selected from 'a' parameter in script query string.<br>
 The result is usable <a class="el" href="classCore.html">Core</a>::$Layout, <a class="el" href="classCore.html">Core</a>::$LayoutList and cached layout list if caching is enabled.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> <a class="el" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">Core::ParseLayoutListDOM()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="da9dbb1f356160b8174d1d815e75120f"></a><!-- doxytag: member="Core::LoadModuleList" ref="da9dbb1f356160b8174d1d815e75120f" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::LoadModuleList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
If everything's fine, we get nice'n'chill <a class="el" href="classCore.html">Core</a>::$ModuleList array after all, in addition to cached modlist if caching's on.<br>
 Also perform a dependency check if <a class="el" href="classCore.html">Core</a>::$Config['DepCheck'] is TRUE.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> <a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">Core::ParseModuleListDOM()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="119b3cb7f093eefba2e1096baeaadab8"></a><!-- doxytag: member="Core::MakeRunList" ref="119b3cb7f093eefba2e1096baeaadab8" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::MakeRunList           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepares a "runlist" in <a class="el" href="classCore.html">Core</a>::$RunList, list of modules to run according to current layout.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="5ab0c08fca09009556bad907ea604ec1"></a><!-- doxytag: member="Core::ParseConfigDOM" ref="5ab0c08fca09009556bad907ea604ec1" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseConfigDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parses core configuration DOM tree, previously loaded with <a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">Core::LoadDOM()</a> Core::Config() gets a configuration on success<p>
<dl compact><dt><b>Note:</b></dt><dd>All these ParseFooDOM methods are getting smaller and smaller as we move through the class ^_^</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="92fe821bc14994668b0d2e58d2fb71ef"></a><!-- doxytag: member="Core::ParseLayoutListDOM" ref="92fe821bc14994668b0d2e58d2fb71ef" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseLayoutListDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parse layout list XML file into <a class="el" href="classCore.html">Core</a>::$LayoutList.<p>
<dl compact><dt><b>Note:</b></dt><dd>Surely I know that it was copypasted (y&amp;p'ed, to be exactly) from <a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">Core::ParseModuleListDOM()</a> method. So what? <p>
You may ask, "Why is DOM extension used here instead of fluffy SimpleXML?". Answer: SimpleXML is n00b shit. We need tr00 DOM functions to op modules in future.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="9b4be0a58f539006b8c05287d4cda753"></a><!-- doxytag: member="Core::ParseModuleListDOM" ref="9b4be0a58f539006b8c05287d4cda753" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::ParseModuleListDOM           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parse module list XML, <a class="el" href="classCore.html">Core</a>::$ModuleList gets a parsed module list on success.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="8b789def87026eb966ddf3ad2babfae3"></a><!-- doxytag: member="Core::RestoreSkinAndLang" ref="8b789def87026eb966ddf3ad2babfae3" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::RestoreSkinAndLang           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set <a class="el" href="classCore.html">Core</a>::$CurrentSkin and <a class="el" href="classCore.html">Core</a>::$CurrentLang values according to user's preferences which are stored in cookies/session data<p>
<dl compact><dt><b>Note:</b></dt><dd>This is low-level core method which is called at each system run.<br>
 Skin and language values set by this method may be overriden by system modules' behaviour, e.g. skin may be selected from user preferences which are kept in database and managed by system module, not system core. <p>
Previous note is really hard to understand.</dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="d1b6c2ef037c930720235b0bfd51a13b"></a><!-- doxytag: member="Core::Run" ref="d1b6c2ef037c930720235b0bfd51a13b" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Run           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Main method in the whole system. 
<p>
This is a wrapper method actually making system work.<br>
 First we prepare core, then load module list and layout we need, make list of modules to run and sequentually run them one-by-one. Sounds easy, eh?<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Core::Startup()</a> <a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">Core::LoadModuleList()</a> <a class="el" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">Core::LoadLayout()</a> <a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">Core::MakeRunList()</a></dd></dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="ab69b5da88ad2901f68b2e010e0ec308"></a><!-- doxytag: member="Core::SetLang" ref="ab69b5da88ad2901f68b2e010e0ec308" args="($lang)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::SetLang           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>lang</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set and store language for current user<p>
<dl compact><dt><b>Note:</b></dt><dd>Haha, it's y&amp;p'ed from <a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">Core::SetSkin()</a> method, lol rofol!!!!!!!</dd></dl>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$lang</em>&nbsp;</td><td>string Internal name of language to choose (name in langs directory)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="b013cd82ca954a4c4f31d30ad3a9901a"></a><!-- doxytag: member="Core::SetSkin" ref="b013cd82ca954a4c4f31d30ad3a9901a" args="($skin)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::SetSkin           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>skin</em>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Set and store skin for current user<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$skin</em>&nbsp;</td><td>string Internal name of skin to choose (name in skins directory)</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="f7fea025d37b95401bcad814d34fb4f5"></a><!-- doxytag: member="Core::StartTimer" ref="f7fea025d37b95401bcad814d34fb4f5" args="($number=1)" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::StartTimer           </td>
          <td>(</td>
          <td class="paramtype">$&nbsp;</td>
          <td class="paramname"> <em>number</em> = <code>1</code>          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Start ticking specified timer<p>
<dl compact><dt><b>Parameters:</b></dt><dd>
  <table border="0" cellspacing="2" cellpadding="0">
    <tr><td valign="top"></td><td valign="top"><em>$number</em>&nbsp;</td><td>integer Number of timer to start ticking</td></tr>
  </table>
</dl>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<a class="anchor" name="3a99f8ccf5081616ebc92d9af3a9562a"></a><!-- doxytag: member="Core::Startup" ref="3a99f8ccf5081616ebc92d9af3a9562a" args="()" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">static Core::Startup           </td>
          <td>(</td>
          <td class="paramname">          </td>
          <td>&nbsp;)&nbsp;</td>
          <td width="100%"><code> [static, private]</code></td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Prepare <a class="el" href="classCore.html">Core</a> for work. 
<p>
Set error handling directives, call some misc methodz.<p>
<dl compact><dt><b>Returns:</b></dt><dd>void </dd></dl>

</div>
</div><p>
<hr><h2>Member Data Documentation</h2>
<a class="anchor" name="0a4233d6c7fb2f45a854df1e395c9326"></a><!-- doxytag: member="Core::$Config" ref="0a4233d6c7fb2f45a854df1e395c9326" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Config<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Core configuration container.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">Core::LoadConfig()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="8f4a69e9060afbb9eea926f029e41055"></a><!-- doxytag: member="Core::$ConfigDOM" ref="8f4a69e9060afbb9eea926f029e41055" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ConfigDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with core configuration 
</div>
</div><p>
<a class="anchor" name="855055f9bbc19bc705ea05747653644a"></a><!-- doxytag: member="Core::$CurrentLang" ref="855055f9bbc19bc705ea05747653644a" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$CurrentLang<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Internal name of currently selected language for user Language internal name is a name of directory in default system language folder<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">Core::SetLang()</a> <a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">Core::RestoreSkinAndLang()</a> <a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="26ebc052e21bea592bfba86e793dc48b"></a><!-- doxytag: member="Core::$CurrentSkin" ref="26ebc052e21bea592bfba86e793dc48b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$CurrentSkin<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Internal name of currently selected skin for user. Skin internal name is a name of directory in default system skin folder.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">Core::SetSkin()</a> <a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">Core::RestoreSkinAndLang()</a> <a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">Core::GetSkinAndLang()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="1cbc06a311ea7ac7fcc4dc60fbede26d"></a><!-- doxytag: member="Core::$Errors" ref="1cbc06a311ea7ac7fcc4dc60fbede26d" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Errors<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Errors container, used to keep non-fatal error entries generated via <a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Core::Error()</a>. 
</div>
</div><p>
<a class="anchor" name="de337a75ba1c07b69c1a17b63313f18f"></a><!-- doxytag: member="Core::$Layout" ref="de337a75ba1c07b69c1a17b63313f18f" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Layout<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Current layout container.<p>
<dl compact><dt><b>Note:</b></dt><dd>This is NOT the name of current layout, but the whole corresponding ENTRY from <a class="el" href="classCore.html">Core</a>::$LayoutList! </dd></dl>

</div>
</div><p>
<a class="anchor" name="c23a132a6ef90503a264e5f2b16c81d5"></a><!-- doxytag: member="Core::$LayoutList" ref="c23a132a6ef90503a264e5f2b16c81d5" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$LayoutList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parsed layout list.<p>
<dl compact><dt><b>See also:</b></dt><dd>Core::LoadLayoutList() </dd></dl>

</div>
</div><p>
<a class="anchor" name="89f8206b99ec81d78b8604b47768b757"></a><!-- doxytag: member="Core::$LayoutListDOM" ref="89f8206b99ec81d78b8604b47768b757" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$LayoutListDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with layout list. 
</div>
</div><p>
<a class="anchor" name="9669b1e25f58a6327346627e0718714b"></a><!-- doxytag: member="Core::$ModuleList" ref="9669b1e25f58a6327346627e0718714b" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ModuleList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Parsed module list.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">Core::LoadModuleList()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="19f8bd4f1c50b416fa32043a7a53b715"></a><!-- doxytag: member="Core::$ModuleListDOM" ref="19f8bd4f1c50b416fa32043a7a53b715" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$ModuleListDOM<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
DOM tree from XML with module list. 
</div>
</div><p>
<a class="anchor" name="135f0995570f8535b094481c563132f4"></a><!-- doxytag: member="Core::$RunList" ref="135f0995570f8535b094481c563132f4" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$RunList<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
List of modules to run in current layout<p>
<dl compact><dt><b>Note:</b></dt><dd>This list contains list of module NAMES from <a class="el" href="classCore.html">Core</a>::$ModuleList</dd></dl>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">Core::MakeRunList()</a> </dd></dl>

</div>
</div><p>
<a class="anchor" name="6d83c303b0d63c3c5157b062e1a12d5a"></a><!-- doxytag: member="Core::$Timers" ref="6d83c303b0d63c3c5157b062e1a12d5a" args="" -->
<div class="memitem">
<div class="memproto">
      <table class="memname">
        <tr>
          <td class="memname">Core::$Timers<code> [static, private]</code>          </td>
        </tr>
      </table>
</div>
<div class="memdoc">

<p>
Timers container, used for perfomance measuring.<p>
<dl compact><dt><b>See also:</b></dt><dd><a class="el" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">Core::StartTimer()</a> <a class="el" href="classCore.html#b779a4778fcbfe097daab723acb4f092">Core::GetTimer()</a> </dd></dl>

</div>
</div><p>
<hr>The documentation for this class was generated from the following file:<ul>
<li>/home/sphinx/Projects/PHP/YAMWS/<a class="el" href="core_8php.html">core.php</a></ul>
<hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                       doc/html/classCore-members.html                                                                     0000644 0001750 0001750 00000023326 10472421406 016435  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
<title>YAMWS: Yet Another Modular Web System: Member List</title>
<link href="doxygen.css" rel="stylesheet" type="text/css">
<link href="tabs.css" rel="stylesheet" type="text/css">
</head><body>
<!-- Generated by Doxygen 1.4.7 -->
<div class="tabs">
  <ul>
    <li><a href="main.html"><span>Main&nbsp;Page</span></a></li>
    <li id="current"><a href="annotated.html"><span>Classes</span></a></li>
    <li><a href="files.html"><span>Files</span></a></li>
    <li><a href="pages.html"><span>Related&nbsp;Pages</span></a></li>
  </ul></div>
<div class="tabs">
  <ul>
    <li><a href="annotated.html"><span>Class&nbsp;List</span></a></li>
    <li><a href="functions.html"><span>Class&nbsp;Members</span></a></li>
  </ul></div>
<h1>Core Member List</h1>This is the complete list of members for <a class="el" href="classCore.html">Core</a>, including all inherited members.<p><table>
  <tr class="memlist"><td><a class="el" href="classCore.html#0a4233d6c7fb2f45a854df1e395c9326">$Config</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#8f4a69e9060afbb9eea926f029e41055">$ConfigDOM</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#855055f9bbc19bc705ea05747653644a">$CurrentLang</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#26ebc052e21bea592bfba86e793dc48b">$CurrentSkin</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#1cbc06a311ea7ac7fcc4dc60fbede26d">$Errors</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#de337a75ba1c07b69c1a17b63313f18f">$Layout</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#c23a132a6ef90503a264e5f2b16c81d5">$LayoutList</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#89f8206b99ec81d78b8604b47768b757">$LayoutListDOM</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#9669b1e25f58a6327346627e0718714b">$ModuleList</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#19f8bd4f1c50b416fa32043a7a53b715">$ModuleListDOM</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#135f0995570f8535b094481c563132f4">$RunList</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#6d83c303b0d63c3c5157b062e1a12d5a">$Timers</a></td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#4cf7e042593cd3c058bb4eb9bd35c33a">AddModuleToRunlist</a>($module)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#1735ab51c34cbedda6b963629de1839d">ArrayToDefinition</a>($array, $cache=&quot;&quot;, $inner=false)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#ce80a07ac8a6d86d867705a8061a9a9b">CheckModuleDependencies</a>($module, $tracePath=array())</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#4ee5f73d2b08c38a8887b9d51e245300">CheckModuleList</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#9c2596c82e620380c71bed110db40ca7">DumpArrayToCache</a>($contents, $file)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#016b101e5b3db10be21ebffed42e9b19">DumpStringToCache</a>($contents, $file)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#6d24271a9d5c59bf494eaeff5175f15a">Error</a>($errorCode, $errorMessage, $errorFile, $errorLine, $errorContext)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#7e860483a3033de70dc374e452900079">GetCacheName</a>($source_file, $prefix=&quot;&quot;)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#c8a8d74c512360172e80b0343028b605">GetConfig</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#cb511097b2a57cd46dfde95b9600c68d">GetDebugInfo</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#85d74c60de9057926fa4abacaa71f3e9">GetSkinAndLang</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#b779a4778fcbfe097daab723acb4f092">GetTimer</a>($number=1)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#992b63771f336cc051fcf9e59f6b143f">IsCacheOk</a>($source_file, $prefix=&quot;&quot;)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#b1f22bca6ee5f4e8d6431e0a008cbdcf">LoadConfig</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#58fd0063e86c9cbb6470b3c560fcd37b">LoadDOM</a>($source, &amp;$r_variable)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#7c84d7f528dfa0178955627a1f83a715">LoadLayout</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#da9dbb1f356160b8174d1d815e75120f">LoadModuleList</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#119b3cb7f093eefba2e1096baeaadab8">MakeRunList</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#5ab0c08fca09009556bad907ea604ec1">ParseConfigDOM</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#92fe821bc14994668b0d2e58d2fb71ef">ParseLayoutListDOM</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#9b4be0a58f539006b8c05287d4cda753">ParseModuleListDOM</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#8b789def87026eb966ddf3ad2babfae3">RestoreSkinAndLang</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#d1b6c2ef037c930720235b0bfd51a13b">Run</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#ab69b5da88ad2901f68b2e010e0ec308">SetLang</a>($lang)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#b013cd82ca954a4c4f31d30ad3a9901a">SetSkin</a>($skin)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#f7fea025d37b95401bcad814d34fb4f5">StartTimer</a>($number=1)</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
  <tr class="memlist"><td><a class="el" href="classCore.html#3a99f8ccf5081616ebc92d9af3a9562a">Startup</a>()</td><td><a class="el" href="classCore.html">Core</a></td><td><code> [private, static]</code></td></tr>
</table><hr size="1"><address style="align: right;"><small>Generated on Tue Aug 22 01:17:58 2006 for YAMWS: Yet Another Modular Web System by&nbsp;
<a href="http://www.doxygen.org/index.html">
<img src="doxygen.png" alt="doxygen" align="middle" border="0"></a> 1.4.7 </small></address>
</body>
</html>
                                                                                                                                                                                                                                                                                                          doc/html/tree.html                                                                                  0000644 0001750 0001750 00000011300 10472421406 014013  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/xhtml;charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Language" content="en" />
    <link rel="stylesheet" href="doxygen.css">
    <title>TreeView</title>
    <script type="text/javascript">
    <!-- // Hide script from old browsers
    
    function toggleFolder(id, imageNode) 
    {
      var folder = document.getElementById(id);
      var l = imageNode.src.length;
      if (imageNode.src.substring(l-20,l)=="ftv2folderclosed.png" || 
          imageNode.src.substring(l-18,l)=="ftv2folderopen.png")
      {
        imageNode = imageNode.previousSibling;
        l = imageNode.src.length;
      }
      if (folder == null) 
      {
      } 
      else if (folder.style.display == "block") 
      {
        if (imageNode != null) 
        {
          imageNode.nextSibling.src = "ftv2folderclosed.png";
          if (imageNode.src.substring(l-13,l) == "ftv2mnode.png")
          {
            imageNode.src = "ftv2pnode.png";
          }
          else if (imageNode.src.substring(l-17,l) == "ftv2mlastnode.png")
          {
            imageNode.src = "ftv2plastnode.png";
          }
        }
        folder.style.display = "none";
      } 
      else 
      {
        if (imageNode != null) 
        {
          imageNode.nextSibling.src = "ftv2folderopen.png";
          if (imageNode.src.substring(l-13,l) == "ftv2pnode.png")
          {
            imageNode.src = "ftv2mnode.png";
          }
          else if (imageNode.src.substring(l-17,l) == "ftv2plastnode.png")
          {
            imageNode.src = "ftv2mlastnode.png";
          }
        }
        folder.style.display = "block";
      }
    }

    // End script hiding -->        
    </script>
  </head>

  <body class="ftvtree">
    <div class="directory">
      <h3>YAMWS: Yet Another Modular Web System</h3>
      <div style="display: block;">
        <p><img src="ftv2pnode.png" alt="o" width=16 height=22 onclick="toggleFolder('folder1', this)"/><img src="ftv2folderclosed.png" alt="+" width=24 height=22 onclick="toggleFolder('folder1', this)"/><a class="el" href="annotated.html" target="basefrm">Class List</a></p>
        <div id="folder1">
          <p><img src="ftv2vertline.png" alt="|" width=16 height=22 /><img src="ftv2node.png" alt="o" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="classBaseModule.html" target="basefrm">BaseModule</a></p>
          <p><img src="ftv2vertline.png" alt="|" width=16 height=22 /><img src="ftv2lastnode.png" alt="\" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="classCore.html" target="basefrm">Core</a></p>
        </div>
        <p><img src="ftv2node.png" alt="o" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="functions.html" target="basefrm">Class Members</a></p>
        <p><img src="ftv2pnode.png" alt="o" width=16 height=22 onclick="toggleFolder('folder2', this)"/><img src="ftv2folderclosed.png" alt="+" width=24 height=22 onclick="toggleFolder('folder2', this)"/><a class="el" href="files.html" target="basefrm">File List</a></p>
        <div id="folder2">
          <p><img src="ftv2vertline.png" alt="|" width=16 height=22 /><img src="ftv2node.png" alt="o" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="base__module_8php.html" target="basefrm">/home/sphinx/Projects/PHP/YAMWS/base_module.php</a></p>
          <p><img src="ftv2vertline.png" alt="|" width=16 height=22 /><img src="ftv2node.png" alt="o" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="core_8php.html" target="basefrm">/home/sphinx/Projects/PHP/YAMWS/core.php</a></p>
          <p><img src="ftv2vertline.png" alt="|" width=16 height=22 /><img src="ftv2lastnode.png" alt="\" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="index_8php.html" target="basefrm">/home/sphinx/Projects/PHP/YAMWS/index.php</a></p>
        </div>
        <p><img src="ftv2plastnode.png" alt="\" width=16 height=22 onclick="toggleFolder('folder3', this)"/><img src="ftv2folderclosed.png" alt="+" width=24 height=22 onclick="toggleFolder('folder3', this)"/><a class="el" href="pages.html" target="basefrm">Related Pages</a></p>
        <div id="folder3">
          <p><img src="ftv2blank.png" alt="&nbsp;" width=16 height=22 /><img src="ftv2lastnode.png" alt="\" width=16 height=22 /><img src="ftv2doc.png" alt="*" width=24 height=22 /><a class="el" href="todo.html" target="basefrm">Todo List</a></p>
        </div>
      </div>
    </div>
  </body>
</html>
                                                                                                                                                                                                                                                                                                                                doc/html/ftv2blank.png                                                                              0000644 0001750 0001750 00000000256 10472421406 014575  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR         ��G   tRNS ���   tEXtSoftware gif2png 2.4.2�^G   %tEXtComment Ulead GIF SmartSaver Ver 2.0!��^S   IDATx�c8���0�B�  x<2�r�|    IEND�B`�                                                                                                                                                                                                                                                                                                                                                  doc/html/ftv2doc.png                                                                                0000644 0001750 0001750 00000000377 10472421406 014257  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        _Tq-   PLTE����� ���������     �����   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   vIDATx�c````c``d '0bq�$8`q �'3001�2
pT2Si`q'ʀ\\�����RKRSiȔ�RR�i
6त�h��Q�kqMaNU�`'E$�pgc 0�o# ���U�G�    IEND�B`�                                                                                                                                                                                                                                                                 doc/html/ftv2folderclosed.png                                                                       0000644 0001750 0001750 00000000403 10472421406 016145  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        _Tq-   PLTE������������   � �B�@�   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   }IDATxڍ��
�0C#�w�B�+�em�����3��Z������ �@S���QB%�zv���yyIs��2�_I��#` �6f�@K�����x ��m�n�Pd�p�P
�x����]�%������`��    IEND�B`�                                                                                                                                                                                                                                                             doc/html/ftv2folderopen.png                                                                         0000644 0001750 0001750 00000000405 10472421406 015637  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        _Tq-   PLTE����� ���������   � ��2��   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   |IDATxڅ�A�0E_�-S��׍������: !�o�����
j�<*a��Ci����p��ʻ����&F�L����}���FTFN*�Ε=��d���sq�u��� ����C����)��G�D    IEND�B`�                                                                                                                                                                                                                                                           doc/html/ftv2lastnode.png                                                                           0000644 0001750 0001750 00000000351 10472421406 015313  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        L�1�   0PLTE���                                          ���Ӡ�   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   &tEXtComment Ulead GIF SmartSaver Ver 2.0io?�   IDATx�c`0�O�3 $� ��3=    IEND�B`�                                                                                                                                                                                                                                                                                       doc/html/ftv2link.png                                                                               0000644 0001750 0001750 00000000546 10472421406 014445  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        _Tq-   0PLTE��� � ��   �  � ��������                        ~�   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   &tEXtComment Ulead GIF SmartSaver Ver 2.0�	�J   �IDATx�}��
�0Eo3�c~�SW�~�n�Z��:d5�d�/�y�!P���� � ���f}��Qp@ϭt��b� ��a;j�;�h��Q�$��Kh��^~���m��((8��LF-Y�8\����+q�y�gy�Ti��/��q�+�x��    IEND�B`�                                                                                                                                                          doc/html/ftv2mlastnode.png                                                                          0000644 0001750 0001750 00000000240 10472421406 015465  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        ���y   	PLTE������   <^�,   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   #IDATx�c`  
`���� �ɨ�P��a m���i�    IEND�B`�                                                                                                                                                                                                                                                                                                                                                                doc/html/ftv2mnode.png                                                                              0000644 0001750 0001750 00000000302 10472421406 014600  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        L�1�   $PLTE���                              ���S��   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   *IDATx�c`�.�Bt�RT�n ���b�LJJ����"05��� �y'���    IEND�B`�                                                                                                                                                                                                                                                                                                                              doc/html/ftv2node.png                                                                               0000644 0001750 0001750 00000000353 10472421406 014431  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        L�1�   0PLTE���                                          ���Ӡ�   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   &tEXtComment Ulead GIF SmartSaver Ver 2.0io?�   IDATx�c`0�O�V��  .R ��E:�    IEND�B`�                                                                                                                                                                                                                                                                                     doc/html/ftv2plastnode.png                                                                          0000644 0001750 0001750 00000000245 10472421406 015475  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        ���y   	PLTE������   <^�,   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   (IDATx�c` 0���ach(�`2�
BY 1,  n����u�    IEND�B`�                                                                                                                                                                                                                                                                                                                                                           doc/html/ftv2pnode.png                                                                              0000644 0001750 0001750 00000000310 10472421406 014602  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        L�1�   $PLTE���                              ���S��   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   0IDATx�c`�.�Bn&8��T����>D��II���Y�S;: �k/��o    IEND�B`�                                                                                                                                                                                                                                                                                                                        doc/html/ftv2vertline.png                                                                           0000644 0001750 0001750 00000000345 10472421406 015335  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 �PNG

   IHDR        L�1�   0PLTE���                                          ���Ӡ�   tRNS @��f   tEXtSoftware gif2png 2.4.2�^G   &tEXtComment Ulead GIF SmartSaver Ver 2.0io?�   IDATx�c`0�O[ ! ��<�:    IEND�B`�                                                                                                                                                                                                                                                                                           dtd/                                                                                                0000755 0001750 0001750 00000000000 10472265143 011241  5                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 dtd/modlist.dtd                                                                                     0000644 0001750 0001750 00000001323 10471155140 013402  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!ELEMENT modlist (module+)>
<!ELEMENT module (setting*)>
<!ELEMENT setting EMPTY>
<!ATTLIST module 
name            ID                      #REQUIRED
file            NMTOKEN                 #REQUIRED
disabled        (1|0)                   #IMPLIED
<!-- core modules run always, std - on demand (w/output) -->
type            (core|std)      #REQUIRED
deps            IDREFS                  #IMPLIED
desc            CDATA                   #IMPLIED
version         CDATA                   #REQUIRED
>
<!ATTLIST setting 
name            NMTOKEN                 #REQUIRED
value           NMTOKEN                 #REQUIRED
desc            NMTOKEN                 #IMPLIED
type            (integer|string|bool)   #REQUIRED
>
                                                                                                                                                                                                                                                                                                             dtd/core.cfg.dtd                                                                                    0000644 0001750 0001750 00000000372 10470120304 013411  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!ELEMENT config (setting+)>
<!ATTLIST setting 
name            NMTOKEN                 #REQUIRED
value           NMTOKEN                 #REQUIRED
desc            NMTOKEN                 #IMPLIED 
type            (integer|string|bool)   #REQUIRED
>
                                                                                                                                                                                                                                                                      dtd/layouts.dtd                                                                                     0000644 0001750 0001750 00000000431 10467035601 013432  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!ELEMENT layouts (layout+)>
<!ELEMENT layout EMPTY>
<!ATTLIST layout
name            ID              #REQUIRED
title           CDATA           #REQUIRED
desc            CDATA           #IMPLIED
template        NMTOKEN         #REQUIRED
modules         NMTOKENS        #REQUIRED
>
                                                                                                                                                                                                                                       dtd/language.dtd                                                                                    0000644 0001750 0001750 00000000223 10471143431 013510  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <!ELEMENT language (bit+)>
<!ELEMENT bit CDATA>
<!ATTLIST bit
name            ID              #REQUIRED
comment         CDATA           #IMPLIED
>
                                                                                                                                                                                                                                                                                                                                                                             index.php                                                                                           0000644 0001750 0001750 00000000111 10472421646 012301  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?php

require("core.php");
require("base_module.php");
Core::Run();

?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                       layouts.xml                                                                                         0000644 0001750 0001750 00000000336 10472267775 012727  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?xml version="1.0" encoding="utf-8" standalone="no"?>
<!DOCTYPE layout SYSTEM "dtd/layout.dtd">
<layouts>
        <layout name="blog" title="Blog" desc="Blog page layout" modules="blog" template="blog.xhtml"/>
</layouts>
                                                                                                                                                                                                                                                                                                  modlist.xml                                                                                         0000644 0001750 0001750 00000001140 10471155154 012656  0                                                                                                    ustar   sphinx                          sphinx                                                                                                                                                                                                                 <?xml version="1.0" encoding="utf-8" standalone="no"?>
<!DOCTYPE modlist SYSTEM "./dtd/modlist.dtd">
<modlist>
        <module name="db" type="std" file="db_loader.php" version="0.0.1">
                <setting name="Driver" desc="Internal db driver name" type="string" value="mysql" />
                <setting name="DriversFolder" type="string" desc="Path to folder with db drivers" value="shared/db_drivers" />
        </module>
        <module name="error_logger" file="error_logger.php" type="core" version="0.0.1" />
        <module name="blog" type="std" file="blog.php" version="0.0.1" />
</modlist>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
<?php

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
 * @todo Delete this comment.
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

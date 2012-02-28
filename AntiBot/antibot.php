<?php

/**
 * This class provides facilities for generating
 * human verification images containing mathematical expressions.
 *
 * <b>How to use it</b>\n
 * First you need to call AntiBot::GenerateExpression() with some parameters (check method's documentation for details).
 * Then you're able to use AntiBot::GetExpression() and AntiBot::GetResult().
 * To generate an image with expression (which may be used for anti-bot spam protection), you must first set up image parameters
 * with AntiBot::SetImgConfig() and then feel free to call AntiBot::GetImage(). *Please note*, that GetImage() return a GD image resource,
 * which may be processed by common GD function afterwards or printed. There's also handy function AntiBot::SetExpression().
 */
class AntiBot
{
        /**
         * Holds a string with a generated expression
         *
         * @note Use AntiBot::GetExpression() to get this string and AntiBot::GetResult() to get result of expression
         */
        static private $Expression;

        /**
         * Holds a string with human-friendly representation of an expression
         *
         * @see AntiBot::GetHumanizedExpression()
         */
        static private $HumanizedExpression;

        /**
         * @see AntiBot::SetDictionary()
         */
        static private $Dictionary;

        /**
         * ImgConfig container
         *
         * @see AntiBot::SetImgConfig()
         */
        static private $ImgConfig;

        /**
         * Set parameters for further image drawing
         *
         * @note Only $fontFile, $width and $height parameter required, other are optional
         *
         * @param $fontFile     string  Full path to .ttf font file
         * @param $width        integer Width of generated picture
         * @param $height       integer Height of generated picture
         * @param $fontColor    string  Hexadecimal color value (like #a2cdef) for text
         * @param $backColor    string  Hexadecimal color value for image background
         * @param $prefix       string  Text to be added before the expression text
         * @param $suffix       string  Text to be added after the expression text (e.g " = ?")
         *
         * @return              void
         */
        function SetImgConfig ($fontFile, $width, $height, $fontColor="#ffffff", $backColor="#000000", $prefix="", $suffix=" = ?")
        {
                /// Various checks follow
                if ( is_readable($fontFile) )
                {
                        self::$ImgConfig['FontFile'] = $fontFile;
                }
                else
                {
                        trigger_error("&&E_NO_FONT_FILE", E_USER_ERROR);
                }

                if ( is_numeric($width) && is_numeric($height) )
                {
                        self::$ImgConfig['Width'] = $width;
                        self::$ImgConfig['Height'] = $height;
                }
                else
                {
                        trigger_error("&&E_BAD_DIMENSIONS", E_USER_ERROR);
                }

                /// Colors should be passed as #abcdef hexadecimal color value
                if ( preg_match("/#[a-fA-F0-9]{6}/", $fontColor) )
                {
                        self::$ImgConfig['FontColor'] = $fontColor;
                }
                else
                {
                        trigger_error("&&E_BAD_FONT_COLOR", E_USER_ERROR);
                }

                if ( preg_match("/#[a-fA-F0-9]{6}/", $backColor) )
                {
                        self::$ImgConfig['BackColor'] = $backColor;
                }
                else
                {
                        trigger_error("&&E_BAD_BACK_COLOR", E_USER_ERROR);
                }

                self::$ImgConfig['Prefix'] = $prefix;
                self::$ImgConfig['Suffix'] = $suffix;
        }

        function SetDictionary ($dictionary)
        {
                self::$Dictionary = $dictionary;
        }

        /**
         * Set AntiBot::$Expression value directly.\n
         * This may be handy when you need to save an expression and generate image for it later.
         *
         * @param $expression   string  String containing valid arithmetic expression (e.g. '5+3/6*2-321'). No trailing equal signs!
         *
         * @note Expressions set via this method don't get humanized on the fly, consider manual step-by-step humanization using AntiBot::HumanizeEntity()
         *
         * @todo        Validate expression to match regexp before assignment
         *
         * @return              void
         */
        function SetExpression ($expression)
        {
                self::$Expression = $expression;
        }

        /**
         * This function must be called first in order to allow retrieval of image, expression and expression result 
         *
         * @param $tokens       array   List of pairs of integers for minimum and maximum value on each step respectively. This is an array of arrays, each containing two integer values for minimum and maximum value for each expression "step". The amount of "steps" in expression is the same as the amount of such pairs in $tokens array. PLEASE NOTE, that a lower&upper bound for both minimum and maximum values are 1 (one) and 99 (ninety-nine) respectively.
         * @param $ops          array   Array with operation-patterns for each step. Operation pattern is a string containing list of possible arithmetic operations on step without spaces, e.g. "+-" means that only adding and subtracting are allowed on this step. Possible operations are "+", "-", "/", "*".
         *
         * @note Amount of elements in $ops argument array should be the same as the amount of pairs in $tokens minus one in. So, if $tokens contains 5 pairs, $ops should have at least 4 elements.
         *
         * @return              void
         */
        function GenerateExpression ($tokens, $ops)
        {
                $step = 0;

                if ( (count($tokens)) > count($ops) )
                {
                        trigger_error("&&E_NOT_ENOUGH_OPERATIONS", E_USER_ERROR);
                }

                foreach ( $tokens as $token )
                {
                        /// Get a number for this step
                        foreach ( array(0,1) as $n )
                        {
                                if ( $token[$n] > 99 )
                                {
                                        $token[$n] = 99;
                                }
                                if ( $token[$n] < 1 )
                                {
                                        $token[$n] = 1; 
                                }
                        }
                        $number = rand($token[0], $token[1]);

                        /// Add this number to AntiBot::$HumanizedExpression
                        self::$HumanizedExpression .= sprintf("%d", self::HumanizeEntity($number));

                        /// Isn't it the last step?
                        /// We don't need some signs at the end of expression
                        if ( array_key_exists($step+1, $ops) )
                        {
                                $step_ops = str_split($ops[$step]);

                                /// Get on operation for this step
                                $op = $step_ops[array_rand($step_ops)];

                                /// Add op to humanized
                                self::$HumanizedExpression .= sprintf(" %s ", self::HumanizeEntity($op));
                        }
                        else
                        {
                                $op = NULL;
                        }

                        /// Add a number and another sign (if needed) to the expression
                        self::$Expression .= ($number.$op);

                        $step++;
                }
        }


        /**
         * Return human-friendly representation of number. Humanization bounds depend on dictionary set via AntiBot::SetHumanDictionary()
         *
         * @note Although this method is declared as 'public' one, do not explicitly use it to humanize the whole generated expression
         * 
         * @param $entity       string  String entity to be humanized (numbers (from zero to 999) and sign operators (+, -, / and *) with default dictionary)
         *
         * @return              string "Humanized" number
         */
        function HumanizeEntity ($entity)
        {
                $number = strval($number);

                $length = strlen($number);

                for ( $i=0; $i<$length; $i++ )
                {
                        $slice = substr($number, $i, $length);

                        if ( array_key_exists($slice, $dictionary['d']) )
                        {
                                $string .= $dictionary['d'][$slice]." ";
                                return $string;
                        }
                        else
                        {
                                $string .= $dictionary[$length-$i][$number[$i]]." ";
                        }
                }

                return $string;
        }

        /**
         * Return the whole humanized representation of expression including all numbers and operator signs!
         *
         * @return              string
         */
        function GetHumanizedExpression ()
        {
                return self::HumanizedExpression();

        }

        /**
         * Get the RESULT of expression evaluating
         *
         * @return              integer Result
         */
        function GetResult ()
        {
                eval('$return = ('.self::$Expression.');');

                return $return;
        }

        /**
         * Get the expression itself (not evaluated)
         *
         * @return              string Expression
         */
        function GetExpression ()
        {
                return self::$Expression;
        }

        /**
         * Get image with expression (not evaluated) according to image settings set with SetImgConfig() method
         *
         * @return              GD image resource 
         */
        function GetImage ($text='expression')
        {
                switch ( $text )
                {
                        case 'expression': $fill = self::GetExpression(); break;
                        case 'humanized':
                                $fill = self::GetHumanizedExpression();
                                break;
                        case
                $text = " ".self::$ImgConfig['Prefix'].self::GetExpression().self::$ImgConfig['Suffix']." ";

                /// Create an image
                $image = imagecreatetruecolor(self::$ImgConfig['Width'], self::$ImgConfig['Height']);

                /// Let's choose a proper font size for image
                /// Start off with this obvious value equal to image height
                $font_size = self::$ImgConfig['Height'];
                do {
                        $box = imagettfbbox($font_size, 0, self::$ImgConfig['FontFile'], $text); 
                        $box_width = $box[2] - $box[0];
                        $box_height = $box[5] - $box[3];
                        $font_size--;
                /// Special condition follows, checking whether text box fits well into canvas
                } while ( $box_width >= ( self::$ImgConfig['Width'] ) || $box_height >= ( self::$ImgConfig['Height'] ) );

                /// Convert hexadecimal colors to use in drawing functions
                $dec_font_color = self::ConvertHexColor(self::$ImgConfig['FontColor']);
                $img_font_color = imagecolorallocate($image, $dec_font_color[0], $dec_font_color[1], $dec_font_color[2]); 

                $dec_back_color = self::ConvertHexColor(self::$ImgConfig['BackColor']);
                $img_back_color = imagecolorallocate($image, $dec_back_color[0], $dec_back_color[1], $dec_back_color[2]);

                
                /// Fill an image
                imagefill($image, 0, 0, $img_back_color);

                /// Make our fancy text appear!
                /// Text is ALMOST vertically and horizontally center-aligned
                /// The shit is that coordinates in imagettftext() are specified for basepoint of first character in label,
                /// not its lower-left bottom =(
                if ( !@imagettftext($image, $font_size, 0, (self::$ImgConfig['Width'] - $box_width)/2 + 1, (self::$ImgConfig['Height'] - $box_height)/2 - 1, $img_font_color, self::$ImgConfig['FontFile'], $text) )
                {
                       trigger_error("&&E_FAILED_TO_DRAW", E_USER_ERROR);
                }

                return $image;
        }

        /**
         * Convert a hexadecimal color string into an array containing values for red, green and blue.
         *
         * @return              array Array with 3 elements, each containing integer value from 0 to 255 for red, green and blue respectively
         */
        private function ConvertHexColor ($string)
        {
                $r = hexdec($string[1].$string[2]);
                $g = hexdec($string[3].$string[4]);
                $b = hexdec($string[5].$string[6]);

                return array ($r, $g, $b);
        }
}

AntiBot::SetDictionary(include(human_dict.php));
AntiBot::GenerateExpression(array(array(0,9),array(20,25),array(30,32),array(0,5)), array("+*-","*/","-+","*"));
AntiBot::SetImgConfig("/usr/share/fonts/ttf-bitstream-vera/Vera.ttf",200,40,"#aadd1f","#000000");
imagejpeg(AntiBot::GetImage(),'',100);



?>

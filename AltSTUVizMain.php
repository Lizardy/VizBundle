<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AltSTU\VizBundle;

use TCPDF;
use AltSTU\VizBundle\AltSTUVizColorHelper;

/**
 * Description of AltSTUVizMain
 *
 * @author Liz
 */
class AltSTUVizMain 
{
    
    private $data;
    private $color_helper;
    private $im;
    private $w, $h;
    private $font, $tx, $ty, $pdff;
    private $white, $black;
    private $axes_ending;

    public function __construct( $data, $w, $h, $tx=null, $ty=null, $font, $color, $pdff=null)
    {
        $this->data = $data;
        $this->color_helper = new AltSTUVizColorHelper($color);         
        $this->im = imagecreatetruecolor($w,$h);
        $this->w = $w;
        $this->h = $h;
        $this->font = $font;
        $this->tx = $tx; $this->ty = $ty;
        $this->pdff = $pdff;
        
        $this->white = 0x00ffffff;
        $this->black = 0x00000000;
        $this->axes_ending = 10;
    }

    public function drawAxesLabels($width, $height, $column_width, $padding_h,$padding_v, $maxv)
    {
        //axes
        imageline($this->im,$padding_h,$padding_v-$this->axes_ending,$padding_h,$this->h-$padding_v,$this->black);
        imageline($this->im,$padding_h,$this->h-$padding_v,$this->w-$padding_h+$this->axes_ending,$this->h-$padding_v,$this->black);

        $values = $this->data;
        
        if ($this->w >= 400)
        {
            $font_size = 10;
            $padding_l = 5;
        }
        else if ($this->w >=300)
        {
            $font_size = 9;
            $padding_l = 4;
        }
        else if ($this->w <= 200)
        {
            $font_size = 8;
            $padding_l = 2;
        }
        $flag_tok = false;      //need to separate the tokens among few strings
        $flag_rotate = false;   //need to rotate labels
        if($this->w < 100)
            $flag_nolabels = true; //do not draw labels at all
        else 
            $flag_nolabels = false;
        //labels
        //x-axis calculations
        $longest_tok;
        $text_frame = imagettfbbox($font_size,0,$this->font,'test'); //just for the initial value, it is changed later
        $text_height = abs($text_frame[1] - $text_frame[7]);
        $text_width = abs($text_frame[0] - $text_frame[2]);
        foreach ($values as $key => $value)
        {
            //find the widest label on y-axis
            $text_frame = imagettfbbox($font_size,0,$this->font,$key);
            if (abs($text_frame[0] - $text_frame[2]) > $text_width)
            {
                $text_width = abs($text_frame[0] - $text_frame[2]);
                if ($text_width > $column_width)
                {
                    $flag_tok = true; //need to separate the tokens among few strings
                    break;
                }
            }
        }
        if ($flag_tok)
        {
            foreach ($values as $key => $value)
            {
                $tok = strtok($key, " -");
                $longest_tok = $tok;
                while ($tok !== false) 
                {
                    $frame = imagettfbbox($font_size,0,$this->font,$tok);
                    if (abs($frame[0] - $frame[2]) > $column_width)
                    {
                        //$font_size--;
                        if (strlen($tok)>strlen($longest_tok))
                            $longest_tok = $tok;
                        $flag_rotate = true;
                        //break;
                    }
                    $tok = strtok(" -");
                }

            }
        }

        if ($flag_rotate)
        {
            $frame_tmp = imagettfbbox($font_size,0,$this->font,$longest_tok);
            $tok_width = abs($frame_tmp[0] - $frame_tmp[2]);
            //if ($text_width < $padding_v) //$tok_width > $padding_v &&
            while ($tok_width > $padding_v) // минимально разрешенный размер шрифта
            {//проходит ли самая длинная перевернутая подпись по высоте, которая её ширина в прямом виде
                if ($font_size-- == 5) 
                {
                    $flag_nolabels = true;
                    break;
                }
                //пересчитать ширину текста с меньшим размером шрфита
                $frame_tmp = imagettfbbox($font_size,0,$this->font,$longest_tok);
                $tok_width = abs($frame_tmp[0] - $frame_tmp[2]);
            }
           
            $x_leftlow = $padding_h + $text_height + $padding_l;
            $y_leftlow = $this->h - $padding_l; 
            
        }
        else
        {
            $x_leftlow = $padding_h + $padding_l;
            $y_leftlow = $padding_v + $height + $padding_l + $text_height;            
        }
        if (!$flag_nolabels)
        {
            foreach ($values as $key => $value)
            {
                $label = "";
                if ($flag_tok)
                {
                    $tok = strtok($key, " -");
                    while ($tok !== false) 
                    {
                        $label .= $tok . "\n";
                        $tok = strtok(" -");
                    }
                     if ($flag_rotate)
                     {
                         imagettftext($this->im,$font_size,90,$x_leftlow,$y_leftlow,$this->black,$this->font,$label);
                         //$x_leftlow = $x_leftlow
                     }
                     else
                     {
                        imagettftext($this->im,$font_size,0,$x_leftlow,$y_leftlow,$this->black,$this->font,$label);
                        //$x_leftlow = $x_leftlow + $column_width;
                     }
                }
                //else if ($flag_rotate)
                //{
                //    imagettftext($im,9,90,$x_leftlow,$y_leftlow,$black,$font,$key);
                //}
                else
                {
                    imagettftext($this->im,$font_size,0,$x_leftlow,$y_leftlow,$this->black,$this->font,$key);
                    //$x_leftlow = $x_leftlow + $column_width;
                }
                $x_leftlow = $x_leftlow + $column_width;
            }
        }
        
        /////////////////////
        //y-axis calculations
        if (!$flag_nolabels)
        {
            /*$text_frame = imagettfbbox($font_size,0,$this->font,'test');
            $text_height = abs($text_frame[1] - $text_frame[7]);
            $text_width = abs($text_frame[0] - $text_frame[2]);
            foreach ($values as $key => $value)
            {
                //find the widest label on y-axis
                $text_frame = imagettfbbox($font_size,0,$this->font,$value);
                if (abs($text_frame[0] - $text_frame[2]) > $text_width)
                    $text_width = abs($text_frame[0] - $text_frame[2]);
            }*/

            //ordinate, y-axis
            
            $num_labels_v = round ($height / 100);
            if ($num_labels_v < 3) $num_labels_v = 3;
            else if ($num_labels_v > 10) $num_labels_v = 10;

            $maxy = $maxv;
            $multiplier = 1; 
            while (abs($maxy) > 9)
            {//определим первую значимую цифру
                $maxy = floor($maxy / 10); 
                $multiplier *= 10;
            }
            if (fmod($maxv,10)!=0)
                $maxy = ($maxy+1) * $multiplier;
            else
                $maxy = $maxy * $multiplier;

            $text_frame = imagettfbbox($font_size,0,$this->font,$maxy);
            $text_height = abs($text_frame[1] - $text_frame[7]);
            $text_width = abs($text_frame[0] - $text_frame[2]);
            $x_leftlow = $this->w - $width - $padding_h - $text_width - $padding_l;
            $y_leftlow = $padding_v + $height;
            
            $delta_val = round($maxy / $num_labels_v);
            $delta_px = round(($height - $text_height*$num_labels_v) / $num_labels_v);

            for ($val=0; $val <= $maxy; $val+=$delta_val)
            {
                imagettftext($this->im,$font_size,0,$x_leftlow,$y_leftlow,$this->black,$this->font,$val);
                $y_leftlow = $y_leftlow - $text_height - $delta_px;
            }
        }
    }
    
    public function generatePictureBar() 
    {
    // associative collection: key -> value
       $values = $this->data;

    // Get the total number of columns we are going to plot
       $columns  = count($values);

    // Get the height and width of the diagram itself
        $width = round($this->w*0.7);
        $height = round($this->h*0.7);

        $padding = 0;
        $padding_h = round(($this->w - $width) / 2);
        $padding_v = round(($this->h - $height) / 2); 

    // Get the width of 1 column
        $column_width = round ($width / $columns) ;


    // Fill in the background of the image
        imagefilledrectangle($this->im,0,0,$this->w,$this->h,$this->white);

        $maxv = 0;

    // Calculate the maximum value we are going to plot

        foreach($values as $key => $value) 
        {
                $maxv = max($value,$maxv); 
        }
        
        $this->drawAxesLabels($width, $height, $column_width, $padding_h,$padding_v, $maxv);

        //diagram itself
        $i = 0;
        foreach ($values as $key => $value)
        {
            $column_height = ($height / 100) * (( $value / $maxv) *100);

            $x1 = $i*$column_width + $padding_h;
            $y1 = $height-$column_height + $padding_v;
            $x2 = (($i+1)*$column_width)-$padding + $padding_h;
            $y2 = $height + $padding_v;

            imagefilledrectangle($this->im,$x1,$y1,$x2,$y2,$this->color_helper->img_colorallocate($this->im, $this->color_helper->nextColor()));

            $i++;
        }

        return $this->im;
    }
    
    public function generatePictureLine()
    {
        // associative collection: key -> value, value may be collection as well
           $values = $this->data;

        // Get the total number of columns we are going to plot
           $columns  = count($values);

        // Get the height and width of the diagram itself
            $width = round($this->w*0.75);
            $height = round($this->h*0.75);

            //$padding = 0;
            //$padding_l = 5;
            $padding_h = round(($this->w - $width) / 2);
            $padding_v = round(($this->h - $height) / 2); 

        // Get the width of 1 column
            $column_width = round ($width / $columns) ;
            
            
        // Fill in the background of the image
            imagefilledrectangle($this->im,0,0,$this->w,$this->h,$this->white);

            $maxv = 0;

        // Calculate the maximum value we are going to plot

            foreach($values as $key => $value) 
            {
                    $maxv = max($value,$maxv); 
            }
            
            $this->drawAxesLabels($width, $height, $column_width, $padding_h,$padding_v, $maxv);
            
            //diagram itself
            $i=0;
            //for ($i = 0;$i<count($values);)
            foreach ($values as $key => $value)
            {
                //if ($i==count($values))
                //    break;
                $column_height1 = ($height / 100) * (( $value / $maxv) *100);
                $x1 = $i*$column_width + $padding_h + $column_width/2;
                $y1 = $height-$column_height1 + $padding_v;
                $i++;
                if ($i<2)
                {
                $column_height2 = ($height / 100) * (( $value / $maxv) *100);
                $x2 = (($i)*$column_width) + $padding_h + $column_width/2;
                $y2 = $height-$column_height2 + $padding_v;
                }
                if ($i>1)
                    imageline($this->im,$x1,$y1,$x2,$y2,$this->color_helper->img_colorallocate($this->im, $this->color_helper->nextColor()));
                $x2 = $x1; 
		$y2 = $y1;
            }
            
            return $this->im;
    }
    
    public function generatePicturePie()
    {
        imagefilledrectangle($this->im, 0, 0, $this->w, $this->h, $this->white); 
        
           if ($this->w >= 400)
           {
               $font_size = 10;
               $padding_l = 5;
           }
           else if ($this->w >=300)
           {
               $font_size = 9;
               $padding_l = 4;
           }
           else if ($this->w <= 200)
           {
               $font_size = 8;
               $padding_l = 2;
           }        
        
        $piewidth = $this->w * 0.70;/* pie area */ 
        $height = $piewidth;
        $x = ($piewidth/2); 
        $y = ($this->h/2); 
        //$total = array_sum($data); 
        $total = 0;
        foreach ($this->data as $key => $value) 
        {
            $total+=$value;
        }
        $angle_start = 270; 
        $ylegend = 2;                 
        //$i=0;
        foreach($this->data as $label => $value) 
        { 
            $angle_done    = ($value/$total) * 360; /** angle calculated for 360 degrees */ 
            $perc          = round(($value/$total) * 100, 1); /** percentage calculated */ 
            //$color = $colors[$i++]; //imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255)); 
            $color = $this->color_helper->img_colorallocate($this->im, $this->color_helper->nextColor());
            imagefilledarc($this->im, $x, $y, $piewidth, $height, $angle_start, $angle_done+= $angle_start, $color, IMG_ARC_PIE); 
            $xtext = $x + (cos(deg2rad(($angle_start+$angle_done)/2))*($piewidth/4)) - 10; 
            $ytext = $y + (sin(deg2rad(($angle_start+$angle_done)/2))*($height /4)); 
            imagettftext($this->im, $font_size, 0, $xtext, $ytext, $this->black, $this->font, "$perc %"); 
            imagefilledrectangle($this->im, $piewidth+2, $ylegend, $piewidth+20, $ylegend+=20, $color); 
            imagettftext($this->im, $font_size, 0, $piewidth+22, $ylegend-5, $this->black, $this->font, $label); 
            $ylegend += 4; 
            $angle_start = $angle_done; 
        }
        
        return $this->im;
    }
}

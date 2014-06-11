<?php

namespace AltSTU\VizBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TCPDF;

class DefaultController extends Controller
{
//    public function chartAction($name)
//    {
//        return $this->render('AltSTUVizBundle:Default:index.html.twig', array('name' => $name));
//    }
    
    public function chartAction()
    {
        return $this->render('AltSTUVizBundle:Default:index.html.twig');
    }
    
    public function piechartAction()
    {
        return $this->render('AltSTUVizBundle:Default:pie.html.twig');
    }
    
    public function pdfAction()
    {
        // Include the main TCPDF library (search for installation path).
        //require_once('/tcpdf/tcpdf.php'); 
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();
        
        //main actions
        $pdf->setRasterizeVectorImages(true);
        //$pdf->ImageSVG($file='@', $x=30, $y=100, $w='', $h=100, $link='', $align='', $palign='', $border=0, $fitonpage=false);
        // create some HTML content
        
        //$mysum = $this->get('alt_stu.viz_service')->sum(111,111);
        
        $html = '<svg width="100" height="100">
                <circle cx="50" cy="50" r="40" stroke="green" stroke-width="4" fill="yellow" />
                </svg>';
        $pdf->writeHTML($html, true, false, true, false, '');
        
        //$html = '<html>'.$mysum.'</html>';
        //$pdf->writeHTML($html, true, false, true, false, '');
        
        $html = '<h2>HTML TABLE:</h2>
        <table border="1" cellspacing="3" cellpadding="4">
            <tr>
                <th>#</th>
                <th align="right">RIGHT align</th>
                <th align="left">LEFT align</th>
                <th>4A</th>
            </tr>
            <tr>
                <td>1</td>
                <td bgcolor="#cccccc" align="center" colspan="2">A1 ex<i>amp</i>le <a href="http://www.tcpdf.org">link</a> column span. One two tree four five six seven eight nine ten.<br />line after br<br /><small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal  bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla<ol><li>first<ol><li>sublist</li><li>sublist</li></ol></li><li>second</li></ol><small color="#FF0000" bgcolor="#FFFF00">small small small small small small small small small small small small small small small small small small small small</small></td>
                <td>4B</td>
            </tr>
            <tr>
                <td>1A</td>
                <td rowspan="2" colspan="2" bgcolor="#FFFFCC">2AA<br />2AB<br />2AC</td>
                <td bgcolor="#FF0000">4D</td>
            </tr>
            <tr>
                <td>1B</td>
                <td>4E</td>
            </tr>
            <tr>
                <td>1C</td>
                <td>2C</td>
                <td>3C</td>
                <td>4F</td>
            </tr>
        </table>';
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        //$pdf->ImageSVG('@iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABlBMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDrEX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==', $x=100, $y=100);
                
        
        //Close and output PDF document
        $pdf->Output('example.pdf', 'I');
        
        return $pdf;
    }
    
    public function imgAction()
    {
        // This array of values is just here for the example.

            $values = array("23","32","35","57","12",
                            "3","36","54","32","15",
                            "43","24","30");

        // Get the total number of columns we are going to plot

            $columns  = count($values);

        // Get the height and width of the final image

            $width = 300;
            $height = 200;

        // Set the amount of space between each column

            $padding = 5;

        // Get the width of 1 column

            $column_width = $width / $columns ;

        // Generate the image variables

            $im        = imagecreate($width,$height);
            $gray      = imagecolorallocate ($im,0xcc,0xcc,0xcc);
            $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
            $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
            $white     = imagecolorallocate ($im,0xff,0xff,0xff);

        // Fill in the background of the image

            imagefilledrectangle($im,0,0,$width,$height,$white);

            $maxv = 0;

        // Calculate the maximum value we are going to plot

            for($i=0;$i<$columns;$i++)$maxv = max($values[$i],$maxv);

        // Now plot each column

            for($i=0;$i<$columns;$i++)
            {
                $column_height = ($height / 100) * (( $values[$i] / $maxv) *100);

                $x1 = $i*$column_width;
                $y1 = $height-$column_height;
                $x2 = (($i+1)*$column_width)-$padding;
                $y2 = $height;

                imagefilledrectangle($im,$x1,$y1,$x2,$y2,$gray);

        // This part is just for 3D effect

                imageline($im,$x1,$y1,$x1,$y2,$gray_lite);
                imageline($im,$x1,$y2,$x2,$y2,$gray_lite);
                imageline($im,$x2,$y1,$x2,$y2,$gray_dark);

            }

        // Send the PNG header information. Replace for JPEG or GIF or whatever

            //header ("Content-type: image/png");
            //imagepng($im);
            $headers = array('Content-Type' => 'image/png',
                             'Content-Disposition' => 'inline', 
                             'filename="image.png"');
            ob_start();
            imagepng($im);
            $str = ob_get_clean();
            return new Response($str, 200, $headers);
            
    }
}


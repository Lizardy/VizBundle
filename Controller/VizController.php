<?php

namespace AltSTU\VizBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use TCPDF;
use AltSTU\VizBundle\AltSTUVizMain;

class VizController extends Controller
{
    private $templating;

    public function __construct(EngineInterface $templating)
    {
        //dependency injection for the standard templating service
        $this->templating = $templating;
    }
    
    public function picAction($type, $data, $w, $h, $tx=null, $ty=null, $font, $color, $pdff=null)
    {
        //color categories just like in d3js
        //$colors = array(0x1f77b4, 0xff7f0e, 0x2ca02c, 0xd62728, 0x9467bd, 0x8c564b, 0xe377c2, 0x7f7f7f, 0xbcbd22, 0x17becf);
        
        $main_generator = new AltSTUVizMain($data, $w, $h, $tx=null, $ty=null, $font, $color, $pdff=null);
        
        //define what type of diagram we are gonna plot
        switch ($type) {
            case 'bar':
                
                $im = $main_generator->generatePictureBar();
            
                break;
            
            case 'line':
                
                $im = $main_generator->generatePictureLine();
                
                break;
            
            case 'pie':
                
                $im = $main_generator->generatePicturePie();               
                
                break;
            default:
                break;
        }
                

        // Send the PNG header information. Replace for JPEG or GIF or whatever

            $headers = array('Content-Type' => 'image/jpeg',
                             'Content-Disposition' => 'inline', 
                             'filename="chart.jpeg"'
                            );
            ob_start();//working with output buffer
            imagejpeg($im,NULL,100);
            $str = ob_get_clean();//$image is in $str now
            imagedestroy($im);
            
            return new Response($str, 200, $headers);
    }

    public function pdfAction($type, $data, $w, $h, $tx=null, $ty=null, $font, $color, $pdff)
    {
        //get the picture with a diagram from the picAction
        $imgdata = VizController::picAction($type, $data, $w, $h, $tx, $ty, $font,$color)->getContent();
        
        //TODO: determine, what format is needed to pass to the tcpdf
		
	// create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'pt', PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
        
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set font
        $pdf->SetFont('arial', '', 10);

        // add a page
        $pdf->AddPage();
        
        if ($pdff == 'format_d' || $pdff == 'format_dt')
        {
            $pdf->setJPEGQuality(100);
            //putting the image with a diagram to the pdf
            //@ means that it's not a file, but a datastream
            $pdf->Image('@'.$imgdata);

            $pdf->setPageUnit('pt');
            $pdf->SetXY(PDF_MARGIN_LEFT, $h);
            //$pdf->setPageUnit('mm');
            //$pdf->AddPage();
            // reset pointer to the last page
            //$pdf->lastPage();
        }
        
        if ($pdff == 'format_t' || $pdff == 'format_dt')
        {
            $html = $this->templating->render('AltSTUVizBundle:Viz:pdf.html.twig',
                array('data' => $data, 'tx' => $tx, 'ty' => $ty)
                );

            //$pdf->Ln();
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
        }
        
        //Close and output PDF document
        $pdf->Output('vizualization.pdf', 'I');
        
        return $pdf;
    }
    
    public function d3Action($type, $data, $w, $h, $tx=null, $ty=null, $font, $color, $pdff=null)
    {
        //define what type of diagram we are gonna plot
        switch ($type) 
        {
            case 'bar':
            {
                $twigname = 'AltSTUVizBundle:Viz:d3-bar.html.twig';
                
                //change the format of the data
                $data_new = array();
                $tmp_keys = array();
                $tmp_values = array();
                foreach ($data as $key => $value) 
                {
                    $tmp_keys[] = $key;
                    $tmp_values[] = $value;
                }
                $data_new['labels'] = $tmp_keys;
                $data_new['values'] = $tmp_values;
                
                break;
            }
            case 'line':
                $twigname = 'AltSTUVizBundle:Viz:d3-line.html.twig';
                
                //change the format of the data
                $data_new = array();
                foreach ($data as $key => $value) 
                {
                    $tmp = array('labels' => $key, 'values' => $value);
                    $data_new[] = $tmp;
                }
                
                break;
            case 'pie':
            {
                $twigname = 'AltSTUVizBundle:Viz:d3-pie.html.twig';
                
                //change the format of the data
                $data_new = array();
                foreach ($data as $key => $value) 
                {
                    $tmp = array($tx => $key, $ty => $value);
                    //$data_new[] = json_encode($tmp);
                    $data_new[] = $tmp;
                }
                
                break;
            }
        }

        
        //$json = json_encode($data_new);
        //$str = serialize($data_new);
        //var data = JSON.stringfy({{data}});
        //utf8_encode()
        //var data = [{"variants":"roses","votes":11},{"variants":"lilies","votes":28},{"variants":"tulips","votes":19},{"variants":"dandelions","votes":63},{"variants":"gladioluses","votes":4}];
    
        return $this->templating->renderResponse($twigname,
                //array('data'=>json_encode($data), 'w'=>$w,'h'=>$h)
                array(
                    'data'=>$data_new, 
                    'w'=>$w,'h'=>$h, 'tx'=>$tx, 'ty'=>$ty, 'color'=>$color)
                );
    }
}


<?php
/**
 * FPDF - Minimal version for QR Label generation
 * Based on FPDF 1.85 by Olivier PLATHEY
 * License: MIT/Freeware
 */

if (!class_exists('FPDF')) {

class FPDF
{
	protected $page;
	protected $n;
	protected $offsets;
	protected $buffer;
	protected $pages;
	protected $state;
	protected $compress;
	protected $k;
	protected $DefOrientation;
	protected $CurOrientation;
	protected $StdPageSizes;
	protected $DefPageSize;
	protected $CurPageSize;
	protected $CurRotation;
	protected $PageInfo;
	protected $wPt, $hPt;
	protected $w, $h;
	protected $lMargin;
	protected $tMargin;
	protected $rMargin;
	protected $bMargin;
	protected $cMargin;
	protected $x, $y;
	protected $lasth;
	protected $LineWidth;
	protected $fontpath;
	protected $FontSizePt;
	protected $FontSize;
	protected $DrawColor;
	protected $FillColor;
	protected $TextColor;
	protected $ColorFlag;
	protected $WithAlpha;
	protected $ws;
	protected $images;
	protected $PageLinks;
	protected $links;
	protected $InHeader;
	protected $InFooter;
	protected $AliasNbPages;
	protected $ZoomMode;
	protected $LayoutMode;
	protected $metadata;
	protected $PDFVersion;

	function __construct($orientation='P', $unit='mm', $size='A4')
	{
		$this->page = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->pages = array();
		$this->PageInfo = array();
		$this->state = 0;
		$this->compress = false;
		$this->k = $this->_getpagesize($size) ? 1 : ($unit=='pt' ? 1 : ($unit=='mm' ? 72/25.4 : ($unit=='cm' ? 72/2.54 : 72)));
		$this->DefOrientation = $orientation;
		$this->CurOrientation = $orientation;
		$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
			'letter'=>array(612,792), 'legal'=>array(612,1008));
		$this->DefPageSize = $this->_getpagesize($size);
		$this->CurPageSize = $this->DefPageSize;
		$this->CurRotation = 0;
		$this->offsets = array();
		$this->wPt = $this->DefPageSize[0];
		$this->hPt = $this->DefPageSize[1];
		$this->w = $this->DefPageSize[0]/$this->k;
		$this->h = $this->DefPageSize[1]/$this->k;
		$this->lMargin = 0;
		$this->tMargin = 0;
		$this->rMargin = 0;
		$this->bMargin = 0;
		$this->cMargin = 0;
		$this->x = 0;
		$this->y = 0;
		$this->lasth = 0;
		$this->LineWidth = .567/$this->k;
		$this->fontpath = '';
		$this->DrawColor = '0 G';
		$this->FillColor = '0 g';
		$this->TextColor = '0 g';
		$this->ColorFlag = false;
		$this->WithAlpha = false;
		$this->ws = 0;
		$this->images = array();
		$this->PageLinks = array();
		$this->links = array();
		$this->InHeader = false;
		$this->InFooter = false;
		$this->ZoomMode = 'default';
		$this->LayoutMode = 'default';
		$this->metadata = array();
		$this->PDFVersion = '1.3';
	}

	function SetMargins($left, $top, $right=-1)
	{
		$this->lMargin = $left;
		$this->tMargin = $top;
		if($right == -1)
			$right = $left;
		$this->rMargin = $right;
	}

	function SetAutoPageBreak($auto, $margin=0)
	{
		$this->AutoPageBreak = $auto;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	function AddPage($orientation='', $size='', $rotation=0)
	{
		if($this->state==3)
			$this->Error('The document is closed');
		$family = '';
		$style = '';
		$fontsize = 0;
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		if($this->page>0)
		{
			$this->InFooter = true;
			$this->InFooter = false;
			$this->_endpage();
		}
		$this->_beginpage($orientation,$size,$rotation);
		$this->_out('2 J');
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
		$this->DrawColor = $dc;
		if($dc!='0 G')
			$this->_out($dc);
		$this->FillColor = $fc;
		if($fc!='0 g')
			$this->_out($fc);
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		$this->InHeader = true;
		$this->InHeader = false;
		if($this->LineWidth!=$lw)
		{
			$this->LineWidth = $lw;
			$this->_out(sprintf('%.2F w',$lw*$this->k));
		}
	}

	function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
	{
		if($this->state!=2)
			$this->Error('No page has been added');
		if(!isset($this->images[$file]))
		{
			if($type=='')
			{
				$pos = strrpos($file,'.');
				if(!$pos)
					$this->Error('Image file has no extension and no type was specified: '.$file);
				$type = substr($file,$pos+1);
			}
			$type = strtolower($type);
			if($type=='jpeg')
				$type = 'jpg';
			$mtd = '_parse'.$type;
			if(!method_exists($this,$mtd))
				$this->Error('Unsupported image type: '.$type);
			$info = $this->$mtd($file);
			$info['i'] = count($this->images)+1;
			$this->images[$file] = $info;
		}
		else
			$info = $this->images[$file];

		if($w==0 && $h==0)
		{
			$w = -96;
			$h = -96;
		}
		if($w<0)
			$w = -$info['w']*72/$w/$this->k;
		if($h<0)
			$h = -$info['h']*72/$h/$this->k;
		if($w==0)
			$w = $h*$info['w']/$info['h'];
		if($h==0)
			$h = $w*$info['h']/$info['w'];

		if($y===null)
			$y = $this->y;

		$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
		if($link)
			$this->Link($x,$y,$w,$h,$link);

		return array('w'=>$w,'h'=>$h);
	}

	function Output($dest='', $name='', $isUTF8=false)
	{
		if($this->state<3)
			$this->Close();
		if(is_bool($dest))
			$dest = $dest ? 'D' : 'F';
		$dest = strtoupper($dest);
		if($dest=='')
		{
			if($name=='')
			{
				$name = 'doc.pdf';
				$dest = 'I';
			}
			else
				$dest = 'F';
		}
		switch($dest)
		{
			case 'I':
				$this->_checkoutput();
				if(PHP_SAPI!='cli')
				{
					if(headers_sent($hfile,$hline))
						$this->Error("Some data has already been output, can't send PDF file (output started at $hfile:$hline)");
				}
				if(ob_get_length())
				{
					if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
					{
						ob_end_clean();
					}
					else
						$this->Error("Some data has already been output, can't send PDF file");
				}
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				echo $this->buffer;
				break;
			case 'D':
				$this->_checkoutput();
				if(headers_sent($hfile,$hline))
					$this->Error("Some data has already been output, can't send PDF file (output started at $hfile:$hline)");
				if(ob_get_length())
				{
					if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
					{
						ob_end_clean();
					}
					else
						$this->Error("Some data has already been output, can't send PDF file");
				}
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				echo $this->buffer;
				break;
			case 'F':
				if(!file_put_contents($name,$this->buffer))
					$this->Error('Unable to create output file: '.$name);
				break;
			case 'S':
				return $this->buffer;
			default:
				$this->Error('Incorrect output destination: '.$dest);
		}
		return '';
	}

	protected function _getpagesize($size)
	{
		if(is_string($size))
		{
			$size = strtolower($size);
			if(!isset($this->StdPageSizes[$size]))
				$this->Error('Unknown page size: '.$size);
			$a = $this->StdPageSizes[$size];
			return array($a[0],$a[1]);
		}
		else
		{
			if($size[0]>$size[1])
				return array($size[1],$size[0]);
			else
				return $size;
		}
	}

	protected function _beginpage($orientation, $size, $rotation)
	{
		$this->page++;
		$this->pages[$this->page] = '';
		$this->state = 2;
		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
		$this->lasth = 0;
		if(!$orientation)
			$orientation = $this->DefOrientation;
		else
			$orientation = strtoupper($orientation[0]);
		if(!$size)
			$size = $this->DefPageSize;
		else
			$size = $this->_getpagesize($size);
		if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
		{
			if($orientation=='P')
			{
				$this->w = $size[0];
				$this->h = $size[1];
			}
			else
			{
				$this->w = $size[1];
				$this->h = $size[0];
			}
			$this->wPt = $this->w*$this->k;
			$this->hPt = $this->h*$this->k;
			$this->PageInfo[$this->page]['size'] = array($this->wPt,$this->hPt);
			$this->CurOrientation = $orientation;
			$this->CurPageSize = $size;
		}
		if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
			$this->PageInfo[$this->page]['size'] = array($this->wPt,$this->hPt);
		if($rotation!=0)
		{
			if($rotation%90!=0)
				$this->Error('Incorrect rotation value: '.$rotation);
			$this->CurRotation = $rotation;
			$this->PageInfo[$this->page]['rotation'] = $rotation;
		}
	}

	protected function _endpage()
	{
		$this->state = 1;
	}

	protected function _parsejpg($file)
	{
		$a = getimagesize($file);
		if(!$a)
			$this->Error('Missing or incorrect image file: '.$file);
		if($a[2]!=2)
			$this->Error('Not a JPEG file: '.$file);
		if(!isset($a['channels']) || $a['channels']==3)
			$colspace = 'DeviceRGB';
		elseif($a['channels']==4)
			$colspace = 'DeviceCMYK';
		else
			$colspace = 'DeviceGray';
		$bpc = isset($a['bits']) ? $a['bits'] : 8;
		$data = file_get_contents($file);
		return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
	}

	protected function _parsepng($file)
	{
		$f = fopen($file,'rb');
		if(!$f)
			$this->Error('Can\'t open image file: '.$file);
		$info = $this->_parsepngstream($f,$file);
		fclose($f);
		return $info;
	}

	protected function _parsepngstream($f, $file)
	{
		if(fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
			$this->Error('Not a PNG file: '.$file);

		fread($f,4);
		if(fread($f,4)!='IHDR')
			$this->Error('Incorrect PNG file: '.$file);
		$w = $this->_readint($f);
		$h = $this->_readint($f);
		$bpc = ord(fread($f,1));
		if($bpc>8)
			$this->Error('16-bit depth not supported: '.$file);
		$ct = ord(fread($f,1));
		if($ct==0 || $ct==4)
			$colspace = 'DeviceGray';
		elseif($ct==2 || $ct==6)
			$colspace = 'DeviceRGB';
		elseif($ct==3)
			$colspace = 'Indexed';
		else
			$this->Error('Unknown color type: '.$file);
		if(ord(fread($f,1))!=0)
			$this->Error('Unknown compression method: '.$file);
		if(ord(fread($f,1))!=0)
			$this->Error('Unknown filter method: '.$file);
		if(ord(fread($f,1))!=0)
			$this->Error('Interlacing not supported: '.$file);
		fread($f,4);
		$dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

		$pal = '';
		$trns = '';
		$data = '';
		do
		{
			$n = $this->_readint($f);
			$type = fread($f,4);
			if($type=='PLTE')
			{
				$pal = fread($f,$n);
				fread($f,4);
			}
			elseif($type=='tRNS')
			{
				$t = fread($f,$n);
				if($ct==0)
					$trns = array(ord(substr($t,1,1)));
				elseif($ct==2)
					$trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
				else
				{
					$pos = strpos($t,chr(0));
					if($pos!==false)
						$trns = array($pos);
				}
				fread($f,4);
			}
			elseif($type=='IDAT')
			{
				$data .= fread($f,$n);
				fread($f,4);
			}
			elseif($type=='IEND')
				break;
			else
			{
				fread($f,$n+4);
			}
		}
		while($n);

		if($colspace=='Indexed' && empty($pal))
			$this->Error('Missing palette in '.$file);
		$info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
		if($ct>=4)
		{
			if(!function_exists('gd_info'))
				$this->Error('GD extension is required for transparency');
			$info['data'] = $this->_pngalpha($data,$info);
		}
		else
			$info['data'] = $data;
		return $info;
	}

	protected function _readint($f)
	{
		$a = unpack('Ni',fread($f,4));
		return $a['i'];
	}

	protected function _pngalpha($data, $info)
	{
		$w = $info['w'];
		$h = $info['h'];
		if($info['cs']=='DeviceGray')
		{
			$ct = 0;
			$bpc = $info['bpc'];
			$colspace = 'DeviceGray';
		}
		else
		{
			$ct = 2;
			$bpc = 8;
			$colspace = 'DeviceRGB';
		}
		$color = '';
		$alpha = '';

		$data = gzuncompress($data);
		$color_data = '';
		$alpha_data = '';
		if($bpc==8)
		{
			$dp = $ct==0 ? 2 : 4;
			for($i=0;$i<$h;$i++)
			{
				$filter = ord($data[0]);
				$data = substr($data,1);
				$color_row = '';
				$alpha_row = '';
				for($j=0;$j<$w;$j++)
				{
					if($ct==0)
					{
						$gray = $data[0];
						$transparent = $data[1];
						$color_row .= $gray;
						$alpha_row .= $transparent;
					}
					else
					{
						$r = $data[0];
						$g = $data[1];
						$b = $data[2];
						$transparent = $data[3];
						$color_row .= $r.$g.$b;
						$alpha_row .= $transparent;
					}
					$data = substr($data,$dp);
				}
				$color_data .= chr($filter).$color_row;
				$alpha_data .= chr($filter).$alpha_row;
			}
		}
		$color = gzcompress($color_data);
		$alpha = gzcompress($alpha_data);
		$this->WithAlpha = true;
		$info['cs'] = $colspace;
		$info['bpc'] = $bpc;
		unset($info['dp']);
		$info['data'] = $color;
		$info['pal'] = '';
		$info['smask'] = $alpha;
		return $info;
	}

	protected function _out($s)
	{
		if($this->state==2)
			$this->pages[$this->page] .= $s."\n";
		else
			$this->buffer .= $s."\n";
	}

	protected function _putpages()
	{
		$nb = $this->page;
		for($n=1;$n<=$nb;$n++)
		{
			$this->PageInfo[$n]['n'] = $this->n;
			$this->_putstreamobject($this->pages[$n]);
			$this->_out('endstream');
			$this->_out('endobj');
		}
		for($n=1;$n<=$nb;$n++)
		{
			$this->_newobj();
			$this->_out('<</Type /Page');
			$this->_out('/Parent 1 0 R');
			if(isset($this->PageInfo[$n]['size']))
				$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
			if(isset($this->PageInfo[$n]['rotation']))
				$this->_out('/Rotate '.$this->PageInfo[$n]['rotation']);
			$this->_out('/Resources 2 0 R');
			if(isset($this->PageLinks[$n]))
			{
				$annots = '/Annots [';
				foreach($this->PageLinks[$n] as $pl)
				{
					$rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
					$annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
					if(is_string($pl[4]))
						$annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
					else
					{
						$l = $this->links[$pl[4]];
						if(isset($this->PageInfo[$l[0]]['size']))
							$h = $this->PageInfo[$l[0]]['size'][1];
						else
							$h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
						$annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',2*$l[0],($h-$l[1]*$this->k));
					}
				}
				$this->_out($annots.']');
			}
			$this->_out('/Contents '.($this->PageInfo[$n]['n']).' 0 R>>');
			$this->_out('endobj');
		}
		$this->_newobj();
		$this->_out('<</Type /Pages');
		$kids = '/Kids [';
		for($i=0;$i<$nb;$i++)
			$kids .= (3+2*$i).' 0 R ';
		$this->_out($kids.']');
		$this->_out('/Count '.$nb);
		if($this->DefOrientation=='P')
		{
			$w = $this->DefPageSize[0];
			$h = $this->DefPageSize[1];
		}
		else
		{
			$w = $this->DefPageSize[1];
			$h = $this->DefPageSize[0];
		}
		$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$w*$this->k,$h*$this->k));
		$this->_out('>>');
		$this->_out('endobj');
	}

	protected function _putimages()
	{
		foreach(array_keys($this->images) as $file)
		{
			$this->_putimage($this->images[$file]);
			unset($this->images[$file]['data']);
			unset($this->images[$file]['smask']);
		}
	}

	protected function _putimage(&$info)
	{
		$this->_newobj();
		$info['n'] = $this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_out('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		if(isset($info['f']))
			$this->_out('/Filter /'.$info['f']);
		if(isset($info['dp']))
			$this->_out('/DecodeParms <<'.$info['dp'].'>>');
		if(isset($info['trns']) && is_array($info['trns']))
		{
			$trns = '';
			for($i=0;$i<count($info['trns']);$i++)
				$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		if(isset($info['smask']))
			$this->_out('/SMask '.($this->n+1).' 0 R');
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstreamobject($info['data']);
		$this->_out('endstream');
		$this->_out('endobj');
		if(isset($info['smask']))
		{
			$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
			$smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>'FlateDecode', 'dp'=>$dp, 'data'=>$info['smask']);
			$this->_putimage($smask);
		}
		if($info['cs']=='Indexed')
		{
			$filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
			$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_newobj();
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstreamobject($pal);
			$this->_out('endstream');
			$this->_out('endobj');
		}
	}

	protected function _putresources()
	{
		$this->_putimages();
		$this->offsets[2] = strlen($this->buffer);
		$this->_out('2 0 obj');
		$this->_out('<<');
		$this->_putresourcedict();
		$this->_out('>>');
		$this->_out('endobj');
	}

	protected function _putresourcedict()
	{
		$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
		if(!empty($this->images))
		{
			$this->_out('/XObject <<');
			foreach($this->images as $image)
				$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
			$this->_out('>>');
		}
	}

	protected function _putinfo()
	{
		$this->metadata['Producer'] = 'FPDF QR Label Generator';
		$this->metadata['CreationDate'] = 'D:'.@date('YmdHis');
		foreach($this->metadata as $key=>$value)
			$this->_out('/'.$key.' '.$this->_textstring($value));
	}

	protected function _putcatalog()
	{
		$n = $this->PageInfo[1]['n'];
		$this->_out('/Type /Catalog');
		$this->_out('/Pages 1 0 R');
		if($this->ZoomMode=='fullpage')
			$this->_out('/OpenAction ['.($n).' 0 R /Fit]');
		elseif($this->ZoomMode=='fullwidth')
			$this->_out('/OpenAction ['.($n).' 0 R /FitH null]');
		elseif($this->ZoomMode=='real')
			$this->_out('/OpenAction ['.($n).' 0 R /XYZ null null 1]');
		elseif(!is_string($this->ZoomMode))
			$this->_out('/OpenAction ['.($n).' 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
		if($this->LayoutMode=='single')
			$this->_out('/PageLayout /SinglePage');
		elseif($this->LayoutMode=='continuous')
			$this->_out('/PageLayout /OneColumn');
		elseif($this->LayoutMode=='two')
			$this->_out('/PageLayout /TwoColumnLeft');
	}

	protected function _putheader()
	{
		$this->_out('%PDF-'.$this->PDFVersion);
	}

	protected function _puttrailer()
	{
		$this->_out('/Size '.($this->n+1));
		$this->_out('/Root '.$this->n.' 0 R');
		$this->_out('/Info '.($this->n-1).' 0 R');
	}

	protected function _enddoc()
	{
		$this->_putheader();
		$this->_putpages();
		$this->_putresources();
		$this->_newobj();
		$this->_out('<<');
		$this->_putinfo();
		$this->_out('>>');
		$this->_out('endobj');
		$this->_newobj();
		$this->_out('<<');
		$this->_putcatalog();
		$this->_out('>>');
		$this->_out('endobj');
		$o = strlen($this->buffer);
		$this->_out('xref');
		$this->_out('0 '.($this->n+1));
		$this->_out('0000000000 65535 f ');
		for($i=1;$i<=$this->n;$i++)
			$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
		$this->_out('trailer');
		$this->_out('<<');
		$this->_puttrailer();
		$this->_out('>>');
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state = 3;
	}

	function Close()
	{
		if($this->state==3)
			return;
		if($this->page==0)
			$this->AddPage();
		$this->InFooter = true;
		$this->InFooter = false;
		$this->_endpage();
		$this->_enddoc();
	}

	protected function _newobj()
	{
		$this->n++;
		$this->offsets[$this->n] = strlen($this->buffer);
		$this->_out($this->n.' 0 obj');
	}

	protected function _putstreamobject($data)
	{
		$this->_out('stream');
		$this->_out($data);
	}

	protected function _textstring($s)
	{
		$has_magic_quotes_runtime = function_exists('get_magic_quotes_runtime') ? get_magic_quotes_runtime() : false;
		if(!$has_magic_quotes_runtime)
		{
			$s = str_replace('\\','\\\\',$s);
		}
		$s = str_replace('(','\\(',$s);
		$s = str_replace(')','\\)',$s);
		$s = str_replace("\r",'\\r',$s);
		return '('.$s.')';
	}

	protected function _httpencode($param, $value, $isUTF8)
	{
		if($isUTF8)
			$value = rawurlencode($value);
		return $param.'="'.$value.'"';
	}

	protected function _checkoutput()
	{
		if(PHP_SAPI!='cli')
		{
			if(headers_sent($file,$line))
				$this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
		}
		if(ob_get_length())
		{
			if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
			{
				ob_end_clean();
			}
			else
				$this->Error("Some data has already been output, can't send PDF file");
		}
	}

	function Error($msg)
	{
		throw new Exception('FPDF error: '.$msg);
	}
}

}

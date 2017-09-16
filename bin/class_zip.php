<?php 
/* $Id: zip.lib.php,v 1.1 2004/02/14 15:21:18 anoncvs_tusedb Exp $ */ 
// vim: expandtab sw=4 ts=4 sts=4: 


/** 
* Zip file creation class. 
* Makes zip files. 
* 
* Last Modification and Extension By : 
* 
*  Hasin Hayder 
*  HomePage : www.hasinme.info 
*  Email : countdraculla@gmail.com 
*  IDE : PHP Designer 2005 
* 
* 
* Originally Based on : 
* 
*  http://www.zend.com/codex.php?id=535&single=1 
*  By Eric Mueller <eric@themepark.com> 
* 
*  http://www.zend.com/codex.php?id=470&single=1 
*  by Denis125 <webmaster@atlant.ru> 
* 
*  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified 
*  date and time of the compressed file 
* 
* Official ZIP file format: http://www.pkware.com/appnote.txt 
* 
* @access  public 
*/ 
class ZIP 
{ 
	
	var $filename		= '';
    /** 
     * Array to store compressed data 
     * 
     * @var  array    $datasec 
     */ 
    var $datasec      = array(); 

    /** 
     * Central directory 
     * 
     * @var  array    $ctrl_dir 
     */ 
    var $ctrl_dir     = array(); 

    /** 
     * End of central directory record 
     * 
     * @var  string   $eof_ctrl_dir 
     */ 
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; 

    /** 
     * Last offset position 
     * 
     * @var  integer  $old_offset 
     */ 
    var $old_offset   = 0; 


    /** 
     * Converts an Unix timestamp to a four byte DOS date and time format (date 
     * in high two bytes, time in low two bytes allowing magnitude comparison). 
     * 
     * @param  integer  the current Unix timestamp 
     * 
     * @return integer  the current date in a four byte DOS format 
     * 
     * @access private 
     */ 
     
    function ZIP($filename) {
    	$temp			= strtolower($filename);
    	if (substr($temp, -4) == '.zip')
    		$filename	= substr($filename, 0, -4);
    	$this->filename	= $filename;
    }
     
    function init() {
        @unlink($this->filename.'_zip.cache');
        @unlink($this->filename.'_zip.meta');
        @unlink($this->filename.'_zip.rec');
    }

    function unix2DosTime($unixtime = 0) { 
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime); 

        if ($timearray['year'] < 1980) { 
            $timearray['year']    = 1980; 
            $timearray['mon']     = 1; 
            $timearray['mday']    = 1; 
            $timearray['hours']   = 0; 
            $timearray['minutes'] = 0; 
            $timearray['seconds'] = 0; 
        } // end if 

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | 
                ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1); 
    } // end of the 'unix2DosTime()' method 


    function addFile($file) {
		ini_set('memory_limit', '32M');
    	// directory check
	    if (is_file($file)) { 
			$data = file_get_contents($file);
			$this->addContent($data,$file);
		}
    }

    /** 
     * Adds "file" to archive 
     * 
     * @param  string   file contents 
     * @param  string   name of the file in the archive (may contains the path) 
     * @param  integer  the current timestamp 
     * 
     * @access public 
     */ 
    function addContent($data, $name, $time = 0) { 
        $name     = str_replace('\\', '/', $name); 

        $dtime    = dechex($this->unix2DosTime($time)); 
        $hexdtime = '\x' . $dtime[6] . $dtime[7] 
                  . '\x' . $dtime[4] . $dtime[5] 
                  . '\x' . $dtime[2] . $dtime[3] 
                  . '\x' . $dtime[0] . $dtime[1]; 
        eval('$hexdtime = "' . $hexdtime . '";'); 

        $fr   = "\x50\x4b\x03\x04"; 
        $fr   .= "\x14\x00";            // ver needed to extract 
        $fr   .= "\x00\x00";            // gen purpose bit flag 
        $fr   .= "\x08\x00";            // compression method 
        $fr   .= $hexdtime;             // last mod time and date 

        // "local file header" segment 
        $unc_len = strlen($data); 
        $crc     = crc32($data); 
        $zdata   = gzcompress($data);
		unset($data);
        $zdata   = substr($zdata, 2, strlen($zdata) - 6); // fix crc bug 
//        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug 
        $c_len   = strlen($zdata); 

        $fr      .= pack('V', $crc);             // crc32 
        $fr      .= pack('V', $c_len);           // compressed filesize 
        $fr      .= pack('V', $unc_len);         // uncompressed filesize 
        $fr      .= pack('v', strlen($name));    // length of filename 
        $fr      .= pack('v', 0);                // extra field length 
        $fr      .= $name; 

        // "file data" segment 
        $fr .= $zdata; 

        // "data descriptor" segment (optional but necessary if archive is not 
        // served as file) 
        $fr .= pack('V', $crc);                 // crc32 
        $fr .= pack('V', $c_len);               // compressed filesize 
        $fr .= pack('V', $unc_len);             // uncompressed filesize 

        // add this entry to cache
        $fp		= fopen($this->filename.'_zip.cache', 'ab');
        fwrite($fp, $fr);
        fclose($fp);
        

        // now add to central directory record 
        $cdrec = "\x50\x4b\x01\x02"; 
        $cdrec .= "\x00\x00";                // version made by 
        $cdrec .= "\x14\x00";                // version needed to extract 
        $cdrec .= "\x00\x00";                // gen purpose bit flag 
        $cdrec .= "\x08\x00";                // compression method 
        $cdrec .= $hexdtime;                 // last mod time & date 
        $cdrec .= pack('V', $crc);           // crc32 
        $cdrec .= pack('V', $c_len);         // compressed filesize 
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize 
        $cdrec .= pack('v', strlen($name) ); // length of filename 
        $cdrec .= pack('v', 0 );             // extra field length 
        $cdrec .= pack('v', 0 );             // file comment length 
        $cdrec .= pack('v', 0 );             // disk number start 
        $cdrec .= pack('v', 0 );             // internal file attributes 
        $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set 

        $cdrec .= pack('V', filesize($this->filename.'_zip.cache') - strlen($fr)); // relative offset of local header 

        $cdrec .= $name; 

        // optional extra field, file comment goes here 
        // save to central directory 
        $fp		= fopen($this->filename.'_zip.meta', 'ab');
        fwrite($fp, $cdrec);
        fclose($fp);

        $fp		= fopen($this->filename.'_zip.rec', 'ab');
        fwrite($fp, '1');
        fclose($fp);

    } // end of the 'addFile()' method 


    function addFiles($files /*Only Pass Array*/) { 
        foreach ($files as $file) {
        	$this->addFile($file);
        }
    }
    

    function output() {
        $data_size		= filesize($this->filename.'_zip.cache');
        $ctrldir_size	= filesize($this->filename.'_zip.meta');
        $ctrldir_rec	= filesize($this->filename.'_zip.rec');

        echo file_get_contents($this->filename.'_zip.cache');
        echo file_get_contents($this->filename.'_zip.meta');
		echo 
            $this -> eof_ctrl_dir . 
            pack('v', $ctrldir_rec) .			// total # of entries "on this disk" 
            pack('v', $ctrldir_rec) .			// total # of entries overall 
            pack('V', $ctrldir_size) .			// size of central dir 
            pack('V', $data_size) .				// offset to start of central dir 
            "\x00\x00"							// .zip file comment length 
  				;

        unlink($this->filename.'_zip.cache');
        unlink($this->filename.'_zip.meta');
        unlink($this->filename.'_zip.rec');

    }
    
    function save() {
    	
    	if (!is_file($this->filename.'_zip.cache'))
    		return false;
    	
        $data_size		= filesize($this->filename.'_zip.cache');
        $ctrldir_size	= filesize($this->filename.'_zip.meta');
        $ctrldir_rec	= filesize($this->filename.'_zip.rec');

        $ctrldir		= file_get_contents($this->filename.'_zip.meta');

        $fp				= fopen($this->filename.'_zip.cache', 'ab');
        fwrite($fp, $ctrldir);
        fwrite($fp,
            $this -> eof_ctrl_dir . 
            pack('v', $ctrldir_rec) .			// total # of entries "on this disk" 
            pack('v', $ctrldir_rec) .			// total # of entries overall 
            pack('V', $ctrldir_size) .			// size of central dir 
            pack('V', $data_size) .				// offset to start of central dir 
            "\x00\x00"							// .zip file comment length 
        			);
        
        fclose($fp);
        
        @unlink($this->filename.'.zip');
        unlink($this->filename.'_zip.meta');
        unlink($this->filename.'_zip.rec');

        rename($this->filename.'_zip.cache', $this->filename.'.zip');

    }

    

}
?>
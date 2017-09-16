<?php


$file	= $_GET['file'];


$down	= new SD_DownLoad();
$down->Down($file);





class SD_DownLoad {
  
  /**
      * 下载的开始点
      * 
      * @access private
      * @var integer
      */
  private $mDownStart;
  
  /**
      * 文件大小
      * 
      * @access private
      * @var integer
      */
  private $mFileSize;
  
  /**
      * 文件句柄
      * 
      * @access private
      * @var integer
      */
  private $mFileHandle;
  
  /**
      * 文件全路径
      * 
      * @access private
      * @var string
      */
  private $mFilePath;
  
  /**
      * 文件下载时显示的文件名
      * 
      * @access private
      * @var string
      */
  private $mFileName;
  
  /**
      * 构造函数
      * 
      * @access public
      * @return void
      **/
  public function __construct() {
  }
  
  /**
      * 下载
      * 
      * @param string $pFilePath 文件全路径
      * @param string pFileName 文件下载时显示的文件名，缺省为实际文件名
      * @access public
      * @return void
      **/
  public function Down($pFilePath, $pFileName = '') {
      $this->mFilePath = $pFilePath;
      if(!$this->IniFile()) $this->SendError();
      $this->mFileName = empty($pFileName) ? $this->GetFileName() : $pFileName;
      
      $this->IniFile();
      $this->SetStart();
      $this->SetHeader();
      
      $this->Send();
  }

  
  /**
      * 初始化文件信息
      * 
      * @access private
      * @return boolean
      **/
  private function IniFile() {
      if(!is_file($this->mFilePath)) return false;
      $this->mFileHandle = fopen($this->mFilePath, 'rb');
      $this->mFileSize = filesize($this->mFilePath);
      return true;
  }
  
  /**
      * 设置下载开始点
      * 
      * @access private
      * @return void
      **/
  private function SetStart() {
      if (!empty($_SERVER['HTTP_RANGE']) && preg_match("/^bytes=([\d]?)-([\d]?)$/i", $_SERVER['HTTP_RANGE'], $match)) {
          if(empty($match[1])) $this->mDownStart = $match[1];
          fseek($this->mFileHandle, $this->mDownStart);
      }
      else {
          $this->mDownStart = 0;
      }
  }
  
  /**
      * 设置http头
      * 
      * @access private
      * @return void
      **/
  private function SetHeader() {
      @header("Cache-control: public");
      @header("Pragma: public");
      Header("Content-Length: " . ($this->mFileSize - $this->mDownStart));
      if ($this->mDownStart > 0) {
          @Header("HTTP/1.1 206 Partial Content");
          Header("Content-Ranges: bytes" . $this->mDownStart . "-" . ($this->mFileSize - 1) . "/" . $this->mFileSize);
      }
      else {
          Header("Accept-Ranges: bytes");
      }
      @header("Content-Type: application/octet-stream");
      @header("Content-Disposition: attachment;filename=" . $this->mFileName);
  }
  
  /**
      * 获取全路径里的文件名部分
      * 
      * @access private
      * @return string
      **/
  private function GetFileName() {
      return basename ($this->mFilePath);
  }
  
  /**
      * 发送数据
      * 
      * @access private
      * @return void
      **/
  private function Send() {
      fpassthru($this->mFileHandle);
  }
  
  /**
      * 发送错误
      * 
      * @access public
      * @return void
      **/
  public function SendError() {
      @header("HTTP/1.0 404 Not Found");
      @header("Status: 404 Not Found");
      exit();
  }
}
?> 
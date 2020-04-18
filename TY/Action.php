<?php
class GitStatic_Action extends Typecho_Widget implements Widget_Interface_Do
{
  private $_db;
  private $_options;
  public function action()
    { 
      $this->on($this->request->is('do=on'))->OnImgHandle("on"); 
      $this->on($this->request->is('do=off'))->OnImgHandle("off"); 
    }
    public function OnImgHandle($_img){
        $this->init();
        if(!$this->widget('Widget_User')->pass('administrator'));
        {
          $this->widget('Widget_Notice')->set("无权限", 'fail' ); 
        }
        $result = $this->_db->fetchAll($this->_db->select('value')->from('table.options')->where('name = ?', "plugin:GitStatic"));        
        if(!isset($result[0]["value"])) $this->widget('Widget_Notice')->set("数据库错误", 'fail' ) ;
        $array_options=unserialize($result[0]["value"]);
        if($_img == "on")Typecho_Plugin::factory('Widget_Upload')->uploadHandle = array('GitStatic_Plugin', 'uploadHandle');
        $array_options["ImgHandle"]=$_img;
        $this->_db->query($this->_db->update('table.options')->rows(array('value'=>serialize($array_options)))->where('name = ?',"plugin:GitStatic"));
           $this->response->goBack();
      }
      /**
      * 初始化
      * @return $this
      */
      public function init()
        {
          $this->_db = Typecho_Db::get();
          $this->_options = Helper::options()->plugin('GitStatic');
        }
      }
<?
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
* 没有想念过什么,不曾期待着什么
*
* @package GitStatic
* @author 乔千
* @version 2.0.0
* @link https://blog.mumuli.cn
*/
class GitStatic_Plugin implements Typecho_Plugin_Interface
{
  public static function activate()
    {
      Helper::addAction("GitImg", 'GitStatic_Action');
      Helper::addPanel(1, "GitStatic/console.php", '图片地址优化', '图片地址简化设置', 'administrator');
      //Typecho_Plugin::factory('Widget_Upload')->modifyHandle = array('GitStatic_Plugin', 'modifyHandle');
      // Typecho_Plugin::factory('Widget_Upload')->deleteHandle = array('GitStatic_Plugin', 'deleteHandle');
      Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = array('GitStatic_Plugin', 'attachmentHandle');
      Typecho_Plugin::factory('Widget_Upload')->uploadHandle = array('GitStatic_Plugin', 'uploadHandle');

      //Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = array('GitStatic_Plugin', 'attachmentDataHandle'); 
      return _t("启用成功啦！快先设置下吧。");
    }
    public static function deactivate()
      {
        Helper::removeAction("GitImg");
        Helper::removePanel(1, "GitStatic/console.php");

        return _t("关闭啦，不能享受加速了唉");
      }
      public static function personalConfig(Typecho_Widget_Helper_Form $form)
        {

        }
        public static function config(Typecho_Widget_Helper_Form $form)
          {
            $t = new Typecho_Widget_Helper_Form_Element_Text('text_a', NULL, '', _t('插件功能介绍：'),
            _t('<ol>
 <li>插件是一款基于jsdelivr开发的静态资源加速插件</li>
            <li>需要仔细阅读文档配置插件否则不能运行</li>
            <li>操作规范:删除修改必备份 出现问题看文档</li>
            </ol>'));
 $form->addInput($t);
            $t = new Typecho_Widget_Helper_Form_Element_Text('ImgHandle', NULL, 'off', _t(''), 
            "");
            $form->addInput($t);

            $t = new Typecho_Widget_Helper_Form_Element_Text('text_b', NULL, '', _t('插件使用说明：'),
            _t('<ol>
 <li><a href="https://jq.qq.com/?_wv=1027&k=5pK3hCm">加入官方内测群，和作者py吧</a></li>
            <li><a href="https://blog.mumuli.cn/379.html">官方的各种姿势的教程</a></li>
            <li><a href="https://blog.mumuli.cn">非官方的各种魔改插件指南</a></li>
            </ol>'));
 $form->addInput($t);

            $t = new Typecho_Widget_Helper_Form_Element_Text('serverurl',
            null, null,
            _t('源加速服务器:'),
            _t('填写服务器地址,没有请移步教程搭建喵'));
            $form->addInput($t->addRule('required', _t('源不能为空喵')));
            echo '<script>
 window.onload = function() 
              { 
                document.getElementsByName("ImgHandle")[0].type = "hidden"; 
                document.getElementsByName("text_a")[0].type = "hidden";
                document.getElementsByName("text_b")[0].type = "hidden";
              }
              </script>';
 } 
            public static function attachmentHandle(array $content)
              { 
                $options = Typecho_Widget::widget('Widget_Options')->plugin('GitStatic'); 
                $_path=(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : Widget_Upload::UPLOAD_DIR);
                if($options->ImgHandle =="on"){
                  $result=substr(strrchr($content['attachment']->path, $_path), 1);
                }else{
                  $result=$content['attachment']->path;
                }
                return Typecho_Common::url($result,$options->serverurl);
              }
              /**
              * 上传文件处理函数,如果需要实现自己的文件哈希或者特殊的文件系统,请在options表里把uploadHandle改成自己的函数
              *
              * @access public
              * @param array $file 上传的文件
              * @return mixed
              */
              public static function uploadHandle($file)
                {
                  if (empty($file['name'])) {
                    return false;
                  }

                  $ext = self::getSafeName($file['name']);

                  if (!self::checkFileType($ext) || Typecho_Common::isAppEngine()) {
                    return false;
                  }
                  $path = Typecho_Common::url(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ :Widget_Upload::UPLOAD_DIR,
                  defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__);

                  //创建上传目录
                  if (!is_dir($path)) {
                    if (!self::makeUploadDir($path)) {
                      return false;
                    }
                  }
                  $fileName = time(). '.' . $ext;
                  $path = $path . '/' . $fileName;
                  //获取文件名
                  if (isset($file['tmp_name'])) { 
                    //移动上传文件
                    if (!@move_uploaded_file($file['tmp_name'], $path)) {
                      return false;
                    }
                  } else if (isset($file['bytes'])) {
                    //直接写入文件
                    $nabo_is=self::isNabo($_SERVER["HTTP_USER_AGENT"],$nabo_reg);
                    if (!file_put_contents($path, $nabo_is?base64_decode($file['bytes']):$file['bytes'])) {
                      return false;
                    }
                  } else {
                    return false;
                  }


                  if (!isset($file['size'])) {
                    $file['size'] = filesize($path);
                  }

                  //返回相对存储路径
                  return array(
                  'name' => $file['name'],
                  'path' => (defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : Widget_Upload::UPLOAD_DIR) 
                  . '/' . $fileName,
                  'size' => $file['size'],
                  'type' => $ext,
                  'mime' => Typecho_Common::mimeContentType($path)
                  );
                }
                /**
                * 获取安全的文件名 
                * 
                * @param string $name 
                * @static
                * @access private
                * @return string
                */
                private static function getSafeName(&$name)
                  {
                    $name = str_replace(array('"', '<', '>'), '', $name);
                    $name = str_replace('\\', '/', $name);
                    $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
                    $info = pathinfo($name);
                    $name = substr($info['basename'], 1);

                    return isset($info['extension']) ? strtolower($info['extension']) : '';
                  }
                  /**
                  * 检查文件名
                  *
                  * @access private
                  * @param string $ext 扩展名
                  * @return boolean
                  */
                  public static function checkFileType($ext)
                    {
                      $options = Typecho_Widget::widget('Widget_Options');
                      return in_array($ext, $options->allowedAttachmentTypes);
                    }
                    public static function isNabo($agent,&$regs)
                      { 
                        return preg_match('/Kraitnabo\/([^\s|;]+)/i', $agent, $regs); 
                      }
                      private static function makeUploadDir($path)
                        {
                          $path = preg_replace("/\\\+/", '/', $path);
                          $current = rtrim($path, '/');
                          $last = $current;

                          while (!is_dir($current) && false !== strpos($path, '/')) {
                            $last = $current;
                            $current = dirname($current);
                          }

                          if ($last == $current) {
                            return true;
                          }

                          if (!@mkdir($last)) {
                            return false;
                          }

                          $stat = @stat($last);
                          $perms = $stat['mode'] & 0007777;
                          @chmod($last, $perms);

                          return self::makeUploadDir($path);
                        }
                      }
<?php
function remote_file_exists($url) { 
    $executeTime = ini_get('max_execution_time'); 
    ini_set('max_execution_time', 0); 
    $headers = @get_headers($url); 
    ini_set('max_execution_time', $executeTime); 
    if ($headers) { 
      $head = explode(' ', $headers[0]); 
      if ( !empty($head[1]) && intval($head[1]) < 400) return true; 
    } 
    return false; 
  }
  function api_push($username,$token,$curl_url,$data,$method){
      $curl_token_auth = 'Authorization: token ' . $token;
      $ch = curl_init($curl_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'User-Agent: $username', $curl_token_auth ));
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
      $response = curl_exec($ch); 
      curl_close($ch);
      return $response;
    }
    function api_get($url)
      {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url); //设置访问的url地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'User-Agent: GitStatic'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//不输出内容
        $result = curl_exec($ch);
        curl_close ($ch);
        return $result;
      }

      function user_info($username) {return json_decode(api_get("https://api.github.com/users/".$username)); }
        //不存在会返回一个message Not Found 获取用户基本信息

        function repos_all($username) {return json_decode(api_get("https://api.github.com/users/".$username."/repos")); }
          //获取所有repos

          function repos_info($username,$reposname) {return json_decode(api_get("https://api.github.com/repos/".$username."/".$reposname)); }
            //获取repos info

            function repos_path($username,$reposname,$path) {return json_decode(api_get("https://api.github.com/repos/".$username."/".$reposname."/contents/".$path)); }
              //获取repos 目录内容

              function files_upload($username,$token,$repos,$path,$files,&$ret)
                {
                  $data=array("message"=>"upload by GitStatic","content"=>$files);
                  // echo "https://api.github.com/repos/".$username."/".$repos."/contents".$path;
                  $ret=api_push($username,$token,"https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),"PUT");
                  $json=(array)json_decode($ret);
                  // var_dump($json);
                  $ret=$
                  return !isset($json["message"]);
                  //上传需要判断失败或者成功
                } 
                function files_updata($username,$token,$repos,$path,$files,$sha,&$ret)
                  {
                    $data=array("message"=>"updata by GitStatic","content"=>$files,"sha"=>$sha);
                    $ret=api_push($username,$token,"https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),"PUT");
                    $json=(array)json_decode($ret);
                    //更新需要判断失败或者成功
                    // var_dump($data);
                    return !isset($json["message"]);
                  }
                  function files_del($username,$token,$repos,$path,$sha)
                    {
                      $data=array("message"=>"upload a new file","sha"=>$sha);
                      $json=(array)json_decode(api_push($username,$token, "https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),"DELETE"));
                      //var_dump($json);
                      return !isset($json["message"]);
                      //删除需要判断失败或者成功
                    }
                    function get_sha($username,$repos,$path){
                        $json=(array)repos_path($username,$repos,$path);
                        return $json["sha"];
                      }
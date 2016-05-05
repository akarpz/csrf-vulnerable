<?php
    //error_reporting(E_ALL);
    session_start();
    $topic = $_GET["message"];
    function sanitize_html_string($string){
        $pattern[0] = '/\&/';
        $pattern[1] = '/</';
        $pattern[2] = "/>/";
        $pattern[3] = '/\n/';
        $pattern[4] = '/"/';
        $pattern[5] = "/'/";
        $pattern[6] = "/%/";
        $pattern[7] = '/\(/';
        $pattern[8] = '/\)/';
        $pattern[9] = '/\+/';
        $pattern[10] = '/-/';
        $replacement[0] = '&amp;';
        $replacement[1] = '&lt;';
        $replacement[2] = '&gt;';
        $replacement[3] = '<br>';
        $replacement[4] = '&quot;';
        $replacement[5] = '&#39;';
        $replacement[6] = '&#37;';
        $replacement[7] = '&#40;';
        $replacement[8] = '&#41;';
        $replacement[9] = '&#43;';
        $replacement[10] = '&#45;';
        return preg_replace($pattern, $replacement, $string);
  }

  function add_comment($newComment){
    global $topic;
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    if($newComment=="") die("comment cannot be blank!");
    $prestatement = $dbhandle->prepare("select id from messages WHERE message=:message");
    $prestatement->bindParam(":message",$topic);
    $prestatement->execute();
    $id=$prestatement->fetchAll(PDO::FETCH_ASSOC);
    $id=$id[0]["id"];
    $statement = $dbhandle->prepare("insert into comments ('username','comment','topicID') values (:username,:message,:topicID)");
    $statement->bindParam(":username", $_SESSION["username"]);
    $statement->bindParam(":message", $newComment);
    $statement->bindParam(":topicID", $id);
    $statement->execute();
  };
  
  function check_rank($role){
    if($role=="user"){
      return 0;
    }
    if($role=="author"){
      return 1;
    }
    if($role=="moderator"){
      return 2;
    }
    if($role=="admin"){
      return 3;
    }
  }
  
    function render_topic(){
    global $topic;
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $prestatement = $dbhandle->prepare("select id from messages WHERE message=:message");
    $prestatement->bindParam(":message",$topic);
    $prestatement->execute();
    $id=$prestatement->fetchAll(PDO::FETCH_ASSOC);
    $id=$id[0]["id"];
    $statement = $dbhandle->prepare("select username, comment from comments WHERE topicID=:id2 order by id ASC limit 0, 100");
    $statement->bindParam(":id2",$id);
    $statement->execute();
    $messages = $statement->fetchAll(PDO::FETCH_ASSOC);
    $template = file_get_contents("topic.html");
    $message_template = file_get_contents("comment.html");
    $message_rows = "";
    foreach($messages as $message){
      $message_rows .= str_replace("USERNAME", sanitize_html_string($message["username"]), 
                            str_replace("MESSAGEHERE", $message["comment"], $message_template));
                            
    }
    
       echo str_replace("MESSAGESHERE", $message_rows, 
        str_replace("MYUSERNAME",sanitize_html_string($_SESSION["username"]), str_replace("POSTLISTINGHERE", $topic ,$template)));
        echo "You are an " . $_SESSION["role"];
  };
    
    
    if(isset($_SESSION["logged_in"])){
        if($_SESSION["logged_in"]==1){
            if (isset($_REQUEST["comment"])){
              add_comment($_REQUEST["comment"]);
              render_topic();
          } else {
            render_topic();
          }
        }
    }
    
    
?>
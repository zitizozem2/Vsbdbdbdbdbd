<?php
 
    // Bot token
    const TOKEN = '7285951498:AAFLIxGKudTbK-B3DD4NCN2Q0q--wTy_GHA';
     
    // Chat ID 
    const CHATID = '6245535196';
     
    // An array of valid file type values.
    $types = array('image/gif', 'image/png', 'image/jpeg', 'application/pdf');
     
    // Maximum file size in kilobytes
    // 1048576; // 1 ÐœÐ‘
    $size = 1073741824; // 1 Ð“Ð‘
 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
     
      $fileSendStatus = '';
      $textSendStatus = '';
      $msgs = [];
       
      // Check if the fields with the name and phone number are empty
      if (!empty($_POST['name']) && !empty($_POST['phone'])) {
         
        // If not empty, then validate these fields and save and add to the body of the message. The minimum for the test is as follows:
        $txt = "";
         
        // Name
        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $txt .= "Name: " . strip_tags(trim(urlencode($_POST['name']))) . "%0A";
        }
        // Phone number
        if (isset($_POST['phone']) && !empty($_POST['phone'])) {
            $txt .= "Phone: " . strip_tags(trim(urlencode($_POST['phone']))) . "%0A";
        }
         
        // Don't forget the subject line
        if (isset($_POST['theme']) && !empty($_POST['theme'])) {
            $txt .= "Themes: " . strip_tags(urlencode($_POST['theme']));
        }
     
        $textSendStatus = @file_get_contents('https://api.telegram.org/bot'. TOKEN .'/sendMessage?chat_id=' . CHATID . '&parse_mode=html&text=' . $txt); 
     
        if( isset(json_decode($textSendStatus)->{'ok'}) && json_decode($textSendStatus)->{'ok'} ) {
          if (!empty($_FILES['files']['tmp_name'])) {
         
              $urlFile =  "https://api.telegram.org/bot" . TOKEN . "/sendMediaGroup";
               
              // File upload path
              $path = $_SERVER['DOCUMENT_ROOT'] . '/telegram/tmp/';
               
              // Loading a file and displaying a message
              $mediaData = [];
              $postContent = [
                'chat_id' => CHATID,
              ];
              
              for ($ct = 0; $ct < count($_FILES['files']['tmp_name']); $ct++) {
                if ($_FILES['files']['name'][$ct] && @copy($_FILES['files']['tmp_name'][$ct], $path . $_FILES['files']['name'][$ct])) {
                  if ($_FILES['files']['size'][$ct] < $size && in_array($_FILES['files']['type'][$ct], $types)) {
                    $filePath = $path . $_FILES['files']['name'][$ct];
                    $postContent[$_FILES['files']['name'][$ct]] = new CURLFile(realpath($filePath));
                    $mediaData[] = ['type' => 'document', 'media' => 'attach://'. $_FILES['files']['name'][$ct]];
                  }
                }
              }
           
              $postContent['media'] = json_encode($mediaData);
           
              $curl = curl_init();
              curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
              curl_setopt($curl, CURLOPT_URL, $urlFile);
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($curl, CURLOPT_POSTFIELDS, $postContent);
              $fileSendStatus = curl_exec($curl);
              curl_close($curl);
              $files = glob($path.'*');
              foreach($files as $file){
                if(is_file($file))
                  unlink($file);
              }
          }
         echo json_encode('SUCCESS');
        } else {
          echo json_encode('ERROR');
          // 
          // echo json_decode($textSendStatus);
        }
      } else {
        echo json_encode('NOTVALID');
      }
    } else {
      header("Location: /");
    }
